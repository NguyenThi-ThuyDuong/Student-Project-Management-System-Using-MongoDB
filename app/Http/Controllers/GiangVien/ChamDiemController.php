<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\NhomDoAn;
use App\Models\GiangVien;
use App\Models\DeTai;
use App\Models\AuditLog;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChamDiemController extends Controller
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

    public function index() {
        $user = Auth::user();
        $gv = GiangVien::where('MaTK', (string) $user->_id)->first();
        if (!$gv) abort(403, 'Không tìm thấy thông tin giảng viên');

        $gvKeys = $this->getGiangVienKeys($gv, $user);

        $lhps = \App\Models\LopHocPhan::whereIn('MaGV', $gvKeys)
            ->orWhereIn('GiangVienMaGV', $gvKeys)->get();
        $lhpKeys = [];
        foreach ($lhps as $lhp) {
            if (!empty($lhp->_id)) $lhpKeys[] = (string) $lhp->_id;
            if (!empty($lhp->MaLopHP)) $lhpKeys[] = (string) $lhp->MaLopHP;
        }
        $lhpKeys = array_values(array_unique(array_filter($lhpKeys)));

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

        $nhoms = NhomDoAn::where(function ($q) use ($gvKeys, $lhpKeys, $deTaiKeys) {
                $q->whereIn('HuongDan.MaGV', $gvKeys)
                  ->orWhereIn('MaLopHP', $lhpKeys)
                  ->orWhereIn('DangKyDeTai.MaDeTai', $deTaiKeys);
            })
            ->with(['monHoc', 'hocKy'])
            ->get();

        return view('giangvien.chamdiem.index', compact('nhoms'));
    }

    public function store(Request $request, $maNhom) {
        $request->validate([
            'DiemBaoCao' => 'required|numeric|min:0|max:10',
            'DiemBaoVe' => 'required|numeric|min:0|max:10',
            'NhanXet' => 'nullable|string'
        ], [
            'DiemBaoCao.required' => 'Vui lòng nhập điểm báo cáo (0 - 10).',
            'DiemBaoVe.required' => 'Vui lòng nhập điểm bảo vệ (0 - 10).',
            'DiemBaoCao.min' => 'Điểm số phải từ 0 đến 10.',
            'DiemBaoCao.max' => 'Điểm số tối đa là 10.',
            'DiemBaoVe.min' => 'Điểm số phải từ 0 đến 10.',
            'DiemBaoVe.max' => 'Điểm số tối đa là 10.',
        ]);

        $user = Auth::user();
        $gv = GiangVien::where('MaTK', (string) $user->_id)->first();

        $diemTong = round(($request->DiemBaoCao * 0.5) + ($request->DiemBaoVe * 0.5), 2);

        try {
            $nhom = NhomDoAn::find($maNhom) ?? NhomDoAn::findOrFail($maNhom);

            $nhom->setChamDiem(
                (string) ($gv->_id ?? $user->_id),
                $request->DiemBaoCao,
                $request->DiemBaoVe,
                $diemTong,
                $request->NhanXet,
                'Cuối kỳ'
            );

            $nhom->update(['TrangThai' => 'Đã có điểm']);

            // Gửi thông báo đến nhóm
            $notiService = new NotificationService();
            $cdObj = (object) ['DiemTong' => $diemTong];
            $notiService->guiDiemMoi($nhom, $cdObj);

            AuditLog::log('cham_diem', 'ChamDiem', (string)$nhom->_id, ['MaNhom' => (string) $nhom->_id, 'DiemTong' => $diemTong]);

            return redirect()->back()->with('success', 'Chấm điểm cho nhóm "' . $nhom->TenNhom . '" thành công!');
        } catch (\Exception $e) {
            Log::error('Lỗi khi chấm điểm: ' . $e->getMessage());
            return redirect()->back()->withErrors('Có lỗi khi chấm điểm: ' . $e->getMessage());
        }
    }
}
