<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SinhVien;
use App\Models\GiangVien;
use App\Models\DeTai;
use App\Models\NhomDoAn;
use App\Models\TaiKhoan;
use App\Models\YeuCauDoiMatKhau;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $dashboardData = Cache::remember('cache:dashboard:admin', 600, function () {
            $tong_sinh_vien = SinhVien::count();
            $tong_giang_vien = GiangVien::count();
            $tong_nhom = NhomDoAn::count();
            $tong_de_tai = DeTai::count();
            $de_tai_da_duyet = DeTai::where('TrangThaiPheDuyet', 'Đã duyệt')->orWhere('TrangThai', 'Đã duyệt')->count();
            $soLopHocPhan = \App\Models\LopHocPhan::count();
            $soYeuCauMatKhau = YeuCauDoiMatKhau::where('TrangThai', 'cho_duyet')->count();

            // Đếm báo cáo đã nộp & nhóm chậm tiến độ từ MongoDB
            $bao_cao_da_nop = 0;
            $nhom_cham_tien_do = 0;

            $allNhoms = NhomDoAn::all();
            foreach ($allNhoms as $nhom) {
                $bcList = $nhom->getBaoCaoList();
                $bcCount = $bcList->count();
                $bao_cao_da_nop += $bcCount;

                $dk = $nhom->getDangKyDeTai();
                if ($dk && ($dk['TrangThai'] ?? '') === 'Đã duyệt' && $bcCount === 0) {
                    $nhom_cham_tien_do++;
                }
            }

            return [
                'tong_sinh_vien' => $tong_sinh_vien,
                'tong_giang_vien' => $tong_giang_vien,
                'tong_nhom' => $tong_nhom,
                'tong_de_tai' => $tong_de_tai,
                'de_tai_da_duyet' => $de_tai_da_duyet,
                'bao_cao_da_nop' => $bao_cao_da_nop,
                'nhom_cham_tien_do' => $nhom_cham_tien_do,
                'soLopHocPhan' => $soLopHocPhan,
                'soYeuCauMatKhau' => $soYeuCauMatKhau,
            ];
        });

        $soSinhVien = $dashboardData['tong_sinh_vien'];
        $soGiangVien = $dashboardData['tong_giang_vien'];
        $soDeTai = $dashboardData['tong_de_tai'];
        $soNhom = $dashboardData['tong_nhom'];
        $soLopHocPhan = $dashboardData['soLopHocPhan'];
        $soYeuCauMatKhau = $dashboardData['soYeuCauMatKhau'];

        // Thống kê trạng thái nhóm - MongoDB aggregation
        $trangThaiNhom = Cache::remember('dashboard_trangThaiNhom', 600, function () {
            $results = NhomDoAn::raw(function ($collection) {
                return $collection->aggregate([
                    ['$group' => ['_id' => '$TrangThai', 'total' => ['$sum' => 1]]]
                ]);
            });
            $data = [];
            foreach ($results as $r) {
                $data[$r->_id ?? 'Không xác định'] = $r->total;
            }
            return $data;
        });

        $chartLabels = array_keys($trangThaiNhom);
        $chartData = array_values($trangThaiNhom);

        return view('admin.dashboard', compact(
            'soSinhVien', 'soGiangVien', 'soDeTai', 'soNhom', 'soLopHocPhan', 'soYeuCauMatKhau',
            'chartLabels', 'chartData', 'dashboardData'
        ));
    }

    public function toggleLockAccount($id)
    {
        $tk = TaiKhoan::findOrFail($id);
        if ($tk->VaiTro === 'Admin' && (string) $tk->_id === (string) auth()->user()->_id) {
            return redirect()->back()->withErrors('Không thể tự khóa tài khoản Admin đang sử dụng!');
        }

        $tk->TrangThai = !$tk->TrangThai;
        $tk->save();

        $msg = $tk->TrangThai ? 'Mở khóa tài khoản thành công!' : 'Đã khóa tài khoản thành công!';
        return redirect()->back()->with('success', $msg);
    }
}
