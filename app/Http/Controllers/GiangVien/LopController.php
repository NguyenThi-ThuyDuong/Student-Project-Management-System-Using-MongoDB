<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\LopHocPhan;
use App\Models\GiangVien;
use App\Models\NhomDoAn;
use App\Models\SinhVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LopController extends Controller
{
    public function index(Request $request)
    {
        $gv = GiangVien::where('MaTK', (string) Auth::user()->_id)->first();
        if (!$gv) abort(403);
        
        $query = LopHocPhan::with(['monHoc', 'hocKy'])
            ->where(function($q) use ($gv) {
                $q->where('MaGV', (string) $gv->_id)
                  ->orWhere('MaGV', (string) $gv->MaGV);
            });

        if ($request->filled('ma_hoc_ky')) {
            $query->where('MaHocKy', $request->ma_hoc_ky);
        }

        $lopHocPhans = $query->orderBy('_id', 'desc')->get();
        $hocKies = \App\Models\HocKy::orderBy('_id', 'desc')->get();

        return view('giangvien.lop.index', compact('lopHocPhans', 'hocKies'));
    }

    public function show($id)
    {
        $user = Auth::user();
        $maTK = (string) $user->_id;
        $gv = GiangVien::where('MaTK', $maTK)->orWhere('_id', $maTK)->first();
        if (!$gv) abort(403);
        
        $lopHP = LopHocPhan::with(['monHoc', 'hocKy', 'giangVien'])
            ->where('_id', $id)
            ->orWhere('MaLopHP', $id)
            ->firstOrFail();

        // Lấy danh sách nhóm đồ án thuộc Lớp Học Phần này
        $nhoms = NhomDoAn::where('MaLopHP', (string) $lopHP->_id)
            ->orWhere('MaLopHP', $lopHP->MaLopHP)
            ->with(['monHoc', 'hocKy'])
            ->get();

        // Thu thập mã SV từ danh sách đăng ký và từ các nhóm thành viên
        $svKeysFromGroups = [];
        foreach ($nhoms as $nhom) {
            foreach ($nhom->getSinhVienThanhVien() as $sv) {
                if ($sv) {
                    $svKeysFromGroups[] = (string) $sv->_id;
                    $svKeysFromGroups[] = (string) $sv->MaSV;
                }
            }
        }

        $allSvKeys = array_values(array_unique(array_filter(array_merge(
            $lopHP->getSinhVienIds()->toArray(),
            $svKeysFromGroups
        ))));

        $sinhVienList = SinhVien::with('lop')->where(function($q) use ($allSvKeys) {
            $q->whereIn('_id', $allSvKeys)
              ->orWhereIn('MaSV', $allSvKeys);
        })->get();

        // Tự động đồng bộ lại mảng DanhSachSinhVien trên LopHocPhan nếu thiếu
        if ($sinhVienList->isNotEmpty()) {
            $dsCurrent = $lopHP->DanhSachSinhVien ?? [];
            $dsMap = collect($dsCurrent)->pluck('MaSV')->toArray();
            $changed = false;
            foreach ($sinhVienList as $svObj) {
                if (!in_array($svObj->MaSV, $dsMap)) {
                    $dsCurrent[] = [
                        'MaSV' => $svObj->MaSV,
                        'sinh_vien_id' => (string)$svObj->_id,
                        'NgayDangKy' => date('Y-m-d H:i:s')
                    ];
                    $changed = true;
                }
            }
            if ($changed) {
                $lopHP->update(['DanhSachSinhVien' => $dsCurrent]);
            }
        }

        // Enrich nhóm data
        foreach ($nhoms as $nhom) {
            $nhom->setAttribute('thanhVienSVs', $nhom->getSinhVienThanhVien());
            $dk = $nhom->getDangKyDeTai();
            if ($dk && !empty($dk['MaDeTai'])) {
                $nhom->setAttribute('deTaiDangKy', \App\Models\DeTai::find($dk['MaDeTai']));
            }
        }

        return view('giangvien.lop.show', compact('lopHP', 'sinhVienList', 'nhoms'));
    }

    public function updateGroupLimit(Request $request, $id)
    {
        $request->validate([
            'SoThanhVienNhomToiDa' => 'required|integer|min:1|max:20'
        ], [
            'SoThanhVienNhomToiDa.required' => 'Vui lòng nhập giới hạn số thành viên.',
            'SoThanhVienNhomToiDa.min' => 'Số thành viên tối đa ít nhất là 1.',
            'SoThanhVienNhomToiDa.max' => 'Số thành viên tối đa không quá 20.'
        ]);

        $lopHP = LopHocPhan::where('_id', $id)->orWhere('MaLopHP', $id)->firstOrFail();
        $lopHP->update(['SoThanhVienNhomToiDa' => (int) $request->SoThanhVienNhomToiDa]);

        return redirect()->back()->with('success', "Cập nhật giới hạn thành viên cho Lớp HP '{$lopHP->TenLopHP}' thành công: tối đa {$request->SoThanhVienNhomToiDa} SV/nhóm!");
    }
}
