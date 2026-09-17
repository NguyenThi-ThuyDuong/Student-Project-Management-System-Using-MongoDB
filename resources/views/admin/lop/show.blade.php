@extends('layouts.admin')
@section('page_title', 'Chi Tiết Lớp ' . $lop->TenLop)

@section('content')
<div class="page-header-zone mb-3">
    <div>
        <h1 class="fw-bold"><i class="fa-solid fa-users-rectangle me-2 text-cyan"></i>Chi Tiết Lớp: {{ $lop->TenLop }}</h1>
        <div class="text-muted small">Mã Lớp: <span class="badge-cyan me-2">{{ $lop->MaLop }}</span> Ngành: <span class="badge-indigo me-2">{{ $lop->nganh->TenNganh ?? 'Chưa xác định' }}</span> Khóa: <strong>{{ $lop->KhoaHoc }}</strong></div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('lop.edit', $lop->_id ?? $lop->MaLop) }}" class="btn btn-cyan btn-sm rounded-pill px-3">
            <i class="fa-solid fa-pen me-1"></i>Chỉnh Sửa
        </a>
        <a href="{{ route('lop.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fa-solid fa-arrow-left me-1"></i>Quay Lại
        </a>
    </div>
</div>

<!-- 8 Tabs Header -->
<ul class="nav nav-tabs nav-tabs-vietnix" id="lopDetailTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-thongtin-tab" data-bs-toggle="tab" data-bs-target="#tab-thongtin" type="button" role="tab">
            <i class="fa-solid fa-circle-info me-1"></i> THÔNG TIN
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-sinhvien-tab" data-bs-toggle="tab" data-bs-target="#tab-sinhvien" type="button" role="tab">
            <i class="fa-solid fa-user-graduate me-1"></i> SINH VIÊN ({{ count($sinhViens) }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-hocphan-tab" data-bs-toggle="tab" data-bs-target="#tab-hocphan" type="button" role="tab">
            <i class="fa-solid fa-book-open me-1"></i> HỌC PHẦN
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-giangvien-tab" data-bs-toggle="tab" data-bs-target="#tab-giangvien" type="button" role="tab">
            <i class="fa-solid fa-chalkboard-user me-1"></i> GIẢNG VIÊN ({{ count($phanCongs) }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-doan-tab" data-bs-toggle="tab" data-bs-target="#tab-doan" type="button" role="tab">
            <i class="fa-solid fa-folder-open me-1"></i> ĐỒ ÁN ({{ count($nhoms) }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-tiendo-tab" data-bs-toggle="tab" data-bs-target="#tab-tiendo" type="button" role="tab">
            <i class="fa-solid fa-spinner me-1"></i> TIẾN ĐỘ
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-baocao-tab" data-bs-toggle="tab" data-bs-target="#tab-baocao" type="button" role="tab">
            <i class="fa-solid fa-file-invoice me-1"></i> BÁO CÁO
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-ketqua-tab" data-bs-toggle="tab" data-bs-target="#tab-ketqua" type="button" role="tab">
            <i class="fa-solid fa-square-poll-vertical me-1"></i> KẾT QUẢ
        </button>
    </li>
</ul>

<!-- 8 Tabs Content -->
<div class="tab-content" id="lopDetailTabContent">
    
    <!-- 1. THÔNG TIN -->
    <div class="tab-pane fade show active" id="tab-thongtin" role="tabpanel">
        <div class="card-modern">
            <div class="card-modern-header">
                <div class="title-group">
                    <div class="title-icon"><i class="fa-solid fa-building"></i></div>
                    <div>
                        <div class="main-title">Thông Tin Tổng Quan Lớp {{ $lop->TenLop }}</div>
                        <div class="sub-title">Cấu trúc hành chính & khóa học</div>
                    </div>
                </div>
            </div>
            <div class="card-modern-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="saas-info-row">
                            <span class="saas-info-label"><i class="fa-solid fa-hashtag"></i> Mã Lớp:</span>
                            <span class="saas-info-value">{{ $lop->MaLop }}</span>
                        </div>
                        <div class="saas-info-row">
                            <span class="saas-info-label"><i class="fa-solid fa-signature"></i> Tên Lớp:</span>
                            <span class="saas-info-value">{{ $lop->TenLop }}</span>
                        </div>
                        <div class="saas-info-row">
                            <span class="saas-info-label"><i class="fa-solid fa-graduation-cap"></i> Khóa Học:</span>
                            <span class="saas-info-value">{{ $lop->KhoaHoc }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="saas-info-row">
                            <span class="saas-info-label"><i class="fa-solid fa-book"></i> Chuyên Ngành:</span>
                            <span class="saas-info-value">{{ $lop->nganh->TenNganh ?? 'Chưa xác định' }}</span>
                        </div>
                        <div class="saas-info-row">
                            <span class="saas-info-label"><i class="fa-solid fa-calendar"></i> Học Kỳ Hiện Tại:</span>
                            <span class="saas-info-value">{{ $lop->hocKy->TenHocKy ?? 'HK1 2026–2027' }}</span>
                        </div>
                        <div class="saas-info-row">
                            <span class="saas-info-label"><i class="fa-solid fa-users"></i> Sĩ Số Lớp:</span>
                            <span class="saas-info-value"><span class="badge-cyan">{{ count($sinhViens) }} Sinh viên</span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. SINH VIÊN -->
    <div class="tab-pane fade" id="tab-sinhvien" role="tabpanel">
        <div class="saas-card-grid">
            @forelse($sinhViens as $sv)
            <div class="saas-card">
                <div class="saas-card-header">
                    <div class="saas-card-avatar"><i class="fa-solid fa-user-graduate"></i></div>
                    <div>
                        <h4 class="saas-card-title">{{ $sv->HoTen }}</h4>
                        <div class="saas-card-subtitle">MSSV: {{ $sv->MaSV }}</div>
                    </div>
                </div>
                <div class="saas-card-body">
                    <div class="saas-info-row">
                        <span class="saas-info-label"><i class="fa-solid fa-envelope"></i> Email:</span>
                        <span class="saas-info-value text-truncate ms-2" style="max-width: 170px;">{{ $sv->Email }}</span>
                    </div>
                    <div class="saas-info-row">
                        <span class="saas-info-label"><i class="fa-solid fa-phone"></i> SĐT:</span>
                        <span class="saas-info-value">{{ $sv->SoDienThoai ?? '—' }}</span>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-4 text-muted">Chưa có sinh viên nào trong lớp này.</div>
            @endforelse
        </div>
    </div>

    <!-- 3. HỌC PHẦN -->
    <div class="tab-pane fade" id="tab-hocphan" role="tabpanel">
        <div class="card-modern">
            <div class="card-modern-body">
                <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: var(--v-ice-blue);">
                    <i class="fa-solid fa-book-bookmark fs-3 text-cyan"></i>
                    <div>
                        <h5 class="fw-bold mb-1">Đồ Án Chuyên Ngành / Khóa Luận Tốt Nghiệp</h5>
                        <div class="small text-muted">Học phần áp dụng cho lớp {{ $lop->TenLop }} trong học kỳ hiện tại</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. GIẢNG VIÊN -->
    <div class="tab-pane fade" id="tab-giangvien" role="tabpanel">
        <div class="saas-card-grid">
            @forelse($phanCongs as $pc)
            <div class="saas-card">
                <div class="saas-card-header">
                    <div class="saas-card-avatar" style="color: var(--v-indigo);"><i class="fa-solid fa-chalkboard-user"></i></div>
                    <div>
                        <h4 class="saas-card-title">{{ $pc->giangVien->HoTen ?? 'Giảng viên' }}</h4>
                        <div class="saas-card-subtitle"><span class="badge-indigo">GV Phụ Trách &amp; GVHD</span></div>
                    </div>
                </div>
                <div class="saas-card-body">
                    <div class="saas-info-row">
                        <span class="saas-info-label"><i class="fa-solid fa-envelope"></i> Email:</span>
                        <span class="saas-info-value">{{ $pc->giangVien->Email ?? '—' }}</span>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-4 text-muted">
                Chưa có giảng viên nào được phân công phụ trách lớp này. 
                <a href="{{ route('phancong.index') }}" class="btn btn-cyan btn-sm ms-2 rounded-pill px-3">Phân công ngay</a>
            </div>
            @endforelse
        </div>
    </div>

    <!-- 5. ĐỒ ÁN -->
    <div class="tab-pane fade" id="tab-doan" role="tabpanel">
        <div class="saas-card-grid">
            @forelse($nhoms as $n)
            <div class="saas-card">
                <div class="saas-card-header">
                    <div class="saas-card-avatar"><i class="fa-solid fa-users"></i></div>
                    <div>
                        <h4 class="saas-card-title">{{ $n->TenNhom ?? 'Nhóm ' . $n->MaNhom }}</h4>
                        <div class="saas-card-subtitle"><span class="badge-cyan">{{ count($n->ThanhVien ?? []) }} Thành viên</span></div>
                    </div>
                </div>
                <div class="saas-card-body">
                    <div class="saas-info-row">
                        <span class="saas-info-label"><i class="fa-solid fa-folder"></i> Đề tài:</span>
                        <span class="saas-info-value text-truncate ms-2" style="max-width: 170px;">{{ $n->getTenDeTaiDangKy() }}</span>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-4 text-muted">Chưa có nhóm đồ án nào đăng ký trong lớp này.</div>
            @endforelse
        </div>
    </div>

    <!-- 6. TIẾN ĐỘ -->
    <div class="tab-pane fade" id="tab-tiendo" role="tabpanel">
        <div class="card-modern">
            <div class="card-modern-body">
                <h5 class="fw-bold mb-3 text-cyan"><i class="fa-solid fa-list-check me-2"></i>Các Mốc Tiến Độ Lớp {{ $lop->TenLop }}</h5>
                <div class="timeline-huit">
                    <div class="timeline-item-huit">
                        <div class="timeline-marker-huit done"><i class="fa-solid fa-check"></i></div>
                        <div class="timeline-content-huit">
                            <h6 class="fw-bold mb-1">Mốc 1: Phân tích yêu cầu &amp; Đăng ký đề tài</h6>
                            <span class="badge-success">Đã hoàn thành</span>
                        </div>
                    </div>
                    <div class="timeline-item-huit">
                        <div class="timeline-marker-huit done"><i class="fa-solid fa-check"></i></div>
                        <div class="timeline-content-huit">
                            <h6 class="fw-bold mb-1">Mốc 2: Thiết kế hệ thống &amp; cơ sở dữ liệu</h6>
                            <span class="badge-success">Đã hoàn thành</span>
                        </div>
                    </div>
                    <div class="timeline-item-huit">
                        <div class="timeline-marker-huit"><i class="fa-solid fa-spinner"></i></div>
                        <div class="timeline-content-huit">
                            <h6 class="fw-bold mb-1">Mốc 3: Lập trình phát triển các chức năng</h6>
                            <span class="badge-cyan">Đang thực hiện</span>
                        </div>
                    </div>
                    <div class="timeline-item-huit">
                        <div class="timeline-marker-huit"><i class="fa-solid fa-clock"></i></div>
                        <div class="timeline-content-huit">
                            <h6 class="fw-bold mb-1">Mốc 4: Nộp báo cáo &amp; Chấm điểm đồ án</h6>
                            <span class="badge-indigo">Chưa thực hiện</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 7. BÁO CÁO -->
    <div class="tab-pane fade" id="tab-baocao" role="tabpanel">
        <div class="card-modern">
            <div class="card-modern-body text-center py-4 text-muted">
                <i class="fa-solid fa-file-invoice fs-2 mb-2 text-cyan"></i>
                <div>Danh sách báo cáo tiến độ của sinh viên lớp {{ $lop->TenLop }}</div>
            </div>
        </div>
    </div>

    <!-- 8. KẾT QUẢ -->
    <div class="tab-pane fade" id="tab-ketqua" role="tabpanel">
        <div class="card-modern">
            <div class="card-modern-body text-center py-4 text-muted">
                <i class="fa-solid fa-square-poll-vertical fs-2 mb-2 text-indigo"></i>
                <div>Bảng tổng hợp điểm và kết quả đồ án của sinh viên lớp {{ $lop->TenLop }}</div>
            </div>
        </div>
    </div>

</div>
@endsection
