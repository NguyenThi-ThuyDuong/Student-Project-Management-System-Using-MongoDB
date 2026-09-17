<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\NhomDoAn;
use App\Models\SinhVien;
use App\Models\HocKy;
use App\Models\MonHoc;
use App\Models\DeTai;
use App\Models\LopHocPhan;
use App\Models\AuditLog;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NhomController extends Controller
{
    private function getStudentKeys($sinhVien)
    {
        if (!$sinhVien) return [];
        return array_values(array_unique(array_filter([
            (string) $sinhVien->_id,
            (string) $sinhVien->MaSV,
            strtoupper((string) $sinhVien->MaSV),
            strtolower((string) $sinhVien->MaSV),
        ])));
    }

    public function index()
    {
        $user = Auth::user();
        $sinhVien = SinhVien::with('lop')->where('MaTK', (string) $user->_id)->first();
        if (!$sinhVien) {
            $sinhVien = SinhVien::where('Email', 'like', $user->TenDangNhap . '%')->first();
            if ($sinhVien) {
                $sinhVien->update(['MaTK' => (string) $user->_id]);
            } else {
                abort(404, "Không tìm thấy hồ sơ Sinh Viên tương ứng với tài khoản {$user->TenDangNhap}.");
            }
        }

        $svKeys = $this->getStudentKeys($sinhVien);

        // 1. Danh sách tất cả các nhóm mà sinh viên đang tham gia
        $nhoms = NhomDoAn::where(function($q) use ($svKeys) {
            $q->whereIn('ThanhVien.MaSV', $svKeys)
              ->orWhereIn('TruongNhom', $svKeys);
        })->get();

        // Nạp thêm thông tin relationships
        foreach ($nhoms as $n) {
            $n->load(['monHoc', 'hocKy', 'lopHocPhan']);
            // Nạp thông tin sinh viên cho thành viên
            $thanhVienSVs = $n->getSinhVienThanhVien();
            $n->setAttribute('thanhVienSVs', $thanhVienSVs);

            // Nạp thông tin giảng viên lớp học phần
            if ($n->lopHocPhan) {
                $n->lopHocPhan->load('giangVien');
            }

            // Nạp thông tin đề tài đã đăng ký
            $dk = $n->getDangKyDeTai();
            if ($dk && !empty($dk['MaDeTai'])) {
                $deTai = DeTai::where('MaDeTai', $dk['MaDeTai'])->orWhere('_id', $dk['MaDeTai'])->first();
                $n->setAttribute('deTaiDangKy', $deTai);
            }
        }

        // 2. Lời mời tham gia nhóm đang chờ xác nhận
        $nhomCoLoiMoi = NhomDoAn::whereIn('LoiMoi.MaSV_DuocMoi', $svKeys)
            ->where('LoiMoi.TrangThai', 'cho_xac_nhan')
            ->get();

        $loiMois = collect();
        foreach ($nhomCoLoiMoi as $nhom) {
            foreach ($nhom->getLoiMoiList() as $lm) {
                if (in_array((string)($lm['MaSV_DuocMoi'] ?? ''), $svKeys) && ($lm['TrangThai'] ?? '') === 'cho_xac_nhan') {
                    $svMoi = SinhVien::where('_id', $lm['MaSV_Moi'])->orWhere('MaSV', $lm['MaSV_Moi'])->first();
                    $loiMois->push((object)[
                        'id' => $lm['_id'] ?? '',
                        '_id' => $lm['_id'] ?? '',
                        'nhomDoAn' => $nhom,
                        'sinhVienMoi' => $svMoi,
                        'TrangThai' => $lm['TrangThai'],
                        'NgayMoi' => $lm['NgayMoi'] ?? null,
                    ]);
                }
            }
        }

        $hockys = HocKy::orderBy('_id', 'desc')->get();
        $currentHocKy = $hockys->first();
        $currentHocKyId = $currentHocKy ? (string) $currentHocKy->_id : null;

        // Danh sách Lớp Học Phần mà sinh viên được thêm vào
        $rawLhps = LopHocPhan::with(['monHoc', 'hocKy', 'giangVien'])
            ->where('TrangThai', 'Đang mở')
            ->orderBy('_id', 'desc')
            ->get();

        // Lọc các Lớp Học Phần mà sinh viên này thực sự tham gia (trong DanhSachSinhVien)
        $myLhps = $rawLhps->filter(function ($lhp) use ($svKeys) {
            foreach ($svKeys as $k) {
                if ($lhp->hasSinhVien($k)) return true;
            }
            return false;
        });

        // Nếu sinh viên có lớp học phần được phân công, ưu tiên dùng danh sách đó, nếu chưa có thì hiển thị tất cả LHP mở
        $allLopHocPhans = $myLhps->isNotEmpty() ? $myLhps : $rawLhps;
        $allLopHocPhans->each(function ($lhp) use ($svKeys) {
            $isEnrolled = false;
            foreach ($svKeys as $k) {
                if ($lhp->hasSinhVien($k)) {
                    $isEnrolled = true;
                    break;
                }
            }
            $lhp->setAttribute('is_enrolled', $isEnrolled);
        });

        $allClassMonHocs = MonHoc::all();
        $availableMonHocs = $allClassMonHocs;

        return view('sinhvien.nhom.index', compact('nhoms', 'sinhVien', 'loiMois', 'hockys', 'availableMonHocs', 'allLopHocPhans', 'currentHocKyId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenNhom' => 'required|string|max:100',
            'MaLopHP' => 'nullable|string',
            'MaMon' => 'required_without:MaLopHP|nullable|string',
            'MaHocKy' => 'required_without:MaLopHP|nullable|string'
        ], [
            'TenNhom.required' => 'Vui lòng nhập tên nhóm đồ án.',
            'MaMon.required_without' => 'Vui lòng chọn môn học.',
            'MaHocKy.required_without' => 'Vui lòng chọn học kỳ.'
        ]);

        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', (string) $user->_id)->first();
        if (!$sinhVien) abort(403, 'Không tìm thấy thông tin sinh viên.');

        $svKeys = $this->getStudentKeys($sinhVien);

        $maMon = $request->MaMon;
        $maHocKy = $request->MaHocKy;
        $maLopHP = $request->MaLopHP;

        if ($maLopHP) {
            $lhp = LopHocPhan::where('_id', $maLopHP)->orWhere('MaLopHP', $maLopHP)->first();
            if ($lhp) {
                $maMon = (string) $lhp->MaMon;
                $maHocKy = (string) $lhp->MaHocKy;
            }
        }

        // Kiểm tra trùng nhóm đang hoạt động trong môn học
        if ($maMon && $maHocKy) {
            $alreadyInGroup = NhomDoAn::where(function($q) use ($svKeys) {
                $q->whereIn('ThanhVien.MaSV', $svKeys)
                  ->orWhereIn('TruongNhom', $svKeys);
            })
            ->where('MaMon', $maMon)
            ->where('MaHocKy', $maHocKy)
            ->whereNotIn('TrangThai', ['Đã hoàn thành', 'Đã chấm điểm'])
            ->where(function ($q) {
                $q->whereNull('ChamDiem')
                  ->orWhere('ChamDiem', null);
            })
            ->exists();

            if ($alreadyInGroup) {
                return redirect()->back()->withErrors('Bạn đang có một nhóm đồ án ĐANG HOẠT ĐỘNG cho môn học này trong học kỳ đã chọn!')->withInput();
            }
        }

        try {
            $nhom = NhomDoAn::create([
                'TenNhom' => $request->TenNhom,
                'MaMon' => $maMon,
                'MaHocKy' => $maHocKy,
                'MaLopHP' => $maLopHP,
                'TruongNhom' => (string) $sinhVien->MaSV,
                'TrangThai' => 'Đang hoạt động',
                'ThanhVien' => [
                    [
                        'MaSV' => (string) $sinhVien->MaSV,
                        'VaiTro' => 'Trưởng nhóm',
                        'TrangThai' => 'da_tham_gia',
                        'created_at' => now()->toDateTimeString(),
                    ]
                ],
                'DangKyDeTai' => null,
                'HuongDan' => null,
                'LoiMoi' => [],
                'BaoCaoTienDo' => [],
                'SanPham' => [],
                'ChamDiem' => null,
            ]);

            AuditLog::log('tao_nhom', 'NhomDoAn', $nhom->_id, ['TenNhom' => $nhom->TenNhom, 'MaMon' => $maMon]);

            return redirect()->route('sinhvien.nhom.index')->with('success', "Tạo nhóm '{$nhom->TenNhom}' thành công!");
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Lỗi khi tạo nhóm: ' . $e->getMessage());
        }
    }

    public function searchSV(Request $request)
    {
        $term = trim($request->get('q', ''));
        $maNhom = $request->get('maNhom');
        $svCurrent = SinhVien::where('MaTK', (string) Auth::user()->_id)->first();

        if (!$svCurrent || empty($term)) {
            return response()->json([]);
        }

        $svKeys = $this->getStudentKeys($svCurrent);

        $nhom = $maNhom ? NhomDoAn::find($maNhom) : null;
        $maMon = $nhom ? $nhom->MaMon : null;
        $maHocKy = $nhom ? $nhom->MaHocKy : null;

        $query = SinhVien::with('taiKhoan')
            ->whereNotIn('_id', $svKeys)
            ->whereNotIn('MaSV', $svKeys);

        // Lọc theo Lớp Học Phần hoặc Lớp
        if ($nhom && $nhom->MaLopHP) {
            $lhp = LopHocPhan::where('_id', $nhom->MaLopHP)->orWhere('MaLopHP', $nhom->MaLopHP)->first();
            if ($lhp) {
                $svLhpIds = $lhp->getSinhVienIds();
                if ($svLhpIds->isNotEmpty()) {
                    $query->where(function($q) use ($svLhpIds) {
                        $q->whereIn('_id', $svLhpIds->toArray())
                          ->orWhereIn('MaSV', $svLhpIds->toArray());
                    });
                } else {
                    $query->where('MaLop', $svCurrent->MaLop);
                }
            }
        } else {
            $query->where('MaLop', $svCurrent->MaLop);
        }

        // Tìm kiếm theo tên hoặc MSSV
        $query->where(function ($q) use ($term) {
            $q->where('HoTen', 'like', "%{$term}%")
              ->orWhere('MaSV', 'like', "%{$term}%");
        });

        $results = $query->limit(10)->get()->map(function ($sv) {
            $mssv = $sv->MaSV ?? ($sv->taiKhoan->TenDangNhap ?? '');
            return [
                'id' => (string) $sv->MaSV,
                'mssv' => $mssv,
                'name' => $sv->HoTen,
                'text' => "{$sv->HoTen} ({$mssv})"
            ];
        });

        return response()->json($results);
    }

    public function moiThanhVien(Request $request)
    {
        $request->validate([
            'MaNhom' => 'required|string',
            'TenDangNhap_Them' => 'required|string'
        ], [
            'MaNhom.required' => 'Vui lòng chọn nhóm.',
            'TenDangNhap_Them.required' => 'Vui lòng nhập MSSV của sinh viên.'
        ]);

        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', (string) $user->_id)->firstOrFail();
        $svKeys = $this->getStudentKeys($sinhVien);

        $nhom = NhomDoAn::where('_id', $request->MaNhom)->orWhere('MaNhom', $request->MaNhom)->firstOrFail();

        if (!$nhom->isTruongNhom($sinhVien)) {
            return redirect()->back()->withErrors('Chỉ trưởng nhóm mới có quyền mời thành viên!');
        }

        $maxMembers = $nhom->getSoThanhVienToiDa();
        if ($nhom->countThanhVien() >= $maxMembers) {
            return redirect()->back()->withErrors("Nhóm đã đạt số lượng tối đa {$maxMembers} thành viên theo quy định!");
        }

        $targetMssv = trim($request->TenDangNhap_Them);
        $svThem = SinhVien::where('MaSV', $targetMssv)
            ->orWhere('_id', $targetMssv)
            ->orWhereHas('taiKhoan', function($q) use ($targetMssv) {
                $q->where('TenDangNhap', $targetMssv);
            })->first();

        if (!$svThem) {
            return redirect()->back()->withErrors('Không tìm thấy sinh viên với mã vừa nhập.');
        }

        $targetKeys = $this->getStudentKeys($svThem);

        if (array_intersect($svKeys, $targetKeys)) {
            return redirect()->back()->withErrors('Bạn không thể tự mời chính mình!');
        }

        $loiMoiId = $nhom->addLoiMoi((string)$sinhVien->MaSV, (string)$svThem->MaSV);

        $notiService = new NotificationService();
        if ($svThem->MaTK) {
            $notiService->guiLoiMoiNhom($svThem, $nhom, $sinhVien);
        }

        AuditLog::log('moi_thanh_vien', 'LoiMoiNhom', $nhom->_id, ['MaSV_DuocMoi' => (string) $svThem->MaSV]);

        return redirect()->back()->with('success', "Đã gửi lời mời gia nhập nhóm '{$nhom->TenNhom}' đến sinh viên {$svThem->HoTen}!");
    }

    public function xacNhanLoiMoi($id)
    {
        $sinhVien = SinhVien::where('MaTK', (string) Auth::user()->_id)->firstOrFail();
        $svKeys = $this->getStudentKeys($sinhVien);

        $nhom = NhomDoAn::whereIn('LoiMoi.MaSV_DuocMoi', $svKeys)->firstOrFail();

        $loiMoi = $nhom->findLoiMoi($id);
        if (!$loiMoi || ($loiMoi['TrangThai'] ?? '') !== 'cho_xac_nhan') {
            return redirect()->back()->withErrors('Lời mời này đã được xử lý trước đó.');
        }

        $maxMembers = $nhom->getSoThanhVienToiDa();
        if ($nhom->countThanhVien() >= $maxMembers) {
            $nhom->updateLoiMoi($id, ['TrangThai' => 'da_tu_choi', 'NgayPhanHoi' => now()->toDateTimeString()]);
            return redirect()->back()->withErrors("Không thể gia nhập: Nhóm đã đủ {$maxMembers} thành viên tối đa theo quy định!");
        }

        $nhom->addThanhVien((string)$sinhVien->MaSV, 'Thành viên', 'da_tham_gia');
        $nhom->updateLoiMoi($id, ['TrangThai' => 'da_chap_nhan', 'NgayPhanHoi' => now()->toDateTimeString()]);

        $notiService = new NotificationService();
        $notiService->guiChapNhanLoiMoi($nhom, $sinhVien);

        AuditLog::log('chap_nhan_loi_moi', 'LoiMoiNhom', $id, ['MaNhom' => (string) $nhom->_id]);

        return redirect()->back()->with('success', "Bạn đã tham gia nhóm '{$nhom->TenNhom}' thành công!");
    }

    public function tuChoiLoiMoi($id)
    {
        $sinhVien = SinhVien::where('MaTK', (string) Auth::user()->_id)->firstOrFail();
        $svKeys = $this->getStudentKeys($sinhVien);

        $nhom = NhomDoAn::whereIn('LoiMoi.MaSV_DuocMoi', $svKeys)->firstOrFail();

        $nhom->updateLoiMoi($id, [
            'TrangThai' => 'da_tu_choi',
            'NgayPhanHoi' => now()->toDateTimeString()
        ]);

        $notiService = new NotificationService();
        $notiService->guiTuChoiLoiMoi($nhom, $sinhVien);

        AuditLog::log('tu_choi_loi_moi', 'LoiMoiNhom', $id, ['MaNhom' => (string) $nhom->_id]);

        return redirect()->back()->with('success', 'Đã từ chối lời mời tham gia nhóm.');
    }
}