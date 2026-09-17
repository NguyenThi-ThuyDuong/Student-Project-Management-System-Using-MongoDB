@extends('layouts.admin')
@section('page_title', 'Thống Kê Báo Cáo Tổng Hợp')

@section('content')
<div class="page-header-zone mb-4">
    <div>
        <h1 class="fw-bold"><i class="fa-solid fa-chart-pie me-2 text-cyan"></i>Báo Cáo Thống Kê Tổng Hợp</h1>
        <div class="text-muted small">Phân tích đa chiều theo Học kỳ, Lớp, Học phần, Giảng viên, Sinh viên, Đề tài, Tiến độ &amp; Kết quả điểm số</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.thongke.export') }}" class="btn btn-emerald btn-sm rounded-pill px-3 fw-bold">
            <i class="fa-solid fa-file-excel me-1"></i>Xuất Báo Cáo Excel
        </a>
    </div>
</div>

{{-- THỐNG KÊ TỔNG QUAN --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary">
            <span class="text-muted small d-block mb-1"><i class="fa-solid fa-user-graduate me-1 text-primary"></i>Sinh Viên</span>
            <strong class="fs-4 text-dark">{{ $allSvs->count() }} SV</strong>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-info">
            <span class="text-muted small d-block mb-1"><i class="fa-solid fa-chalkboard-user me-1 text-info"></i>Giảng Viên</span>
            <strong class="fs-4 text-dark">{{ $allGvs->count() }} GV</strong>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-purple">
            <span class="text-muted small d-block mb-1"><i class="fa-solid fa-book me-1 text-purple"></i>Học Phần Đồ Án</span>
            <strong class="fs-4 text-dark">{{ $monHocs->count() }} Môn</strong>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-warning">
            <span class="text-muted small d-block mb-1"><i class="fa-solid fa-lightbulb me-1 text-warning"></i>Đề Tài Đồ Án</span>
            <strong class="fs-4 text-dark">{{ $allDts->count() }} Đề Tài</strong>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- BẢNG PHÂN BỐ XẾP LOẠI ĐIỂM SỐ --}}
    <div class="col-md-6">
        <div class="card card-modern h-100">
            <div class="card-modern-header">
                <span class="fw-bold"><i class="fa-solid fa-chart-column me-2 text-cyan"></i>Phân Bố Xếp Loại Điểm Số</span>
            </div>
            <div class="card-modern-body p-3">
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-award text-warning me-2"></i>Xuất Sắc (9.0 - 10.0):</span>
                        <span class="badge bg-success rounded-pill px-3">{{ $distGrade['XuatSac'] }} nhóm</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-star text-primary me-2"></i>Giỏi (8.0 - 8.9):</span>
                        <span class="badge bg-primary rounded-pill px-3">{{ $distGrade['Gioi'] }} nhóm</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-thumbs-up text-info me-2"></i>Khá (6.5 - 7.9):</span>
                        <span class="badge bg-info text-dark rounded-pill px-3">{{ $distGrade['Kha'] }} nhóm</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-check text-secondary me-2"></i>Trung Bình (5.0 - 6.4):</span>
                        <span class="badge bg-secondary rounded-pill px-3">{{ $distGrade['TrungBinh'] }} nhóm</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-circle-xmark text-danger me-2"></i>Không Đạt (< 5.0):</span>
                        <span class="badge bg-danger rounded-pill px-3">{{ $distGrade['KhongDat'] }} nhóm</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-clock text-muted me-2"></i>Chưa Nộp / Chưa Chấm:</span>
                        <span class="badge bg-light text-dark border rounded-pill px-3">{{ $distGrade['ChuaNop'] }} nhóm</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- THỐNG KÊ TRẠNG THÁI TIẾN ĐỘ --}}
    <div class="col-md-6">
        <div class="card card-modern h-100">
            <div class="card-modern-header">
                <span class="fw-bold"><i class="fa-solid fa-bars-progress me-2 text-cyan"></i>Thống Kê Trạng Thái Tiến Độ</span>
            </div>
            <div class="card-modern-body p-4 d-flex flex-column justify-content-center">
                <div class="d-flex justify-content-around text-center mb-3">
                    <div class="p-3 bg-success-subtle border border-success rounded-4" style="min-width: 160px;">
                        <span class="text-success small fw-bold d-block mb-1"><i class="fa-solid fa-circle-check me-1"></i>ĐÚNG TIẾN ĐỘ</span>
                        <strong class="fs-2 text-success">{{ $progressOnTimeCount }}</strong>
                        <small class="text-muted d-block">Nhóm</small>
                    </div>
                    <div class="p-3 bg-danger-subtle border border-danger rounded-4" style="min-width: 160px;">
                        <span class="text-danger small fw-bold d-block mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i>CHẬM TIẾN ĐỘ</span>
                        <strong class="fs-2 text-danger">{{ $progressOverdueCount }}</strong>
                        <small class="text-muted d-block">Nhóm</small>
                    </div>
                </div>
                <div class="progress" style="height: 12px;">
                    @php
                        $totalProg = max(1, $progressOnTimeCount + $progressOverdueCount);
                        $pctOnTime = round(($progressOnTimeCount / $totalProg) * 100);
                        $pctOverdue = 100 - $pctOnTime;
                    @endphp
                    <div class="progress-bar bg-success" style="width: {{ $pctOnTime }}%"></div>
                    <div class="progress-bar bg-danger" style="width: {{ $pctOverdue }}%"></div>
                </div>
                <div class="d-flex justify-content-between small text-muted mt-2">
                    <span>Tỉ lệ đúng tiến độ: {{ $pctOnTime }}%</span>
                    <span>Tỉ lệ chậm tiến độ: {{ $pctOverdue }}%</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- THỐNG KÊ THEO HỌC KỲ --}}
