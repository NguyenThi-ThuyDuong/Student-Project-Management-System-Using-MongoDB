<?php
namespace App\Http\Controllers;

use App\Http\Traits\HandlesExcelImport;
use App\Models\GiangVien;
use App\Models\BoMon;
use App\Models\TaiKhoan;
use App\Models\LopHocPhan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class GiangVienController extends Controller
{
    use HandlesExcelImport;
    public function index(Request $request) {
        $query = GiangVien::with(['boMon', 'taiKhoan']);

        if ($request->filled('search')) {
            $kw = $request->search;
            $query->where(function($q) use ($kw) {
                $q->where('HoTen', 'like', "%{$kw}%")
                  ->orWhere('MaGV', 'like', "%{$kw}%")
                  ->orWhere('Email', 'like', "%{$kw}%");
            });
        }

        if ($request->filled('MaBoMon')) {
            $query->where('MaBoMon', $request->MaBoMon);
        }

        if ($request->filled('HocVi')) {
            $query->where('HocVi', $request->HocVi);
        }

        $giangviens = $query->orderBy('_id', 'desc')->paginate(5)->withQueryString();
        return view('admin.giangvien.index', compact('giangviens'));
    }
    public function create() {
        $bomons = BoMon::all();
        return view('admin.giangvien.create', compact('bomons'));
    }
    public function store(Request $request) {
        $request->validate([
            'TenDangNhap' => 'required|string|max:50|unique:tai_khoan,TenDangNhap',
            'HoTen' => 'required|string|max:100',
            'HocVi' => 'required|string|max:50',
            'MaBoMon' => 'required|exists:bo_mon,MaBoMon',
            'Email' => 'required|email|max:100|unique:giang_vien,Email',
            'SoDienThoai' => ['required', 'string', 'regex:/^0[0-9]{8,10}$/', 'unique:giang_vien,SoDienThoai']
        ], [
            'TenDangNhap.required' => 'Vui lòng nhập tên đăng nhập.',
            'TenDangNhap.unique' => 'Tên đăng nhập đã tồn tại.',
            'HoTen.required' => 'Vui lòng nhập họ tên.',
            'HocVi.required' => 'Vui lòng nhập học vị.',
            'MaBoMon.required' => 'Vui lòng chọn bộ môn.',
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
                'VaiTro' => 'Giảng viên',
                'TrangThai' => true
            ]);
            GiangVien::create([
                'MaGV' => strtoupper($request->TenDangNhap),
                'MaTK' => (string) $tk->_id,
                'TenDangNhap' => $request->TenDangNhap,
                'MaBoMon' => $request->MaBoMon,
                'HoTen' => $request->HoTen,
                'Email' => $request->Email,
                'SoDienThoai' => $request->SoDienThoai,
                'HocVi' => $request->HocVi
            ]);
            return redirect()->route('giangvien.index')->with('success', 'Thêm giảng viên thành công!');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Thêm Giảng viên lỗi: ' . $e->getMessage());
            return redirect()->back()->withErrors('Không thể thêm giảng viên: ' . $e->getMessage())->withInput();
        }
    }
    private function findGiangVien($id) {
        return GiangVien::where('_id', $id)->orWhere('MaGV', $id)->orWhere('TenDangNhap', $id)->firstOrFail();
    }

    public function show($id) {
        return $this->edit($id);
    }

    public function edit($id) {
        $giangvien = $this->findGiangVien($id);
        $bomons = BoMon::all();
        return view('admin.giangvien.edit', compact('giangvien', 'bomons'));
    }

    public function update(Request $request, $id) {
        $giangvien = $this->findGiangVien($id);
        
        $request->validate([
            'HoTen' => 'required|string|max:100',
            'HocVi' => 'required|string|max:50',
            'MaBoMon' => 'required',
            'Email' => 'required|email|max:100',
            'SoDienThoai' => ['required', 'string', 'regex:/^0[0-9]{8,10}$/']
        ], [
            'HoTen.required' => 'Vui lòng nhập họ tên.',
            'HocVi.required' => 'Vui lòng chọn học vị.',
            'MaBoMon.required' => 'Vui lòng chọn bộ môn.',
            'Email.required' => 'Vui lòng nhập email.',
            'Email.email' => 'Định dạng email không hợp lệ.',
            'SoDienThoai.required' => 'Vui lòng nhập số điện thoại.',
            'SoDienThoai.regex' => 'Số điện thoại phải bắt đầu bằng số 0 và có từ 9-11 chữ số hợp lệ.',
        ]);

        try {
            $giangvien->update([
                'HoTen' => $request->HoTen,
                'HocVi' => $request->HocVi,
                'MaBoMon' => $request->MaBoMon,
                'Email' => $request->Email,
                'SoDienThoai' => $request->SoDienThoai,
            ]);

            if ($request->filled('TenDangNhap') && $giangvien->taiKhoan) {
                $giangvien->taiKhoan->update(['TenDangNhap' => $request->TenDangNhap]);
            }

            return redirect()->route('giangvien.index')->with('success', 'Cập nhật giảng viên thành công!');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Cập nhật Giảng viên lỗi: ' . $e->getMessage());
            return redirect()->back()->withErrors('Không thể cập nhật giảng viên: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id) {
        try {
            $giangvien = $this->findGiangVien($id);
            $maTK = $giangvien->MaTK;

            LopHocPhan::where('MaGV', $giangvien->MaGV)->update(['MaGV' => null]);

            if (!empty($maTK) || !empty($giangvien->TenDangNhap)) {
                TaiKhoan::where('_id', $maTK)
                    ->orWhere('TenDangNhap', $giangvien->TenDangNhap)
                    ->orWhere('TenDangNhap', strtolower($giangvien->MaGV))
                    ->delete();
            }

            $giangvien->delete();
            return redirect()->route('giangvien.index')->with('success', 'Xóa giảng viên thành công!');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Xóa Giảng viên lỗi: ' . $e->getMessage());
            return redirect()->back()->withErrors("Không thể xóa giảng viên: " . $e->getMessage());
        }
    }

    public function toggleStatus($id) {
        try {
            $giangvien = $this->findGiangVien($id);
            $taiKhoan = $giangvien->taiKhoan;

            if (!$taiKhoan && (!empty($giangvien->MaTK) || !empty($giangvien->TenDangNhap))) {
                $taiKhoan = TaiKhoan::where('_id', $giangvien->MaTK)
                    ->orWhere('TenDangNhap', $giangvien->TenDangNhap)
                    ->orWhere('TenDangNhap', strtolower($giangvien->MaGV))
                    ->first();
            }

            if ($taiKhoan) {
                $newStatus = !$taiKhoan->TrangThai;
                $taiKhoan->update(['TrangThai' => $newStatus]);
                $statusText = $newStatus ? 'Mở khóa' : 'Khóa';
                return redirect()->back()->with('success', "Đã {$statusText} tài khoản giảng viên {$giangvien->HoTen} thành công!");
            } else {
                $tk = TaiKhoan::create([
                    'TenDangNhap' => $giangvien->TenDangNhap ?? $giangvien->MaGV,
                    'MatKhau' => Hash::make('123456'),
                    'VaiTro' => 'Giảng viên',
                    'TrangThai' => false
                ]);
                $giangvien->update(['MaTK' => (string)$tk->_id]);
                return redirect()->back()->with('success', "Đã tạo và khóa tài khoản giảng viên {$giangvien->HoTen} thành công!");
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Lỗi toggle status giảng viên: ' . $e->getMessage());
            return redirect()->back()->withErrors('Không thể thay đổi trạng thái tài khoản: ' . $e->getMessage());
        }
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importGiangVien', [], 'Giảng viên');
    }
}