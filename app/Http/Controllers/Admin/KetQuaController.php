<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NhomDoAn;
use App\Models\GiangVien;
use App\Models\DeTai;
use App\Models\HocKy;
use App\Models\MonHoc;
use App\Models\Lop;
use App\Models\SinhVien;

class KetQuaController extends Controller
{
    public function index(Request $request)
    {
        $query = NhomDoAn::query();

        // Lọc 6 Tầng
        if ($request->filled('MaHocKy') || $request->filled('maHK')) {
            $query->where('MaHocKy', $request->MaHocKy ?? $request->maHK);
        }

        if ($request->filled('MaLopHP')) {
            $query->where('MaLopHP', $request->MaLopHP);
        }

        if ($request->filled('MaMon') || $request->filled('maMon')) {
            $query->where('MaMon', $request->MaMon ?? $request->maMon);
        }

        if ($request->filled('KetQua')) {
            $kq = $request->KetQua;
            if ($kq === 'Đạt') {
                $query->where('ChamDiem.DiemTong', '>=', 5.0);
            } elseif ($kq === 'Không đạt') {
                $query->where('ChamDiem.DiemTong', '<', 5.0)->whereNotNull('ChamDiem');
            } elseif ($kq === 'Chưa có điểm') {
                $query->whereNull('ChamDiem');
            }
        }

        if ($request->filled('keyword')) {
            $kw = trim($request->keyword);
            $query->where(function($q) use ($kw) {
                $q->where('TenNhom', 'like', "%{$kw}%")
                  ->orWhere('MaNhom', 'like', "%{$kw}%")
                  ->orWhere('ThanhVien.MaSV', 'like', "%{$kw}%")
                  ->orWhere('ThanhVien.HoTen', 'like', "%{$kw}%");
            });
        }

        $nhoms = $query->orderBy('_id', 'desc')->paginate(12)->withQueryString();
        $danhSach = $nhoms;

        foreach ($danhSach as $nhom) {
            $hd = $nhom->getHuongDan();
            $giangVien = ($hd && !empty($hd['MaGV'])) ? GiangVien::where('_id', $hd['MaGV'])->orWhere('MaGV', $hd['MaGV'])->first() : null;

            $dk = $nhom->getDangKyDeTai();
            $deTai = ($dk && !empty($dk['MaDeTai'])) ? DeTai::find($dk['MaDeTai']) : null;

            $svTruongNhom = null;
            if (!empty($nhom->TruongNhom)) {
                $svTruongNhom = SinhVien::where('MaSV', $nhom->TruongNhom)->orWhere('_id', $nhom->TruongNhom)->first();
            }
            if (!$svTruongNhom && !empty($nhom->ThanhVien)) {
                foreach ($nhom->ThanhVien as $tv) {
                    $isLeader = ($tv['VaiTro'] ?? '') === 'Trưởng nhóm' || ($tv['MaSV'] ?? '') === $nhom->TruongNhom;
                    if ($isLeader && !empty($tv['MaSV'])) {
                        $svTruongNhom = SinhVien::where('MaSV', $tv['MaSV'])->orWhere('_id', $tv['MaSV'])->first();
                        if (!$svTruongNhom && !empty($tv['HoTen'])) {
                            $svTruongNhom = (object) ['HoTen' => $tv['HoTen'], 'MaSV' => $tv['MaSV']];
                        }
                        if ($svTruongNhom) break;
                    }
                }
                if (!$svTruongNhom && isset($nhom->ThanhVien[0])) {
                    $tv1 = $nhom->ThanhVien[0];
                    if (!empty($tv1['MaSV'])) {
                        $svTruongNhom = SinhVien::where('MaSV', $tv1['MaSV'])->orWhere('_id', $tv1['MaSV'])->first();
                    }
                    if (!$svTruongNhom && !empty($tv1['HoTen'])) {
                        $svTruongNhom = (object) ['HoTen' => $tv1['HoTen'], 'MaSV' => $tv1['MaSV'] ?? 'N/A'];
                    }
                }
            }
            $nhom->sinhVienTruongNhom = $svTruongNhom;
            $nhom->svTruongNhom = $svTruongNhom;

            $chamDiemArr = $nhom->ChamDiem ?? null;
            if (is_array($chamDiemArr)) {
                $nhom->chamDiem = (object) $chamDiemArr;
            } else {
                $nhom->chamDiem = $chamDiemArr;
            }

            // Xếp loại Đạt / Không Đạt
            $diemTong = $nhom->chamDiem->DiemTong ?? null;
            if ($diemTong !== null) {
                $nhom->xepLoaiKetQua = $diemTong >= 5.0 ? 'Đạt' : 'Không đạt';
            } else {
                $nhom->xepLoaiKetQua = 'Chưa có điểm';
            }

            $nhom->nhomDoAn = $nhom;
            $nhom->deTai = $deTai;
            $nhom->giangVien = $giangVien;
        }

        $hocKys = HocKy::orderBy('_id', 'desc')->get();
        $monHocs = MonHoc::orderBy('TenMon', 'asc')->get();
        $lops = Lop::orderBy('TenLop', 'asc')->get();
        $lopHocPhans = \App\Models\LopHocPhan::orderBy('_id', 'desc')->get();

        $totalGroups = NhomDoAn::count();
        $gradedGroups = NhomDoAn::whereNotNull('ChamDiem')->count();
        $passGroups = NhomDoAn::where('ChamDiem.DiemTong', '>=', 5.0)->count();
        $failGroups = NhomDoAn::where('ChamDiem.DiemTong', '<', 5.0)->whereNotNull('ChamDiem')->count();
        $ungradedGroups = max(0, $totalGroups - $gradedGroups);

        return view('admin.ketqua.index', compact('danhSach', 'nhoms', 'hocKys', 'monHocs', 'lops', 'lopHocPhans', 'totalGroups', 'gradedGroups', 'passGroups', 'failGroups', 'ungradedGroups'));
    }
}
