<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\LopHocPhan;
use App\Models\PhanCongHuongDanLop;
use App\Models\DeTai;
use App\Models\NhomDoAn;
use App\Models\ThongBao;
use App\Models\GiangVien;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $maTK = (string) $user->_id;
        $gv = GiangVien::where('MaTK', $maTK)->orWhere('_id', $maTK)->first();
        $gvIds = array_filter([(string)$user->_id, $gv ? (string)$gv->_id : null, $gv ? (string)$gv->MaGV : null]);

        // 1. Lớp phụ trách
        $countLopHP = LopHocPhan::whereIn('MaGV', $gvIds)->count();
        $countLopHC = PhanCongHuongDanLop::whereIn('MaGV', $gvIds)->count();

        // Danh sách Lớp HP IDs
        $lhpList = LopHocPhan::whereIn('MaGV', $gvIds)->get();
        $myLhpKeys = [];
        foreach ($lhpList as $l) {
            $myLhpKeys[] = (string) $l->_id;
            if (!empty($l->MaLopHP)) $myLhpKeys[] = (string) $l->MaLopHP;
        }
        $myLhpKeys = array_values(array_unique(array_filter($myLhpKeys)));

        // 2. Đề tài thuộc giảng viên
        $deTais = DeTai::where(function($q) use ($maTK, $gvIds, $myLhpKeys) {
            $q->whereIn('MaTK', $gvIds)->orWhereIn('MaGV', $gvIds);
            if (!empty($myLhpKeys)) {
                $q->orWhereIn('MaLopHP', $myLhpKeys);
            }
        })->get();

        $totalDeTai = $deTais->count();
        $approvedDeTai = $deTais->filter(fn($dt) => $dt->TrangThaiPheDuyet === 'Đã duyệt')->count();
        $pendingDeTai = $deTais->filter(fn($dt) => in_array($dt->TrangThaiPheDuyet, ['Chờ Giáo vụ duyệt', 'Chờ duyệt']))->count();

        $myDeTaiKeys = [];
        foreach ($deTais as $dt) {
            $myDeTaiKeys[] = (string) $dt->_id;
            if (!empty($dt->MaDeTai)) $myDeTaiKeys[] = (string) $dt->MaDeTai;
            if (!empty($dt->MaDT)) $myDeTaiKeys[] = (string) $dt->MaDT;
        }
        $myDeTaiKeys = array_values(array_unique(array_filter($myDeTaiKeys)));

        // 3. Danh sách nhóm thuộc các Lớp HP hoặc Đề tài hoặc HuongDan của giảng viên
        $nhoms = NhomDoAn::where(function($q) use ($gvIds, $myLhpKeys, $myDeTaiKeys) {
            $q->whereIn('HuongDan.MaGV', $gvIds);
            if (!empty($myLhpKeys)) {
                $q->orWhereIn('MaLopHP', $myLhpKeys);
            }
            if (!empty($myDeTaiKeys)) {
                $q->orWhereIn('DangKyDeTai.MaDeTai', $myDeTaiKeys);
            }
        })->orderBy('_id', 'desc')->get();

        $totalNhoms = $nhoms->count();

        // Nhóm chờ duyệt đề tài
        $pendingApprovalGroups = $nhoms->filter(function($n) {
            $st = $n->getTrangThaiDangKy();
            return in_array($st, ['Chờ duyệt', 'Cho_duyet', 'Chờ Giáo vụ duyệt']);
        })->count();

        // Sản phẩm đã nộp & Đã chấm điểm
        $submittedProducts = $nhoms->filter(fn($n) => $n->getSanPhamList()->isNotEmpty())->count();
        $gradedGroups = $nhoms->filter(fn($n) => $n->hasChamDiem())->count();

        // Recent Notifications
        $thongBaos = ThongBao::where(function($q) use ($maTK, $gvIds) {
            $q->whereIn('MaTK', $gvIds)->orWhereNull('MaTK');
        })->orderBy('_id', 'desc')->limit(5)->get();

        // Recent Topics
        $recentDeTais = $deTais->sortByDesc('_id')->take(5)->values();

        return view('giangvien.dashboard', compact(
            'gv',
            'countLopHP',
            'countLopHC',
            'totalDeTai',
            'approvedDeTai',
            'pendingDeTai',
            'totalNhoms',
            'pendingApprovalGroups',
            'submittedProducts',
            'gradedGroups',
            'nhoms',
            'thongBaos',
            'recentDeTais'
        ));
    }
}
