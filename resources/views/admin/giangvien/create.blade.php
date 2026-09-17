@extends('layouts.admin')
@section('page_title', 'Thêm Giảng Viên Mới')

@section('content')
<div class="container-fluid max-w-5xl py-3">
    <div class="card card-modern col-lg-10 col-xl-9 mx-auto">
        <!-- Header -->
        <div class="card-modern-header">
            <div class="title-group">
                <div class="title-icon">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
                <div>
                    <h1 class="main-title">Thêm Giảng Viên Mới</h1>
                    <div class="sub-title">Nhập thông tin chi tiết để khởi tạo tài khoản và hồ sơ giảng viên</div>
                </div>
            </div>
            <a href="{{ route('giangvien.index') }}" class="btn btn-modern-secondary text-white border-0 bg-white bg-opacity-20 hover-bg-opacity-30">
                <i class="fa-solid fa-arrow-left"></i> Quay lại
            </a>
        </div>

        <!-- Form Body -->
        <div class="card-modern-body">
            <!-- Alert Thông báo lỗi Hệ thống / Validation -->
            @if ($errors->any())
                <div class="alert alert-modern-danger mb-4">
                    <i class="fa-solid fa-circle-exclamation text-danger fs-5 flex-shrink-0 mt-1"></i>
                    <div>
                        <strong class="fw-bold d-block mb-1">Vui lòng kiểm tra lại thông tin nhập liệu:</strong>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Alert Thông báo Mật khẩu mặc định -->
            <div class="alert alert-modern-info mb-4">
                <i class="fa-solid fa-circle-info fs-5 flex-shrink-0 mt-1"></i>
                <div>
                    <strong class="fw-bold">Lưu ý hệ thống:</strong> Mật khẩu tài khoản đăng nhập của giảng viên mới sẽ được khởi tạo mặc định là <code class="bg-white px-2 py-1 rounded text-primary fw-bold border">123456</code>.
                </div>
            </div>

            <form action="{{ route('giangvien.store') }}" method="POST">
                @csrf
                <div class="row g-4">
                    <!-- Tên đăng nhập -->
                    <div class="col-md-6">
                        <label class="form-label-modern">
                            <i class="fa-solid fa-id-card-clip"></i> Tên Đăng Nhập / Mã GV <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-modern">
                            <input type="text" name="TenDangNhap" value="{{ old('TenDangNhap') }}" class="form-control form-control-modern" required placeholder="Ví dụ: GV202401">
                            <i class="fa-solid fa-user input-icon"></i>
                        </div>
                    </div>

                    <!-- Họ tên giảng viên -->
                    <div class="col-md-6">
                        <label class="form-label-modern">
                            <i class="fa-solid fa-signature"></i> Họ và Tên Giảng Viên <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-modern">
                            <input type="text" name="HoTen" value="{{ old('HoTen') }}" class="form-control form-control-modern" required placeholder="Ví dụ: TS. Nguyễn Văn Minh">
                            <i class="fa-solid fa-font input-icon"></i>
                        </div>
                    </div>

                    <!-- Học vị -->
                    <div class="col-md-6">
                        <label class="form-label-modern">
                            <i class="fa-solid fa-graduation-cap"></i> Học Vị <span class="text-danger">*</span>
                        </label>
                        <select name="HocVi" class="form-select form-select-modern" required>
                            <option value="">-- Chọn học vị --</option>
                            <option value="Cử nhân" {{ old('HocVi') == 'Cử nhân' ? 'selected' : '' }}>Cử nhân</option>
                            <option value="Thạc sĩ" {{ old('HocVi', 'Thạc sĩ') == 'Thạc sĩ' ? 'selected' : '' }}>Thạc sĩ</option>
                            <option value="Tiến sĩ" {{ old('HocVi') == 'Tiến sĩ' ? 'selected' : '' }}>Tiến sĩ</option>
                            <option value="Phó Giáo sư" {{ old('HocVi') == 'Phó Giáo sư' ? 'selected' : '' }}>Phó Giáo sư</option>
                            <option value="Giáo sư" {{ old('HocVi') == 'Giáo sư' ? 'selected' : '' }}>Giáo sư</option>
                        </select>
                    </div>

                    <!-- Bộ môn -->
                    <div class="col-md-6">
                        <label class="form-label-modern">
                            <i class="fa-solid fa-building-columns"></i> Bộ Môn Phụ Trách <span class="text-danger">*</span>
                        </label>
                        <select name="MaBoMon" class="form-select form-select-modern" required>
                            <option value="">-- Chọn bộ môn --</option>
                            @foreach($bomons as $bomon)
                                <option value="{{ $bomon->MaBoMon ?? $bomon->_id }}" {{ old('MaBoMon') == ($bomon->MaBoMon ?? $bomon->_id) ? 'selected' : '' }}>
                                    {{ $bomon->TenBoMon }} ({{ $bomon->MaBoMon }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Email giảng viên -->
                    <div class="col-md-6">
                        <label class="form-label-modern">
                            <i class="fa-solid fa-envelope"></i> Địa Chỉ Email <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-modern">
                            <input type="email" name="Email" value="{{ old('Email') }}" class="form-control form-control-modern" required placeholder="Ví dụ: minh.nv@huit.edu.vn">
                            <i class="fa-solid fa-at input-icon"></i>
                        </div>
                    </div>

                    <!-- Số điện thoại -->
                    <div class="col-md-6">
                        <label class="form-label-modern">
                            <i class="fa-solid fa-phone"></i> Số Điện Thoại Liên Hệ <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-modern">
                            <input type="text" name="SoDienThoai" value="{{ old('SoDienThoai') }}" class="form-control form-control-modern" required placeholder="Ví dụ: 0901234567">
                            <i class="fa-solid fa-mobile-screen input-icon"></i>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="d-flex justify-content-end gap-3 mt-5 pt-3 border-top">
                    <a href="{{ route('giangvien.index') }}" class="btn btn-modern-secondary">
                        <i class="fa-solid fa-xmark"></i> Hủy Bỏ
                    </a>
                    <button type="submit" class="btn btn-modern-primary">
                        <i class="fa-solid fa-user-plus"></i> Thêm Giảng Viên
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection