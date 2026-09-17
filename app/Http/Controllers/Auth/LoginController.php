<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller implements HasMiddleware
{
    use AuthenticatesUsers;

    /**
     * Tối đa 5 lần đăng nhập sai liên tiếp trước khi khóa
     */
    protected int $maxAttempts = 5;

    /**
     * Thời gian khóa tạm thời: 3 phút (180 giây)
     */
    protected int $decayMinutes = 3;

    public function redirectTo()
    {
        /** @var \App\Models\TaiKhoan $user */
        $user = auth()->user();
        $role = $user->VaiTro ?? '';

        if ($role === 'Admin') return route('admin.dashboard');
        if ($role === 'Giảng viên') return route('giangvien.dashboard');
        if ($role === 'Sinh viên') return route('sinhvien.dashboard');

        return route('login');
    }

    public static function middleware(): array
    {
        return [
            new Middleware('guest', except: ["logout"]),
            new Middleware('auth', only: ["logout"])
        ];
    }

    public function username()
    {
        return 'TenDangNhap';
    }

    protected function credentials(Request $request)
    {
        return [
            'TenDangNhap' => $request->get('TenDangNhap'),
            'password' => $request->get('password'),
            'TrangThai' => true
        ];
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);

        // Kiểm tra xem tài khoản/IP có bị khóa tạm thời do sai quá 5 lần không
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            if ($request->hasSession()) {
                $request->session()->put('auth.password_confirmed_at', time());
            }

            // Đăng nhập thành công -> Reset số lần đăng nhập sai
            $this->clearLoginAttempts($request);

            return $this->sendLoginResponse($request);
        }

        // Đăng nhập thất bại -> Tăng số lần thử sai
        $this->incrementLoginAttempts($request);

        $key = $this->throttleKey($request);
        $attempts = RateLimiter::attempts($key);
        $remaining = max(0, $this->maxAttempts - $attempts);

        if ($remaining > 0) {
            $errorMsg = "Mật khẩu hoặc tên đăng nhập không chính xác. Bạn còn {$remaining} lần thử.";
        } else {
            $errorMsg = "Bạn đã nhập sai mật khẩu 5 lần. Tài khoản tạm thời bị khóa đăng nhập trong 3 phút.";
        }

        return $this->sendFailedLoginResponse($request, $errorMsg);
    }

    protected function sendFailedLoginResponse(Request $request, ?string $message = null)
    {
        return redirect()->back()
            ->withInput($request->only($this->username(), 'remember'))
            ->withErrors([
                $this->username() => $message ?? trans('auth.failed'),
            ]);
    }

    protected function sendLockoutResponse(Request $request)
    {
        $seconds = RateLimiter::availableIn($this->throttleKey($request));
        $minutes = floor($seconds / 60);
        $remainingSecs = $seconds % 60;
        $formattedTime = sprintf('%02d:%02d', $minutes, $remainingSecs);

        $msg = "Bạn đã nhập sai mật khẩu quá 5 lần. Tài khoản tạm thời bị khóa đăng nhập. Vui lòng thử lại sau {$formattedTime}.";

        return redirect()->back()
            ->withInput($request->only($this->username(), 'remember'))
            ->with('lockout_seconds', $seconds)
            ->withErrors([
                $this->username() => $msg,
            ]);
    }

    protected function authenticated(Request $request, $user)
    {
        if (!$user->TrangThai) {
            auth()->logout();
            return redirect()->route('login')->withErrors(['TenDangNhap' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ Admin.']);
        }
        \App\Models\AuditLog::log('dang_nhap', 'TaiKhoan', $user->_id, ['TenDangNhap' => $user->TenDangNhap]);

        $role = $user->VaiTro ?? '';
        if ($role === 'Admin') return redirect()->route('admin.dashboard');
        if ($role === 'Giảng viên') return redirect()->route('giangvien.dashboard');
        if ($role === 'Sinh viên') return redirect()->route('sinhvien.dashboard');

        return redirect()->route('login');
    }
}

