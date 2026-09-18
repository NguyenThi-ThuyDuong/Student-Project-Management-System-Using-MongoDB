<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\GiangVien;
use App\Models\NhomDoAn;
use App\Models\DeTai;
use App\Models\LopHocPhan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SanPhamController extends Controller
{
    private function getGiangVienKeys($gv, $user)
    {
        return array_values(array_unique(array_filter([
            (string) ($gv->_id ?? ''),
            (string) ($gv->MaGV ?? ''),
            (string) ($gv->MaTK ?? ''),
            (string) ($user->_id ?? ''),
        ])));
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $gv = GiangVien::where('MaTK', (string) $user->_id)
            ->orWhere('_id', (string) $user->_id)
            ->orWhere('MaGV', (string) $user->TenDangNhap)
            ->first();
        if (!$gv) abort(403, 'Không tìm thấy thông tin giảng viên');

        $gvKeys = $this->getGiangVienKeys($gv, $user);

        // 1. Nhóm từ HuongDan embedded
        $nhomIds1 = NhomDoAn::whereIn('HuongDan.MaGV', $gvKeys)
            ->get()->map(fn($item) => (string) $item->_id)->toArray();

        // 2. Nhóm thuộc Lớp Học Phần do Giảng viên phụ trách
        $lhps = LopHocPhan::whereIn('MaGV', $gvKeys)
            ->orWhereIn('GiangVienMaGV', $gvKeys)->get();
        $lhpKeys = [];
        foreach ($lhps as $lhp) {
            if (!empty($lhp->_id)) $lhpKeys[] = (string) $lhp->_id;
            if (!empty($lhp->MaLopHP)) $lhpKeys[] = (string) $lhp->MaLopHP;
        }
        $lhpKeys = array_values(array_unique(array_filter($lhpKeys)));

        $nhomIds2 = NhomDoAn::whereIn('MaLopHP', $lhpKeys)
            ->get()->map(fn($item) => (string) $item->_id)->toArray();

        // 3. Nhóm có đề tài do giảng viên tạo / duyệt / hướng dẫn
        $deTais = DeTai::whereIn('MaTK', $gvKeys)
            ->orWhereIn('MaGV', $gvKeys)
            ->get();
        $deTaiKeys = [];
        foreach ($deTais as $dt) {
            if (!empty($dt->_id)) $deTaiKeys[] = (string) $dt->_id;
            if (!empty($dt->MaDeTai)) $deTaiKeys[] = (string) $dt->MaDeTai;
            if (!empty($dt->MaDT)) $deTaiKeys[] = (string) $dt->MaDT;
        }
        $deTaiKeys = array_values(array_unique(array_filter($deTaiKeys)));

        $nhomIds3 = NhomDoAn::whereIn('DangKyDeTai.MaDeTai', $deTaiKeys)
            ->get()->map(fn($item) => (string) $item->_id)->toArray();

        $allNhomIds = array_values(array_unique(array_filter(array_merge($nhomIds1, $nhomIds2, $nhomIds3))));

        $allNhoms = NhomDoAn::whereIn('_id', $allNhomIds)->get();

        $selectedNhomId = $request->get('maNhom');

        $query = NhomDoAn::whereIn('_id', $allNhomIds)
                         ->with(['monHoc', 'hocKy']);

        if (!empty($selectedNhomId)) {
            $query->where('_id', $selectedNhomId);
        }

        $nhoms = $query->paginate(5)->withQueryString();

        return view('giangvien.sanpham.index', compact('nhoms', 'allNhoms', 'selectedNhomId'));
    }
}
