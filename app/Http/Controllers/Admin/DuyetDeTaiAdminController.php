<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeTai;
use App\Models\NhomDoAn;
use App\Models\AuditLog;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DuyetDeTaiAdminController extends Controller
{
    /**
     * Danh sách đề tài chờ Giáo vụ duyệt (Đề xuất từ Giảng viên & Sinh viên)
     */
    public function index(Request $request)
    {
        $query = DeTai::with(['monHoc', 'lop', 'hocKy', 'lopHocPhan']);

        if ($request->filled('TrangThaiPheDuyet')) {
            $query->where('TrangThaiPheDuyet', $request->TrangThaiPheDuyet);
        }

        if ($request->filled('LoaiDeTai')) {
            $query->where('LoaiDeTai', $request->LoaiDeTai);
        }

        if ($request->filled('search')) {
            $query->where('TenDeTai', 'like', '%' . trim($request->search) . '%');
        }

        $detais = $query->orderBy('_id', 'desc')->paginate(12)->withQueryString();

        // Lấy giảng viên cho từng đề tài
        foreach ($detais as $dt) {
            $gv = \App\Models\GiangVien::where('MaTK', $dt->MaTK)->first();
            $dt->setAttribute('giangVien', $gv);

            if ($dt->NhomTuDeXuat_id) {
                $nhom = NhomDoAn::find($dt->NhomTuDeXuat_id);
                $dt->setAttribute('nhomDeXuat', $nhom);
            }
        }

        $lopHocPhans = \App\Models\LopHocPhan::orderBy('_id', 'desc')->get();
        $hocKies = \App\Models\HocKy::all();

        return view('admin.duyet_detai.index', compact('detais', 'lopHocPhans', 'hocKies'));
    }

    /**
     * Phê duyệt đề tài (Mở đăng ký cho sinh viên)
     */
    public function approve($id)
    {
        $deTai = DeTai::findOrFail($id);

        $deTai->update([
            'TrangThaiPheDuyet' => 'Đã duyệt',
            'TrangThai' => 'Đang mở đăng ký',
            'LyDoPheDuyet' => null
        ]);

        // Nếu là Đề tài sinh viên tự đề xuất -> Tự động chấp nhận đăng ký cho Nhóm sinh viên đề xuất
        if ($deTai->NhomTuDeXuat_id) {
            $nhom = NhomDoAn::find($deTai->NhomTuDeXuat_id);
            if ($nhom) {
                $nhom->DangKyDeTai = [
                    'MaDeTai' => (string) $deTai->_id,
                    'NgayDangKy' => date('Y-m-d H:i:s'),
                    'TrangThai' => 'Đã duyệt',
                    'NgayDuyet' => date('Y-m-d H:i:s'),
                ];
                $nhom->TrangThai = 'Đã có đề tài';
                
                // Gán GVHD nếu đề tài thuộc Lớp HP có GV phụ trách
                $lhp = \App\Models\LopHocPhan::find($deTai->MaLopHP);
                if ($lhp && $lhp->MaGV) {
                    $gv = \App\Models\GiangVien::where('MaGV', $lhp->MaGV)->orWhere('_id', $lhp->MaGV)->first();
                    if ($gv) {
                        $nhom->HuongDan = [
                            'MaGV' => (string) $gv->_id,
                            'MaDeTai' => (string) $deTai->_id,
                            'NgayPhanCong' => date('Y-m-d'),
                            'TrangThai' => 'Đang hướng dẫn',
                        ];
                    }
                }
                $nhom->save();
            }
        }

        AuditLog::log('duyet_de_tai_admin', 'DeTai', $deTai->_id, ['TenDeTai' => $deTai->TenDeTai]);

        return redirect()->back()->with('success', "Đã phê duyệt đề tài '{$deTai->TenDeTai}' thành công!");
    }

    /**
     * Từ chối đề tài
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'LyDoPheDuyet' => 'required|string|max:500'
        ], [
            'LyDoPheDuyet.required' => 'Vui lòng nhập lý do từ chối đề tài.'
        ]);

        $deTai = DeTai::findOrFail($id);

        $deTai->update([
            'TrangThaiPheDuyet' => 'Từ chối',
            'TrangThai' => 'Từ chối',
            'LyDoPheDuyet' => $request->LyDoPheDuyet
        ]);

        AuditLog::log('tu_choi_de_tai_admin', 'DeTai', $deTai->_id, ['TenDeTai' => $deTai->TenDeTai, 'LyDo' => $request->LyDoPheDuyet]);

        return redirect()->back()->with('success', "Đã từ chối đề tài '{$deTai->TenDeTai}'!");
    }

    /**
     * Yêu cầu điều chỉnh đề tài
     */
    public function requestAdjustment(Request $request, $id)
    {
        $request->validate([
            'LyDoPheDuyet' => 'required|string|max:500'
        ], [
            'LyDoPheDuyet.required' => 'Vui lòng nhập nội dung yêu cầu điều chỉnh.'
        ]);

        $deTai = DeTai::findOrFail($id);

        $deTai->update([
            'TrangThaiPheDuyet' => 'Yêu cầu điều chỉnh',
            'TrangThai' => 'Chờ chỉnh sửa',
            'LyDoPheDuyet' => $request->LyDoPheDuyet
        ]);

        AuditLog::log('yeu_cau_dieu_chinh_de_tai_admin', 'DeTai', $deTai->_id, ['TenDeTai' => $deTai->TenDeTai, 'Noidung' => $request->LyDoPheDuyet]);

        return redirect()->back()->with('success', "Đã gửi yêu cầu điều chỉnh đề tài '{$deTai->TenDeTai}'!");
    }
}
