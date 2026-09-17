<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NhomDoAn;
use App\Models\LopHocPhan;
use App\Models\HocKy;
use App\Models\DeTai;
use Illuminate\Http\Request;

class TienDoAdminController extends Controller
{
    /**
     * Quản lý tiến độ đồ án phân cấp 5 tầng: Học kỳ → Lớp → Sinh viên/Nhóm → Đề tài → Các mốc tiến độ
     */
    public function index(Request $request)
    {
        $query = NhomDoAn::with(['monHoc', 'hocKy', 'lopHocPhan']);

        if ($request->filled('MaHocKy')) {
            $query->where('MaHocKy', $request->MaHocKy);
        }

        if ($request->filled('MaLopHP')) {
            $query->where('MaLopHP', $request->MaLopHP);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('TenNhom', 'like', "%{$search}%")
                  ->orWhere('ThanhVien.MaSV', 'like', "%{$search}%")
                  ->orWhere('ThanhVien.HoTen', 'like', "%{$search}%");
            });
        }

        $nhoms = $query->orderBy('_id', 'desc')->paginate(10)->withQueryString();

        // Process 5-tier structure for each group
        foreach ($nhoms as $nhom) {
            // Tier 4: Topic info
            $dk = $nhom->getDangKyDeTai();
            $deTai = ($dk && !empty($dk['MaDeTai'])) ? DeTai::find($dk['MaDeTai']) : null;
            $nhom->setAttribute('deTai', $deTai);

            // Tier 5: Milestone reports & Overdue status calculation
            $baoCaos = collect($nhom->getBaoCaoList());
            $nhom->setAttribute('baoCaoList', $baoCaos);

            $isOverdue = false;
            $overdueReason = null;

            if ($deTai && $deTai->HanBaoCao && date('Y-m-d') > $deTai->HanBaoCao && $baoCaos->isEmpty()) {
                $isOverdue = true;
                $overdueReason = "Quá hạn báo cáo tiến độ (" . date('d/m/Y', strtotime($deTai->HanBaoCao)) . ") nhưng nhóm chưa nộp báo cáo!";
            }

            // Check if last report was rejected or needs adjustment
            $latestBc = $baoCaos->sortByDesc('NgayNop')->first();
            if ($latestBc) {
                $latestBcArr = (array) $latestBc;
                $nhanXets = (array) ($latestBcArr['NhanXetList'] ?? $latestBcArr['NhanXet'] ?? []);
                if (!empty($nhanXets)) {
                    $latestComment = (array) collect($nhanXets)->last();
                    if (isset($latestComment['DanhGia']) && in_array($latestComment['DanhGia'], ['Cần bổ sung', 'Chưa đạt'])) {
                        $isOverdue = true;
                        $overdueReason = "Báo cáo bị đánh giá: " . $latestComment['DanhGia'];
                    }
                }
            }

            $nhom->setAttribute('isOverdue', $isOverdue);
            $nhom->setAttribute('overdueReason', $overdueReason);
        }

        $hocKies = HocKy::orderBy('_id', 'desc')->get();
        $lopHocPhans = LopHocPhan::with(['monHoc', 'hocKy'])->orderBy('_id', 'desc')->get();

        return view('admin.tiendo.index', compact('nhoms', 'hocKies', 'lopHocPhans'));
    }
}
