@extends('layouts.admin')
@section('page_title', 'Tổng Quan Hệ Thống')
@section('content')
<div class="page-header-zone mb-4">
    <div>
        <h1 class="fw-bold"><i class="fa-solid fa-chart-line me-2 text-cyan"></i>Tổng Quan Hệ Thống</h1>
        <div class="text-muted small">Hệ thống quản lý đồ án sinh viên — Trường Đại học Công Thương TP.HCM (HUIT)</div>
    </div>
    @if(isset($soYeuCauMatKhau) && $soYeuCauMatKhau > 0)
        <a href="{{ route('admin.yeucau.index') }}" class="btn btn-cyan rounded-pill px-3 shadow-sm animate__animated animate__pulse animate__infinite">
            <i class="fa-solid fa-key me-1"></i> Yêu cầu đổi mật khẩu ({{ $soYeuCauMatKhau }})
        </a>
    @endif
</div>

<!-- Stat Cards System -->
<div class="row g-3 mb-4">
    <!-- Sinh Viên -->
    <div class="col-md-4 col-xl-2">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--v-ice-blue); border-color: var(--v-pale-sky);">
                <i class="fa-solid fa-user-graduate" style="color: var(--v-cyan);"></i>
            </div>
            <div>
                <div class="stat-value">{{ $soSinhVien }}</div>
                <div class="stat-label">Sinh Viên</div>
            </div>
        </div>
    </div>
    
    <!-- Giảng Viên -->
    <div class="col-md-4 col-xl-2">
        <div class="stat-card">
            <div class="stat-icon" style="background: #EEF2FF; border-color: var(--v-periwinkle);">
                <i class="fa-solid fa-chalkboard-user" style="color: var(--v-indigo);"></i>
            </div>
            <div>
                <div class="stat-value">{{ $soGiangVien }}</div>
                <div class="stat-label">Giảng Viên</div>
            </div>
        </div>
    </div>

    <!-- Lớp Học Phần -->
    <div class="col-md-4 col-xl-2">
        <div class="stat-card">
            <div class="stat-icon" style="background: #ECFDF5; border-color: #A7F3D0;">
                <i class="fa-solid fa-book-bookmark" style="color: #047857;"></i>
            </div>
            <div>
                <div class="stat-value">{{ $soLopHocPhan ?? 0 }}</div>
                <div class="stat-label">Lớp Học Phần</div>
            </div>
        </div>
    </div>
    
    <!-- Đề Tài -->
    <div class="col-md-4 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: #FFFBEB; border-color: #FDE68A;">
                <i class="fa-solid fa-book-open" style="color: #D97706;"></i>
            </div>
            <div>
                <div class="stat-value">{{ $soDeTai }}</div>
                <div class="stat-label">Đề Tài Khóa Luận</div>
            </div>
        </div>
    </div>
    
    <!-- Nhóm -->
    <div class="col-md-4 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--v-ice-blue); border-color: var(--v-light-sky);">
                <i class="fa-solid fa-users" style="color: var(--v-cyan);"></i>
            </div>
            <div>
                <div class="stat-value">{{ $soNhom }}</div>
                <div class="stat-label">Nhóm Đồ Án</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Chart -->
    <div class="col-lg-6">
        <div class="card-modern h-100">
            <div class="card-modern-header">
                <div class="title-group">
                    <div class="title-icon">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <div>
                        <div class="main-title">Thống Kê Tiến Độ Đồ Án</div>
                        <div class="sub-title">Tỷ lệ các nhóm theo từng trạng thái</div>
                    </div>
                </div>
            </div>
            <div class="card-modern-body d-flex flex-column align-items-center justify-content-center p-4">
                <div style="width: 100%; max-width: 360px;">
                    <canvas id="trangThaiChart"></canvas>
                </div>
                @if(empty($chartLabels))
                    <div class="text-center text-muted mt-3 small">
                        <i class="fa-solid fa-circle-info me-1 text-cyan"></i> Chưa có dữ liệu nhóm đồ án trong hệ thống.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Shortcuts Grid -->
    <div class="col-lg-6">
        <div class="card-modern h-100">
            <div class="card-modern-header">
                <div class="title-group">
                    <div class="title-icon">
                        <i class="fa-solid fa-compass"></i>
                    </div>
                    <div>
                        <div class="main-title">Lối Tắt Thao Tác Nhanh</div>
                        <div class="sub-title">Truy cập nhanh các chức năng quản trị</div>
                    </div>
                </div>
            </div>
            <div class="card-modern-body p-4">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <a href="{{ route('sinhvien.index') }}" class="saas-card text-decoration-none p-3 d-flex align-items-center gap-3">
                            <div class="saas-card-avatar" style="width: 42px; height: 42px;">
                                <i class="fa-solid fa-user-graduate"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.88rem;">Quản lý Sinh Viên</div>
                                <div class="small text-muted">Danh sách &amp; Import</div>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6">
                        <a href="{{ route('giangvien.index') }}" class="saas-card text-decoration-none p-3 d-flex align-items-center gap-3">
                            <div class="saas-card-avatar" style="width: 42px; height: 42px; background: #EEF2FF; color: var(--v-indigo);">
                                <i class="fa-solid fa-chalkboard-user"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.88rem;">Quản lý Giảng Viên</div>
                                <div class="small text-muted">Hồ sơ &amp; Đơn vị</div>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6">
                        <a href="{{ route('lop.index') }}" class="saas-card text-decoration-none p-3 d-flex align-items-center gap-3">
                            <div class="saas-card-avatar" style="width: 42px; height: 42px; background: #ECFDF5; color: #047857;">
                                <i class="fa-solid fa-users-rectangle"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.88rem;">Quản lý Lớp Hành Chính</div>
                                <div class="small text-muted">8 Tabs Chi Tiết Lớp</div>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6">
                        <a href="{{ route('phancong.index') }}" class="saas-card text-decoration-none p-3 d-flex align-items-center gap-3">
                            <div class="saas-card-avatar" style="width: 42px; height: 42px; background: #FFFBEB; color: #D97706;">
                                <i class="fa-solid fa-sitemap"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.88rem;">Phân Công Lớp (GVHD)</div>
                                <div class="small text-muted">Tự động gán GVHD</div>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6">
                        <a href="{{ route('thongbao.index') }}" class="saas-card text-decoration-none p-3 d-flex align-items-center gap-3">
                            <div class="saas-card-avatar" style="width: 42px; height: 42px; background: var(--v-ice-blue); color: var(--v-cyan);">
                                <i class="fa-solid fa-bullhorn"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.88rem;">Gửi Thông Báo</div>
                                <div class="small text-muted">Thông báo toàn trường</div>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6">
                        <a href="{{ route('admin.yeucau.index') }}" class="saas-card text-decoration-none p-3 d-flex align-items-center gap-3">
                            <div class="saas-card-avatar" style="width: 42px; height: 42px; background: #F1F5F9; color: var(--navy-deep);">
                                <i class="fa-solid fa-key"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.88rem;">Duyệt Đổi Mật Khẩu</div>
                                <div class="small text-muted">Duyệt cấp lại MK</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const labels = {!! json_encode($chartLabels) !!};
        const data = {!! json_encode($chartData) !!};
        
        if (labels.length > 0) {
            const ctx = document.getElementById('trangThaiChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: [
                            '#27A4F2', // Vietnix Cyan
                            '#6586E6', // Vietnix Indigo
                            '#3EAEF4', // Vietnix Sky
                            '#10B981', // Emerald Success
                            '#F59E0B', // Amber
                            '#91A8ED', // Lavender
                        ],
                        borderWidth: 3,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 16,
                                font: {
                                    family: "'Be Vietnam Pro', sans-serif",
                                    size: 12
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endsection
