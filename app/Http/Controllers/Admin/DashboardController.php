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
        $soSinhVien = Cache::remember('dashboard_soSinhVien', 60, function () { return SinhVien::count(); });
        $soGiangVien = Cache::remember('dashboard_soGiangVien', 60, function () { return GiangVien::count(); });
        $soDeTai = Cache::remember('dashboard_soDeTai', 60, function () { return DeTai::count(); });
        $soNhom = Cache::remember('dashboard_soNhom', 60, function () { return NhomDoAn::count(); });
        $soLopHocPhan = Cache::remember('dashboard_soLopHocPhan', 60, function () { return \App\Models\LopHocPhan::count(); });
        $soYeuCauMatKhau = Cache::remember('dashboard_soYeuCauMatKhau', 30, function () {
            return YeuCauDoiMatKhau::where('TrangThai', 'cho_duyet')->count();
        });

        // Thống kê trạng thái nhóm - MongoDB aggregation
        $trangThaiNhom = Cache::remember('dashboard_trangThaiNhom', 60, function () {
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
            'chartLabels', 'chartData'
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
