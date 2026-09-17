@extends('layouts.giangvien')
@section('page_title', 'Danh Sách Lớp Học Phần Phụ Trách')
@section('content')

{{-- BANNER HEADER & LECTURER WORKLOAD SUMMARY --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card card-premium border-0 p-3 text-white shadow-sm" style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-white-50 small fw-bold text-uppercase">Lớp HP Phụ Trách</span>
                    <h2 class="mb-0 fw-bold text-white mt-1">{{ count($lopHocPhans) }}</h2>
                </div>
                <div class="rounded-circle bg-white bg-opacity-20 p-3">
                    <i class="fa-solid fa-graduation-cap fs-3 text-white"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-premium border-0 p-3 text-white shadow-sm" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-white-50 small fw-bold text-uppercase">Tổng Sinh Viên Hướng Dẫn</span>
                    @php
                        $totalSvCount = 0;
                        foreach($lopHocPhans as $lhp) {
                            $totalSvCount += count($lhp->getSinhVienIds());
                        }
                    @endphp
                    <h2 class="mb-0 fw-bold text-white mt-1">{{ number_format($totalSvCount) }}</h2>
                </div>
                <div class="rounded-circle bg-white bg-opacity-20 p-3">
                    <i class="fa-solid fa-users fs-3 text-white"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-premium border-0 p-3 text-white shadow-sm" style="background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-white-50 small fw-bold text-uppercase">Vai Trò Đăng Ký</span>
                    <h5 class="mb-0 fw-bold text-white mt-2"><i class="fa-solid fa-shield-halved me-1"></i>GV Phụ Trách & GVHD</h5>
                </div>
                <div class="rounded-circle bg-white bg-opacity-20 p-3">
                    <i class="fa-solid fa-award fs-3 text-white"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- BỘ LỌC HỌC KỲ --}}
<div class="card card-premium mb-4 shadow-sm">
    <div class="card-body p-3">
        <form action="{{ route('giangvien.lop.index') }}" method="GET" class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-filter text-primary fs-5"></i>
                <label class="small text-dark fw-bold text-nowrap mb-0">Học Kỳ Phân Công:</label>
                <select name="ma_hoc_ky" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 240px;">
                    <option value="">-- Tất cả các Học Kỳ --</option>
                    @foreach($hocKies as $hk)
                        <option value="{{ $hk->MaHocKy }}" {{ request('ma_hoc_ky') == $hk->MaHocKy ? 'selected' : '' }}>
                            {{ $hk->TenHocKy }} ({{ $hk->NamHoc }})
                        </option>
                    @endforeach
                </select>
                @if(request('ma_hoc_ky'))
                    <a href="{{ route('giangvien.lop.index') }}" class="btn btn-sm btn-light border rounded-pill px-3" title="Xóa lọc">
                        <i class="fa-solid fa-rotate-left me-1"></i>Bỏ lọc
                    </a>
                @endif
            </div>
            <span class="badge bg-primary-subtle text-primary border border-primary px-3 py-2 fw-bold">
                <i class="fa-solid fa-layer-group me-1"></i>{{ count($lopHocPhans) }} Lớp Học Phần
            </span>
        </form>
    </div>
</div>

{{-- DANH SÁCH LỚP HỌC PHẦN DẠNG CARD GRID --}}
<div class="row g-4 mb-4">
    @forelse($lopHocPhans as $item)
        @php
            $svCount = count($item->getSinhVienIds());
            $maxSiSo = $item->SiSoToiDa ?? 40;
            $percent = min(100, round(($svCount / max(1, $maxSiSo)) * 100));
        @endphp
        <div class="col-lg-6 col-xl-4">
            <div class="card card-premium h-100 border-0 shadow-sm rounded-3 hover-shadow transition-all" style="border-left: 4px solid #2563eb !important;">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        {{-- Top Badges --}}
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-light text-primary border fw-bold">
                                <i class="fa-solid fa-hashtag me-1"></i>{{ $item->MaLopHP }}
                            </span>
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                {{ $item->hocKy->TenHocKy ?? 'HK' }} ({{ $item->hocKy->NamHoc ?? '' }})
                            </span>
                        </div>

                        {{-- Class Name --}}
                        <h5 class="fw-bold text-dark mb-2">
                            <a href="{{ route('giangvien.lop.show', $item->_id) }}" class="text-dark text-decoration-none text-primary-hover">
                                <i class="fa-solid fa-graduation-cap text-primary me-2"></i>
                                {{ $item->TenLopHP }}
                            </a>
                        </h5>

                        {{-- Subject & Info --}}
                        <div class="text-muted small mb-3">
                            <i class="fa-solid fa-book text-secondary me-1"></i>
                            Môn học: <strong class="text-dark">{{ $item->monHoc->TenMon ?? '—' }}</strong>
                        </div>

                        {{-- Capacity Progress bar --}}
                        <div class="mb-3 p-3 bg-light rounded-3 border">
                            <div class="d-flex justify-content-between align-items-center small mb-1">
                                <span class="text-muted">Sĩ số sinh viên:</span>
                                <span class="fw-bold text-dark">{{ $svCount }} / {{ $maxSiSo }} SV</span>
                            </div>
                            <div class="progress" style="height: 8px; border-radius: 4px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $percent }}%;" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Action Footer --}}
                    <div class="pt-2 border-top">
                        <a href="{{ route('giangvien.lop.show', $item->_id) }}" class="btn btn-primary w-100 fw-bold rounded-pill shadow-sm">
                            <i class="fa-solid fa-circle-arrow-right me-1"></i>Quản Lý Lớp &amp; Nhóm Đồ Án
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card card-premium text-center py-5 border-0 shadow-sm">
                <i class="fa-solid fa-folder-open fs-1 text-muted mb-3 opacity-50"></i>
                <h6 class="fw-bold text-secondary mb-1">Chưa có lớp học phần nào được phân công</h6>
                <p class="text-muted small mb-0">Liên hệ Ban Quản trị Giáo vụ nếu bạn có thắc mắc về phân công giảng dạy.</p>
            </div>
        </div>
    @endforelse
</div>

@endsection
