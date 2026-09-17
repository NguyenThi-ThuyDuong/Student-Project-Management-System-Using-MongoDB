<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\TaiKhoan;

class ProfileController extends Controller
{
    public function showProfile()
    {
        $user = Auth::user();
        $role = strtolower($user->VaiTro ?? '');
        $profile = null;
        $maTK = (string) ($user->_id ?? $user->MaTK);

        $myLhps = collect();
        if ($role === 'admin') {
            $layout = 'layouts.admin';
        } elseif ($role === 'giangvien') {
            $layout = 'layouts.giangvien';
            $profile = \App\Models\GiangVien::with('boMon')->where('MaTK', $maTK)->first();
        } else {
            $layout = 'layouts.sinhvien';
            $profile = \App\Models\SinhVien::with('lop')->where('MaTK', $maTK)->first();
            if ($profile) {
                $allLhps = \App\Models\LopHocPhan::all();
                $myLhps = $allLhps->filter(function($lhp) use ($profile) {
                    return $lhp->hasSinhVien($profile->MaSV) || $lhp->hasSinhVien((string)$profile->_id);
                });
            }
        }

        return view('profile.show', compact('layout', 'profile', 'role', 'user', 'myLhps'));
    }
    public function showChangePasswordForm()
    {
        $user = Auth::user();
        $role = strtolower($user->VaiTro ?? '');
        
        // Determine which layout to use based on role
        if ($role === 'admin') {
            $layout = 'layouts.admin';
        } elseif ($role === 'giangvien') {
            $layout = 'layouts.giangvien';
        } else {
            $layout = 'layouts.sinhvien';
        }

        return view('profile.password', compact('layout'));
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => ['required', 'min:6', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&]).+$/', 'confirmed'],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'new_password.required' => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'new_password.regex' => 'Mật khẩu mới phải chứa chữ hoa, chữ thường, số và ký tự đặc biệt (@$!%*#?&).',
            'new_password.confirmed' => 'Xác nhận mật khẩu mới không khớp.'
        ]);

        $user = Auth::user();

        // Check if current password matches
        if (!Hash::check($request->current_password, $user->MatKhau)) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng']);
        }

        // Update password
        TaiKhoan::where('MaTK', $user->MaTK)->update([
            'MatKhau' => Hash::make($request->new_password)
        ]);

        return back()->with('success', 'Đổi mật khẩu thành công!');
    }
}
