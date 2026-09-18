<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\NhomDoAn;
use App\Models\LopHocPhan;
use App\Models\HocKy;
use App\Models\GiangVien;
use App\Models\DeTai;
use App\Models\AuditLog;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DuyetBaoCaoController extends Controller
{
    public function index(Request $request) {
        $user = Auth::user();
        $maTK = (string) $user->_id;
        $gv = GiangVien::where('MaTK', $maTK)->orWhere('_id', $maTK)->first();
        $gvIds = array_filter([(string)$user->_id, $gv ? (string)$gv->_id : null, $gv ? (string)$gv->MaGV : null]);

        // 1. Lấy tất cả Lớp Học Phần của Giảng viên
        $lhpQuery = LopHocPhan::with(['monHoc', 'hocKy']);
        if ($user->VaiTro !== 'Admin') {
            $lhpQuery->whereIn('MaGV', $gvIds);
        }
        $lopHocPhans = $lhpQuery->orderBy('_id', 'desc')->get();
        $hocKies = HocKy::orderBy('_id', 'desc')->get();

        $lhpKeys = [];
        foreach ($lopHocPhans as $l) {
            $lhpKeys[] = (string) $l->_id;
            if (!empty($l->MaLopHP)) $lhpKeys[] = (string) $l->MaLopHP;
        }
        $lhpKeys = array_values(array_unique(array_filter($lhpKeys)));

        // 2. Lấy tất cả nhóm thuộc phụ trách của Giảng viên
        $allNhomsQuery = NhomDoAn::query();
        if ($user->VaiTro !== 'Admin') {
            $allNhomsQuery->where(function($q) use ($gvIds, $lhpKeys) {
                $q->whereIn('HuongDan.MaGV', $gvIds);
                if (!empty($lhpKeys)) {
                    $q->orWhereIn('MaLopHP', $lhpKeys);
                }
            });
        }

        // Áp dụng bộ lọc Học Kỳ / Lớp HP
        if ($request->filled('MaHocKy')) {
            $lhpsInHkModels = LopHocPhan::where('MaHocKy', $hkId)->orWhere('MaHK', $hkId)->get();
            $lhpsInHk = [];
            foreach ($lhpsInHkModels as $lh) {
                if (!empty($lh->_id)) $lhpsInHk[] = (string) $lh->_id;
                if (!empty($lh->MaLopHP)) $lhpsInHk[] = (string) $lh->MaLopHP;
            }
            $lhpsInHk = array_values(array_unique(array_filter($lhpsInHk)));
            $allNhomsQuery->whereIn('MaLopHP', $lhpsInHk);
        }

        if ($request->filled('MaLopHP')) {
            $val = $request->MaLopHP;
            $lhp = LopHocPhan::where('_id', $val)->orWhere('MaLopHP', $val)->first();
            $matchKeys = array_filter([$val, $lhp ? (string)$lhp->_id : null, $lhp ? $lhp->MaLopHP : null]);
            $allNhomsQuery->whereIn('MaLopHP', $matchKeys);
        }

        $allNhoms = $allNhomsQuery->orderBy('_id', 'desc')->get();

        // 3. Phân loại Nhóm chậm tiến độ
        $chamTienDoNhoms = collect();
        foreach ($allNhoms as $nhom) {
            $dk = $nhom->getDangKyDeTai();
            $bcCount = $nhom->getBaoCaoList()->count();
            $deTai = ($dk && !empty($dk['MaDeTai'])) ? DeTai::find($dk['MaDeTai']) : null;
            
            $isOverdueDeadline = $deTai && $deTai->HanBaoCao && (date('Y-m-d') > $deTai->HanBaoCao) && ($bcCount < 2);
            $hasNoReports = ($dk && ($dk['TrangThai'] ?? '') === 'Đã duyệt' && $bcCount === 0);

            if ($isOverdueDeadline || $hasNoReports) {
                $nhom->setAttribute('deTaiDangKy', $deTai);
                $nhom->setAttribute('lyDoChamTienDo', $isOverdueDeadline ? 'Đã quá hạn chót nộp báo cáo (' . date('d/m/Y', strtotime($deTai->HanBaoCao)) . ')' : 'Chưa nộp báo cáo tiến độ lần nào sau khi duyệt đề tài');
                $chamTienDoNhoms->push($nhom);
            }
        }

        // 4. Flatten danh sách báo cáo tiến độ từ các nhóm
        $baocaos = collect();
        foreach ($allNhoms as $nhom) {
            $dk = $nhom->getDangKyDeTai();
            $deTai = ($dk && !empty($dk['MaDeTai'])) ? DeTai::find($dk['MaDeTai']) : null;
            $lhp = $nhom->MaLopHP ? LopHocPhan::where('_id', $nhom->MaLopHP)->orWhere('MaLopHP', $nhom->MaLopHP)->first() : null;

            foreach ($nhom->getBaoCaoList() as $bc) {
                $bcArray = (array) $bc;
                $bcIdStr = (string) ($bcArray['_id'] ?? '');
                $baocaos->push((object) array_merge($bcArray, [
                    '_id' => $bcIdStr,
                    'MaBaoCao' => $bcIdStr,
                    'nhom' => $nhom,
                    'deTai' => $deTai,
                    'lopHocPhan' => $lhp,
                    'MaNhom' => (string) $nhom->_id,
                ]));
            }
        }

        // Lọc theo trạng thái nhận xét
        if ($request->filled('TrangThaiBaoCao')) {
            $stFilter = $request->TrangThaiBaoCao;
            if ($stFilter === 'chu_a_nhan_xet') {
                $baocaos = $baocaos->filter(fn($b) => empty($b->NhanXet));
            } elseif ($stFilter === 'da_nhan_xet') {
                $baocaos = $baocaos->filter(fn($b) => !empty($b->NhanXet));
            }
        }

        $baocaos = $baocaos->sortByDesc('NgayNop')->values();

        return view('giangvien.baocao.index', compact(
            'baocaos',
            'allNhoms',
            'chamTienDoNhoms',
            'lopHocPhans',
            'hocKies'
        ));
    }

    public function storeNhanXet(Request $request, $maBaoCao) {
        $request->validate([
            'NoiDung' => 'required|string'
        ], [
            'NoiDung.required' => 'Vui lòng nhập nội dung nhận xét / đánh giá tiến độ.'
        ]);

        $user = Auth::user();
        $gv = GiangVien::where('MaTK', (string) $user->_id)->orWhere('_id', (string) $user->_id)->first();
        $gvId = $gv ? (string)($gv->MaTK ?? $gv->_id) : (string) $user->_id;
        
        // Tìm nhóm chứa báo cáo này theo id, _id hoặc MaBaoCao
        $nhom = NhomDoAn::where('BaoCaoTienDo.id', (string) $maBaoCao)
            ->orWhere('BaoCaoTienDo._id', (string) $maBaoCao)
            ->orWhere('BaoCaoTienDo.MaBaoCao', (string) $maBaoCao)
            ->first();

        if (!$nhom) {
            // Thử tìm theo mã báo cáo trong tất cả nhóm
            $allNhoms = NhomDoAn::all();
            foreach ($allNhoms as $n) {
                if ($n->findBaoCao($maBaoCao)) {
                    $nhom = $n;
                    break;
                }
            }
        }

        if (!$nhom) {
            return redirect()->back()->withErrors('Không tìm thấy báo cáo tiến độ tương ứng.');
        }

        $nhom->addNhanXetToBaoCao($maBaoCao, $gvId, $request->NoiDung);

        // Gửi thông báo đến sinh viên trong nhóm
        $baoCao = $nhom->findBaoCao($maBaoCao);
        $notiService = new NotificationService();
        $bcObj = (object) ['LanBaoCao' => is_array($baoCao) ? ($baoCao['LanBaoCao'] ?? 1) : ($baoCao->LanBaoCao ?? 1)];
        $notiService->guiNhanXetMoi($nhom, $bcObj);

        AuditLog::log('nhan_xet_bao_cao', 'BaoCaoTienDo', $maBaoCao, ['MaNhom' => (string) $nhom->_id, 'NoiDung' => $request->NoiDung]);

        return redirect()->back()->with('success', 'Đã lưu nhận xét và phản hồi đến nhóm sinh viên thành công!');
    }
}
