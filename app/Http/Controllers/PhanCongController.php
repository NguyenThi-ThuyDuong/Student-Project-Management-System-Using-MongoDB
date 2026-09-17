<?php
namespace App\Http\Controllers;

use App\Models\PhanCongHuongDanLop;
use App\Models\LopHocPhan;
use App\Models\GiangVien;
use App\Models\Lop;
use App\Models\HocKy;
use App\Models\AuditLog;
use App\Http\Traits\HandlesExcelImport;
use Illuminate\Http\Request;

class PhanCongController extends Controller
{
    use HandlesExcelImport;

    protected function findPhanCong($id) {
        return PhanCongHuongDanLop::where('_id', $id)
            ->orWhere('MaPhanCong', $id)
            ->orWhere('MaPhanCong', (int)$id)
            ->orWhere('MaPhanCong', (string)$id)
            ->firstOrFail();
    }

    protected function findLopHocPhan($id) {
        return LopHocPhan::where('_id', $id)
            ->orWhere('MaLopHP', $id)
            ->firstOrFail();
    }

    public function index(Request $request) {
        $qHc = trim($request->input('q_hc', ''));
        $qHp = trim($request->input('q_hp', ''));

        $queryHc = PhanCongHuongDanLop::with(['giangVien.boMon', 'lop', 'hocKy']);
        if ($qHc !== '') {
            $queryHc->where(function($q) use ($qHc) {
                $q->whereHas('giangVien', function($gq) use ($qHc) {
                    $gq->where('HoTen', 'LIKE', "%{$qHc}%")->orWhere('MaGV', 'LIKE', "%{$qHc}%");
                })->orWhereHas('lop', function($lq) use ($qHc) {
                    $lq->where('TenLop', 'LIKE', "%{$qHc}%")->orWhere('MaLop', 'LIKE', "%{$qHc}%");
                });
            });
        }
        $phancongs = $queryHc->paginate(10, ['*'], 'page_hc')->withQueryString();

        $queryHp = LopHocPhan::with(['giangVien.boMon', 'monHoc', 'hocKy'])->orderBy('_id', 'desc');
        if ($qHp !== '') {
            $queryHp->where(function($q) use ($qHp) {
                $q->where('TenLopHP', 'LIKE', "%{$qHp}%")
                  ->orWhere('MaLopHP', 'LIKE', "%{$qHp}%")
                  ->orWhereHas('giangVien', function($gq) use ($qHp) {
                      $gq->where('HoTen', 'LIKE', "%{$qHp}%")->orWhere('MaGV', 'LIKE', "%{$qHp}%");
                  });
            });
        }
        $lophocphans = $queryHp->paginate(10, ['*'], 'page_hp')->withQueryString();

        $giangviens = GiangVien::with('boMon')->get();
        $lops = Lop::all();
        $hockys = HocKy::orderBy('_id', 'desc')->get();

        return view('admin.phancong.index', compact('phancongs', 'lophocphans', 'giangviens', 'lops', 'hockys'));
    }

