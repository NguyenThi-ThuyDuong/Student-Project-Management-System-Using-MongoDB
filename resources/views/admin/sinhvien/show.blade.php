@extends('layouts.admin')
@section('page_title', 'Hồ Sơ Sinh Viên: ' . $sinhvien->HoTen)

@section('content')
<div class="container-fluid max-w-5xl py-2">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fa-solid fa-id-card-clip text-cyan me-2"></i>Hồ Sơ Sinh Viên: {{ $sinhvien->HoTen }}
            </h4>
            <span class="text-muted small">Mã Sinh Viên (MSSV): <strong>{{ $sinhvien->MaSV }}</strong></span>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('admin.sinhvien.toggleStatus', $sinhvien->_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn thay đổi trạng thái tài khoản sinh viên này?')">
                @csrf
                <button type="submit" class="btn btn-sm rounded-pill px-3 {{ $sinhvien->taiKhoan && $sinhvien->taiKhoan->TrangThai ? 'btn-outline-warning' : 'btn-outline-success' }}">
                    <i class="fa-solid {{ $sinhvien->taiKhoan && $sinhvien->taiKhoan->TrangThai ? 'fa-lock' : 'fa-lock-open' }} me-1"></i>
                    {{ $sinhvien->taiKhoan && $sinhvien->taiKhoan->TrangThai ? 'Khóa Tài Khoản' : 'Mở Khóa Tài Khoản' }}
                </button>
            </form>
            <a href="{{ route('sinhvien.edit', $sinhvien->_id) }}" class="btn btn-cyan btn-sm rounded-pill px-3">
                <i class="fa-solid fa-pen-to-square me-1"></i>Chỉnh Sửa Hồ Sơ
            </a>
            <a href="{{ route('sinhvien.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i>Quay Lại
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- THÔNG TIN CÁ NHÂN & TÀI KHOẢN --}}
        <div class="col-md-5">
            <div class="card card-modern h-100 shadow-sm">
                <div class="card-modern-header">
                    <span><i class="fa-solid fa-user-gear me-2"></i>Thông Tin Cá Nhân</span>
                    @if($sinhvien->taiKhoan && $sinhvien->taiKhoan->TrangThai)
                        <span class="badge-success"><i class="fa-solid fa-circle-check me-1"></i>Hoạt động</span>
                    @else
                        <span class="badge-danger"><i class="fa-solid fa-lock me-1"></i>Đã khóa</span>
                    @endif
                </div>
                <div class="card-body p-3">
                    <div class="text-center py-3 border-bottom mb-3">
                        <div class="rounded-circle bg-light bg-opacity-75 p-3 d-inline-block border border-cyan mb-2" style="width: 80px; height: 80px;">
                            <i class="fa-solid fa-user-graduate fs-1 text-cyan"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">{{ $sinhvien->HoTen }}</h5>
                        <span class="badge-indigo">MSSV: {{ $sinhvien->MaSV }}</span>
                    </div>

                    <div class="d-flex flex-column gap-3 pt-2">
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted small"><i class="fa-solid fa-user me-2 text-cyan"></i>Tên đăng nhập:</span>
                            <span class="fw-bold text-dark">{{ $sinhvien->taiKhoan->TenDangNhap ?? $sinhvien->TenDangNhap ?? $sinhvien->MaSV }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted small"><i class="fa-solid fa-cake-candles me-2 text-cyan"></i>Ngày sinh:</span>
                            <span class="fw-bold text-dark">{{ $sinhvien->NgaySinh ? \Carbon\Carbon::parse($sinhvien->NgaySinh)->format('d/m/Y') : 'Chưa cập nhật' }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted small"><i class="fa-solid fa-envelope me-2 text-cyan"></i>Email liên hệ:</span>
                            <span class="fw-bold text-dark text-break">{{ $sinhvien->Email }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2">
                            <span class="text-muted small"><i class="fa-solid fa-phone me-2 text-cyan"></i>Số điện thoại:</span>
                            <span class="fw-bold text-dark">{{ $sinhvien->SoDienThoai }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- THÔNG TIN HỌC TẬP & NHÓM ĐỒ ÁN --}}
        <div class="col-md-7">
            <div class="card card-modern shadow-sm mb-4">
                <div class="card-modern-header">
                    <span><i class="fa-solid fa-graduation-cap me-2"></i>Thông Tin Học Tập</span>
                </div>
                <div class="card-body p-3">
                    @php
                        $lopModel = $sinhvien->lopModel;
                        $nganhModel = $sinhvien->nganhModel;
                    @endphp
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="text-muted small d-block"><i class="fa-solid fa-users-rectangle me-1 text-cyan"></i>Lớp Hành Chính</span>
                                <strong class="fs-6 text-dark">{{ $lopModel->TenLop ?? ($sinhvien->lop->TenLop ?? 'Chưa xếp lớp') }}</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="text-muted small d-block"><i class="fa-solid fa-building-columns me-1 text-cyan"></i>Ngành Học</span>
                                <strong class="fs-6 text-dark">{{ $nganhModel->TenNganh ?? ($sinhvien->nganh->TenNganh ?? 'Chưa phân ngành') }}</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="text-muted small d-block"><i class="fa-solid fa-calendar me-1 text-cyan"></i>Khóa Học</span>
                                <strong class="fs-6 text-dark">Khóa {{ $sinhvien->KhoaHoc ?? '12' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="text-muted small d-block"><i class="fa-solid fa-shield-halved me-1 text-cyan"></i>Trạng Thái Tài Khoản</span>
                                <strong class="fs-6 text-emerald">Đang Hoạt Động</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- LỚP HỌC PHẦN ĐÃ ĐĂNG KÝ THEO HỌC KỲ --}}
            @php
                $enrolledLhps = \App\Models\LopHocPhan::where('DanhSachSinhVien.MaSV', $sinhvien->MaSV)
                    ->orWhere('DanhSachSinhVien.sinh_vien_id', (string)$sinhvien->_id)
                    ->get();
            @endphp
            <div class="card card-modern shadow-sm mb-4">
                <div class="card-modern-header">
                    <span><i class="fa-solid fa-graduation-cap me-2"></i>Lớp Học Phần Đã Đăng Ký ({{ $enrolledLhps->count() }})</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Lớp Học Phần</th>
                                    <th>Môn Học</th>
                                    <th>Học Kỳ</th>
                                    <th>GV Phụ Trách</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($enrolledLhps as $lhp)
                                <tr>
                                    <td class="fw-bold text-cyan">
                                        <a href="{{ route('admin.lophocphan.show', $lhp->MaLopHP ?? $lhp->_id) }}" class="text-decoration-none">
                                            {{ $lhp->TenLopHP }}
                                        </a>
                                    </td>
                                    <td>{{ $lhp->monHoc->TenMon ?? ($lhp->mon_hoc_model->TenMon ?? '—') }}</td>
                                    <td>
                                        <span class="badge bg-info-subtle text-dark border">
                                            {{ $lhp->hocKy->TenHocKy ?? ($lhp->hoc_ky_model->TenHocKy ?? '—') }}
                                        </span>
                                    </td>
                                    <td>{{ $lhp->giangVien->HoTen ?? ($lhp->giang_vien_model->HoTen ?? 'Chưa phân công') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-book-open fs-3 d-block mb-1 opacity-50"></i>
                                        Chưa có thông tin lớp học phần theo học kỳ.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- DỰ ÁN & NHÓM THAM GIA --}}
            <div class="card card-modern shadow-sm">
                <div class="card-modern-header">
                    <span><i class="fa-solid fa-folder-open me-2"></i>Lịch Sử Đồ Án &amp; Nhóm ({{\App\Models\NhomDoAn::where('ThanhVien.MaSV', $sinhvien->MaSV)->orWhere('TruongNhom', $sinhvien->MaSV)->count()}})</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nhóm</th>
                                    <th>Đề Tài Đồ Án</th>
                                    <th class="text-center">Vai Trò</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(\App\Models\NhomDoAn::where('ThanhVien.MaSV', $sinhvien->MaSV)->orWhere('TruongNhom', $sinhvien->MaSV)->get() as $nhom)
                                <tr>
                                    <td class="fw-bold text-cyan">{{ $nhom->TenNhom }}</td>
                                    <td>{{ $nhom->dangKyDeTai->deTai->TenDeTai ?? 'Chưa chọn đề tài' }}</td>
                                    <td class="text-center">
                                        @if($nhom->TruongNhom === $sinhvien->MaSV)
                                            <span class="badge bg-warning text-dark">Trưởng nhóm</span>
                                        @else
                                            <span class="badge bg-light text-dark border">Thành viên</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-folder-open fs-3 d-block mb-1 opacity-50"></i>
                                        Sinh viên chưa tham gia nhóm đồ án nào.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
