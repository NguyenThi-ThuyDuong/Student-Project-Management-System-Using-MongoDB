<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('page_title', 'Giảng Viên') – Hệ Thống QLĐA | HUIT</title>
    <meta name="description" content="Cổng Giảng Viên – Hệ thống quản lý đồ án HUIT">
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
                    <span class="sidebar-subtitle">Cổng Giảng Viên HUIT</span>
                </div>
            </div>
        </div>

        <div class="px-3 pb-2">
            <div class="role-badge">
                <i class="fa-solid fa-chalkboard-user"></i>
                Giảng Viên
            </div>
        </div>

        <ul class="list-unstyled components">
            <!-- 1. Dashboard -->
            <li class="{{ request()->routeIs('giangvien.dashboard') ? 'active' : '' }}">
                <a href="{{ route('giangvien.dashboard') }}">
                    <i class="fa-solid fa-chart-line"></i> Tổng Quan Dashboard
                </a>
            </li>

            <!-- 2 & 3. Lớp phụ trách & Sinh viên/Nhóm -->
            <li class="nav-section-label">Lớp &amp; Sinh Viên</li>
            <li class="{{ request()->routeIs('giangvien.lop.*') ? 'active' : '' }}">
                <a href="{{ route('giangvien.lop.index') }}">
                    <i class="fa-solid fa-users-gear"></i> Lớp phụ trách &amp; Nhóm SV
                </a>
            </li>

            <!-- 4 & 5. Đề tài & Duyệt đăng ký -->
            <li class="nav-section-label">Quản Lý Đề Tài</li>
            <li class="{{ request()->routeIs('giangvien.detai.*') ? 'active' : '' }}">
                <a href="{{ route('giangvien.detai.index') }}">
                    <i class="fa-solid fa-folder-open"></i> Đề tài của tôi
                </a>
            </li>
            <li class="{{ request()->routeIs('giangvien.duyet.*') ? 'active' : '' }}">
                <a href="{{ route('giangvien.duyet.index') }}">
                    <i class="fa-solid fa-user-check"></i> Duyệt đăng ký đề tài
                </a>
            </li>

            <!-- 6, 7 & 8. Tiến độ, Báo cáo, Chấm điểm -->
            <li class="nav-section-label">Hướng Dẫn &amp; Chấm Điểm</li>
            <li class="{{ request()->routeIs('giangvien.baocao.*') ? 'active' : '' }}">
                <a href="{{ route('giangvien.baocao.index') }}">
                    <i class="fa-solid fa-clipboard-check"></i> Theo dõi Tiến độ &amp; Báo cáo
                </a>
            </li>
            <li class="{{ request()->routeIs('giangvien.sanpham.*') ? 'active' : '' }}">
                <a href="{{ route('giangvien.sanpham.index') }}">
                    <i class="fa-solid fa-box-open"></i> Sản phẩm nộp
                </a>
            </li>
            <li class="{{ request()->routeIs('giangvien.chamdiem.*') ? 'active' : '' }}">
                <a href="{{ route('giangvien.chamdiem.index') }}">
                    <i class="fa-solid fa-gavel"></i> Chấm điểm đồ án
                </a>
            </li>

            <!-- 9, 10 & 11. Kết quả, Thông báo, Cá nhân -->
            <li class="nav-section-label">Hệ Thống &amp; Cá Nhân</li>
            @php
                $unreadGvNoti = \App\Models\ThongBao::where('MaTK', Auth::user()->MaTK)->where('DaDoc', false)->count();
            @endphp
            <li class="{{ request()->routeIs('giangvien.thongbao.*') ? 'active' : '' }}">
                <a href="{{ route('giangvien.thongbao.index') }}" class="d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-bell"></i> Thông báo</span>
                    @if($unreadGvNoti > 0)
                        <span class="badge bg-danger rounded-pill" style="font-size: 0.65rem;">{{ $unreadGvNoti }}</span>
                    @endif
                </a>
            </li>
            <li class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <a href="{{ route('profile.show') }}">
                    <i class="fa-solid fa-id-card"></i> Tài khoản cá nhân
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
                    @if($unreadGvNoti > 0)
                    <a href="{{ route('giangvien.thongbao.index') }}" class="position-relative text-decoration-none me-2"
                       title="{{ $unreadGvNoti }} thông báo chưa đọc">
                        <i class="fa-solid fa-bell" style="font-size: 1.15rem; color: var(--v-cyan);"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                              style="font-size: 0.6rem; padding: 3px 5px;">{{ $unreadGvNoti }}</span>
                    </a>
                    @endif

                    <!-- User Dropdown -->
                    <div class="dropdown">
                        <a class="user-avatar-btn dropdown-toggle text-decoration-none"
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                           id="gvUserDropdown">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->giangVien->HoTen ?? Auth::user()->TenDangNhap) }}&background=CFEBFC&color=27A4F2&bold=true&size=64"
                                 alt="Avatar" class="rounded-circle">
                            <span class="user-name d-none d-sm-inline">{{ Auth::user()->giangVien->HoTen ?? Auth::user()->TenDangNhap }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4" aria-labelledby="gvUserDropdown">
                            <li>
                                <div class="px-3 py-2 mb-1 border-bottom">
                                    <div style="font-size: 0.78rem; font-weight: 600; color: var(--v-cyan);">Xin chào!</div>
                                    <div style="font-size: 0.85rem; font-weight: 800; color: var(--navy-deep);">{{ Auth::user()->giangVien->HoTen ?? Auth::user()->TenDangNhap }}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 1px;"><i class="fa-solid fa-chalkboard-user me-1 text-cyan"></i>Giảng Viên</div>
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
                                   onclick="event.preventDefault(); document.getElementById('logout-form-gv').submit();">
                                    <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                                </a>
                            </li>
                            <form id="logout-form-gv" action="{{ route('logout') }}" method="POST" class="d-none">
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