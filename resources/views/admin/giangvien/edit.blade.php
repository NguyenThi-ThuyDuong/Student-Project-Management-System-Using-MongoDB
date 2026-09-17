@extends('layouts.admin')
@section('page_title', 'Chỉnh Sửa Giảng Viên: ' . $giangvien->HoTen)

@section('content')
<div class="container-fluid max-w-5xl py-3">
    <div class="card card-modern col-lg-10 col-xl-9 mx-auto shadow-sm">
        <!-- Header -->
        <div class="card-modern-header">
            <div class="title-group">
                <div class="title-icon">
                    <i class="fa-solid fa-user-pen"></i>
                </div>
                <div>
                    <h1 class="main-title">Chỉnh Sửa Giảng Viên</h1>
                    <div class="sub-title">Cập nhật thông tin hồ sơ và bộ môn của giảng viên <strong>{{ $giangvien->HoTen }}</strong></div>
                </div>
            </div>
            <a href="{{ route('giangvien.index') }}" class="btn btn-modern-secondary text-white border-0 bg-white bg-opacity-20 hover-bg-opacity-30">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>

        <!-- Form Body -->
        <div class="card-modern-body p-4">
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

            <form action="{{ route('giangvien.update', $giangvien->_id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-4">
                    <!-- Mã GV / Tên đăng nhập -->
                    <div class="col-md-6">
                        <label class="form-label-modern">
                            <i class="fa-solid fa-id-card-clip me-1 text-cyan"></i> Tên Đăng Nhập / Mã GV
                        </label>
                        <div class="input-group-modern">
                            <input type="text" name="TenDangNhap" value="{{ old('TenDangNhap', $giangvien->taiKhoan->TenDangNhap ?? $giangvien->TenDangNhap ?? $giangvien->MaGV) }}" class="form-control form-control-modern" placeholder="Mã GV">
                        </div>
                    </div>

                    <!-- Họ tên giảng viên -->
                    <div class="col-md-6">
                        <label class="form-label-modern">
                            <i class="fa-solid fa-signature me-1 text-cyan"></i> Họ và Tên Giảng Viên <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-modern">
                            <input type="text" name="HoTen" value="{{ old('HoTen', $giangvien->HoTen) }}" class="form-control form-control-modern" required placeholder="Ví dụ: TS. Nguyễn Văn Minh">
                        </div>
                    </div>

                    <!-- Học vị (Dropdown Select) -->
                    <div class="col-md-6">
                        <label class="form-label-modern">
                            <i class="fa-solid fa-graduation-cap me-1 text-cyan"></i> Học Vị <span class="text-danger">*</span>
                        </label>
                        <select name="HocVi" class="form-select form-select-modern" required>
                            <option value="">-- Chọn học vị --</option>
                            @php $hvVal = old('HocVi', $giangvien->HocVi); @endphp
                            <option value="Cử nhân" {{ $hvVal == 'Cử nhân' ? 'selected' : '' }}>Cử nhân</option>
                            <option value="Thạc sĩ" {{ $hvVal == 'Thạc sĩ' ? 'selected' : '' }}>Thạc sĩ</option>
                            <option value="Tiến sĩ" {{ $hvVal == 'Tiến sĩ' ? 'selected' : '' }}>Tiến sĩ</option>
                            <option value="Phó Giáo sư" {{ $hvVal == 'Phó Giáo sư' ? 'selected' : '' }}>Phó Giáo sư</option>
                            <option value="Giáo sư" {{ $hvVal == 'Giáo sư' ? 'selected' : '' }}>Giáo sư</option>
                        </select>
                    </div>

                    <!-- Bộ môn -->
                    <div class="col-md-6">
                        <label class="form-label-modern">
                            <i class="fa-solid fa-building-columns me-1 text-cyan"></i> Bộ Môn Phụ Trách <span class="text-danger">*</span>
                        </label>
                        <select name="MaBoMon" class="form-select form-select-modern" required>
                            <option value="">-- Chọn bộ môn --</option>
                            @foreach($bomons as $bomon)
                                @php
                                    $bmKey = $bomon->MaBoMon ?? $bomon->_id;
                                    $selected = (old('MaBoMon', $giangvien->MaBoMon) == $bmKey || old('MaBoMon', $giangvien->MaBoMon) == $bomon->TenBoMon);
                                @endphp
                                <option value="{{ $bmKey }}" {{ $selected ? 'selected' : '' }}>
                                    {{ $bomon->TenBoMon }} ({{ $bomon->MaBoMon }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Email -->
                    <div class="col-md-6">
                        <label class="form-label-modern">
                            <i class="fa-solid fa-envelope me-1 text-cyan"></i> Địa Chỉ Email <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-modern">
                            <input type="email" name="Email" value="{{ old('Email', $giangvien->Email) }}" class="form-control form-control-modern" required placeholder="minh.nv@huit.edu.vn">
                        </div>
                    </div>

                    <!-- Số điện thoại -->
                    <div class="col-md-6">
                        <label class="form-label-modern">
                            <i class="fa-solid fa-phone me-1 text-cyan"></i> Số Điện Thoại Liên Hệ <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-modern">
                            <input type="text" name="SoDienThoai" value="{{ old('SoDienThoai', $giangvien->SoDienThoai) }}" class="form-control form-control-modern" required placeholder="0901234567">
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="d-flex justify-content-end gap-3 mt-5 pt-3 border-top">
                    <a href="{{ route('giangvien.index') }}" class="btn btn-modern-secondary">
                        <i class="fa-solid fa-xmark me-1"></i> Hủy Bỏ
                    </a>
                    <button type="submit" class="btn btn-cyan btn-sm rounded-pill px-4 py-2">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Lưu Cập Nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection