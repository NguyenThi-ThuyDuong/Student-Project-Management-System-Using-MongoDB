@extends('layouts.app')

@section('content')
<style>
    nav.navbar { display: none !important; }
    main { padding: 0 !important; }
    html, body {
        min-height: 100vh;
        margin: 0 !important;
        padding: 0 !important;
        font-family: 'Be Vietnam Pro', 'Inter', sans-serif !important;
        background: #051528;
    }

    /* ═══ PROPORTIONED DESKTOP SAAS CONTAINER ═══ */
    .login-saas-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(160deg, #051528 0%, #0A2540 50%, #0066B2 100%);
        padding: 40px 20px;
    }

    /* PERFECT CARD DIMENSIONS (NOT TOO BIG, NOT TOO SMALL) */
    .login-saas-card {
        width: 94%;
        max-width: 1120px;
        min-height: 600px;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(5, 21, 40, 0.45);
        display: flex;
        background: #ffffff;
        border: 1.5px solid rgba(255, 255, 255, 0.2);
    }

    /* ── LEFT SIDE: BRANDING (50% WIDTH) ── */
    .saas-left-brand {
        width: 50%;
        position: relative;
        overflow: hidden;
        background: #0A2540;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 40px 36px;
        color: #ffffff;
    }

    .saas-left-brand .bg-photo {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        opacity: 0.45;
        transition: transform 8s ease;
    }

    .saas-left-brand:hover .bg-photo {
        transform: scale(1.06);
    }

    .saas-left-brand .overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(145deg, 
            rgba(5, 21, 40, 0.92) 0%, 
            rgba(10, 37, 64, 0.85) 50%, 
            rgba(0, 102, 178, 0.55) 100%);
        z-index: 1;
    }

    .brand-top-group {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .school-logo-glow {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        object-fit: cover;
        border: 2px solid #0066B2;
        background: #ffffff;
        box-shadow: 0 0 20px rgba(0, 102, 178, 0.45);
    }

    .school-title-text {
        font-size: 1.05rem;
        font-weight: 800;
        color: #ffffff;
        line-height: 1.3;
        letter-spacing: 0.2px;
        text-transform: uppercase;
    }

    .school-sub-text {
        font-size: 0.78rem;
        color: #C7E5F4;
        font-weight: 500;
        display: block;
        margin-top: 2px;
    }

    .brand-main-body {
        position: relative;
        z-index: 2;
        margin: auto 0;
        padding: 20px 0;
    }

    .hero-badge-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(199, 229, 244, 0.2);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(91, 155, 213, 0.4);
        padding: 6px 16px;
        border-radius: 30px;
        font-size: 0.8rem;
        font-weight: 700;
        color: #C7E5F4;
        margin-bottom: 20px;
    }

    .hero-headline-text {
        font-size: 1.85rem;
        font-weight: 800;
        line-height: 1.3;
        color: #ffffff;
        margin-bottom: 14px;
    }

    .hero-headline-text span {
        color: #5B9BD5;
    }

    .hero-desc-text {
        font-size: 0.88rem;
        color: rgba(255, 255, 255, 0.85);
        line-height: 1.65;
        margin-bottom: 24px;
    }

    /* Stats Grid */
    .stats-pills-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .stat-pill-box {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.16);
        border-radius: 12px;
        padding: 12px;
        text-align: center;
    }

    .stat-pill-box .num {
        font-size: 1.2rem;
        font-weight: 800;
        color: #ffffff;
        line-height: 1;
    }

    .stat-pill-box .lbl {
        font-size: 0.68rem;
        color: #C7E5F4;
        font-weight: 600;
        margin-top: 4px;
        text-transform: uppercase;
    }

    .brand-bottom-info {
        position: relative;
        z-index: 2;
        font-size: 0.78rem;
        color: rgba(255, 255, 255, 0.75);
        border-top: 1px solid rgba(255, 255, 255, 0.12);
        padding-top: 16px;
        display: flex;
        justify-content: space-between;
    }

    /* ── RIGHT SIDE: FORM (50% WIDTH) ── */
    .saas-right-form {
        width: 50%;
        background: #ffffff;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 44px 40px;
    }

    .form-inner-wrapper {
        max-width: 400px;
        width: 100%;
        margin: 0 auto;
    }

    .form-head-zone {
        margin-bottom: 24px;
    }

    .form-head-zone .welcome-heading {
        font-size: 1.65rem;
        font-weight: 800;
        color: #0A2540;
        margin-bottom: 6px;
        letter-spacing: -0.2px;
    }

    .form-head-zone .welcome-subheading {
        font-size: 0.86rem;
        color: #475569;
        line-height: 1.5;
    }

    .field-group {
        margin-bottom: 18px;
    }

    .field-group label {
        font-size: 0.83rem;
        font-weight: 700;
        color: #0A2540;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .field-group label i {
        color: #0066B2;
        font-size: 0.88rem;
    }

    .input-icon-wrap {
        position: relative;
    }

    .field-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.92rem;
        color: #64748B;
        transition: color 0.25s ease;
    }

    .input-icon-wrap input {
        width: 100%;
        height: 46px;
        border-radius: 10px;
        border: 1.5px solid #BCE0F5;
        background: #F3F7FA;
        padding-left: 44px;
        padding-right: 16px;
        font-size: 0.9rem;
        font-family: 'Be Vietnam Pro', 'Inter', sans-serif !important;
        color: #1E293B;
        transition: all 0.25s ease;
    }

    .input-icon-wrap input:focus {
        border-color: #0066B2;
        background: #ffffff;
        outline: none;
        box-shadow: 0 0 0 4px rgba(0, 102, 178, 0.14);
    }

    .input-icon-wrap input:focus ~ .field-icon {
        color: #0066B2;
    }

    .form-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 22px;
        font-size: 0.84rem;
    }

    .form-check-input:checked {
        background-color: #0066B2;
        border-color: #0066B2;
    }

    .link-forgot {
        color: #0066B2;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .link-forgot:hover {
        color: #005291;
        text-decoration: underline;
    }

    .btn-login-submit {
        width: 100%;
        height: 48px;
        border: none;
        border-radius: 10px;
        background: linear-gradient(135deg, #0066B2 0%, #5B9BD5 100%);
        color: #ffffff;
        font-size: 0.95rem;
        font-weight: 700;
        transition: all 0.22s ease;
        cursor: pointer;
        box-shadow: 0 4px 14px rgba(0, 102, 178, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        letter-spacing: 0.2px;
    }

    .btn-login-submit:hover {
        background: linear-gradient(135deg, #005291 0%, #4A8AC4 100%);
        transform: translateY(-1.5px);
        box-shadow: 0 6px 20px rgba(0, 102, 178, 0.4);
    }

    .form-footer-note {
        margin-top: 24px;
        text-align: center;
        font-size: 0.75rem;
        color: #64748B;
        border-top: 1px solid #E3F2FC;
        padding-top: 14px;
    }

    @media (max-width: 992px) {
        .login-saas-card { flex-direction: column; height: auto; }
        .saas-left-brand, .saas-right-form { width: 100%; }
        .saas-left-brand { padding: 32px 24px; min-height: 320px; }
        .saas-right-form { padding: 36px 24px; }
    }
</style>

<div class="login-saas-container">
    <div class="login-saas-card">

        {{-- ═══ LEFT SIDE: BRANDING SHOWCASE ═══ --}}
        <div class="saas-left-brand">
            <img src="{{ asset('images/hinhcongtruong.jpg') }}" alt="Cổng Trường HUIT" class="bg-photo">
            <div class="overlay"></div>

            <div class="brand-top-group">
                <img src="{{ asset('images/logotruong.jpg') }}" alt="Logo HUIT" class="school-logo-glow">
                <div>
                    <div class="school-title-text">ĐH Công Thương TP.HCM</div>
                    <span class="school-sub-text">Hệ Thống Quản Lý Đồ Án</span>
                </div>
            </div>

            <div class="brand-main-body">
                <div class="hero-badge-pill">
                    <i class="fa-solid fa-graduation-cap"></i> Cổng Đăng Nhập Trực Tuyến
                </div>

                <h1 class="hero-headline-text">
                    Quản Lý Đồ Án <span>Tốt Nghiệp</span>
                </h1>

                <p class="hero-desc-text">
                    Hệ thống theo dõi tiến độ, nộp báo cáo và quản lý đồ án dành cho Giảng viên và Sinh viên HUIT.
                </p>
            </div>

            <div class="brand-bottom-info">
                <div><i class="fa-solid fa-location-dot me-1" style="color: #5B9BD5;"></i> 140 Lê Trọng Tấn, Tân Phú, TP.HCM</div>
                <div><i class="fa-solid fa-graduation-cap me-1" style="color: #5B9BD5;"></i> HUIT {{ date('Y') }}</div>
            </div>
        </div>

        {{-- ═══ RIGHT SIDE: LOGIN FORM ═══ --}}
        <div class="saas-right-form">
            <div class="form-inner-wrapper">

                <div class="form-head-zone">
                    <h1 class="welcome-heading">Đăng Nhập</h1>
                    <p class="welcome-subheading">Vui lòng nhập MSSV hoặc MSGV và mật khẩu để tiếp tục</p>
                </div>

                @if (session('lockout_seconds'))
                    <div class="alert alert-danger border-0 mb-4 p-3 shadow-sm" style="border-left: 5px solid #dc3545 !important; border-radius: 12px; background: #FFF5F5;" id="lockout-alert-box">
                        <div class="d-flex align-items-center gap-2 mb-1" style="color: #991B1B; font-weight: 700; font-size: 1.05rem;">
                            <i class="fa-solid fa-user-lock text-danger fs-4"></i>
                            <span>TÀI KHOẢN TẠM THỜI BỊ KHÓA</span>
                        </div>
                        <div style="font-size: 0.95rem; color: #7F1D1D; line-height: 1.5;">
                            Bạn đã nhập sai mật khẩu quá 5 lần liên tiếp. Vui lòng thử lại sau:
                            <div class="mt-2 text-center">
                                <span id="countdown-timer" style="font-size: 1.6rem; font-weight: 800; color: #DC2626; font-family: monospace; background: #FEE2E2; padding: 4px 16px; border-radius: 8px; border: 1px solid #FCA5A5; display: inline-block;">
                                    03:00
                                </span>
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" id="login-form-main">
                    @csrf

                    {{-- Tên đăng nhập --}}
                    <div class="field-group">
                        <label for="TenDangNhap">
                            <i class="fa-solid fa-id-badge"></i> Tên Đăng Nhập
                        </label>
                        <div class="input-icon-wrap">
                            <i class="fa-solid fa-user field-icon"></i>
                            <input
                                id="TenDangNhap"
                                type="text"
                                name="TenDangNhap"
                                value="{{ old('TenDangNhap') }}"
                                placeholder="Nhập MSSV hoặc MSGV..."
                                required
                                autocomplete="username"
                                autofocus
                                class="{{ $errors->has('TenDangNhap') ? 'is-invalid' : '' }}"
                            >
                        </div>
                        @error('TenDangNhap')
                            <span class="text-danger mt-1 d-block fw-bold" style="font-size: 0.85rem;">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}
                            </span>
                        @enderror
                    </div>

                    {{-- Mật khẩu --}}
                    <div class="field-group">
                        <label for="password">
                            <i class="fa-solid fa-lock"></i> Mật Khẩu
                        </label>
                        <div class="input-icon-wrap">
                            <i class="fa-solid fa-key field-icon"></i>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="Nhập mật khẩu..."
                                required
                                autocomplete="current-password"
                                class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                            >
                        </div>
                        @error('password')
                            <span class="text-danger mt-1 d-block fw-bold" style="font-size: 0.85rem;">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}
                            </span>
                        @enderror
                    </div>

                    {{-- Ghi nhớ + Quên mật khẩu --}}
                    <div class="form-meta">
                        <div class="form-check me-auto">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                   {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label text-dark fw-semibold" for="remember">Ghi nhớ đăng nhập</label>
                        </div>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="link-forgot">Quên mật khẩu?</a>
                        @endif
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit" class="btn-login-submit" id="btn-submit-login">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        Đăng Nhập
                    </button>
                </form>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        @if (session('lockout_seconds'))
                            let remainingSeconds = parseInt("{{ session('lockout_seconds') }}");
                            let btn = document.getElementById('btn-submit-login');
                            let usernameInput = document.getElementById('TenDangNhap');
                            let passwordInput = document.getElementById('password');
                            let countdownEl = document.getElementById('countdown-timer');

                            if (btn) btn.disabled = true;
                            if (usernameInput) usernameInput.disabled = true;
                            if (passwordInput) passwordInput.disabled = true;

                            let timer = setInterval(function() {
                                remainingSeconds--;
                                if (remainingSeconds <= 0) {
                                    clearInterval(timer);
                                    if (countdownEl) countdownEl.innerText = "00:00 - Hãy thử lại";
                                    if (btn) btn.disabled = false;
                                    if (usernameInput) usernameInput.disabled = false;
                                    if (passwordInput) passwordInput.disabled = false;
                                    let alertBox = document.getElementById('lockout-alert-box');
                                    if (alertBox) alertBox.style.display = 'none';
                                } else {
                                    let mins = Math.floor(remainingSeconds / 60);
                                    let secs = remainingSeconds % 60;
                                    let formatted = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
                                    if (countdownEl) countdownEl.innerText = formatted;
                                }
                            }, 1000);
                        @endif
                    });
                </script>

                <div class="form-footer-note">
                    <i class="fa-solid fa-shield-halved me-1" style="color: #0066B2;"></i>
                    Bảo mật dữ liệu HUIT &bull; &copy; {{ date('Y') }}
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