<div class="card card-modern mb-4">
    <div class="card-modern-header">
        <span class="fw-bold"><i class="fa-solid fa-calendar-days me-2 text-cyan"></i>Thống Kê Theo Học Kỳ</span>
    </div>
    <div class="card-modern-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4">Học Kỳ</th>
                        <th>Năm Học</th>
                        <th class="text-center">Số Nhóm Tham Gia</th>
                        <th class="text-center">Số Nhóm Đã Có Điểm</th>
                        <th class="text-center">Điểm Trung Bình Khóa</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($statsByHocKy as $stHk)
                    <tr>
                        <td class="px-4 fw-bold text-dark">{{ $stHk['tenHocKy'] }}</td>
                        <td>{{ $stHk['namHoc'] }}</td>
                        <td class="text-center fw-bold">{{ $stHk['tongSoNhom'] }} Nhóm</td>
                        <td class="text-center"><span class="badge bg-primary rounded-pill px-3">{{ $stHk['daCoDiem'] }} Nhóm</span></td>
                        <td class="text-center"><span class="badge bg-emerald fs-6 rounded-pill px-3">{{ $stHk['diemTrungBinh'] }} / 10.0</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-3 text-muted">Chưa có dữ liệu học kỳ.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- THỐNG KÊ THEO GIẢNG VIÊN HƯỚNG DẪN --}}
<div class="card card-modern">
    <div class="card-modern-header">
        <span class="fw-bold"><i class="fa-solid fa-user-tie me-2 text-cyan"></i>Thống Kê Phụ Trách Giảng Viên</span>
    </div>
    <div class="card-modern-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4">Mã GV</th>
                        <th>Họ Và Tên Giảng Viên</th>
                        <th class="text-center">Số Nhóm Đang Hướng Dẫn</th>
                        <th class="text-center">Số Nhóm Đã Chấm Điểm</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($statsByGiangVien as $stGv)
                    <tr>
                        <td class="px-4 fw-bold text-cyan">{{ $stGv['maGV'] }}</td>
                        <td class="fw-bold text-dark">{{ $stGv['hoTen'] }}</td>
                        <td class="text-center"><span class="badge bg-info text-dark rounded-pill px-3">{{ $stGv['tongNhomHD'] }} Nhóm</span></td>
                        <td class="text-center"><span class="badge bg-success rounded-pill px-3">{{ $stGv['daChamDiem'] }} Nhóm</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-3 text-muted">Chưa có dữ liệu giảng viên.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
