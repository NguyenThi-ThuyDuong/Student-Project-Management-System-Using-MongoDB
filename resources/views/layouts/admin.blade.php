<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('page_title', 'Admin') – Hệ Thống QLĐA | HUIT</title>
    <meta name="description" content="Hệ thống quản lý đồ án  – Trường Đại Học Công Thương TP.HCM">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Vietnix Theme -->
    <link rel="stylesheet" href="{{ asset('css/huit_theme.css') }}">
    
    @stack('styles')
</head>
<body>

<div class="wrapper d-flex">

    <!-- ═══ SIDEBAR ═══ -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <img src="{{ asset('images/logotruong.jpg') }}" alt="Logo HUIT" class="sidebar-logo">
                <div>
                    <div class="sidebar-title">ĐH Công Thương<br>TP. Hồ Chí Minh</div>
                    <span class="sidebar-subtitle">VIETNIX SaaS Admin</span>
                </div>
            </div>
        </div>

        <div class="px-3 pb-2">
            <div class="role-badge">
                <i class="fa-solid fa-shield-halved"></i>
                Giáo Vụ / Admin
            </div>
        </div>

        <ul class="list-unstyled components">
            <!-- 1. Dashboard -->
            <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="fa-solid fa-chart-pie"></i> Dashboard Tổng Quan
                </a>
            </li>

            <!-- Quản Lý Dữ Liệu -->
            <li class="nav-section-label">Quản Lý Dữ Liệu</li>
            <li class="{{ request()->routeIs('sinhvien.*') ? 'active' : '' }}">
                <a href="{{ route('sinhvien.index') }}">
                    <i class="fa-solid fa-user-graduate"></i> Quản lý Sinh viên
                </a>
            </li>
            <li class="{{ request()->routeIs('giangvien.*') ? 'active' : '' }}">
                <a href="{{ route('giangvien.index') }}">
                    <i class="fa-solid fa-chalkboard-user"></i> Quản lý Giảng viên
                </a>
            </li>
            <li class="{{ request()->routeIs('lop.*') ? 'active' : '' }}">
                <a href="{{ route('lop.index') }}">
                    <i class="fa-solid fa-users-rectangle"></i> Quản lý Lớp Hành chính
                </a>
            </li>
            <li class="{{ request()->routeIs('monhoc.*') ? 'active' : '' }}">
                <a href="{{ route('monhoc.index') }}">
                    <i class="fa-solid fa-book"></i> Quản lý Học phần
                </a>
            </li>
            <li class="{{ request()->routeIs('hocky.*') ? 'active' : '' }}">
                <a href="{{ route('hocky.index') }}">
                    <i class="fa-solid fa-calendar-days"></i> Quản lý Học kỳ
                </a>
            </li>
            <li class="{{ request()->routeIs('phancong.index') ? 'active' : '' }}">
                <a href="{{ route('phancong.index') }}">
                    <i class="fa-solid fa-sitemap"></i> Phân công Lớp (GVHD)
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.phancong.thongke_gvhd') ? 'active' : '' }}">
                <a href="{{ route('admin.phancong.thongke_gvhd') }}">
                    <i class="fa-solid fa-chart-user"></i> Thống kê Tải GVHD
                </a>
            </li>

            <!-- Quản Lý Đồ Án -->
            <li class="nav-section-label">Quản Lý Đồ Án</li>
            <li class="{{ request()->routeIs('admin.lophocphan.*') ? 'active' : '' }}">
                <a href="{{ route('admin.lophocphan.index') }}">
                    <i class="fa-solid fa-graduation-cap"></i> Lớp Học Phần Đồ Án
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.duyet_detai.*') ? 'active' : '' }}">
                <a href="{{ route('admin.duyet_detai.index') }}">
                    <i class="fa-solid fa-file-signature"></i> Giáo Vụ Duyệt Đề Tài
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.tiendo.*') ? 'active' : '' }}">
                <a href="{{ route('admin.tiendo.index') }}">
                    <i class="fa-solid fa-bars-staggered"></i> Tiến Độ Đồ Án
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.ketqua.*') ? 'active' : '' }}">
                <a href="{{ route('admin.ketqua.index') }}">
                    <i class="fa-solid fa-square-poll-vertical"></i> Quản Lý Kết Quả
                </a>
            </li>

            <!-- Hệ Thống & Báo Cáo -->
            <li class="nav-section-label">Hệ Thống &amp; Báo Cáo</li>
            <li class="{{ request()->routeIs('admin.thongke.*') ? 'active' : '' }}">
                <a href="{{ route('admin.thongke.baocao') }}">
                    <i class="fa-solid fa-chart-line"></i> Báo Cáo &amp; Thống Kê
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.sanpham.*') ? 'active' : '' }}">
                <a href="{{ route('admin.sanpham.index') }}">
                    <i class="fa-solid fa-box-archive"></i> Sản phẩm Nộp
                </a>
            </li>
            <li class="{{ request()->routeIs('thongbao.*') ? 'active' : '' }}">
                <a href="{{ route('thongbao.index') }}">
                    <i class="fa-solid fa-bell"></i> Quản lý Thông báo
                </a>
            </li>
            <li class="{{ request()->routeIs('admin.yeucau.*') ? 'active' : '' }}">
                <a href="{{ route('admin.yeucau.index') }}">
                    <i class="fa-solid fa-key"></i> Duyệt Quên Mật Khẩu
                </a>
            </li>
        </ul>
    </nav>
    <!-- /SIDEBAR -->

    <!-- ═══ MAIN CONTENT ═══ -->
    <div id="content">

        <!-- Banner -->
        <div class="huit-banner-wrap">
            <img src="{{ asset('images/anhbanner.png') }}" alt="Banner HUIT">
        </div>

        <!-- Topbar -->
        <nav class="navbar navbar-expand-lg navbar-custom">
            <div class="container-fluid">
                <div class="page-title-text">
                    <i class="fa-solid fa-angle-right"></i>
                    @yield('page_title', 'Dashboard')
                </div>
                <div class="ms-auto d-flex align-items-center gap-3">
                    <!-- User Dropdown -->
                    <div class="dropdown">
                        <a class="user-avatar-btn dropdown-toggle text-decoration-none"
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                           id="adminUserDropdown">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->TenDangNhap ?? 'Admin') }}&background=CFEBFC&color=27A4F2&bold=true&size=64"
                                 alt="Avatar" class="rounded-circle">
                            <span class="user-name d-none d-sm-inline">{{ Auth::user()->TenDangNhap ?? 'Admin' }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4" aria-labelledby="adminUserDropdown">
                            <li>
                                <div class="px-3 py-2 mb-1 border-bottom">
                                    <div style="font-size: 0.78rem; font-weight: 600; color: var(--v-cyan);">Xin chào!</div>
                                    <div style="font-size: 0.85rem; font-weight: 800; color: var(--navy-deep);">{{ Auth::user()->TenDangNhap ?? 'Admin' }}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 1px;"><i class="fa-solid fa-shield-halved me-1 text-cyan"></i>Giáo vụ / Admin</div>
                                </div>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.show') }}">
                                    <i class="fa-solid fa-user text-cyan"></i> Hồ sơ cá nhân
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('password.change') }}">
                                    <i class="fa-solid fa-key text-cyan"></i> Đổi mật khẩu
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form-admin').submit();">
                                    <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                                </a>
                            </li>
                            <form id="logout-form-admin" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Alerts + Content -->
        <div class="content-body">

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show border-0 mb-4" role="alert"
                     style="border-left: 4px solid #ef4444 !important; border-radius: 12px; background: #FEF2F2;">
                    <i class="fa-solid fa-circle-xmark me-2 text-danger"></i>
                    <strong>Có lỗi xảy ra:</strong>
                    <ul class="mb-0 mt-1 ps-3">
                        @foreach ($errors->all() as $error)
                            <li style="font-size: 0.875rem;">{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @elseif(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 mb-4" role="alert"
                     style="border-left: 4px solid #ef4444 !important; border-radius: 12px; background: #FEF2F2;">
                    <i class="fa-solid fa-circle-xmark me-2 text-danger"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @elseif(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 mb-4" role="alert"
                     style="border-left: 4px solid #10b981 !important; border-radius: 12px; background: #ECFDF5;">
                    <i class="fa-solid fa-circle-check me-2 text-success"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>

        <!-- Footer -->
        @include('partials.footer')

    </div>
    <!-- /MAIN CONTENT -->

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(function () {
            document.querySelectorAll('.alert-success').forEach(function (el) {
                let a = bootstrap.Alert.getOrCreateInstance(el);
                a.close();
            });
        }, 5000);
    });
</script>

@stack('scripts')
</body>
</html>
