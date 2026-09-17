<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NhomDoAn;
use App\Models\HocKy;
use App\Models\MonHoc;
use App\Models\Lop;
use App\Models\GiangVien;
use App\Models\DeTai;
use Illuminate\Http\Request;

class SanPhamController extends Controller
{
    public function index(Request $request)
    {
        $hockys = HocKy::all();
        $monhocs = MonHoc::all();
        $lops = Lop::all();
        $giangviens = GiangVien::all();

        // Truy vấn NhomDoAn có sản phẩm (embedded array SanPham)
        $query = NhomDoAn::whereNotNull('SanPham')
            ->where('SanPham', '!=', [])
            ->with(['monHoc', 'hocKy']);

        // 1. Lọc theo Học kỳ
        if ($request->filled('MaHocKy')) {
            $query->where('MaHocKy', $request->MaHocKy);
        }

        // 2. Lọc theo Môn học
        if ($request->filled('MaMon')) {
            $query->where('MaMon', $request->MaMon);
        }

        // 3. Lọc theo Giảng viên (qua HuongDan embedded)
        if ($request->filled('MaGV')) {
            $query->where('HuongDan.MaGV', $request->MaGV);
        }

        // 4. Tìm theo tên nhóm / sản phẩm
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('TenNhom', 'like', "%{$search}%")
                  ->orWhere('SanPham.TenSanPham', 'like', "%{$search}%");
            });
        }

        $sanphams = $query->orderBy('_id', 'desc')->paginate(15);

        // Enrich data cho từng item
        foreach ($sanphams as $nhom) {
            $dk = $nhom->getDangKyDeTai();
            $deTai = ($dk && !empty($dk['MaDeTai'])) ? DeTai::find($dk['MaDeTai']) : null;
            if ($deTai) {
                $gv = GiangVien::where('MaTK', $deTai->MaTK)->first();
                $deTai->giangVien = $gv;
            }

            $nhom->dangKyDeTai = (object) [
                'deTai' => $deTai
            ];
            $nhom->nhomDoAn = $nhom;

            $sanPhamArr = $nhom->SanPham ?? [];
            $firstSp = is_array($sanPhamArr) && count($sanPhamArr) > 0 ? (object)$sanPhamArr[0] : null;

            $nhom->LinkFile = $firstSp->LinkFile ?? $firstSp->MoTa ?? null;
            $nhom->LinkSourceCode = $firstSp->LinkSourceCode ?? $firstSp->LinkFile ?? null;
            $nhom->NgayNop = $firstSp->NgayNop ?? $nhom->NgayTao ?? date('Y-m-d');
            $nhom->TrangThai = $firstSp->TrangThai ?? 'Đã nộp';
        }

        $nhoms = $sanphams;
        return view('admin.sanpham.index', compact('sanphams', 'nhoms', 'hockys', 'monhocs', 'lops', 'giangviens'));
    }
}

