<?php

namespace App\Http\Controllers;

use App\Models\SinhVien;
use App\Models\Lop;
use App\Models\Nganh;
use App\Models\TaiKhoan;
use App\Models\NhomDoAn;
use App\Http\Traits\HandlesExcelImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SinhVienController extends Controller
{
    use HandlesExcelImport;

    public function index(Request $request) {
        $query = SinhVien::with(['lop', 'nganh', 'taiKhoan']);

        if ($request->filled('search')) {
            $kw = $request->search;
            $query->where(function($q) use ($kw) {
                $q->where('HoTen', 'like', "%{$kw}%")
                  ->orWhere('MaSV', 'like', "%{$kw}%")
                  ->orWhere('Email', 'like', "%{$kw}%");
            });
        }

        if ($request->filled('MaLop')) {
            $query->where('MaLop', $request->MaLop);
        }

        if ($request->filled('MaNganh')) {
            $query->where('MaNganh', $request->MaNganh);
        }

        $sinhviens = $query->orderBy('_id', 'desc')->paginate(5)->withQueryString();
        return view('admin.sinhvien.index', compact('sinhviens'));
    }

    public function create() {
        $lops = Lop::all();
        $nganhs = Nganh::all();
        return view('admin.sinhvien.create', compact('lops', 'nganhs'));
    }

    public function store(Request $request) {
        $request->validate([
            'TenDangNhap' => 'required|string|max:50|unique:tai_khoan,TenDangNhap',
            'HoTen' => 'required|string|max:100',
            'MaLop' => 'required',
            'MaNganh' => 'nullable',
            'Email' => 'required|email|max:100|unique:sinh_vien,Email',
            'SoDienThoai' => ['required', 'string', 'regex:/^0[0-9]{8,10}$/', 'unique:sinh_vien,SoDienThoai']
        ], [
            'TenDangNhap.required' => 'Vui lòng nhập tên đăng nhập (MSSV).',
            'TenDangNhap.unique' => 'Mã SV / Tên đăng nhập đã tồn tại.',
            'HoTen.required' => 'Vui lòng nhập họ tên.',
            'MaLop.required' => 'Vui lòng chọn lớp hành chính.',
            'Email.required' => 'Vui lòng nhập email.',
            'Email.email' => 'Định dạng email không hợp lệ.',
            'Email.unique' => 'Email đã được sử dụng.',
            'SoDienThoai.required' => 'Vui lòng nhập số điện thoại.',
            'SoDienThoai.unique' => 'Số điện thoại đã được sử dụng.',
            'SoDienThoai.regex' => 'Số điện thoại phải bắt đầu bằng số 0 và có từ 9-11 chữ số hợp lệ.',
        ]);

        try {
            $tk = TaiKhoan::create([
                'TenDangNhap' => $request->TenDangNhap,
                'MatKhau' => Hash::make('123456'),
                'VaiTro' => 'Sinh viên',
                'TrangThai' => true
            ]);

            SinhVien::create([
                'MaSV' => strtoupper($request->TenDangNhap),
                'MaTK' => (string) $tk->_id,
                'TenDangNhap' => $request->TenDangNhap,
                'MaLop' => $request->MaLop,
                'MaNganh' => $request->MaNganh,
                'KhoaHoc' => $request->KhoaHoc ?? '12',
                'NgaySinh' => $request->NgaySinh,
                'HoTen' => $request->HoTen,
                'Email' => $request->Email,
                'SoDienThoai' => $request->SoDienThoai,
                'TrangThai' => 1
            ]);

            return redirect()->route('sinhvien.index')->with('success', 'Thêm sinh viên mới thành công!');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Thêm Sinh viên lỗi: ' . $e->getMessage());
            return redirect()->back()->withErrors('Không thể thêm sinh viên: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id) {
        $sinhvien = SinhVien::where('_id', $id)->orWhere('MaSV', $id)->firstOrFail();
        $nhoms = NhomDoAn::where('ThanhVien.MaSV', $sinhvien->MaSV)->orWhere('TruongNhom', $sinhvien->MaSV)->get();
        return view('admin.sinhvien.show', compact('sinhvien', 'nhoms'));
    }

    public function edit($id) {
        $sinhvien = SinhVien::where('_id', $id)->orWhere('MaSV', $id)->firstOrFail();
        $lops = Lop::all();
        $nganhs = Nganh::all();
        return view('admin.sinhvien.edit', compact('sinhvien', 'lops', 'nganhs'));
    }

    public function update(Request $request, $id) {
        $sinhvien = SinhVien::where('_id', $id)->orWhere('MaSV', $id)->firstOrFail();
        
        $request->validate([
            'HoTen' => 'required|string|max:100',
            'MaLop' => 'required',
            'Email' => 'required|email|max:100',
            'SoDienThoai' => ['required', 'string', 'regex:/^0[0-9]{8,10}$/']
        ], [
            'HoTen.required' => 'Vui lòng nhập họ tên.',
            'MaLop.required' => 'Vui lòng chọn lớp.',
            'Email.required' => 'Vui lòng nhập email.',
            'Email.email' => 'Định dạng email không hợp lệ.',
            'SoDienThoai.required' => 'Vui lòng nhập số điện thoại.',
            'SoDienThoai.regex' => 'Số điện thoại phải bắt đầu bằng số 0 và có từ 9-11 chữ số hợp lệ.',
        ]);

        try {
            $sinhvien->update([
                'HoTen' => $request->HoTen,
                'MaLop' => $request->MaLop,
                'MaNganh' => $request->MaNganh,
                'KhoaHoc' => $request->KhoaHoc ?? $sinhvien->KhoaHoc,
                'NgaySinh' => $request->NgaySinh ?? $sinhvien->NgaySinh,
                'Email' => $request->Email,
                'SoDienThoai' => $request->SoDienThoai,
            ]);

            if ($request->filled('TenDangNhap') && $sinhvien->taiKhoan) {
                $sinhvien->taiKhoan->update(['TenDangNhap' => $request->TenDangNhap]);
            }

            return redirect()->route('sinhvien.index')->with('success', 'Cập nhật sinh viên thành công!');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Cập nhật Sinh viên lỗi: ' . $e->getMessage());
            return redirect()->back()->withErrors('Không thể cập nhật sinh viên: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id) {
        try {
            $sinhvien = SinhVien::where('_id', $id)->orWhere('MaSV', $id)->firstOrFail();

            if (!empty($sinhvien->MaTK) || !empty($sinhvien->TenDangNhap)) {
                TaiKhoan::where('_id', $sinhvien->MaTK)
                    ->orWhere('TenDangNhap', $sinhvien->TenDangNhap)
                    ->orWhere('TenDangNhap', strtolower($sinhvien->MaSV))
                    ->delete();
            }

            $sinhvien->delete();
            return redirect()->route('sinhvien.index')->with('success', 'Xóa sinh viên thành công!');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Xóa Sinh viên lỗi: ' . $e->getMessage());
            return redirect()->back()->withErrors('Không thể xóa sinh viên: ' . $e->getMessage());
        }
    }

    public function importExcel(Request $request) {
        return $this->runImport($request, 'importSinhVien', [], 'Sinh viên');
    }

    public function toggleStatus($id) {
        try {
            $sinhvien = SinhVien::where('_id', $id)->orWhere('MaSV', $id)->firstOrFail();
            
            $taiKhoan = $sinhvien->taiKhoan;
            if (!$taiKhoan && (!empty($sinhvien->MaTK) || !empty($sinhvien->TenDangNhap))) {
                $taiKhoan = TaiKhoan::where('_id', $sinhvien->MaTK)
                    ->orWhere('TenDangNhap', $sinhvien->TenDangNhap)
                    ->orWhere('TenDangNhap', strtolower($sinhvien->MaSV))
                    ->first();
            }

            if ($taiKhoan) {
                $newStatus = !$taiKhoan->TrangThai;
                $taiKhoan->update(['TrangThai' => $newStatus]);
                $statusText = $newStatus ? 'Mở khóa' : 'Khóa';
                return redirect()->back()->with('success', "Đã {$statusText} tài khoản sinh viên {$sinhvien->HoTen} thành công!");
            } else {
                $tk = TaiKhoan::create([
                    'TenDangNhap' => $sinhvien->TenDangNhap ?? $sinhvien->MaSV,
                    'MatKhau' => \Illuminate\Support\Facades\Hash::make('123456'),
                    'VaiTro' => 'Sinh viên',
                    'TrangThai' => false
                ]);
                $sinhvien->update(['MaTK' => (string)$tk->_id]);
                return redirect()->back()->with('success', "Đã tạo và khóa tài khoản sinh viên {$sinhvien->HoTen} thành công!");
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Lỗi toggle status sinh viên: ' . $e->getMessage());
            return redirect()->back()->withErrors('Không thể thay đổi trạng thái tài khoản: ' . $e->getMessage());
        }
    }
}