@extends($layout)
@section('page_title', 'Thông Tin Tài Khoản')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-premium">
            <div class="card-header-premium d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-address-card text-primary me-2"></i> Hồ Sơ Cá Nhân</span>
                <span class="badge bg-primary">{{ $role }}</span>
            </div>
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <img src="https://ui-avatars.com/api/?name={{ $profile->HoTen ?? 'Admin' }}&background=e9f2ff&color=3699ff&size=100" class="rounded-circle shadow-sm mb-3" alt="Avatar">
                    <h4 class="fw-bold">{{ $profile->HoTen ?? $user->TenDangNhap }}</h4>
                    <p class="text-muted">{{ $user->TenDangNhap }}</p>
                </div>

                <div class="row g-4">
                    @if($role === 'Sinh viên')
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold mb-1">Mã Sinh Viên</label>
                            <p class="fw-medium mb-0">{{ $user->TenDangNhap }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold mb-1">Lớp Hành Chính</label>
                            <p class="fw-medium mb-0">{{ $profile->lop->TenLop ?? 'Chưa cập nhật' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold mb-1">Email</label>
                            <p class="fw-medium mb-0">{{ $profile->Email ?? 'Chưa cập nhật' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold mb-1">Số Điện Thoại</label>
                            <p class="fw-medium mb-0">{{ $profile->SoDienThoai ?? 'Chưa cập nhật' }}</p>
                        </div>

                        <!-- Bảng Danh Sách Lớp Học Phần Đã Đăng Ký Theo Học Kỳ -->
                        <div class="col-md-12 mt-4">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="fa-solid fa-graduation-cap me-2"></i> Danh Sách Lớp Học Phần Đã Đăng Ký Theo Học Kỳ
                            </h6>
                            @if(isset($myLhps) && $myLhps->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover align-middle border">
                                        <thead class="table-light">
                                            <tr>
                                                <th>STT</th>
                                                <th>Lớp Học Phần</th>
                                                <th>Môn / Học Phần</th>
                                                <th>Học Kỳ</th>
                                                <th>GV Phụ Trách</th>
                                                <th>Trạng Thái</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($myLhps as $idx => $lhp)
                                                <tr>
                                                    <td>{{ $idx + 1 }}</td>
                                                    <td><strong class="text-navy">{{ $lhp->TenLopHP ?? $lhp->MaLopHP }}</strong></td>
                                                    <td>{{ $lhp->monHocModel->TenMon ?? $lhp->MaMon }}</td>
                                                    <td><span class="badge bg-info text-dark">{{ $lhp->hocKyModel->TenHocKy ?? 'N/A' }}</span></td>
                                                    <td>{{ $lhp->giangVienModel->HoTen ?? 'Chưa gán' }}</td>
                                                    <td>
                                                        <span class="badge bg-success">{{ $lhp->TrangThai ?? 'Đang mở' }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-warning py-2 text-center" style="font-size: 0.85rem;">
                                    <i class="fa-solid fa-info-circle me-1"></i> Chưa ghi nhận Lớp Học Phần được đăng ký trong hệ thống.
                                </div>
                            @endif
                        </div>
                    @elseif($role === 'Giảng viên')
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold mb-1">Mã Giảng Viên</label>
                            <p class="fw-medium mb-0">{{ $user->TenDangNhap }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold mb-1">Bộ Môn</label>
                            <p class="fw-medium mb-0">{{ $profile->boMon->TenBoMon ?? 'Chưa cập nhật' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold mb-1">Học Vị</label>
                            <p class="fw-medium mb-0">{{ $profile->HocVi ?? 'Chưa cập nhật' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold mb-1">Email</label>
                            <p class="fw-medium mb-0">{{ $profile->Email ?? 'Chưa cập nhật' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold mb-1">Số Điện Thoại</label>
                            <p class="fw-medium mb-0">{{ $profile->SoDienThoai ?? 'Chưa cập nhật' }}</p>
                        </div>
                    @else
                        <div class="col-md-12 text-center">
                            <p class="text-muted">Tài khoản quản trị viên không có thông tin chi tiết.</p>
                        </div>
                    @endif
                </div>

                <hr class="my-4">
                <div class="text-center">
                    <a href="{{ route('password.change') }}" class="btn btn-outline-primary btn-custom rounded-pill">
                        <i class="fa-solid fa-key me-2"></i> Đổi Mật Khẩu
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
