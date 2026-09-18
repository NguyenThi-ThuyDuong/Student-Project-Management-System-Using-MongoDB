<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        /** @var \App\Models\TaiKhoan $user */
        $user = Auth::user();
        $userRoleLower = mb_strtolower(trim($user->VaiTro ?? ''));
        $allowedLower = array_map(fn($r) => mb_strtolower(trim($r)), $roles);

        $hasPermission = in_array($userRoleLower, $allowedLower);

        if (!$hasPermission) {
            if (in_array('admin', $allowedLower) && in_array($userRoleLower, ['admin', 'giao_vu', 'giáo vụ'])) {
                $hasPermission = true;
            } elseif (in_array('giảng viên', $allowedLower) && in_array($userRoleLower, ['giảng viên', 'giang_vien', 'giangvien'])) {
                $hasPermission = true;
            } elseif (in_array('sinh viên', $allowedLower) && in_array($userRoleLower, ['sinh viên', 'sinh_vien', 'sinhvien'])) {
                $hasPermission = true;
            }
        }

        if (!$hasPermission) {
            \Illuminate\Support\Facades\Log::error('CheckRole Failed', ['roleName' => $user->VaiTro, 'roles' => $roles, 'user' => $user->toArray()]);
            abort(403, 'Bạn không có quyền truy cập trang này. Vui lòng liên hệ Admin. (' . $user->VaiTro . ')');
        }

        return $next($request);
    }
}
