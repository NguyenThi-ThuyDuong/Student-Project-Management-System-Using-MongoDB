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
        background: #091322;
    }

    .login-saas-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(160deg, #091322 0%, #0F2942 50%, #6586E6 100%);
        padding: 40px 20px;
    }

    .login-saas-card {
        width: 94%;
        max-width: 1160px;
        min-height: 620px;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 24px 80px rgba(9, 19, 34, 0.45);
        display: flex;
        background: #ffffff;
        border: 1.5px solid rgba(255, 255, 255, 0.18);
    }

    /* Left Branding Side */
    .saas-left-brand {
        width: 50%;
        position: relative;
        overflow: hidden;
        background: #0F2942;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 44px 40px;
        color: #ffffff;
    }

    .saas-left-brand .bg-photo {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.45;
    }

    .saas-left-brand .overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(145deg, 
            rgba(9, 19, 34, 0.9) 0%, 
            rgba(15, 41, 66, 0.8) 50%, 
            rgba(39, 164, 242, 0.5) 100%);
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
        width: 58px;
        height: 58px;
        border-radius: 14px;
        object-fit: cover;
        border: 2px solid #27A4F2;
        background: #ffffff;
        box-shadow: 0 0 20px rgba(39, 164, 242, 0.4);
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
        color: #9FD7F9;
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
        background: rgba(39, 164, 242, 0.18);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(39, 164, 242, 0.4);
        padding: 6px 16px;
        border-radius: 30px;
        font-size: 0.8rem;
        font-weight: 700;
        color: #CFEBFC;
        margin-bottom: 20px;
    }

    .hero-headline-text {
        font-size: 1.95rem;
        font-weight: 800;
        line-height: 1.3;
        color: #ffffff;
        margin-bottom: 14px;
    }

    .hero-desc-text {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.85);
        line-height: 1.65;
    }

    .brand-bottom-info {
        position: relative;
        z-index: 2;
        font-size: 0.78rem;
        color: rgba(255, 255, 255, 0.7);
        border-top: 1px solid rgba(255, 255, 255, 0.12);
        padding-top: 16px;
    }

    /* Right Form Side */
    .saas-right-form {
        width: 50%;
        background: #ffffff;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 48px 44px;
    }

    .form-inner-wrapper {
        max-width: 420px;
        width: 100%;
        margin: 0 auto;
    }

    .btn-back-login {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #64748B;
        font-size: 0.88rem;
        font-weight: 700;
        text-decoration: none;
        margin-bottom: 20px;
        transition: color 0.2s ease;
    }

    .btn-back-login:hover {
        color: #27A4F2;
    }

    .welcome-heading {
        font-size: 1.75rem;
        font-weight: 800;
        color: #0F2942;
        margin-bottom: 6px;
    }

    .welcome-subheading {
        font-size: 0.88rem;
        color: #64748B;
        margin-bottom: 24px;
    }

    .form-label-modern {
        font-size: 0.84rem;
        font-weight: 700;
        color: #0F2942;
        margin-bottom: 6px;
        display: block;
    }

    .input-group-modern {
        position: relative;
        margin-bottom: 18px;
    }

    .input-group-modern i.input-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #27A4F2;
        font-size: 0.95rem;
    }

    .input-group-modern .form-control, .form-select-modern {
        padding: 10px 14px 10px 44px;
        border-radius: 12px;
        border: 1.5px solid #BDCBF4;
        background-color: #F0F7FE;
        font-size: 0.92rem;
        font-family: 'Be Vietnam Pro', 'Inter', sans-serif !important;
        color: #0F172A;
        transition: all 0.25s ease;
        width: 100%;
    }

    .input-group-modern .form-control:focus, .form-select-modern:focus {
        border-color: #27A4F2;
        background-color: #ffffff;
        box-shadow: 0 0 0 4px rgba(39, 164, 242, 0.16);
        outline: none;
    }

    .btn-reset-submit {
        background: linear-gradient(135deg, #27A4F2 0%, #6586E6 100%);
        color: #ffffff;
        border: none;
        border-radius: 12px;
        padding: 12px 24px;
        font-weight: 700;
        font-size: 1rem;
        width: 100%;
        box-shadow: 0 4px 16px rgba(39, 164, 242, 0.35);
        transition: all 0.25s ease;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-reset-submit:hover {
        background: linear-gradient(135deg, #1A90DD 0%, #4F70D4 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(39, 164, 242, 0.45);
    }

    @media (max-width: 992px) {
        .login-saas-card { flex-direction: column; height: auto; }
        .saas-left-brand, .saas-right-form { width: 100%; }
        .saas-left-brand { padding: 32px 24px; min-height: 280px; }
        .saas-right-form { padding: 36px 24px; }
    }
</style>

<div class="login-saas-container">
    <div class="login-saas-card">
        <!-- LEFT BRAND HERO -->
        <div class="saas-left-brand">
            <img src="{{ asset('images/hinhcongtruong.jpg') }}" alt="HUIT Gate" class="bg-photo">
            <div class="overlay"></div>

            <div class="brand-top-group">
                <img src="{{ asset('images/logotruong.jpg') }}" alt="HUIT Logo" class="school-logo-glow">
                <div>
                    <div class="school-title-text">ĐH Công Thương TP.HCM</div>
                    <span class="school-sub-text">HUIT Student Project Management Portal</span>
                </div>
            </div>

            <div class="brand-main-body">
                <div class="hero-badge-pill">
                    <i class="fa-solid fa-key"></i> Hỗ Trợ Khôi Phục Mật Khẩu
                </div>
                <h1 class="hero-headline-text">Khôi Phục Mật Khẩu Tài Khoản</h1>
                <p class="hero-desc-text">Gửi yêu cầu tới Ban Quản Trị hệ thống để đặt lại mật khẩu về mặc định (123456). Yêu cầu sẽ được kiểm tra và phê duyệt nhanh chóng.</p>
            </div>

            <div class="brand-bottom-info">
                &copy; {{ date('Y') }} Trường Đại Học Công Thương TP. Hồ Chí Minh. Bản quyền thuộc về HUIT.
            </div>
        </div>

        <!-- RIGHT FORM -->
        <div class="saas-right-form">
            <div class="form-inner-wrapper">
                <a href="{{ route('login') }}" class="btn-back-login">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại trang Đăng nhập
                </a>

                <div class="welcome-heading">Quên Mật Khẩu</div>
                <div class="welcome-subheading">Nhập thông tin mã số của bạn để gửi yêu cầu reset mật khẩu</div>

                @if(session('info'))
                    <div class="alert alert-info border-0 rounded-3 shadow-sm mb-4 p-3" style="background: #EFF6FF; border-left: 4px solid #27A4F2 !important; color: #0F2942;">
                        <i class="fa-solid fa-circle-info me-2 fs-5" style="color: #27A4F2;"></i> {{ session('info') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.send_request') }}">
                    @csrf

                    <!-- Role Dropdown -->
                    <div class="mb-3">
                        <label class="form-label-modern">Đối Tượng Tài Khoản <span class="text-danger">*</span></label>
                        <div class="input-group-modern">
                            <select name="Role" class="form-select form-select-modern ps-5" required>
                                <option value="Sinh viên" {{ old('Role') == 'Sinh viên' ? 'selected' : '' }}>🎓 Sinh viên</option>
                                <option value="Giảng viên" {{ old('Role') == 'Giảng viên' ? 'selected' : '' }}>👨‍🏫 Giảng viên</option>
                            </select>
                            <i class="fa-solid fa-user-gear input-icon"></i>
                        </div>
                    </div>

                    <!-- Tên đăng nhập -->
                    <div class="mb-3">
                        <label class="form-label-modern">Tên Đăng Nhập (Mã SV / Mã GV) <span class="text-danger">*</span></label>
                        <div class="input-group-modern">
                            <input type="text" name="TenDangNhap" class="form-control @error('TenDangNhap') is-invalid @enderror" value="{{ old('TenDangNhap') }}" placeholder="Ví dụ: 2001200101 hoặc GV001" required>
                            <i class="fa-solid fa-id-badge input-icon"></i>
                        </div>
                        @error('TenDangNhap')
                            <div class="text-danger small mt-1 fw-semibold">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label class="form-label-modern">Email Liên Hệ (Không bắt buộc)</label>
                        <div class="input-group-modern">
                            <input type="email" name="Email" class="form-control" value="{{ old('Email') }}" placeholder="Nhập địa chỉ email cá nhân...">
                            <i class="fa-solid fa-envelope input-icon"></i>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-reset-submit">
                        <i class="fa-solid fa-paper-plane"></i> Gửi Yêu Cầu Khôi Phục
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
