<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\ThongBao;
use App\Models\TaiKhoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThongBaoController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $sv = \App\Models\SinhVien::where('MaTK', (string) $user->_id)->first();

        if (!$sv) {
            $thongbaos = collect();
            return view('sinhvien.thongbao.index', compact('thongbaos'));
        }

        // 1. Admin accounts
        $adminTKs = TaiKhoan::where('VaiTro', 'Admin')->get()->map(fn($item) => (string) $item->_id)->toArray();

        // 2. Student's registered Lớp Học Phần IDs
        $studentLhpModels = \App\Models\LopHocPhan::where('DanhSachSinhVien.MaSV', (string) $sv->_id)
            ->orWhere('DanhSachSinhVien.MaSV', (string) $sv->MaSV)->get();
        $studentLhpIds = [];
        foreach ($studentLhpModels as $slhp) {
            if (!empty($slhp->_id)) $studentLhpIds[] = (string) $slhp->_id;
            if (!empty($slhp->MaLopHP)) $studentLhpIds[] = (string) $slhp->MaLopHP;
        }
        $studentLhpIds = array_values(array_unique(array_filter($studentLhpIds)));

        // 3. Lecturers assigned to student's Lớp Học Phần
        $lecturerGvIds = \App\Models\LopHocPhan::whereIn('_id', $studentLhpIds)
            ->whereNotNull('MaGV')
            ->pluck('MaGV')->map(fn($id) => (string) $id)->unique()->toArray();

        $lecturerTKs = \App\Models\GiangVien::whereIn('_id', $lecturerGvIds)
            ->pluck('MaTK')->map(fn($id) => (string) $id)->toArray();

        $thongbaos = ThongBao::where(function($query) use ($adminTKs, $sv, $studentLhpIds, $lecturerTKs) {
                            $query->whereIn('MaTK', $adminTKs);
                            if ($sv->MaLop) {
                                $query->orWhere('MaLop', (string) $sv->MaLop);
                            }
                            if (!empty($studentLhpIds)) {
                                $query->orWhereIn('MaLopHP', $studentLhpIds);
                            }
                            if (!empty($lecturerTKs)) {
                                $query->orWhere(function($subQ) use ($lecturerTKs) {
                                    $subQ->whereIn('MaTK', $lecturerTKs)
                                         ->whereNull('MaLop')
                                         ->whereNull('MaLopHP');
                                });
                            }
                        })
                        ->orderBy('_id', 'desc')
                        ->paginate(5);

        return view('sinhvien.thongbao.index', compact('thongbaos'));
    }

    public function markRead($id)
    {
        $tb = ThongBao::find($id);
        if ($tb) {
            $tb->update(['DaDoc' => true]);
        }
        return response()->json(['success' => true]);
    }

    public function markAllRead()
    {
        $user = Auth::user();
        $sv = \App\Models\SinhVien::where('MaTK', (string) $user->_id)->first();

        if ($sv) {
            $adminTKs = TaiKhoan::where('VaiTro', 'Admin')->get()->map(fn($item) => (string) $item->_id)->toArray();
            $studentLhpModels = \App\Models\LopHocPhan::where('DanhSachSinhVien.MaSV', (string) $sv->_id)
                ->orWhere('DanhSachSinhVien.MaSV', (string) $sv->MaSV)->get();
            $studentLhpIds = [];
            foreach ($studentLhpModels as $slhp) {
                if (!empty($slhp->_id)) $studentLhpIds[] = (string) $slhp->_id;
                if (!empty($slhp->MaLopHP)) $studentLhpIds[] = (string) $slhp->MaLopHP;
            }
            $studentLhpIds = array_values(array_unique(array_filter($studentLhpIds)));

            ThongBao::where(function($query) use ($adminTKs, $sv, $studentLhpIds) {
                $query->whereIn('MaTK', $adminTKs);
                if ($sv->MaLop) {
                    $query->orWhere('MaLop', (string) $sv->MaLop);
                }
                if (!empty($studentLhpIds)) {
                    $query->orWhereIn('MaLopHP', $studentLhpIds);
                }
            })->update(['DaDoc' => true]);
        }

        return redirect()->back()->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc!');
    }
}
