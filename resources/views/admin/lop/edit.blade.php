@extends('layouts.admin')
@section('page_title', 'Chỉnh Sửa Lớp: ' . $lop->TenLop)

@section('content')
<div class="container-fluid max-w-5xl py-3">
    <div class="card card-modern col-lg-9 col-xl-8 mx-auto shadow-sm">
        <div class="card-modern-header">
            <div class="title-group">
                <div class="title-icon">
                    <i class="fa-solid fa-pen-to-square"></i>
                </div>
                <div>
                    <h1 class="main-title">Chỉnh Sửa Lớp Hành Chính</h1>
                    <div class="sub-title">Cập nhật thông tin lớp <strong>{{ $lop->TenLop }}</strong></div>
                </div>
            </div>
            <a href="{{ route('lop.index') }}" class="btn btn-modern-secondary text-white border-0 bg-white bg-opacity-20 hover-bg-opacity-30">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>

        <div class="card-modern-body p-4">
            @if ($errors->any())
                <div class="alert alert-modern-danger mb-4">
                    <i class="fa-solid fa-circle-exclamation text-danger fs-5 flex-shrink-0 mt-1"></i>
                    <div>
                        <strong class="fw-bold d-block mb-1">Vui lòng kiểm tra thông tin nhập liệu:</strong>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('lop.update', $lop->_id ?? $lop->MaLop) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-4">
                    <!-- Tên Lớp -->
                    <div class="col-md-6">
                        <label class="form-label-modern">
                            <i class="fa-solid fa-signature me-1 text-cyan"></i> Tên Lớp Hành Chính <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="TenLop" value="{{ old('TenLop', $lop->TenLop) }}" class="form-control form-control-modern" required placeholder="Ví dụ: 12DHTH01">
                    </div>

                    <!-- Ngành Học -->
                    <div class="col-md-6">
                        <label class="form-label-modern">
                            <i class="fa-solid fa-graduation-cap me-1 text-cyan"></i> Ngành Học Trực Thuộc <span class="text-danger">*</span>
                        </label>
                        <select name="MaNganh" class="form-select form-select-modern" required>
                            <option value="">-- Chọn ngành học --</option>
                            @foreach($nganhs as $nganh)
                                <option value="{{ $nganh->MaNganh }}" {{ old('MaNganh', $lop->MaNganh) == $nganh->MaNganh ? 'selected' : '' }}>
                                    {{ $nganh->TenNganh }} ({{ $nganh->MaNganh }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Học Kỳ Nhập Học / Khóa -->
                    <div class="col-md-6">
                        <label class="form-label-modern">
                            <i class="fa-solid fa-calendar-days me-1 text-cyan"></i> Học Kỳ Khai Giảng / Áp Dụng
                        </label>
                        <select name="MaHocKy" class="form-select form-select-modern">
                            <option value="">-- Chọn học kỳ mở lớp --</option>
                            @foreach($hocKies as $hk)
                                <option value="{{ $hk->MaHocKy }}" {{ old('MaHocKy', $lop->MaHocKy) == $hk->MaHocKy ? 'selected' : '' }}>
                                    {{ $hk->TenHocKy }} (Năm học {{ $hk->NamHoc }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Khóa Học -->
                    <div class="col-md-6">
                        <label class="form-label-modern">
                            <i class="fa-solid fa-clock me-1 text-cyan"></i> Khóa Học (Khóa / Năm Đào Tạo) <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="KhoaHoc" value="{{ old('KhoaHoc', $lop->KhoaHoc) }}" class="form-control form-control-modern" required placeholder="Ví dụ: 12 hoặc 2021-2025">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-5 pt-3 border-top">
                    <a href="{{ route('lop.index') }}" class="btn btn-modern-secondary">
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