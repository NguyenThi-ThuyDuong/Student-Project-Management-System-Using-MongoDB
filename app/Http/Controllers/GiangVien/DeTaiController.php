<?php
namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Http\Traits\HandlesExcelImport;
use App\Models\DeTai;
use App\Models\MonHoc;
use App\Models\HocKy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DeTaiController extends Controller
{
    use HandlesExcelImport;

    public function index(Request $request) {
        $user = Auth::user();
        $maTK = (string) $user->_id;
        $gv = \App\Models\GiangVien::where('MaTK', $maTK)->orWhere('_id', $maTK)->first();
        $gvIds = array_filter([(string)$user->_id, $gv ? (string)$gv->_id : null, $gv ? (string)$gv->MaGV : null]);
        
        $lopHocPhanQuery = \App\Models\LopHocPhan::with(['monHoc', 'hocKy']);
        if ($user->VaiTro !== 'Admin') {
            $lopHocPhanQuery->whereIn('MaGV', $gvIds);
        }
        $lopHocPhans = $lopHocPhanQuery->orderBy('_id', 'desc')->get();

        $hockys = \App\Models\HocKy::all();
        $monhocs = \App\Models\MonHoc::all();

        $query = DeTai::with(['monHoc', 'lop', 'hocKy', 'lopHocPhan']);
        if ($user->VaiTro !== 'Admin') {
            $query->where('MaTK', $maTK);
        }

        if ($request->filled('MaHocKy')) {
            $val = $request->MaHocKy;
            $hk = \App\Models\HocKy::where('_id', $val)->orWhere('MaHocKy', $val)->orWhere('MaHK', $val)->first();
            $matchIds = array_filter([$val, $hk ? (string)$hk->_id : null, $hk ? $hk->MaHocKy : null, $hk ? $hk->MaHK : null]);
            $query->whereIn('MaHocKy', $matchIds);
        }

        if ($request->filled('MaLopHP')) {
            $val = $request->MaLopHP;
            $lhp = \App\Models\LopHocPhan::where('_id', $val)->orWhere('MaLopHP', $val)->first();
            $matchIds = array_filter([$val, $lhp ? (string)$lhp->_id : null, $lhp ? $lhp->MaLopHP : null]);
            $query->whereIn('MaLopHP', $matchIds);
        }

        if ($request->filled('MaMon')) {
            $val = $request->MaMon;
            $mh = \App\Models\MonHoc::where('_id', $val)->orWhere('MaMon', $val)->first();
            $matchIds = array_filter([$val, $mh ? (string)$mh->_id : null, $mh ? $mh->MaMon : null]);
            $query->whereIn('MaMon', $matchIds);
        }

        if ($request->filled('search')) {
            $query->where('TenDeTai', 'like', '%' . trim($request->search) . '%');
        }

        $detais = $query->orderBy('_id', 'desc')->paginate(5)->withQueryString();

        return view('giangvien.detai.index', compact('detais', 'lopHocPhans', 'hockys', 'monhocs'));
    }

    public function create() {
        $user = Auth::user();
        $maTK = (string) $user->_id;
        $gv = \App\Models\GiangVien::where('MaTK', $maTK)->orWhere('_id', $maTK)->first();
        $gvIds = array_filter([(string)$user->_id, $gv ? (string)$gv->_id : null, $gv ? (string)$gv->MaGV : null]);

        $lopHocPhanQuery = \App\Models\LopHocPhan::with(['monHoc', 'hocKy', 'giangVien']);
        if ($user->VaiTro !== 'Admin') {
            $lopHocPhanQuery->whereIn('MaGV', $gvIds);
        }
        $lopHocPhans = $lopHocPhanQuery->orderBy('_id', 'desc')->get();

        $monhocs = MonHoc::all();
        $hockys = HocKy::all();
        return view('giangvien.detai.create', compact('monhocs', 'hockys', 'lopHocPhans'));
    }

    public function store(Request $request) {
        $user = Auth::user();
        $maTK = (string) $user->_id;

        $request->validate([
            'TenDeTai' => 'required|string|max:200',
            'MaLopHP' => 'required|string',
            'HanDangKy' => 'nullable|date',
            'HanBaoCao' => 'nullable|date|after_or_equal:HanDangKy',
            'HanNopSanPham' => 'nullable|date|after_or_equal:HanBaoCao',
        ], [
            'TenDeTai.required' => 'Vui lòng nhập tên đề tài.',
            'MaLopHP.required' => 'Vui lòng chọn Lớp Học Phần.',
            'HanBaoCao.after_or_equal' => 'Hạn báo cáo phải sau hoặc bằng hạn đăng ký.',
            'HanNopSanPham.after_or_equal' => 'Hạn nộp sản phẩm phải sau hoặc bằng hạn báo cáo.',
        ]);

        $lopHP = \App\Models\LopHocPhan::where('_id', $request->MaLopHP)->orWhere('MaLopHP', $request->MaLopHP)->firstOrFail();

        // Kiểm tra trùng tên đề tài trong cùng Lớp Học Phần
        $exists = DeTai::where('TenDeTai', $request->TenDeTai)->where('MaLopHP', (string)$lopHP->_id)->exists();
        if ($exists) {
            return redirect()->back()->withErrors("Đề tài '{$request->TenDeTai}' đã tồn tại trong Lớp Học Phần này!")->withInput();
        }

        $isAdmin = ($user->VaiTro === 'Admin');
        $maMon = $request->filled('MaMon') ? $request->MaMon : (string)$lopHP->MaMon;
        $maHocKy = $request->filled('MaHocKy') ? $request->MaHocKy : (string)$lopHP->MaHocKy;

        $dt = DeTai::create([
            'MaTK' => $maTK,
            'MaMon' => $maMon,
            'MaHocKy' => $maHocKy,
            'MaLopHP' => (string) $lopHP->_id,
            'MaLop' => null,
            'TenDeTai' => $request->TenDeTai,
            'MoTa' => $request->MoTa,
            'YeuCau' => $request->YeuCau,
            'HanDangKy' => $request->HanDangKy,
            'HanBaoCao' => $request->HanBaoCao,
            'HanNopSanPham' => $request->HanNopSanPham,
            'LoaiDeTai' => 'Giảng viên đề xuất',
            'TrangThaiPheDuyet' => $isAdmin ? 'Đã duyệt' : 'Chờ Giáo vụ duyệt',
            'TrangThai' => $isAdmin ? 'Đang mở đăng ký' : 'Chờ duyệt',
            'NgayTao' => date('Y-m-d')
        ]);

        \App\Models\AuditLog::log('tao_de_tai', 'DeTai', $dt->_id, ['TenDeTai' => $dt->TenDeTai]);

        if ($request->hasFile('file_tai_lieu')) {
            $file = $request->file('file_tai_lieu');
            $ext = strtolower($file->getClientOriginalExtension());
            $filename = 'detai_' . $dt->_id . '_' . time() . '.' . $ext;
            $path = $file->storeAs('tai_lieu_de_tai', $filename, 'public');
            $dt->update(['FileTaiLieu' => $path]);
        }

        return redirect()->route('giangvien.detai.index')->with('success', $isAdmin ? 'Thêm đề tài thành công!' : 'Đề xuất đề tài thành công, đang chờ Giáo vụ phê duyệt!');
    }

    public function show($id) {
        return $this->edit($id);
    }

    public function edit($id) {
        $user = Auth::user();
        $query = DeTai::where('_id', $id);
        if ($user->VaiTro !== 'Admin') {
            $query->where('MaTK', (string) $user->_id);
        }
        $detai = $query->firstOrFail();

        $maTK = (string) $user->_id;
        $gv = \App\Models\GiangVien::where('MaTK', $maTK)->orWhere('_id', $maTK)->first();
        $gvIds = array_filter([(string)$user->_id, $gv ? (string)$gv->_id : null, $gv ? (string)$gv->MaGV : null]);

        $lopHocPhanQuery = \App\Models\LopHocPhan::with(['monHoc', 'hocKy', 'giangVien']);
        if ($user->VaiTro !== 'Admin') {
            $lopHocPhanQuery->whereIn('MaGV', $gvIds);
        }
        $lopHocPhans = $lopHocPhanQuery->orderBy('_id', 'desc')->get();

        $monhocs = MonHoc::all();
        $hockys = HocKy::all();

        return view('giangvien.detai.edit', compact('detai', 'monhocs', 'hockys', 'lopHocPhans'));
    }

    public function update(Request $request, $id) {
        $user = Auth::user();
        $query = DeTai::where('_id', $id);
        if ($user->VaiTro !== 'Admin') {
            $query->where('MaTK', (string) $user->_id);
        }
        $detai = $query->firstOrFail();
        
        $request->validate([
            'TenDeTai' => 'required|string|max:200',
            'MaLopHP' => 'required|string',
            'HanDangKy' => 'nullable|date',
            'HanBaoCao' => 'nullable|date|after_or_equal:HanDangKy',
            'HanNopSanPham' => 'nullable|date|after_or_equal:HanBaoCao',
            'file_tai_lieu' => 'nullable|file|mimes:pdf,doc,docx,zip,rar|max:20480',
        ]);

        $lopHP = \App\Models\LopHocPhan::where('_id', $request->MaLopHP)->orWhere('MaLopHP', $request->MaLopHP)->firstOrFail();

        $newPheDuyet = $detai->TrangThaiPheDuyet;
        if ($user->VaiTro !== 'Admin' && in_array($detai->TrangThaiPheDuyet, ['Yêu cầu điều chỉnh', 'Từ chối'])) {
            $newPheDuyet = 'Chờ Giáo vụ duyệt';
        }

        $maMon = $request->filled('MaMon') ? $request->MaMon : (string)$lopHP->MaMon;
        $maHocKy = $request->filled('MaHocKy') ? $request->MaHocKy : (string)$lopHP->MaHocKy;

        $detai->update([
            'TenDeTai' => $request->TenDeTai,
            'MaLopHP' => (string) $lopHP->_id,
            'MaMon' => $maMon,
            'MaHocKy' => $maHocKy,
            'TrangThaiPheDuyet' => $newPheDuyet,
            'TrangThai' => $request->TrangThai ?? $detai->TrangThai,
            'MoTa' => $request->MoTa,
            'YeuCau' => $request->YeuCau,
            'HanDangKy' => $request->HanDangKy,
            'HanBaoCao' => $request->HanBaoCao,
            'HanNopSanPham' => $request->HanNopSanPham,
        ]);

        if ($request->hasFile('file_tai_lieu')) {
            $file = $request->file('file_tai_lieu');
            $ext = strtolower($file->getClientOriginalExtension());
            $filename = 'detai_' . $detai->_id . '_' . time() . '.' . $ext;

            if (!empty($detai->FileTaiLieu) && \Illuminate\Support\Facades\Storage::disk('public')->exists($detai->FileTaiLieu)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($detai->FileTaiLieu);
            }

            $path = $file->storeAs('tai_lieu_de_tai', $filename, 'public');
            $detai->update(['FileTaiLieu' => $path]);
        }
        
        \App\Models\AuditLog::log('cap_nhat_de_tai', 'DeTai', $id, ['TenDeTai' => $request->TenDeTai]);

        return redirect()->route('giangvien.detai.index')->with('success', 'Cập nhật đề tài thành công!');
    }

    public function destroy($id) {
        $user = Auth::user();
        $query = DeTai::where('_id', $id);
        if ($user->VaiTro !== 'Admin') {
            $query->where('MaTK', (string) $user->_id);
        }
        $detai = $query->firstOrFail();

        try {
            $nhoms = \App\Models\NhomDoAn::where('DangKyDeTai.MaDeTai', $id)->get();
            foreach ($nhoms as $nhom) {
                $nhom->clearDangKyDeTai();
                if (!empty($nhom->HuongDan) && ($nhom->HuongDan['MaDeTai'] ?? '') === $id) {
                    $nhom->HuongDan = null;
                    $nhom->save();
                }
            }

            if (!empty($detai->FileTaiLieu) && \Illuminate\Support\Facades\Storage::disk('public')->exists($detai->FileTaiLieu)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($detai->FileTaiLieu);
            }

            \App\Models\AuditLog::log('xoa_de_tai', 'DeTai', $id, ['TenDeTai' => $detai->TenDeTai]);
            $detai->delete();

            return redirect()->route('giangvien.detai.index')->with('success', 'Xóa đề tài thành công!');
        } catch (\Throwable $e) {
            Log::error('Xóa đề tài lỗi: ' . $e->getMessage());
            return redirect()->back()->withErrors('Không thể xóa đề tài này do đang vướng ràng buộc dữ liệu liên quan.');
        }
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importDeTai', [(string) Auth::user()->_id], 'Đề Tài');
    }

    public function uploadTaiLieu(\App\Http\Requests\UploadTaiLieuRequest $request, $id)
    {
        $user = Auth::user();
        $query = DeTai::where('_id', $id);
        if ($user->VaiTro !== 'Admin') {
            $query->where('MaTK', (string) $user->_id);
        }
        $detai = $query->firstOrFail();

        if ($request->hasFile('file_tai_lieu')) {
            $file = $request->file('file_tai_lieu');
            $ext = strtolower($file->getClientOriginalExtension());
            $filename = 'detai_' . $detai->_id . '_' . time() . '_' . rand(1000, 9999) . '.' . $ext;

            if (!empty($detai->FileTaiLieu) && \Illuminate\Support\Facades\Storage::disk('public')->exists($detai->FileTaiLieu)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($detai->FileTaiLieu);
            }

            $path = $file->storeAs('tai_lieu_de_tai', $filename, 'public');
            $detai->update(['FileTaiLieu' => $path]);

            \App\Models\AuditLog::log('upload_tai_lieu_de_tai', 'DeTai', $id, ['path' => $path]);

            return redirect()->back()->with('success', 'Tải lên tài liệu đính kèm đề tài thành công!');
        }

        return redirect()->back()->withErrors('Vui lòng chọn tệp hợp lệ.');
    }

    public function downloadTaiLieu($id)
    {
        $detai = DeTai::findOrFail($id);

        if (empty($detai->FileTaiLieu) || !\Illuminate\Support\Facades\Storage::disk('public')->exists($detai->FileTaiLieu)) {
            return redirect()->back()->withErrors('Tài liệu đính kèm không tồn tại hoặc đã bị xóa.');
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->download($detai->FileTaiLieu);
    }

    public function deleteTaiLieu($id)
    {
        $user = Auth::user();
        $query = DeTai::where('_id', $id);
        if ($user->VaiTro !== 'Admin') {
            $query->where('MaTK', (string) $user->_id);
        }
        $detai = $query->firstOrFail();

        if (!empty($detai->FileTaiLieu) && \Illuminate\Support\Facades\Storage::disk('public')->exists($detai->FileTaiLieu)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($detai->FileTaiLieu);
        }

        $detai->update(['FileTaiLieu' => null]);
        return redirect()->back()->with('success', 'Xóa tài liệu đính kèm thành công!');
    }
}