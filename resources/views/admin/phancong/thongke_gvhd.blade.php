@extends('layouts.admin')
@section('page_title', 'Thống Kê Tải Giảng Viên Hướng Dẫn')

@section('content')
<div class="page-header-zone mb-4">
    <div>
        <h1 class="fw-bold"><i class="fa-solid fa-chart-user me-2 text-cyan"></i>Thống Kê Tải Giảng Viên Hướng Dẫn (GVHD)</h1>
        <div class="text-muted small">Theo dõi chi tiết số lượng Nhóm và Sinh viên mà mỗi Giảng viên đang phụ trách hướng dẫn</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('phancong.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fa-solid fa-arrow-left me-1"></i>Quay Lại Phân Công
        </a>
    </div>
</div>

<div class="card card-modern mb-4">
    <div class="card-modern-header d-flex justify-content-between align-items-center">
        <span class="fw-bold"><i class="fa-solid fa-users-line text-cyan me-2"></i>Danh Sách Tải Giảng Dạy Của Giảng Viên</span>
        <form action="{{ route('admin.phancong.thongke_gvhd') }}" method="GET" class="d-inline-flex gap-2">
            <select name="MaGV" class="form-select form-select-sm rounded-pill" style="min-width: 280px;" onchange="this.form.submit()">
                <option value="">-- Tất cả Giảng Viên --</option>
                @foreach($giangViens as $gv)
                    <option value="{{ $gv->_id }}" {{ $selectedGvId == $gv->_id ? 'selected' : '' }}>
                        {{ $gv->HoTen }} ({{ $gv->MaGV }}) - {{ $gv->boMon->TenBoMon ?? 'Bộ môn' }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="card-modern-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4">Mã GV</th>
                        <th>Họ Và Tên Giảng Viên</th>
                        <th>Bộ Môn</th>
                        <th class="text-center">Số Lớp HP Phụ Trách</th>
                        <th class="text-center">Số Nhóm Đang HD</th>
                        <th class="text-center">Số Sinh Viên HD</th>
                        <th>Tải Giảng Dạy</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stats as $st)
                    <tr class="{{ $selectedGvId == $st->giangVien->_id ? 'table-info' : '' }}">
                        <td class="px-4 fw-bold text-cyan">{{ $st->giangVien->MaGV }}</td>
                        <td>
                            <strong class="text-dark d-block">{{ $st->giangVien->HoTen }}</strong>
                            <small class="text-muted">{{ $st->giangVien->Email }}</small>
                        </td>
                        <td>{{ $st->giangVien->boMon->TenBoMon ?? 'Chưa phân bộ môn' }}</td>
                        <td class="text-center fw-bold">{{ $st->lopHocPhans->count() }} Lớp</td>
                        <td class="text-center">
                            <span class="badge bg-primary fs-6 rounded-pill px-3">{{ $st->nhomCount }} Nhóm</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success fs-6 rounded-pill px-3">{{ $st->sinhVienCount }} SV</span>
                        </td>
                        <td>
                            @if($st->sinhVienCount > 25)
                                <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1"><i class="fa-solid fa-triangle-exclamation me-1"></i>Tải nặng (>25 SV)</span>
                            @elseif($st->sinhVienCount > 15)
                                <span class="badge bg-warning-subtle text-warning border border-warning px-2 py-1"><i class="fa-solid fa-scale-unbalanced me-1"></i>Trung bình (15-25 SV)</span>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i>Tối ưu (&lt;15 SV)</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Chưa có dữ liệu thống kê giảng viên.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
