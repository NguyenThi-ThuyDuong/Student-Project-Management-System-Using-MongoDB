@extends('layouts.admin')

@section('page_title', 'NoSQL Database & Redis Monitoring')

@section('content')
<div class="container-fluid px-0">
    <!-- Page Title Bar -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold text-navy mb-1">
                <i class="fa-solid fa-server me-2 text-primary"></i>Giám Sát Hệ Thống NoSQL (MongoDB & Redis)
            </h3>
            <p class="text-muted mb-0 small">
                Trang dành riêng cho giảng viên/hội đồng kiểm tra trực tiếp kiến trúc <strong>MongoDB Document DB</strong> &amp; <strong>Redis Key-Value Store</strong> trong Đồ án.
            </p>
        </div>
        <div>
            <a href="{{ route('admin.nosql.index') }}" class="btn btn-outline-primary rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-rotate me-1"></i> Làm mới trạng thái
            </a>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="row g-4 mb-4">
        <!-- MongoDB Summary Card -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden" style="border-top: 4px solid #10b981 !important;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 p-3 text-white" style="background: linear-gradient(135deg, #10b981, #059669);">
                                <i class="fa-solid fa-leaf fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0">MongoDB 8.0</h5>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">
                                    <i class="fa-solid fa-circle-check me-1"></i> Primary Document Store
                                </span>
                            </div>
                        </div>
                        <span class="badge bg-emerald text-white px-3 py-2 rounded-pill fs-6 fw-semibold">
                            {{ $mongoStatus }}
                        </span>
                    </div>

                    <div class="row text-center mt-3 pt-3 border-top">
                        <div class="col-6">
                            <div class="text-muted small">Tên CSDL (Database)</div>
                            <div class="fw-bold fs-5 text-dark">{{ $mongoDbName }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Tổng số Collections</div>
                            <div class="fw-bold fs-5 text-success">{{ $totalCollectionsCount }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Redis Summary Card -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden" style="border-top: 4px solid #ef4444 !important;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 p-3 text-white" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                                <i class="fa-solid fa-bolt fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0">Redis In-Memory</h5>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3">
                                    <i class="fa-solid fa-key me-1"></i> Key-Value Cache &amp; OTP
                                </span>
                            </div>
                        </div>
                        <span class="badge {{ str_contains($redisStatus, 'Connected') ? 'bg-success' : 'bg-danger' }} text-white px-3 py-2 rounded-pill fs-6 fw-semibold">
                            {{ $redisStatus }}
                        </span>
                    </div>

                    <div class="row text-center mt-3 pt-3 border-top">
                        <div class="col-6">
                            <div class="text-muted small">Redis Client</div>
                            <div class="fw-bold fs-5 text-dark">Predis / PHP</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Tổng số Key Đang Lưu</div>
                            <div class="fw-bold fs-5 text-danger">{{ count($redisKeysInfo) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MongoDB Collections Detail Table -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0 text-navy">
                <i class="fa-solid fa-folder-tree text-success me-2"></i>Danh Sách Collections Trong MongoDB Database (`{{ $mongoDbName }}`)
            </h6>
            <span class="badge bg-light text-dark border rounded-pill">{{ count($mongoCollections) }} Collections</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">STT</th>
                            <th>Tên Collection</th>
                            <th>Vai trò / Mục đích lưu trữ</th>
                            <th class="text-end pe-4">Số lượng Document</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mongoCollections as $index => $col)
                            <tr>
                                <td class="ps-4 fw-semibold text-muted">{{ $index + 1 }}</td>
                                <td>
                                    <span class="fw-bold font-monospace text-primary">
                                        <i class="fa-solid fa-file-code me-1 text-muted"></i>{{ $col['name'] }}
                                    </span>
                                </td>
                                <td>
                                    @switch($col['name'])
                                        @case('nhom_do_an')
                                            <span class="badge bg-primary-subtle text-primary border rounded-pill px-3">
                                                <i class="fa-solid fa-layer-group me-1"></i>Embedded Doc (Thành viên, Đăng ký, Báo cáo, Sản phẩm, Chấm điểm)
                                            </span>
                                            @break
                                        @case('de_tai')
                                            <span class="badge bg-success-subtle text-success border rounded-pill px-3">Đề tài Đồ án &amp; Khóa luận</span>
                                            @break
                                        @case('tai_khoan')
                                            <span class="badge bg-warning-subtle text-dark border rounded-pill px-3">Tài khoản &amp; Phân quyền Auth</span>
                                            @break
                                        @case('sinh_vien')
                                            <span class="badge bg-info-subtle text-info border rounded-pill px-3">Hồ sơ Sinh viên</span>
                                            @break
                                        @case('giang_vien')
                                            <span class="badge bg-secondary-subtle text-dark border rounded-pill px-3">Hồ sơ Giảng viên</span>
                                            @break
                                        @default
                                            <span class="text-muted small">Dữ liệu nghiệp vụ / Danh mục hệ thống</span>
                                    @endswitch
                                </td>
                                <td class="text-end pe-4">
                                    <span class="badge bg-dark rounded-pill px-3 py-2 fs-6">
                                        {{ number_format($col['count']) }} docs
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Không tìm thấy collection nào trong CSDL MongoDB.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Redis Keys Monitoring Table -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0 text-navy">
                <i class="fa-solid fa-list-check text-danger me-2"></i>Danh Sách Redis Keys Live (OTP, Cache Đề tài, Cache Dashboard)
            </h6>
            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3">
                Redis Store Active
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Redis Key</th>
                            <th>Loại Data (Type)</th>
                            <th>Mục đích sử dụng</th>
                            <th>TTL Còn lại</th>
                            <th class="pe-4">Giá trị Rút gọn (Sample Value)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($redisKeysInfo as $rKey)
                            <tr>
                                <td class="ps-4">
                                    <code class="fw-bold text-danger bg-danger-subtle px-2 py-1 rounded" style="font-size: 0.9rem;">
                                        {{ $rKey['key'] }}
                                    </code>
                                </td>
                                <td>
                                    <span class="badge bg-secondary rounded-pill px-2 py-1">{{ $rKey['type'] }}</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark small">{{ $rKey['purpose'] }}</span>
                                </td>
                                <td>
                                    @if(str_contains($rKey['ttl'], 's'))
                                        <span class="badge bg-warning text-dark rounded-pill px-3">
                                            <i class="fa-regular fa-clock me-1"></i>{{ $rKey['ttl'] }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border rounded-pill px-2">{{ $rKey['ttl'] }}</span>
                                    @endif
                                </td>
                                <td class="pe-4">
                                    <span class="font-monospace small text-muted text-break">
                                        {{ $rKey['value'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted mb-2">
                                        <i class="fa-solid fa-inbox fa-2x"></i>
                                    </div>
                                    <p class="mb-0 text-muted">Chưa có Redis key nào được khởi tạo hoặc Redis đang trống.</p>
                                    <small class="text-muted">Mẹo demo: Bạn có thể thực hiện thao tác Quên mật khẩu hoặc truy cập Danh sách đề tài / Admin Dashboard để khởi tạo OTP &amp; Cache.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
