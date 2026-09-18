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

        if ($request->filled('TrangThaiPheDuyet')) {
            $query->where('TrangThaiPheDuyet', $request->TrangThaiPheDuyet);
        }

        if ($request->filled('LoaiDeTai')) {
            $query->where('LoaiDeTai', $request->LoaiDeTai);
        }

        if ($request->filled('search')) {
            $query->where('TenDeTai', 'like', '%' . trim($request->search) . '%');
        }

        $detais = $query->orderBy('_id', 'desc')->paginate(5)->withQueryString();

        // Lấy giảng viên, nhóm đề xuất, lớp học phần, môn học cho từng đề tài
        foreach ($detais as $dt) {
            // Giảng viên
            $gv = null;
            if ($dt->MaGV) {
                $gv = \App\Models\GiangVien::where('MaGV', $dt->MaGV)->orWhere('_id', $dt->MaGV)->first();
            }
            if (!$gv && $dt->MaTK) {
                $gv = \App\Models\GiangVien::where('MaTK', $dt->MaTK)->orWhere('_id', $dt->MaTK)->first();
            }
            if (!$gv && $dt->MaLopHP) {
                $lhpCheck = \App\Models\LopHocPhan::where('_id', $dt->MaLopHP)->orWhere('MaLopHP', $dt->MaLopHP)->first();
                if ($lhpCheck && $lhpCheck->MaGV) {
                    $gv = \App\Models\GiangVien::where('MaGV', $lhpCheck->MaGV)->orWhere('_id', $lhpCheck->MaGV)->first();
                }
            }
            if (!$gv) {
                $gv = \App\Models\GiangVien::first();
            }
            $dt->setAttribute('giangVien', $gv);

            // Nhóm tự đề xuất hoặc nhóm đăng ký
            $nhom = null;
            if ($dt->NhomTuDeXuat_id) {
                $nhom = NhomDoAn::where('_id', $dt->NhomTuDeXuat_id)->orWhere('MaNhom', $dt->NhomTuDeXuat_id)->first();
            }
            if (!$nhom) {
                $nhom = NhomDoAn::where('DangKyDeTai.MaDeTai', (string)$dt->_id)
                    ->orWhere('DangKyDeTai.MaDeTai', $dt->MaDeTai)
                    ->orWhere('MaDeTai', (string)$dt->_id)
                    ->orWhere('MaDeTai', $dt->MaDeTai)
                    ->first();
            }
            $dt->setAttribute('nhomDeXuat', $nhom);

            // Lớp học phần
            $lhp = null;
            if ($dt->MaLopHP) {
                $lhp = \App\Models\LopHocPhan::where('_id', $dt->MaLopHP)->orWhere('MaLopHP', $dt->MaLopHP)->first();
            }
            if (!$lhp) {
                $lhp = \App\Models\LopHocPhan::first();
            }
            $dt->setAttribute('lopHocPhan', $lhp);

            // Môn học
            $mh = null;
            if ($dt->MaMon) {
                $mh = \App\Models\MonHoc::where('MaMon', $dt->MaMon)->orWhere('_id', $dt->MaMon)->first();
            }
            if (!$mh && $lhp && $lhp->MaMon) {
                $mh = \App\Models\MonHoc::where('MaMon', $lhp->MaMon)->orWhere('_id', $lhp->MaMon)->first();
            }
            if (!$mh) {
                $mh = \App\Models\MonHoc::first();
            }
            $dt->setAttribute('monHoc', $mh);
        }

        $lopHocPhans = \App\Models\LopHocPhan::orderBy('_id', 'desc')->get();
        $hocKies = \App\Models\HocKy::all();

        return view('admin.duyet_detai.index', compact('detais', 'lopHocPhans', 'hocKies'));
    }

    /**
     * Phê duyệt TẤT CẢ đề tài thuộc Lớp Học Phần được chọn
     */
    public function approveAllInClass(Request $request)
    {
        $maLopHP = $request->input('MaLopHP');
        if (!$maLopHP) {
            return redirect()->back()->withErrors('Vui lòng chọn Lớp Học Phần để thực hiện duyệt hàng loạt!');
        }

        $lhp = \App\Models\LopHocPhan::where('_id', $maLopHP)->orWhere('MaLopHP', $maLopHP)->first();
        if (!$lhp) {
            return redirect()->back()->withErrors('Lớp Học Phần không tồn tại!');
        }

        $matchIds = array_filter([$maLopHP, (string)$lhp->_id, $lhp->MaLopHP]);

        $pendingTopics = DeTai::whereIn('MaLopHP', $matchIds)
            ->where('TrangThaiPheDuyet', '!=', 'Đã duyệt')
            ->get();

        if ($pendingTopics->isEmpty()) {
            return redirect()->back()->with('info', "Tất cả đề tài trong Lớp Học Phần '{$lhp->TenLopHP}' đều đã được duyệt trước đó!");
        }

        $count = 0;
        foreach ($pendingTopics as $dt) {
            $this->approve((string)$dt->_id);
            $count++;
        }

        return redirect()->back()->with('success', "Đã phê duyệt thành công toàn bộ {$count} đề tài thuộc Lớp Học Phần '{$lhp->TenLopHP}'!");
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
