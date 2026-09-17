<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\NhomDoAn;
use App\Models\SinhVien;
use App\Models\AuditLog;
use App\Services\FileUploadService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BaoCaoController extends Controller
{
    public function index(Request $request)
    {
        $sv = SinhVien::where('MaTK', (string) Auth::user()->_id)->first();
        if (!$sv) abort(403);

        $svKeys = array_values(array_unique(array_filter([
            (string) $sv->_id,
            (string) $sv->MaSV,
            strtoupper((string) $sv->MaSV),
            strtolower((string) $sv->MaSV)
        ])));

        $nhoms = NhomDoAn::where(function($q) use ($svKeys) {
            $q->whereIn('TruongNhom', $svKeys)
              ->orWhereIn('ThanhVien.MaSV', $svKeys);
        })->get();

        if ($nhoms->isEmpty()) {
            return redirect()->route('sinhvien.nhom.index')->withErrors('Bạn chưa tham gia nhóm nào.');
        }

        $selectedNhomId = $request->get('maNhom', null);

        $nhom = null;
        if ($selectedNhomId) {
            $nhom = $nhoms->first(function($n) use ($selectedNhomId) {
                return (string)$n->_id === (string)$selectedNhomId || (string)$n->MaNhom === (string)$selectedNhomId;
            });
        }

        // Nếu nhóm được chọn không có đề tài được duyệt (hoặc không truyền maNhom), tự động chọn nhóm đã có đề tài được duyệt
        if (!$nhom || !$nhom->getDangKyDeTai() || !in_array(($nhom->getDangKyDeTai()['TrangThai'] ?? ''), ['Đã duyệt', 'Đã duyệt đề tài'])) {
            $approvedGroup = $nhoms->first(function($n) {
                $dk = $n->getDangKyDeTai();
                return $dk && in_array($dk['TrangThai'] ?? '', ['Đã duyệt', 'Đã duyệt đề tài']);
            });
            if ($approvedGroup) {
                $nhom = $approvedGroup;
            }
        }

        if (!$nhom) {
            $nhom = $nhoms->first();
        }

        $nhom->load(['monHoc', 'hocKy']);

        // Lấy thông tin đề tài đã đăng ký
        $dk = $nhom->getDangKyDeTai();
        $deTai = null;
        if ($dk && !empty($dk['MaDeTai'])) {
            $deTai = \App\Models\DeTai::find($dk['MaDeTai']);
        }
        $nhom->setAttribute('deTaiDangKy', $deTai);

        $allNhoms = $nhoms;
        $baocaos = $nhom->getBaoCaoList();

        return view('sinhvien.baocao.index', compact('nhom', 'allNhoms', 'baocaos', 'sv'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'NoiDung' => 'required|string',
            'FileUpLoad' => 'nullable|file|max:20480',
            'FileBaoCao' => 'nullable|string',
            'MaNhom' => 'nullable|string'
        ], [
            'NoiDung.required' => 'Vui lòng nhập tóm tắt nội dung báo cáo tiến độ.'
        ]);

        $sv = SinhVien::where('MaTK', (string) Auth::user()->_id)->first();

        $maNhom = $request->input('MaNhom');
        if (!$maNhom) {
            $nhom = NhomDoAn::where('ThanhVien.MaSV', (string) $sv->_id)->first();
            $maNhom = $nhom ? (string) $nhom->_id : null;
        }

        if (!$maNhom) {
            return redirect()->back()->withErrors('Bạn chưa tham gia nhóm nào!');
        }

        $nhom = NhomDoAn::findOrFail($maNhom);

        // Kiểm tra sinh viên có thuộc nhóm này không
        if (!$nhom->hasThanhVien($sv->_id) && !$nhom->isTruongNhom($sv)) {
            return redirect()->back()->withErrors('Bạn không thuộc nhóm này!');
        }

        if (!$nhom->isTruongNhom($sv)) {
            return redirect()->back()->withErrors('Chỉ trưởng nhóm mới được phép đại diện nộp báo cáo tiến độ!');
        }

        // 1. Kiểm tra đề tài được duyệt & hạn nộp báo cáo
        $dangKy = $nhom->getDangKyDeTai();
        $trangThaiDangKy = is_array($dangKy) ? ($dangKy['TrangThai'] ?? '') : ($nhom->DangKyDeTai->TrangThai ?? '');
        if (!in_array($trangThaiDangKy, ['Đã duyệt', 'Đã duyệt đề tài'])) {
            return redirect()->back()->withErrors('Nhóm chưa có đề tài được duyệt! Vui lòng đăng ký và chờ duyệt đề tài trước khi nộp báo cáo.');
        }

        $deTai = $nhom->deTaiDangKy ?? ($nhom->DangKyDeTai->deTai ?? $nhom->getDeTaiDangKy());
        if ($deTai && $deTai->HanBaoCao && date('Y-m-d') > $deTai->HanBaoCao) {
            return redirect()->back()->withErrors('Đã quá hạn nộp báo cáo tiến độ! (Hạn chót: ' . date('d/m/Y', strtotime($deTai->HanBaoCao)) . ')');
        }

        // 2. Upload file / link
        $fileService = new FileUploadService();
        $fileOrLink = $fileService->handleUploadOrLink($request, 'FileUpLoad', 'FileBaoCao', 'baocao');

        if (!$fileOrLink) {
            return redirect()->back()->withErrors('Vui lòng tải lên file báo cáo hoặc điền đường dẫn liên kết sản phẩm!');
        }

        // 3. Tính số lần báo cáo (hoặc lấy từ form)
        $baoCaoList = $nhom->getBaoCaoList();
        $lanBaoCaoInput = $request->input('LanBaoCao');
        if ($lanBaoCaoInput) {
            $lanBaoCao = (int) $lanBaoCaoInput;
        } else {
            $lanCuoi = $baoCaoList->max('LanBaoCao') ?? 0;
            $lanBaoCao = $lanCuoi + 1;
        }

        if ($lanBaoCao > 5) {
            return redirect()->back()->withErrors('Nhóm đã đạt giới hạn tối đa 5 lần nộp báo cáo tiến độ!');
        }

        // 4. Kiểm tra trùng lần báo cáo
        if ($baoCaoList->contains('LanBaoCao', $lanBaoCao)) {
            return redirect()->back()->withErrors("Báo cáo lần {$lanBaoCao} đã tồn tại trong hệ thống!");
        }

        $bcId = $nhom->addBaoCao($lanBaoCao, $request->NoiDung, $fileOrLink);

        // Gửi thông báo cho Giảng viên hướng dẫn
        if ($deTai && $deTai->MaTK) {
            $notiService = new NotificationService();
            // Create a simple object to pass to notification
            $bcObj = (object) ['LanBaoCao' => $lanBaoCao];
            $notiService->guiBaoCaoMoiChoGV($nhom, $deTai->MaTK, $bcObj);
        }

        AuditLog::log('nop_bao_cao', 'BaoCaoTienDo', $bcId, ['MaNhom' => (string) $nhom->_id, 'LanBaoCao' => $lanBaoCao]);

        return redirect()->route('sinhvien.baocao.index', ['maNhom' => (string) $nhom->_id])->with('success', "Nộp báo cáo tiến độ lần {$lanBaoCao} thành công!");
    }
}
