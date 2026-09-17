@extends('layouts.giangvien')
@section('page_title', 'Tổng Quan Dashboard')
@section('content')

{{-- Welcome Hero --}}
<div class="card card-premium shadow-sm mb-4 border-0 text-white overflow-hidden" style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); position: relative;">
    <div class="card-body p-4 position-relative z-1">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-white text-primary rounded-pill px-3 py-1 mb-2 font-monospace fw-bold">CỔNG GIẢNG VIÊN HUIT</span>
                <h3 class="fw-bold mb-2">Xin chào, {{ $gv->HoTen ?? 'Thầy/Cô' }} 👋</h3>
                <p class="mb-0 text-white-50">
                    Hệ thống Quản lý Đồ án & Báo cáo Tiến độ Sinh viên. Theo dõi các lớp phụ trách, đề tài đề xuất, nhóm sinh viên và chấm điểm sản phẩm.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <a href="{{ route('giangvien.detai.create') }}" class="btn btn-light text-primary fw-bold rounded-pill px-4 py-2 shadow">
                    <i class="fa-solid fa-plus me-2"></i>Tạo Đề Tài Mới
                </a>
            </div>
        </div>
    </div>
</div>

{{-- 4 Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card card-premium border-0 shadow-sm h-100 p-3" style="border-left: 4px solid #2563eb !important;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Lớp Phụ Trách</span>
                    <h2 class="fw-bold text-primary my-1">{{ $countLopHP + $countLopHC }}</h2>
                    <span class="small text-muted">
                        <strong class="text-dark">{{ $countLopHP }}</strong> Lớp HP &bull; <strong class="text-dark">{{ $countLopHC }}</strong> Lớp HC
                    </span>
                </div>
                <div class="rounded-circle bg-primary-subtle p-3 text-primary">
                    <i class="fa-solid fa-graduation-cap fa-2x"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-premium border-0 shadow-sm h-100 p-3" style="border-left: 4px solid #16a34a !important;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Đề Tài Đã Tạo</span>
                    <h2 class="fw-bold text-success my-1">{{ $totalDeTai }}</h2>
                    <span class="small text-muted">
                        <strong class="text-success">{{ $approvedDeTai }}</strong> Đã duyệt &bull; <strong class="text-warning">{{ $pendingDeTai }}</strong> Chờ duyệt
                    </span>
                </div>
                <div class="rounded-circle bg-success-subtle p-3 text-success">
                    <i class="fa-solid fa-book-bookmark fa-2x"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-premium border-0 shadow-sm h-100 p-3" style="border-left: 4px solid #eab308 !important;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Nhóm Hướng Dẫn</span>
                    <h2 class="fw-bold text-warning my-1">{{ $totalNhoms }}</h2>
                    <span class="small text-muted">
                        <strong class="text-danger">{{ $pendingApprovalGroups }}</strong> Nhóm đang chờ duyệt ĐT
                    </span>
                </div>
                <div class="rounded-circle bg-warning-subtle p-3 text-warning">
                    <i class="fa-solid fa-users fa-2x"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-premium border-0 shadow-sm h-100 p-3" style="border-left: 4px solid #06b6d4 !important;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Sản Phẩm & Chấm Điểm</span>
                    <h2 class="fw-bold text-info my-1">{{ $submittedProducts }}</h2>
                    <span class="small text-muted">
                        <strong class="text-info">{{ $submittedProducts }}</strong> Đã nộp &bull; <strong class="text-success">{{ $gradedGroups }}</strong> Đã chấm
                    </span>
                </div>
                <div class="rounded-circle bg-info-subtle p-3 text-info">
                    <i class="fa-solid fa-award fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Quick Shortcuts --}}
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <a href="{{ route('giangvien.detai.index') }}" class="btn btn-outline-primary w-100 p-3 text-start rounded-3 shadow-sm bg-white d-flex align-items-center gap-3">
            <i class="fa-solid fa-folder-open fa-2x text-primary"></i>
            <div>
                <strong class="d-block text-dark">Quản lý Đề tài</strong>
                <span class="small text-muted">Đề xuất & đính kèm file</span>
            </div>
        </a>
    </div>
    <div class="col-md-3 col-6">
        <a href="{{ route('giangvien.duyet.index') }}" class="btn btn-outline-warning w-100 p-3 text-start rounded-3 shadow-sm bg-white d-flex align-items-center gap-3">
            <i class="fa-solid fa-user-check fa-2x text-warning"></i>
            <div>
                <strong class="d-block text-dark">Duyệt Đăng Ký</strong>
                <span class="small text-muted">Phê duyệt đề tài nhóm</span>
            </div>
        </a>
    </div>
    <div class="col-md-3 col-6">
        <a href="{{ route('giangvien.baocao.index') }}" class="btn btn-outline-info w-100 p-3 text-start rounded-3 shadow-sm bg-white d-flex align-items-center gap-3">
            <i class="fa-solid fa-tasks fa-2x text-info"></i>
            <div>
                <strong class="d-block text-dark">Tiến Độ & Báo Cáo</strong>
                <span class="small text-muted">Nhận xét báo cáo SV</span>
            </div>
        </a>
    </div>
    <div class="col-md-3 col-6">
        <a href="{{ route('giangvien.chamdiem.index') }}" class="btn btn-outline-success w-100 p-3 text-start rounded-3 shadow-sm bg-white d-flex align-items-center gap-3">
            <i class="fa-solid fa-clipboard-check fa-2x text-success"></i>
            <div>
                <strong class="d-block text-dark">Chấm Điểm Đồ Án</strong>
                <span class="small text-muted">Đánh giá sản phẩm</span>
            </div>
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Active Groups Supervision List --}}
    <div class="col-lg-8">
        <div class="card card-premium shadow-sm border-0 h-100">
            <div class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold text-primary fs-6"><i class="fa-solid fa-users-gear me-2"></i>Nhóm Đồ Án Đang Phụ Trách ({{ $nhoms->count() }})</span>
                <a href="{{ route('giangvien.lop.index') }}" class="btn btn-sm btn-light rounded-pill px-3">Xem tất cả lớp</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tên Nhóm</th>
                                <th>Đề Tài Đăng Ký</th>
                                <th>Tiến Độ</th>
                                <th>Sản Phẩm</th>
                                <th>Điểm Số</th>
                                <th class="text-end">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($nhoms as $n)
                            <tr>
                                <td>
                                    <strong class="text-dark d-block">{{ $n->TenNhom }}</strong>
                                    <small class="text-muted">Mã: {{ $n->MaNhom }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle text-wrap" style="max-width: 200px;">
                                        {{ $n->getTenDeTaiDangKy() }}
                                    </span>
                                </td>
                                <td>
                                    @php $cntBC = $n->getBaoCaoList()->count(); @endphp
                                    <span class="badge bg-{{ $cntBC > 0 ? 'info' : 'secondary' }} rounded-pill">
                                        {{ $cntBC }} báo cáo
                                    </span>
                                </td>
                                <td>
                                    @if($n->getSanPhamList()->isNotEmpty())
                                        <span class="badge bg-success rounded-pill"><i class="fa-solid fa-check me-1"></i>Đã nộp</span>
                                    @else
                                        <span class="badge bg-light text-muted border">Chưa nộp</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $cd = $n->getChamDiem();
                                        $diemTong = is_array($cd) ? ($cd['DiemTong'] ?? null) : ($n->ChamDiem->DiemTong ?? null);
                                    @endphp
                                    @if($diemTong !== null)
                                        <span class="badge bg-success-subtle text-success border border-success fw-bold fs-6">
                                            {{ number_format((float)$diemTong, 2) }}
                                        </span>
                                    @else
                                        <span class="text-muted small">Chưa chấm</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('giangvien.chamdiem.index') }}" class="btn btn-sm btn-outline-primary rounded-pill" title="Chấm điểm / Đánh giá">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    Chưa có nhóm đồ án nào đăng ký hướng dẫn.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Notifications & Quick Announce --}}
    <div class="col-lg-4">
        <div class="card card-premium shadow-sm border-0 h-100">
            <div class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold text-primary fs-6"><i class="fa-solid fa-bullhorn me-2"></i>Thông Báo Đã Đăng</span>
                <a href="{{ route('giangvien.thongbao.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="fa-solid fa-plus me-1"></i>Tạo mới
                </a>
            </div>
            <div class="card-body p-3">
                <div class="list-group list-group-flush">
                    @forelse($thongBaos as $tb)
                    <div class="list-group-item px-0 py-3">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="fw-bold text-dark mb-0" style="line-height: 1.4;">{{ $tb->TieuDe }}</h6>
                        </div>
                        <p class="small text-muted mb-2 text-truncate">{{ $tb->NoiDung }}</p>
                        <div class="d-flex justify-content-between align-items-center small">
                            <span class="text-muted"><i class="fa-solid fa-clock me-1"></i>{{ $tb->NgayTao }}</span>
                            @if($tb->FileDinhKem)
                                <span class="badge bg-info-subtle text-info"><i class="fa-solid fa-paperclip me-1"></i>Có file</span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        Chưa có thông báo nào.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
