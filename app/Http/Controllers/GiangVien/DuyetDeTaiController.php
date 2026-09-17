<?php
namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\DeTai;
use App\Models\NhomDoAn;
use App\Models\LopHocPhan;
use App\Models\GiangVien;
use App\Models\AuditLog;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DuyetDeTaiController extends Controller
{
    public function index() {
        $user = Auth::user();
        
        if ($user->VaiTro === 'Admin') {
            $dangkys = NhomDoAn::whereNotNull('DangKyDeTai')
                ->orderBy('_id', 'desc')
                ->paginate(15);
        } else {
            $maTK = (string) $user->_id;
            $gv = GiangVien::where('MaTK', $maTK)->orWhere('_id', $maTK)->first();
            $gvIds = array_filter([(string)$user->_id, $gv ? (string)$gv->_id : null, $gv ? (string)$gv->MaGV : null]);

            $lhpList = LopHocPhan::whereIn('MaGV', $gvIds)->get();
            $lhpIds = [];
            foreach ($lhpList as $l) {
                $lhpIds[] = (string) $l->_id;
                if (!empty($l->MaLopHP)) $lhpIds[] = (string) $l->MaLopHP;
            }
            $lhpIds = array_values(array_unique(array_filter($lhpIds)));

            $queryDeTai = DeTai::where(function($q) use ($maTK, $gvIds, $lhpIds) {
                $q->whereIn('MaTK', $gvIds)->orWhereIn('MaGV', $gvIds);
                if (!empty($lhpIds)) {
                    $q->orWhereIn('MaLopHP', $lhpIds);
                }
            });

            $deTais = $queryDeTai->get();
            $deTaiKeys = [];
            foreach ($deTais as $dt) {
                $deTaiKeys[] = (string) $dt->_id;
                if (!empty($dt->MaDeTai)) $deTaiKeys[] = (string) $dt->MaDeTai;
                if (!empty($dt->MaDT)) $deTaiKeys[] = (string) $dt->MaDT;
            }
            $deTaiKeys = array_values(array_unique(array_filter($deTaiKeys)));

            $dangkys = NhomDoAn::where(function($q) use ($deTaiKeys, $lhpIds) {
                if (!empty($deTaiKeys)) {
                    $q->whereIn('DangKyDeTai.MaDeTai', $deTaiKeys);
                }
                if (!empty($lhpIds)) {
                    $q->orWhereIn('MaLopHP', $lhpIds);
                }
            })
            ->whereNotNull('DangKyDeTai')
            ->orderBy('_id', 'desc')
            ->paginate(15);
        }

        foreach ($dangkys as $nhom) {
            $dk = $nhom->getDangKyDeTai();
            $deTai = ($dk && !empty($dk['MaDeTai'])) ? DeTai::find($dk['MaDeTai']) : null;
            $lhp = $nhom->MaLopHP ? LopHocPhan::where('_id', $nhom->MaLopHP)->orWhere('MaLopHP', $nhom->MaLopHP)->first() : null;

            $nhom->MaDangKy = (string) $nhom->_id;
            $nhom->nhomDoAn = $nhom;
            $nhom->deTai = $deTai;
            $nhom->lopHocPhan = $lhp;
            $nhom->NgayDangKy = $dk['NgayDangKy'] ?? $nhom->NgayTao ?? '';
            $nhom->TrangThai = $dk['TrangThai'] ?? 'Chờ duyệt';
            $nhom->LyDoTuChoi = $dk['LyDoTuChoi'] ?? null;
            $nhom->setAttribute('thanhVienSVs', $nhom->getSinhVienThanhVien());
        }
        
        return view('giangvien.duyet.index', compact('dangkys'));
    }
    
    public function update(Request $request, $id) {
        $user = Auth::user();
        $nhom = NhomDoAn::findOrFail($id);
        $dk = $nhom->getDangKyDeTai();
        
        if (!$dk || empty($dk['MaDeTai'])) {
            return redirect()->back()->withErrors('Nhóm chưa thực hiện đăng ký đề tài.');
        }
        
        $deTai = DeTai::find($dk['MaDeTai']);
        $trangThai = $request->input('TrangThai');
        $lyDoTuChoi = $request->input('LyDoTuChoi');

        if (in_array($trangThai, ['Đã duyệt', 'Từ chối'])) {
            $dk['TrangThai'] = $trangThai;
            $dk['NgayDuyet'] = date('Y-m-d');
            $dk['LyDoTuChoi'] = ($trangThai === 'Từ chối') ? ($lyDoTuChoi ?? 'Không đạt yêu cầu') : null;
            $nhom->DangKyDeTai = $dk;
            
            $notiService = new NotificationService();
            $gv = GiangVien::where('MaTK', (string) $user->_id)->orWhere('_id', (string) $user->_id)->first();
            $gvIdToSet = $gv ? (string)($gv->MaTK ?? $gv->_id) : (string) $user->_id;

            if ($trangThai === 'Đã duyệt') {
                $nhom->TrangThai = 'Đã duyệt đề tài';
                $nhom->HuongDan = [
                    'MaGV' => $gvIdToSet,
                    'MaDeTai' => (string) $dk['MaDeTai'],
                    'NgayPhanCong' => date('Y-m-d'),
                    'TrangThai' => 'Đang hướng dẫn',
                ];

                if ($deTai) {
                    $deTai->update([
                        'TrangThaiPheDuyet' => 'Đã duyệt',
                        'TrangThai' => 'Đã duyệt',
                        'MaTK' => $gvIdToSet
                    ]);
                    $notiService->guiDeTaiDuocDuyet($nhom, $deTai);
                }

                AuditLog::log('duyet_de_tai', 'DangKyDeTai', $nhom->_id, [
                    'MaNhom' => (string) $nhom->_id,
                    'MaDeTai' => $dk['MaDeTai']
                ]);
            } else {
                $nhom->TrangThai = 'Đang hoạt động';

                if ($deTai && $deTai->LoaiDeTai === 'Sinh viên đề xuất') {
                    $deTai->update([
                        'TrangThaiPheDuyet' => 'Từ chối',
                        'TrangThai' => 'Từ chối',
                        'LyDoPheDuyet' => $lyDoTuChoi
                    ]);
                }

                if ($deTai) {
                    $notiService->guiDeTaiBiTuChoi($nhom, $deTai, $lyDoTuChoi ?? 'Không đạt yêu cầu');
                }
                AuditLog::log('tu_choi_de_tai', 'DangKyDeTai', $nhom->_id, [
                    'MaNhom' => (string) $nhom->_id,
                    'LyDo' => $lyDoTuChoi
                ]);
            }
            
            $nhom->save();
        }
        
        return redirect()->back()->with('success', 'Đã cập nhật trạng thái phê duyệt đề tài thành công!');
    }
}