    public function store(Request $request) {
        $loai = $request->input('LoaiPhanCong', 'lop_hanh_chinh');

        if ($loai === 'lop_hoc_phan') {
            $request->validate([
                'MaGV' => 'required|string',
                'MaLopHP' => 'required|string',
            ], [
                'MaGV.required' => 'Vui lòng chọn Giảng viên.',
                'MaLopHP.required' => 'Vui lòng chọn Lớp Học Phần.',
            ]);

            $lhp = $this->findLopHocPhan($request->MaLopHP);
            $gv = GiangVien::where('_id', $request->MaGV)->orWhere('MaGV', $request->MaGV)->firstOrFail();

            if ($lhp->MaGV && ((string)$lhp->MaGV === (string)$gv->MaGV || (string)$lhp->MaGV === (string)$gv->_id)) {
                return redirect()->to(route('phancong.index') . '?tab=hp')->withErrors("Giảng viên " . $gv->HoTen . " đã được phân công phụ trách Lớp Học Phần {$lhp->TenLopHP} từ trước rồi!");
            }

            // Kiểm tra trùng lịch giảng dạy nếu Lớp Học Phần có thông tin Thứ và Ca Học
            if (!empty($lhp->Thu) && !empty($lhp->CaHoc)) {
                $conflict = LopHocPhan::where(function($q) use ($gv) {
                    $q->where('MaGV', $gv->MaGV)->orWhere('MaGV', $gv->_id);
                })
                ->where('MaHocKy', $lhp->MaHocKy)
                ->where('Thu', $lhp->Thu)
                ->where('CaHoc', $lhp->CaHoc)
                ->where('_id', '!=', (string)$lhp->_id)
                ->first();

                if ($conflict) {
                    return redirect()->to(route('phancong.index') . '?tab=hp')
                        ->withErrors("CẢNH BÁO TRÙNG LỊCH: Giảng viên {$gv->HoTen} đã có lịch dạy Lớp Học Phần '{$conflict->TenLopHP}' vào {$lhp->Thu}, {$lhp->CaHoc} trong cùng học kỳ!");
                }
            }

            $lhp->update(['MaGV' => $gv->MaGV]);

            AuditLog::log('phan_cong_lhp', 'LopHocPhan', $lhp->_id, [
                'MaGV' => $gv->MaGV,
                'MaLopHP' => $lhp->MaLopHP ?? (string)$lhp->_id
            ]);

            return redirect()->to(route('phancong.index') . '?tab=hp')->with('success', "Cập nhật phân công Giảng viên {$gv->HoTen} phụ trách Lớp Học Phần {$lhp->TenLopHP} thành công!");
        }

        // Lớp Hành Chính
        $request->validate([
            'MaGV' => 'required|string',
            'MaLop' => 'required|string',
            'MaHocKy' => 'required|string'
        ], [
            'MaGV.required' => 'Vui lòng chọn Giảng viên.',
            'MaLop.required' => 'Vui lòng chọn Lớp Hành chính.',
            'MaHocKy.required' => 'Vui lòng chọn Học kỳ.',
        ]);

        $gv = GiangVien::where('_id', $request->MaGV)->orWhere('MaGV', $request->MaGV)->firstOrFail();
        $lop = Lop::where('_id', $request->MaLop)->orWhere('MaLop', $request->MaLop)->firstOrFail();
        $hk = HocKy::where('_id', $request->MaHocKy)->orWhere('MaHocKy', $request->MaHocKy)->orWhere('MaHK', $request->MaHocKy)->firstOrFail();

        // Check if class is already assigned to any lecturer in this semester
        $existing = PhanCongHuongDanLop::where(function($q) use ($lop) {
            $q->where('MaLop', $lop->MaLop)->orWhere('MaLop', $lop->_id);
        })->where(function($q) use ($hk) {
            $q->where('MaHocKy', $hk->MaHocKy)->orWhere('MaHocKy', $hk->_id);
        })->first();

        if ($existing) {
            if ((string)$existing->MaGV === (string)$gv->MaGV || (string)$existing->MaGV === (string)$gv->_id) {
                return redirect()->to(route('phancong.index') . '?tab=hc')->withErrors("Giảng viên {$gv->HoTen} đã được phân công hướng dẫn Lớp {$lop->TenLop} trong học kỳ này rồi!");
            }

            $existing->update([
                'MaGV' => $gv->MaGV,
                'NgayPhanCong' => date('Y-m-d')
            ]);

            AuditLog::log('cap_nhat_phan_cong_gv', 'PhanCongHuongDanLop', $existing->_id, [
                'MaGV' => $gv->MaGV,
                'MaLop' => $lop->MaLop,
                'MaHocKy' => $hk->MaHocKy
            ]);

            return redirect()->to(route('phancong.index') . '?tab=hc')->with('success', "Đã đổi phân công Giảng viên {$gv->HoTen} làm GV chủ nhiệm/hướng dẫn Lớp {$lop->TenLop}!");
        }

        $maxId = PhanCongHuongDanLop::max('MaPhanCong') ?? 0;
        $pc = PhanCongHuongDanLop::create([
            'MaPhanCong' => (int)$maxId + 1,
            'MaGV' => $gv->MaGV,
            'MaLop' => $lop->MaLop,
            'MaHocKy' => $hk->MaHocKy,
            'NgayPhanCong' => date('Y-m-d')
        ]);

        AuditLog::log('phan_cong_gv', 'PhanCongHuongDanLop', $pc->_id, [
            'MaGV' => $gv->MaGV,
            'MaLop' => $lop->MaLop,
            'MaHocKy' => $hk->MaHocKy
        ]);

        return redirect()->to(route('phancong.index') . '?tab=hc')->with('success', "Phân công Giảng viên {$gv->HoTen} làm GV chủ nhiệm/hướng dẫn Lớp {$lop->TenLop} thành công!");
    }

