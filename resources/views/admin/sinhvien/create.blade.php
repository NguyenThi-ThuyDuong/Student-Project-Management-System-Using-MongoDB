@extends('layouts.admin')
@section('page_title', 'Thêm Sinh Viên Mới')

@section('content')
<div class="container-fluid max-w-5xl py-3">
    <div class="card card-modern col-lg-10 col-xl-9 mx-auto">
        <!-- Header -->
        <div class="card-modern-header">
            <div class="title-group">
                <div class="title-icon">
                    <i class="fa-solid fa-user-plus text-cyan"></i>
                </div>
                <div>
                    <h1 class="main-title mb-0 fs-5 fw-bold text-white">Thêm Sinh Viên Mới</h1>
                    <div class="sub-title text-white-50 small">Nhập thông tin chi tiết để khởi tạo tài khoản và hồ sơ sinh viên</div>
                </div>
            </div>
            <a href="{{ route('sinhvien.index') }}" class="btn btn-sm btn-light border rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i>Quay lại
            </a>
        </div>

        <!-- Form Body -->
        <div class="card-modern-body p-4">
            <!-- Alert Thông báo Mật khẩu mặc định -->
            <div class="alert alert-info border-0 rounded-3 mb-4" style="background: #EFF6FF; border-left: 4px solid var(--v-cyan) !important;">
                <i class="fa-solid fa-circle-info me-2 text-cyan"></i>
                <strong>Lưu ý hệ thống:</strong> Mật khẩu tài khoản đăng nhập của sinh viên mới sẽ được khởi tạo mặc định là <code class="bg-white px-2 py-1 rounded text-primary fw-bold border">123456</code>.
            </div>

            <form action="{{ route('sinhvien.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <!-- Tên đăng nhập / MSSV -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">
                            <i class="fa-solid fa-id-card-clip text-cyan me-1"></i>Tên Đăng Nhập / MSSV <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="TenDangNhap" value="{{ old('TenDangNhap') }}" class="form-control" required placeholder="Ví dụ: SV202401">
                    </div>

                    <!-- Họ tên sinh viên -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">
                            <i class="fa-solid fa-user text-cyan me-1"></i>Họ và Tên Sinh Viên <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="HoTen" value="{{ old('HoTen') }}" class="form-control" required placeholder="Ví dụ: Nguyễn Văn An">
                    </div>

                    <!-- Lớp hành chính -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">
                            <i class="fa-solid fa-users-rectangle text-cyan me-1"></i>Lớp Hành Chính <span class="text-danger">*</span>
                        </label>
                        <select name="MaLop" class="form-select" required>
                            <option value="">-- Chọn lớp hành chính --</option>
                            @foreach($lops as $lop)
                                <option value="{{ $lop->MaLop ?? $lop->_id }}" {{ old('MaLop') == ($lop->MaLop ?? $lop->_id) ? 'selected' : '' }}>
                                    {{ $lop->TenLop }} ({{ $lop->MaLop }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Ngành học -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">
                            <i class="fa-solid fa-building-columns text-cyan me-1"></i>Ngành Học
                        </label>
                        <select name="MaNganh" class="form-select">
                            <option value="">-- Chọn ngành học --</option>
                            @foreach($nganhs as $nganh)
                                <option value="{{ $nganh->MaNganh ?? $nganh->_id }}" {{ old('MaNganh') == ($nganh->MaNganh ?? $nganh->_id) ? 'selected' : '' }}>
                                    {{ $nganh->TenNganh }} ({{ $nganh->MaNganh }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Khóa học -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">
                            <i class="fa-solid fa-graduation-cap text-cyan me-1"></i>Khóa Học
                        </label>
                        <input type="text" name="KhoaHoc" value="{{ old('KhoaHoc', '12') }}" class="form-control" placeholder="Ví dụ: 12">
                    </div>

                    <!-- Ngày sinh -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">
                            <i class="fa-solid fa-cake-candles text-cyan me-1"></i>Ngày Sinh
                        </label>
                        <input type="date" name="NgaySinh" value="{{ old('NgaySinh') }}" class="form-control">
                    </div>

                    <!-- Email sinh viên -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">
                            <i class="fa-solid fa-envelope text-cyan me-1"></i>Địa Chỉ Email <span class="text-danger">*</span>
                        </label>
                        <input type="email" name="Email" value="{{ old('Email') }}" class="form-control" required placeholder="Ví dụ: an.nv@student.huit.edu.vn">
                    </div>

                    <!-- Số điện thoại -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">
                            <i class="fa-solid fa-phone text-cyan me-1"></i>Số Điện Thoại Liên Hệ <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="SoDienThoai" value="{{ old('SoDienThoai') }}" class="form-control" required placeholder="Ví dụ: 0901234567">
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('sinhvien.index') }}" class="btn btn-light border btn-sm rounded-pill px-4">
                        <i class="fa-solid fa-xmark me-1"></i>Hủy Bỏ
                    </a>
                    <button type="submit" class="btn btn-cyan btn-sm rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-user-plus me-1"></i>Thêm Sinh Viên
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection