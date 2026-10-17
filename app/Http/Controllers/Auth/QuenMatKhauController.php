<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TaiKhoan;
use App\Models\YeuCauDoiMatKhau;
use Illuminate\Http\Request;

class QuenMatKhauController extends Controller
{
    public function showForm()
    {
        return view('auth.passwords.email');
    }

    public function sendRequest(Request $request)
    {
        $request->validate([
            'TenDangNhap' => 'required|string|max:100',
            'HoTen' => 'required|string|max:150',
            'Role' => 'required|in:Sinh viên,Giảng viên'
        ], [
            'TenDangNhap.required' => 'Vui lòng nhập Mã số (MSSV / Mã GV).',
            'HoTen.required' => 'Vui lòng nhập Họ và tên.',
            'Role.required' => 'Vui lòng chọn vai trò.'
        ]);

        $tk = TaiKhoan::where('TenDangNhap', $request->TenDangNhap)->first();

        if (!$tk) {
            return redirect()->back()->withInput()->withErrors(['TenDangNhap' => 'Mã số này không tồn tại trong hệ thống. Vui lòng kiểm tra lại.']);
        }

        // Kiểm tra xem đã có yêu cầu chờ duyệt chưa
        $existing = YeuCauDoiMatKhau::where('TenDangNhap', $request->TenDangNhap)
                                    ->where('TrangThai', 'Chờ duyệt')
                                    ->first();

        if ($existing) {
            return redirect()->back()->with('status', 'Bạn đã gửi yêu cầu cấp lại mật khẩu rồi. Vui lòng chờ Admin phê duyệt!');
        }

        // Tích hợp Redis Feature 1 - OTP Store
        $otpService = new \App\Services\OtpService();
        $otpCode = $otpService->generateOtp($request->TenDangNhap);

        YeuCauDoiMatKhau::create([
            'TenDangNhap' => $request->TenDangNhap,
            'Email' => $request->HoTen, // Lưu Họ tên vào Email/Note
            'Role' => $request->Role,
            'OTP' => $otpCode,
            'TrangThai' => 'Chờ duyệt',
            'NgayGui' => now()
        ]);

        return redirect()->route('login')->with('success', "Đã tạo mã xác thực Redis OTP: {$otpCode} (Hạn 5 phút, Key: otp:{$request->TenDangNhap}) và gửi yêu cầu cấp lại mật khẩu cho {$request->Role}: {$request->HoTen} thành công! Vui lòng chờ Admin phê duyệt.");
    }
}
