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
        $roleName = $user->VaiTro ?? '';

        if (!in_array($roleName, $roles)) {
            \Illuminate\Support\Facades\Log::error('CheckRole Failed', ['roleName' => $roleName, 'roles' => $roles, 'user' => $user->toArray()]);
            abort(403, 'Bạn không có quyền truy cập trang này. Vui lòng liên hệ Admin. (' . $roleName . ' vs ' . implode(',', $roles) . ')');
        }

        return $next($request);
    }
}
