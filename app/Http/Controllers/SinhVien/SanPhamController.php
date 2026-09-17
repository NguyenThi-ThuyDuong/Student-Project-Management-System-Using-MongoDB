<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\NhomDoAn;
use App\Models\SinhVien;
use App\Models\DeTai;
use App\Models\AuditLog;
use App\Services\FileUploadService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SanPhamController extends Controller
{
    private function getStudentKeys($sv)
    {
        if (!$sv) return [];
        return array_values(array_unique(array_filter([
            (string) $sv->_id,
            (string) $sv->MaSV,
            strtoupper((string) $sv->MaSV),
            strtolower((string) $sv->MaSV),
        ])));
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $sv = SinhVien::where('MaTK', (string) $user->_id)->first();
        if (!$sv) abort(403, 'Không tìm thấy thông tin sinh viên');

        $svKeys = $this->getStudentKeys($sv);

        $nhoms = NhomDoAn::where(function($q) use ($svKeys) {
            $q->whereIn('ThanhVien.MaSV', $svKeys)
              ->orWhereIn('TruongNhom', $svKeys);
        })->get();

        if ($nhoms->isEmpty()) {
            return redirect()->route('sinhvien.nhom.index')->withErrors('Bạn chưa tham gia nhóm nào.');
        }

        $selectedNhomId = $request->get('maNhom', (string) $nhoms->first()->_id);

        $nhom = $nhoms->first(fn($n) => (string) $n->_id === (string) $selectedNhomId) ?? $nhoms->first();
        $nhom->load(['monHoc', 'hocKy']);

        $allNhoms = $nhoms;
        $sanphams = $nhom->getSanPhamList();

        return view('sinhvien.sanpham.index', compact('nhom', 'allNhoms', 'sanphams', 'sv'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenSanPham' => 'required|string|max:200',
            'FileUpLoad' => 'nullable|file|max:20480',
            'LinkFile' => 'nullable|url',
            'MaNhom' => 'nullable|string'
        ], [
            'TenSanPham.required' => 'Vui lòng nhập tên sản phẩm / source code.',
            'FileUpLoad.file' => 'Tệp đính kèm không hợp lệ.',
            'LinkFile.url' => 'Liên kết GitHub / Drive không đúng định dạng URL.'
        ]);

        if (!$request->hasFile('FileUpLoad') && !$request->filled('LinkFile')) {
            return redirect()->back()->withErrors('Vui lòng tải lên file đính kèm HOẶC dán liên kết GitHub/Drive sản phẩm!');
        }

        $user = Auth::user();
        $sv = SinhVien::where('MaTK', (string) $user->_id)->first();
        if (!$sv) abort(403);

        $svKeys = $this->getStudentKeys($sv);

        $maNhom = $request->input('MaNhom');
        if (!$maNhom) {
            $nhom = NhomDoAn::where(function($q) use ($svKeys) {
                $q->whereIn('ThanhVien.MaSV', $svKeys)
                  ->orWhereIn('TruongNhom', $svKeys);
            })->first();
            $maNhom = $nhom ? (string) $nhom->_id : null;
        }

        if (!$maNhom) {
            return redirect()->back()->withErrors('Bạn chưa tham gia nhóm nào!');
        }

        $nhom = NhomDoAn::find($maNhom);
        if (!$nhom) {
            return redirect()->back()->withErrors('Không tìm thấy nhóm đồ án!');
        }

        $isLeader = $nhom->isTruongNhom($sv);
        $isMember = $nhom->hasThanhVien($sv) || in_array((string)$nhom->TruongNhom, $svKeys);

        if (!$isLeader && !$isMember) {
            return redirect()->back()->withErrors('Bạn không thuộc nhóm này!');
        }

        // 1. Kiểm tra đề tài đã duyệt chưa
        $dangKy = $nhom->getDangKyDeTai();
        $trangThaiDK = is_array($dangKy) ? ($dangKy['TrangThai'] ?? '') : ($nhom->DangKyDeTai->TrangThai ?? '');

        if ($trangThaiDK !== 'Đã duyệt' && $nhom->TrangThai !== 'Đã duyệt đề tài' && $nhom->TrangThai !== 'Đã nộp sản phẩm' && $nhom->TrangThai !== 'Đã chấm điểm') {
            return redirect()->back()->withErrors('Chưa thể nộp sản phẩm! Đề tài của nhóm cần được Giảng viên phê duyệt trước.');
        }

        // 2. Kiểm tra hạn nộp sản phẩm
        $deTai = $nhom->getDeTaiDangKy();
        if ($deTai && $deTai->HanNopSanPham && date('Y-m-d') > $deTai->HanNopSanPham) {
            return redirect()->back()->withErrors('Đã quá hạn nộp sản phẩm cuối kỳ! (Hạn chót: ' . date('d/m/Y', strtotime($deTai->HanNopSanPham)) . ')');
        }

        $filePath = null;
        if ($request->hasFile('FileUpLoad')) {
            $fileService = new FileUploadService();
            $filePath = $fileService->handleUploadOrLink($request, 'FileUpLoad', null, 'sanpham');
        }

        $gitUrl = $request->input('LinkFile');

        $spId = $nhom->addSanPham($request->TenSanPham, $filePath, $gitUrl);

        $nhom->update(['TrangThai' => 'Đã nộp sản phẩm']);

        // Gửi thông báo cho Giảng viên hướng dẫn
        $gvHD = $nhom->getGiangVienHuongDan();
        if ($gvHD && $gvHD->MaTK) {
            $notiService = new NotificationService();
            $spObj = (object) ['TenSanPham' => $request->TenSanPham];
            $notiService->guiSanPhamMoiChoGV($nhom, $gvHD->MaTK, $spObj);
        }

        AuditLog::log('nop_san_pham', 'SanPham', $spId, ['MaNhom' => (string) $nhom->_id, 'TenSanPham' => $request->TenSanPham]);

        return redirect()->route('sinhvien.sanpham.index', ['maNhom' => (string) $nhom->_id])->with('success', 'Nộp sản phẩm đồ án thành công!');
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $sv = SinhVien::where('MaTK', (string) $user->_id)->first();
        if (!$sv) abort(403);

        $svKeys = $this->getStudentKeys($sv);

        // Tìm nhóm chứa sản phẩm này
        $nhom = NhomDoAn::where('SanPham._id', $id)->first();
        if (!$nhom) {
            return redirect()->back()->withErrors('Không tìm thấy sản phẩm!');
        }

        $isLeader = $nhom->isTruongNhom($sv);
        $isMember = $nhom->hasThanhVien($sv) || in_array((string)$nhom->TruongNhom, $svKeys);

        if (!$isLeader && !$isMember) {
            return redirect()->back()->withErrors('Bạn không có quyền cập nhật sản phẩm này!');
        }

        $deTai = $nhom->getDeTaiDangKy();
        if ($deTai && $deTai->HanNopSanPham && date('Y-m-d') > $deTai->HanNopSanPham) {
            return redirect()->back()->withErrors('Đã hết hạn nộp sản phẩm, không thể cập nhật!');
        }

        $filePath = null;
        if ($request->hasFile('FileUpLoad')) {
            $fileService = new FileUploadService();
            $filePath = $fileService->handleUploadOrLink($request, 'FileUpLoad', null, 'sanpham');
        }

        $dataUpdate = ['TenSanPham' => $request->TenSanPham ?? 'Sản phẩm đồ án'];
        if ($filePath) {
            $dataUpdate['LinkFile'] = $filePath;
        }
        if ($request->filled('LinkFile')) {
            $dataUpdate['LinkSourceCode'] = $request->input('LinkFile');
        }

        $nhom->updateSanPham($id, $dataUpdate);

        AuditLog::log('cap_nhat_san_pham', 'SanPham', $id, ['MaNhom' => (string) $nhom->_id]);

        return redirect()->back()->with('success', 'Cập nhật sản phẩm thành công!');
    }
}
