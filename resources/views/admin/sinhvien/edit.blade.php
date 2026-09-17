@extends('layouts.admin')
@section('page_title', 'Chỉnh Sửa Hồ Sơ Sinh Viên')

@section('content')
<div class="container-fluid max-w-5xl py-3">
    <div class="card card-modern col-lg-10 col-xl-9 mx-auto shadow-sm">
        <!-- Header -->
        <div class="card-modern-header">
            <div class="d-flex align-items-center gap-2">
                <div class="p-2 bg-white bg-opacity-20 rounded-circle text-white">
                    <i class="fa-solid fa-user-pen fs-5"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold text-white">Chỉnh Sửa Hồ Sơ Sinh Viên</h5>
                    <span class="text-white-50 small">MSSV: {{ $sinhvien->MaSV }}</span>
                </div>
            </div>
            <a href="{{ route('sinhvien.index') }}" class="btn btn-sm btn-light border rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i>Quay lại
            </a>
        </div>

        <!-- Form Body -->
        <div class="card-body p-4">
            <form action="{{ route('sinhvien.update', $sinhvien->_id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <!-- Tên đăng nhập / MSSV -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">
                            <i class="fa-solid fa-id-card-clip text-cyan me-1"></i>Tên Đăng Nhập / MSSV
                        </label>
                        <input type="text" name="TenDangNhap" value="{{ old('TenDangNhap', $sinhvien->taiKhoan->TenDangNhap ?? $sinhvien->TenDangNhap) }}" class="form-control" placeholder="MSSV">
                    </div>

                    <!-- Họ tên sinh viên -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">
                            <i class="fa-solid fa-user text-cyan me-1"></i>Họ và Tên Sinh Viên <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="HoTen" value="{{ old('HoTen', $sinhvien->HoTen) }}" class="form-control" required>
                    </div>

                    <!-- Lớp hành chính -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">
                            <i class="fa-solid fa-users-rectangle text-cyan me-1"></i>Lớp Hành Chính <span class="text-danger">*</span>
                        </label>
                        <select name="MaLop" class="form-select" required>
                            <option value="">-- Chọn lớp --</option>
                            @foreach($lops as $lop)
                                <option value="{{ $lop->MaLop }}" {{ ($sinhvien->MaLop == $lop->MaLop || $sinhvien->MaLop == $lop->_id) ? 'selected' : '' }}>
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
                                <option value="{{ $nganh->MaNganh }}" {{ ($sinhvien->MaNganh == $nganh->MaNganh || $sinhvien->MaNganh == $nganh->_id) ? 'selected' : '' }}>
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
                        <input type="text" name="KhoaHoc" value="{{ old('KhoaHoc', $sinhvien->KhoaHoc ?? '12') }}" class="form-control">
                    </div>

                    <!-- Ngày sinh -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">
                            <i class="fa-solid fa-cake-candles text-cyan me-1"></i>Ngày Sinh
                        </label>
                        <input type="date" name="NgaySinh" value="{{ old('NgaySinh', $sinhvien->NgaySinh) }}" class="form-control">
                    </div>

                    <!-- Email sinh viên -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">
                            <i class="fa-solid fa-envelope text-cyan me-1"></i>Địa Chỉ Email <span class="text-danger">*</span>
                        </label>
                        <input type="email" name="Email" value="{{ old('Email', $sinhvien->Email) }}" class="form-control" required>
                    </div>

                    <!-- Số điện thoại -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">
                            <i class="fa-solid fa-phone text-cyan me-1"></i>Số Điện Thoại Liên Hệ <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="SoDienThoai" value="{{ old('SoDienThoai', $sinhvien->SoDienThoai) }}" class="form-control" required>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('sinhvien.index') }}" class="btn btn-light border btn-sm rounded-pill px-4">
                        <i class="fa-solid fa-xmark me-1"></i>Hủy Bỏ
                    </a>
                    <button type="submit" class="btn btn-cyan btn-sm rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Lưu Thay Đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection