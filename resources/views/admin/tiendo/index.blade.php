@extends('layouts.admin')
@section('page_title', 'Quản Lý Tiến Độ Đồ Án')

@section('content')
<div class="page-header-zone mb-4">
    <div>
        <h1 class="fw-bold"><i class="fa-solid fa-bars-staggered me-2 text-cyan"></i>Quản Lý Tiến Độ Đồ Án</h1>
        <div class="text-muted small">Theo dõi tiến độ chi tiết theo <span class="fw-bold text-dark">Học Kỳ ➔ Lớp Học Phần ➔ Sinh Viên / Nhóm ➔ Đề Tài ➔ Báo Cáo Tiến Độ</span></div>
    </div>
</div>

{{-- BỘ LỌC TIẾN ĐỘ --}}
<div class="card card-modern mb-4">
    <div class="card-modern-header">
        <span class="fw-bold"><i class="fa-solid fa-filter me-2 text-cyan"></i>Bộ Lọc Tìm Kiếm</span>
    </div>
    <div class="card-modern-body p-3">
        <form action="{{ route('admin.tiendo.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <label class="form-label small fw-bold mb-1">Học Kỳ</label>
                <select name="MaHocKy" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                    <option value="">-- Tất cả Học Kỳ --</option>
                    @foreach($hocKies as $hk)
                        <option value="{{ $hk->_id }}" {{ request('MaHocKy') == $hk->_id ? 'selected' : '' }}>
                            {{ $hk->TenHocKy }} ({{ $hk->NamHoc }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold mb-1">Lớp Học Phần</label>
                <select name="MaLopHP" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                    <option value="">-- Tất cả Lớp Học Phần --</option>
                    @foreach($lopHocPhans as $lhp)
                        <option value="{{ $lhp->_id }}" {{ request('MaLopHP') == $lhp->_id ? 'selected' : '' }}>
                            [{{ $lhp->TenLopHP }}] — {{ $lhp->monHoc->TenMon ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold mb-1">Sinh Viên / Nhóm</label>
                <input type="text" name="search" class="form-control form-control-sm rounded-pill" placeholder="Nhập tên Nhóm / MSSV..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end gap-1 pt-3">
                <button type="submit" class="btn btn-cyan btn-sm rounded-pill w-100"><i class="fa-solid fa-magnifying-glass me-1"></i>Lọc</button>
                @if(request()->anyFilled(['MaHocKy', 'MaLopHP', 'search']))
                    <a href="{{ route('admin.tiendo.index') }}" class="btn btn-outline-secondary btn-sm rounded-circle" title="Đặt lại"><i class="fa-solid fa-rotate-left"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- DANH SÁCH TIẾN ĐỘ THEO TỪNG NHÓM --}}
<div class="row g-4">
    @forelse($nhoms as $nhom)
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 {{ $nhom->isOverdue ? 'border-start border-4 border-danger' : 'border-start border-4 border-cyan' }}">
            <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <span class="badge bg-navy text-white px-3 py-1 rounded-pill me-2"><i class="fa-solid fa-users me-1"></i>{{ $nhom->TenNhom }}</span>
                    <span class="badge bg-primary-subtle text-primary border border-primary px-3 py-1 rounded-pill me-2">
                        <i class="fa-solid fa-graduation-cap me-1"></i>Lớp HP: {{ $nhom->lopHocPhan->TenLopHP ?? 'N/A' }}
                    </span>
                    <span class="badge bg-info-subtle text-dark border px-3 py-1 rounded-pill">
                        <i class="fa-solid fa-calendar me-1"></i>{{ $nhom->hocKy->TenHocKy ?? 'N/A' }}
                    </span>
                </div>
                <div>
                    @if($nhom->isOverdue)
                        <span class="badge bg-danger fs-6 px-3 py-1 rounded-pill"><i class="fa-solid fa-triangle-exclamation me-1"></i>CHẬM TIẾN ĐỘ</span>
                    @else
                        <span class="badge bg-success fs-6 px-3 py-1 rounded-pill"><i class="fa-solid fa-circle-check me-1"></i>ĐÚNG TIẾN ĐỘ</span>
                    @endif
                </div>
            </div>
            <div class="card-body p-4 pt-0">
                @if($nhom->isOverdue)
                <div class="alert alert-danger border-0 rounded-3 p-2 px-3 small mb-3">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i><strong>Cảnh báo:</strong> {{ $nhom->overdueReason }}
                </div>
                @endif

                <div class="row g-3">
                    {{-- SINH VIÊN TRONG NHÓM --}}
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 border h-100">
                            <span class="text-muted small fw-bold d-block mb-2"><i class="fa-solid fa-user-group me-1 text-cyan"></i>Thành Viên Nhóm</span>
                            <ul class="list-unstyled mb-0 small">
                                @foreach($nhom->ThanhVien ?? [] as $tv)
                                    <li class="py-1 border-bottom d-flex justify-content-between">
                                        <span><i class="fa-solid fa-user-graduate me-1 text-secondary"></i>{{ $tv['HoTen'] ?? $tv['MaSV'] }}</span>
                                        @if(($tv['MaSV'] ?? '') === $nhom->TruongNhom)
                                            <span class="badge bg-warning text-dark">Trưởng nhóm</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    {{-- ĐỀ TÀI ĐỒ ÁN --}}
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 border h-100">
                            <span class="text-muted small fw-bold d-block mb-2"><i class="fa-solid fa-book-bookmark me-1 text-cyan"></i>Đề Tài Đồ Án</span>
                            @if($nhom->deTai)
                                <strong class="text-dark d-block mb-1">{{ $nhom->deTai->TenDeTai }}</strong>
                                <small class="text-muted d-block mb-1">Môn: {{ $nhom->deTai->monHoc->TenMon ?? 'N/A' }}</small>
                                <small class="text-danger d-block"><i class="fa-regular fa-clock me-1"></i>Hạn nộp SP: {{ $nhom->deTai->HanNopSanPham ? date('d/m/Y', strtotime($nhom->deTai->HanNopSanPham)) : 'Chưa đặt' }}</small>
                            @else
                                <span class="text-muted italic">Chưa đăng ký đề tài</span>
                            @endif
                        </div>
                    </div>

                    {{-- BÁO CÁO TIẾN ĐỘ --}}
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 border h-100">
                            <span class="text-muted small fw-bold d-block mb-2"><i class="fa-solid fa-spinner me-1 text-cyan"></i>Báo Cáo Tiến Độ ({{ $nhom->baoCaoList->count() }} Lần)</span>
                            @forelse($nhom->baoCaoList as $bc)
                                @php
                                    $lanBc = is_object($bc) ? ($bc->LanBaoCao ?? 1) : ($bc['LanBaoCao'] ?? 1);
                                    $ngayNopBc = is_object($bc) ? ($bc->NgayNop ?? date('Y-m-d')) : ($bc['NgayNop'] ?? date('Y-m-d'));
                                    $fileBc = is_object($bc) ? ($bc->FileBaoCao ?? '') : ($bc['FileBaoCao'] ?? '');
                                @endphp
                                <div class="py-1 border-bottom small d-flex justify-content-between align-items-center">
                                    <span>Lần {{ $lanBc }}: <strong>{{ date('d/m/Y', strtotime($ngayNopBc)) }}</strong></span>
                                    @if(!empty($fileBc))
                                        <a href="{{ asset(ltrim($fileBc, '/')) }}" target="_blank" class="badge bg-info text-dark text-decoration-none">File Báo Cáo</a>
                                    @endif
                                </div>
                            @empty
                                <span class="text-muted italic small">Chưa nộp báo cáo tiến độ nào.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card card-modern p-5 text-center text-muted">
            <i class="fa-solid fa-folder-open fa-3x mb-3 text-cyan opacity-50"></i>
            <h5>Không có nhóm đồ án nào phù hợp với bộ lọc tiến độ.</h5>
        </div>
    </div>
    @endforelse
</div>

@if($nhoms->hasPages())
<div class="mt-4 d-flex justify-content-center">
    {{ $nhoms->links('pagination::bootstrap-5') }}
</div>
@endif
@endsection
