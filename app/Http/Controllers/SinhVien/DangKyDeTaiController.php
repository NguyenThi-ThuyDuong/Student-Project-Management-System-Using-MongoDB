<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\DeTai;
use App\Models\NhomDoAn;
use App\Models\SinhVien;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DangKyDeTaiController extends Controller
{
    public function index(Request $request)
    {
        $sinhVien = SinhVien::where('MaTK', (string) Auth::user()->_id)->first();
        if (!$sinhVien) abort(403);

        $svKeys = array_values(array_unique(array_filter([
            (string) $sinhVien->_id,
            (string) $sinhVien->MaSV,
            strtoupper((string) $sinhVien->MaSV),
            strtolower((string) $sinhVien->MaSV)
        ])));

        // Tự động đóng các đề tài đã quá hạn đăng ký
        DeTai::where('TrangThai', 'Đang mở đăng ký')
            ->whereNotNull('HanDangKy')
            ->where('HanDangKy', '<', date('Y-m-d'))
            ->update(['TrangThai' => 'Đã đóng']);

        // 1. Lấy tất cả nhóm mà sinh viên này đang tham gia
        $myGroups = NhomDoAn::where(function($q) use ($svKeys) {
            $q->whereIn('ThanhVien.MaSV', $svKeys)
              ->orWhereIn('TruongNhom', $svKeys);
        })->get();

        // 2. Lấy tất cả Lớp Học Phần mà sinh viên này tham gia (hoặc có nhóm thuộc LHP đó)
        $enrolledLhps = \App\Models\LopHocPhan::where(function($q) use ($svKeys) {
            $q->whereIn('DanhSachSinhVien.MaSV', $svKeys)
              ->orWhereIn('DanhSachSinhVien.sinh_vien_id', $svKeys);
        })->get();

        $lhpKeysFromGroups = [];
        foreach ($myGroups as $g) {
            if ($g->MaLopHP) $lhpKeysFromGroups[] = (string) $g->MaLopHP;
            if ($g->lopHocPhan) {
                $lhpKeysFromGroups[] = (string) $g->lopHocPhan->_id;
                $lhpKeysFromGroups[] = (string) $g->lopHocPhan->MaLopHP;
            }
        }

        $allLhpKeys = array_values(array_unique(array_filter(array_merge(
            $enrolledLhps->pluck('_id')->map(fn($v) => (string)$v)->toArray(),
            $enrolledLhps->pluck('MaLopHP')->map(fn($v) => (string)$v)->toArray(),
            $lhpKeysFromGroups
        ))));

        $myLopHocPhans = \App\Models\LopHocPhan::with(['monHoc', 'hocKy', 'giangVien'])
            ->where(function($q) use ($allLhpKeys) {
                if (!empty($allLhpKeys)) {
                    $q->whereIn('_id', $allLhpKeys)->orWhereIn('MaLopHP', $allLhpKeys);
                }
            })
            ->orderBy('_id', 'desc')
            ->get();

        if ($myLopHocPhans->isEmpty()) {
            $myLopHocPhans = \App\Models\LopHocPhan::with(['monHoc', 'hocKy', 'giangVien'])
                ->where('TrangThai', 'Đang mở')
                ->orderBy('_id', 'desc')
                ->get();
        }

        // Đánh dấu Lớp Học Phần nào sinh viên đã có nhóm để ưu tiên tự chọn LHP đó trước
        $lhpWithGroup_id = null;
        foreach ($myLopHocPhans as $lhp) {
            $lhpIds = [(string)$lhp->_id, (string)$lhp->MaLopHP];
            $hasGroup = $myGroups->contains(function($g) use ($lhpIds, $lhp) {
                return in_array((string)$g->MaLopHP, $lhpIds)
                    || (string)$g->MaMon === (string)$lhp->MaMon;
            });
            $lhp->setAttribute('has_group', $hasGroup);
            if ($hasGroup && !$lhpWithGroup_id) {
                $lhpWithGroup_id = (string) $lhp->_id;
            }
        }

        // 3. Xác định Lớp Học Phần được chọn (Ưu tiên LHP sinh viên đã có nhóm)
        $selectedMaLopHP = $request->query('MaLopHP');
        if (!$selectedMaLopHP) {
            $selectedMaLopHP = $lhpWithGroup_id ?? ($myLopHocPhans->isNotEmpty() ? (string)$myLopHocPhans->first()->_id : null);
        }

        $currentLopHP = null;
        if ($selectedMaLopHP) {
            $currentLopHP = \App\Models\LopHocPhan::with(['monHoc', 'hocKy', 'giangVien'])
                ->where('_id', $selectedMaLopHP)
                ->orWhere('MaLopHP', $selectedMaLopHP)
                ->first();
        }

        // 4. Tìm Nhóm đồ án của sinh viên thuộc Lớp Học Phần được chọn
        $nhom = null;
        if ($currentLopHP) {
            $lhpMatchKeys = array_values(array_unique(array_filter([
                (string) $currentLopHP->_id,
                (string) $currentLopHP->MaLopHP
            ])));
            $monMatchKeys = array_values(array_unique(array_filter([
                (string) $currentLopHP->MaMon,
                (string) ($currentLopHP->monHoc?->_id)
            ])));

            $nhom = NhomDoAn::where(function($q) use ($lhpMatchKeys, $monMatchKeys) {
                    $q->whereIn('MaLopHP', $lhpMatchKeys);
                    if (!empty($monMatchKeys)) {
                        $q->orWhereIn('MaMon', $monMatchKeys);
                    }
                })
                ->where(function($q) use ($svKeys) {
                    $q->whereIn('ThanhVien.MaSV', $svKeys)
                      ->orWhereIn('TruongNhom', $svKeys);
                })
                ->first();
        }

        if (!$nhom) {
            $nhom = $myGroups->first();
        }

        // 5. Lấy Đề tài phù hợp với Lớp Học Phần
        $query = DeTai::where('TrangThaiPheDuyet', 'Đã duyệt')
            ->whereIn('TrangThai', ['Đang mở đăng ký', 'Chờ duyệt'])
            ->with(['monHoc', 'lop', 'hocKy', 'lopHocPhan']);

        if ($currentLopHP) {
            $lhpMatchKeys = array_values(array_unique(array_filter([
                (string) $currentLopHP->_id,
                (string) $currentLopHP->MaLopHP
            ])));
            $monMatchKeys = array_values(array_unique(array_filter([
                (string) $currentLopHP->MaMon,
                (string) ($currentLopHP->monHoc?->_id)
            ])));

            $query->where(function($q) use ($lhpMatchKeys, $monMatchKeys) {
                $q->whereIn('MaLopHP', $lhpMatchKeys);
                if (!empty($monMatchKeys)) {
                    $q->orWhereIn('MaMon', $monMatchKeys);
                }
            });
        } elseif ($sinhVien->MaLop) {
            $query->where('MaLop', $sinhVien->MaLop);
        }

        if ($request->filled('search')) {
            $query->where('TenDeTai', 'like', '%' . trim($request->search) . '%');
        }

        $detais = $query->orderBy('_id', 'desc')->paginate(5);

        foreach ($detais as $dt) {
            $gv = \App\Models\GiangVien::where('MaTK', $dt->MaTK)->first();
            $dt->setAttribute('giangVien', $gv);
        }

        // 6. Kiểm tra tình trạng đăng ký đề tài của nhóm
        $dangky = null;
        if ($nhom) {
            $dk = $nhom->getDangKyDeTai();
            if ($dk && !empty($dk['MaDeTai'])) {
                $deTai = DeTai::find($dk['MaDeTai']);
                if ($deTai) {
                    $gv = \App\Models\GiangVien::where('MaTK', $deTai->MaTK)->first();
                    $deTai->setAttribute('giangVien', $gv);
                }
                $dangky = (object) [
                    'MaDangKy' => $dk['MaDeTai'],
                    'MaNhom' => (string) $nhom->_id,
                    'MaDeTai' => $dk['MaDeTai'],
                    'NgayDangKy' => $dk['NgayDangKy'] ?? null,
                    'TrangThai' => $dk['TrangThai'] ?? null,
                    'LyDoTuChoi' => $dk['LyDoTuChoi'] ?? null,
                    'deTai' => $deTai,
                ];
            }
        }

        return view('sinhvien.dangky.index', compact(
            'detais', 'nhom', 'dangky', 'sinhVien', 'myLopHocPhans', 'selectedMaLopHP', 'currentLopHP'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'MaDeTai' => 'required|string'
        ], [
            'MaDeTai.required' => 'Vui lòng chọn đề tài muốn đăng ký.'
        ]);

        $sinhVien = SinhVien::where('MaTK', (string) Auth::user()->_id)->firstOrFail();
        $deTai = DeTai::findOrFail($request->MaDeTai);

        $svKeys = array_values(array_unique(array_filter([
            (string) $sinhVien->_id,
            (string) $sinhVien->MaSV,
            strtoupper((string) $sinhVien->MaSV),
            strtolower((string) $sinhVien->MaSV)
        ])));

        // Tìm Nhóm của sinh viên thuộc đúng Lớp Học Phần hoặc Môn học
        $nhom = null;
        if ($deTai->MaLopHP) {
            $lhp = \App\Models\LopHocPhan::where('_id', $deTai->MaLopHP)->orWhere('MaLopHP', $deTai->MaLopHP)->first();
            $lhpMatchKeys = $lhp ? array_values(array_unique(array_filter([(string)$lhp->_id, (string)$lhp->MaLopHP]))) : [$deTai->MaLopHP];

            $nhom = NhomDoAn::whereIn('MaLopHP', $lhpMatchKeys)
                ->where(function($q) use ($svKeys) {
                    $q->whereIn('ThanhVien.MaSV', $svKeys)
                      ->orWhereIn('TruongNhom', $svKeys);
                })
                ->first();
        }

        if (!$nhom && $deTai->MaMon) {
            $nhom = NhomDoAn::where('MaMon', $deTai->MaMon)
                ->where(function($q) use ($svKeys) {
                    $q->whereIn('ThanhVien.MaSV', $svKeys)
                      ->orWhereIn('TruongNhom', $svKeys);
                })
                ->first();
        }

        if (!$nhom) {
            $nhom = NhomDoAn::where(function($q) use ($svKeys) {
                $q->whereIn('TruongNhom', $svKeys)
                  ->orWhereIn('ThanhVien.MaSV', $svKeys);
            })->first();
        }

        if (!$nhom) {
            return redirect()->back()->withErrors('Bạn chưa tham gia nhóm nào! Vui lòng tạo hoặc tham gia nhóm trước khi đăng ký đề tài.');
        }

        if (!$nhom->isTruongNhom($sinhVien)) {
            return redirect()->back()->withErrors('Chỉ Trưởng nhóm mới có quyền đại diện đăng ký đề tài!');
        }

        // 1. Kiểm tra thành viên tối thiểu (tối thiểu 2 người)
        if ($nhom->countThanhVien() < 2) {
            return redirect()->back()->withErrors('Nhóm chưa đủ thành viên tối thiểu (cần tối thiểu 2 người) để đăng ký đề tài!');
        }

        // 2. Kiểm tra hạn đăng ký
        if ($deTai->HanDangKy && date('Y-m-d') > $deTai->HanDangKy) {
            $deTai->update(['TrangThai' => 'Đã đóng']);
            return redirect()->back()->withErrors("Đã quá hạn đăng ký cho đề tài này (Hạn chót: " . date('d/m/Y', strtotime($deTai->HanDangKy)) . "). Đề tài đã tự động đóng.");
        }

        // 3. Kiểm tra hai nhóm đăng ký cùng một đề tài
        $isRegistered = NhomDoAn::where('DangKyDeTai.MaDeTai', (string) $deTai->_id)
            ->whereIn('DangKyDeTai.TrangThai', ['Chờ duyệt', 'Đã duyệt'])
            ->where('_id', '!=', (string) $nhom->_id)
            ->exists();

        if ($isRegistered) {
            return redirect()->back()->withErrors('Đề tài này đã được một nhóm khác đăng ký.');
        }

        // 4. Kiểm tra nhóm đã đăng ký đề tài chưa
        $dangKyHienTai = $nhom->getDangKyDeTai();

        if ($dangKyHienTai && !empty($dangKyHienTai['MaDeTai'])) {
            if (($dangKyHienTai['TrangThai'] ?? '') === 'Từ chối') {
                $nhom->DangKyDeTai = [
                    'MaDeTai' => (string) $deTai->_id,
                    'NgayDangKy' => date('Y-m-d'),
                    'TrangThai' => 'Chờ duyệt',
                    'NgayDuyet' => null,
                    'LyDoTuChoi' => null,
                ];
                $nhom->TrangThai = 'Chờ duyệt đề tài';
                $nhom->save();

                AuditLog::log('dang_ky_lai_de_tai', 'DangKyDeTai', $nhom->_id, ['MaNhom' => (string) $nhom->_id, 'MaDeTai' => (string) $deTai->_id]);

                return redirect()->back()->with('success', 'Đăng ký lại đề tài thành công, vui lòng chờ giảng viên duyệt!');
            }
            return redirect()->back()->withErrors('Nhóm của bạn trong Lớp Học Phần này đã đăng ký một đề tài rồi!');
        }

        $nhom->setDangKyDeTai($deTai->_id, 'Chờ duyệt');
        $nhom->update(['TrangThai' => 'Chờ duyệt đề tài']);

        AuditLog::log('dang_ky_de_tai', 'DangKyDeTai', $nhom->_id, ['MaNhom' => (string) $nhom->_id, 'MaDeTai' => (string) $deTai->_id]);

        return redirect()->back()->with('success', 'Đăng ký đề tài thành công, vui lòng chờ giảng viên duyệt!');
    }

    public function destroy($id)
    {
        $sinhVien = SinhVien::where('MaTK', (string) Auth::user()->_id)->firstOrFail();

        $svKeys = array_values(array_unique(array_filter([
            (string) $sinhVien->_id,
            (string) $sinhVien->MaSV,
            strtoupper((string) $sinhVien->MaSV),
            strtolower((string) $sinhVien->MaSV)
        ])));

        // Tìm nhóm của sinh viên (ưu tiên nhóm mà sinh viên làm Trưởng nhóm)
        $nhom = NhomDoAn::whereIn('TruongNhom', $svKeys)->first();

        if (!$nhom) {
            $allNhoms = NhomDoAn::where(function($q) use ($svKeys) {
                $q->whereIn('TruongNhom', $svKeys)
                  ->orWhereIn('ThanhVien.MaSV', $svKeys);
            })->get();

            foreach ($allNhoms as $n) {
                if ($n->isTruongNhom($sinhVien)) {
                    $nhom = $n;
                    break;
                }
            }
        }

        if (!$nhom || !$nhom->isTruongNhom($sinhVien)) {
            return redirect()->back()->withErrors('Bạn phải là trưởng nhóm mới có quyền hủy đăng ký đề tài!');
        }

        $dk = $nhom->getDangKyDeTai();

        if ($dk && in_array($dk['TrangThai'] ?? '', ['Đã duyệt', 'Đã duyệt đề tài'])) {
            return redirect()->back()->withErrors('Đề tài đã được duyệt chính thức, không thể tự hủy đăng ký! Vui lòng liên hệ giảng viên nếu muốn đổi đề tài.');
        }

        // Nếu đây là đề tài tự đề xuất chưa duyệt, xóa bản ghi đề tài đó
        if (!empty($dk['MaDeTai'])) {
            $deTai = DeTai::find($dk['MaDeTai']);
            if ($deTai && $deTai->LoaiDeTai === 'Sinh viên đề xuất' && in_array($deTai->TrangThaiPheDuyet, ['Chờ Giáo vụ duyệt', 'Chờ duyệt', 'Chờ GV duyệt'])) {
                $deTai->delete();
            }
        }

        $nhom->clearDangKyDeTai();
        $nhom->update(['TrangThai' => 'Đang hoạt động']);

        AuditLog::log('huy_dang_ky_de_tai', 'DangKyDeTai', $id, ['MaNhom' => (string) $nhom->_id]);

        return redirect()->back()->with('success', 'Đã hủy đăng ký đề tài thành công! Bạn có thể chọn đề tài mới.');
    }

    /**
     * Sinh viên tự đề xuất đề tài riêng cho nhóm
     */
    public function tuDeXuat(Request $request)
    {
        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', (string) $user->_id)->first();
        if (!$sinhVien) {
            $sinhVien = SinhVien::where('Email', 'like', $user->TenDangNhap . '%')->orWhere('MaSV', $user->TenDangNhap)->first();
        }
        if (!$sinhVien) abort(403, 'Không tìm thấy thông tin hồ sơ Sinh viên.');

        $request->validate([
            'TenDeTai' => 'required|string|max:200',
            'MaLopHP' => 'required|string',
            'MoTa' => 'nullable|string',
            'YeuCau' => 'nullable|string',
            'file_de_cuong' => 'required|file|mimes:pdf,doc,docx,rar,zip|max:20480'
        ], [
            'TenDeTai.required' => 'Vui lòng nhập tên đề tài muốn tự đề xuất.',
            'MaLopHP.required' => 'Vui lòng chọn Lớp Học Phần tương ứng.',
            'file_de_cuong.required' => 'Vui lòng nộp file đề cương chi tiết của đề tài.',
            'file_de_cuong.mimes' => 'File đề cương phải có định dạng: PDF, DOC, DOCX, RAR, ZIP.',
            'file_de_cuong.max' => 'Kích thước file đề cương không được vượt quá 20MB.'
        ]);

        $lhp = \App\Models\LopHocPhan::where('_id', $request->MaLopHP)->orWhere('MaLopHP', $request->MaLopHP)->firstOrFail();

        $lhpMatchKeys = array_values(array_unique(array_filter([
            (string)$lhp->_id,
            (string)$lhp->MaLopHP,
            (string)$request->MaLopHP
        ])));

        $svKeys = array_values(array_unique(array_filter([
            (string)($sinhVien->_id ?? ''),
            (string)($sinhVien->MaSV ?? ''),
            strtoupper((string)($sinhVien->MaSV ?? '')),
            strtolower((string)($sinhVien->MaSV ?? '')),
        ])));

        // Tìm nhóm của sinh viên trong Lớp HP này
        $nhom = NhomDoAn::whereIn('MaLopHP', $lhpMatchKeys)
            ->where(function($q) use ($svKeys) {
                $q->whereIn('ThanhVien.MaSV', $svKeys)
                  ->orWhereIn('TruongNhom', $svKeys);
            })
            ->first();

        if (!$nhom && $lhp->MaMon) {
            $nhom = NhomDoAn::where('MaMon', $lhp->MaMon)
                ->where(function($q) use ($svKeys) {
                    $q->whereIn('ThanhVien.MaSV', $svKeys)
                      ->orWhereIn('TruongNhom', $svKeys);
                })
                ->first();
        }

        if (!$nhom) {
            return redirect()->back()->withErrors('Bạn cần phải tạo nhóm hoặc gia nhập nhóm trong Lớp HP này trước khi đề xuất đề tài!');
        }

        if (!$nhom->isTruongNhom($sinhVien)) {
            return redirect()->back()->withErrors('Chỉ Trưởng nhóm mới có quyền nộp Đề xuất đề tài riêng!');
        }

        // Xử lý upload file đề cương
        $filePath = null;
        if ($request->hasFile('file_de_cuong')) {
            $file = $request->file('file_de_cuong');
            $fileName = time() . '_decuong_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
            $destinationPath = public_path('uploads/decuong');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            $file->move($destinationPath, $fileName);
            $filePath = 'uploads/decuong/' . $fileName;
        }

        // Lấy giảng viên phụ trách Lớp Học Phần này
        $gvLhp = $lhp->getGiangVienModelAttribute();
        $gvMaTK = $gvLhp ? ($gvLhp->MaTK ?? (string)$gvLhp->_id) : (string) Auth::user()->_id;

        // Tạo Đề tài dạng "Sinh viên đề xuất"
        $dt = DeTai::create([
            'MaTK' => (string) $gvMaTK,
            'SinhVienDeXuat_id' => (string) Auth::user()->_id,
            'MaMon' => (string) $lhp->MaMon,
            'MaHocKy' => (string) $lhp->MaHocKy,
            'MaLopHP' => (string) $lhp->_id,
            'TenDeTai' => '[TỰ ĐỀ XUẤT] ' . $request->TenDeTai,
            'MoTa' => $request->MoTa,
            'YeuCau' => $request->YeuCau,
            'FileTaiLieu' => $filePath,
            'LoaiDeTai' => 'Sinh viên đề xuất',
            'TrangThaiPheDuyet' => 'Chờ Giáo vụ duyệt',
            'TrangThai' => 'Chờ duyệt',
            'NhomTuDeXuat_id' => (string) $nhom->_id,
            'NgayTao' => date('Y-m-d')
        ]);

        // Cập nhật thông tin đăng ký đề tài của Nhóm
        $nhom->setDangKyDeTai($dt->_id, 'Chờ Giáo vụ duyệt');
        $nhom->update(['TrangThai' => 'Chờ duyệt đề tài']);

        AuditLog::log('tu_de_xuat_de_tai', 'DeTai', $dt->_id, ['TenDeTai' => $dt->TenDeTai, 'MaNhom' => (string)$nhom->_id]);

        return redirect()->back()->with('success', 'Đề xuất đề tài riêng và file đề cương chi tiết đã được gửi đi thành công! Vui lòng chờ Giáo vụ & Giảng viên phê duyệt.');
    }
}