    public function unassignLhp($id) {
        $lhp = $this->findLopHocPhan($id);
        $lhp->update(['MaGV' => null]);
        AuditLog::log('huy_phan_cong_lhp', 'LopHocPhan', $lhp->_id, []);
        return redirect()->to(route('phancong.index') . '?tab=hp')->with('success', 'Hủy phân công Giảng viên cho Lớp Học Phần thành công!');
    }

    public function destroy($id) {
        $pc = $this->findPhanCong($id);
        AuditLog::log('xoa_phan_cong', 'PhanCongHuongDanLop', $pc->_id, ['MaGV' => $pc->MaGV, 'MaLop' => $pc->MaLop]);
        $pc->delete();
        return redirect()->to(route('phancong.index') . '?tab=hc')->with('success', 'Xóa phân công thành công!');
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importPhanCong', [], 'Phân Công Hướng Dẫn');
    }

    /**
     * Thống kê số lượng sinh viên / nhóm do từng Giảng viên hướng dẫn
     */
    public function thongKeGvhd(Request $request)
    {
        $giangViens = GiangVien::with('boMon')->get();
        $allNhoms = \App\Models\NhomDoAn::all();
        $allLhps  = LopHocPhan::with(['monHoc', 'hocKy'])->get();

        $stats = collect();

        foreach ($giangViens as $gv) {
            $gvTkId = (string) $gv->MaTK;
            $gvCode = (string) $gv->MaGV;
            $gvMongoId = (string) $gv->_id;

            // Lấy Lớp HP phụ trách
            $myLhps = $allLhps->filter(function($lhp) use ($gvCode, $gvMongoId) {
                return (string)$lhp->MaGV === $gvCode || (string)$lhp->MaGV === $gvMongoId;
            });

            $lhpIds = $myLhps->pluck('_id')->map(fn($id) => (string)$id)->toArray();

            // Lấy các Nhóm được hướng dẫn
            $myNhoms = $allNhoms->filter(function($nhom) use ($gvTkId, $gvCode, $gvMongoId, $lhpIds) {
                $huongDanGV = $nhom->HuongDan['MaGV'] ?? null;
                $isDirectSupervised = $huongDanGV && ($huongDanGV === $gvMongoId || $huongDanGV === $gvCode || $huongDanGV === $gvTkId);
                $isSectionSupervised = in_array((string)$nhom->MaLopHP, $lhpIds);
                return $isDirectSupervised || $isSectionSupervised;
            });

            // Lấy danh sách SV đang hướng dẫn
            $studentIds = collect();
            foreach ($myNhoms as $n) {
                foreach ($n->ThanhVien ?? [] as $tv) {
                    if (!empty($tv['MaSV'])) {
                        $studentIds->push((string)$tv['MaSV']);
                    }
                }
            }
            $studentIds = $studentIds->unique()->values();

            $stats->push((object)[
                'giangVien' => $gv,
                'lopHocPhans' => $myLhps,
                'nhomCount' => $myNhoms->count(),
                'sinhVienCount' => $studentIds->count(),
                'nhoms' => $myNhoms,
                'studentIds' => $studentIds
            ]);
        }

        // Filter selected lecturer
        $selectedGvId = $request->input('MaGV');
        $selectedStat = null;
        if ($selectedGvId) {
            $selectedStat = $stats->first(fn($s) => (string)$s->giangVien->_id === $selectedGvId || (string)$s->giangVien->MaGV === $selectedGvId);
        }

        return view('admin.phancong.thongke_gvhd', compact('stats', 'selectedStat', 'selectedGvId', 'giangViens'));
    }
}
