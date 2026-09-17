@extends('layouts.admin')
@section('page_title', 'Chi Tiết Lớp Học Phần - ' . $lopHocPhan->TenLopHP)

@section('content')
<div class="page-header-zone mb-3">
    <div>
        <h1 class="fw-bold"><i class="fa-solid fa-graduation-cap me-2 text-cyan"></i>Lớp Học Phần: {{ $lopHocPhan->TenLopHP }}</h1>
        <div class="text-muted small">Mã Lớp HP: <span class="fw-bold text-dark">#{{ $lopHocPhan->MaLopHP }}</span> | Quản lý sinh viên và theo dõi học phần</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.lophocphan.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fa-solid fa-arrow-left me-1"></i>Quay Lại Danh Sách
        </a>
        <a href="{{ route('admin.lophocphan.edit', $lopHocPhan->MaLopHP ?? $lopHocPhan->_id) }}" class="btn btn-warning btn-sm rounded-pill px-3">
            <i class="fa-solid fa-pen-to-square me-1"></i>Chỉnh Sửa Lớp HP
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show border-0 mb-4" role="alert" style="background: #ECFDF5; border-left: 4px solid #10b981 !important;">
    <i class="fa-solid fa-circle-check me-2 text-success"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error') || $errors->any())
<div class="alert alert-danger alert-dismissible fade show border-0 mb-4" role="alert" style="background: #FEF2F2; border-left: 4px solid #ef4444 !important;">
    <i class="fa-solid fa-circle-exclamation me-2 text-danger"></i>{{ session('error') ?? $errors->first() }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- THÔNG TIN TỔNG QUAN LỚP HP -->
<div class="card card-modern mb-4">
    <div class="card-modern-header">
        <span class="fw-bold"><i class="fa-solid fa-circle-info text-cyan me-2"></i>Thông Tin Tổng Quan Lớp Học Phần</span>
    </div>
    <div class="card-modern-body p-4">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 border">
                    <span class="text-muted small d-block mb-1">Môn Học</span>
                    <h6 class="mb-0 fw-bold text-dark">{{ $lopHocPhan->monHoc->TenMon ?? $lopHocPhan->mon_hoc_model->TenMon ?? 'N/A' }} <span class="badge bg-info text-dark ms-1">{{ $lopHocPhan->monHoc->SoTinChi ?? $lopHocPhan->mon_hoc_model->SoTinChi ?? 0 }} tín chỉ</span></h6>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 border">
                    <span class="text-muted small d-block mb-1">Học Kỳ</span>
                    <h6 class="mb-0 fw-bold text-dark">{{ $lopHocPhan->hocKy->TenHocKy ?? 'N/A' }} ({{ $lopHocPhan->hocKy->NamHoc ?? 'N/A' }})</h6>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 border">
                    <span class="text-muted small d-block mb-1">Giảng Viên Hướng Dẫn</span>
                    <h6 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-user-tie text-secondary me-1"></i>{{ $lopHocPhan->giangVien->HoTen ?? 'Chưa phân công' }}</h6>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 border">
                    <span class="text-muted small d-block mb-1">Sĩ Số / Tối Đa</span>
                    <h6 class="mb-0 fw-bold text-dark">
                        @php $siSoCurrent = count($enrolledStudents ?? []); @endphp
                        <span class="badge {{ $siSoCurrent >= $lopHocPhan->SiSoToiDa ? 'bg-danger' : 'bg-success' }} px-2 py-1">
                            {{ $siSoCurrent }} / {{ $lopHocPhan->SiSoToiDa }} Sinh viên
                        </span>
                    </h6>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 border">
                    <span class="text-muted small d-block mb-1">Trạng Thái Lớp</span>
                    <h6 class="mb-0 fw-bold">
                        @if($lopHocPhan->TrangThai === 'Đang mở')
                            <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="fa-solid fa-lock-open me-1"></i>Đang mở</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary px-2 py-1"><i class="fa-solid fa-lock me-1"></i>Đã đóng</span>
                        @endif
                    </h6>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FORM THÊM SINH VIÊN VÀO LỚP HP -->
<div class="card card-modern mb-4">
    <div class="card-modern-header d-flex justify-content-between align-items-center">
        <span class="fw-bold"><i class="fa-solid fa-user-plus text-cyan me-2"></i>Thêm Sinh Viên Vào Lớp Học Phần</span>
    </div>
    <div class="card-modern-body p-4">
        <form action="{{ route('admin.lophocphan.addStudent', $lopHocPhan->MaLopHP ?? $lopHocPhan->_id) }}" method="POST" class="row g-2 align-items-center">
            @csrf
            <div class="col-md-8">
                <select name="MaSV" class="form-select-modern" required>
                    <option value="">-- Chọn Sinh Viên Cần Thêm Vào Lớp HP Này --</option>
                    @foreach($availableStudents as $sv)
                        <option value="{{ $sv->MaSV }}">
                            MSSV: {{ $sv->MaSV }} - {{ $sv->HoTen }} (Lớp HC: {{ $sv->lop->TenLop ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-cyan rounded-pill px-4" {{ $siSoCurrent >= $lopHocPhan->SiSoToiDa ? 'disabled' : '' }}>
                    <i class="fa-solid fa-plus me-1"></i>Thêm Vào Lớp HP
                </button>
            </div>
        </form>
    </div>
</div>

<!-- DANH SÁCH SINH VIÊN ĐÃ ĐĂNG KÝ HỌC PHẦN NÀY -->
<div class="card card-modern">
    <div class="card-modern-header d-flex justify-content-between align-items-center">
        <span class="fw-bold"><i class="fa-solid fa-users text-cyan me-2"></i>Danh Sách Sinh Viên Thuộc Lớp HP ({{ count($enrolledStudents ?? []) }} SV)</span>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.import.template', 'sinhvien_lophocphan') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                <i class="fa-solid fa-file-excel me-1"></i>File Mẫu .xlsx
            </a>
            <button class="btn btn-sm btn-cyan rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#importStudentModal">
                <i class="fa-solid fa-file-import me-1"></i>Import Excel
            </button>
        </div>
    </div>
    <div class="card-modern-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4">STT</th>
                        <th>MSSV</th>
                        <th>Họ Và Tên</th>
                        <th>Lớp Hành Chính</th>
                        <th>Email</th>
                        <th class="text-end px-4">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enrolledStudents as $index => $sv)
                    <tr>
                        <td class="px-4 fw-bold text-muted">{{ $index + 1 }}</td>
                        <td><span class="badge-cyan px-2 py-1 rounded-pill fw-bold">{{ $sv->MaSV }}</span></td>
                        <td class="fw-bold text-dark">{{ $sv->HoTen }}</td>
                        <td>
                            <span class="badge bg-secondary-subtle text-dark border">
                                {{ $sv->lop->TenLop ?? ($sv->lop_model->TenLop ?? 'N/A') }}
                            </span>
                        </td>
                        <td>{{ $sv->Email ?? '—' }}</td>
                        <td class="text-end px-4">
                            <form action="{{ route('admin.lophocphan.removeStudent', [$lopHocPhan->MaLopHP ?? $lopHocPhan->_id, $sv->MaSV ?? $sv->_id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sinh viên {{ $sv->HoTen }} ({{ $sv->MaSV }}) khỏi lớp học phần này?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light text-danger rounded-circle" title="Xóa khỏi lớp HP" style="width: 34px; height: 34px;">
                                    <i class="fa-solid fa-user-minus"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-user-slash fa-2x mb-2 d-block text-cyan opacity-50"></i>
                            Lớp học phần này chưa có sinh viên nào. Vui lòng thêm sinh viên ở khung phía trên hoặc dùng nút Import Excel.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL IMPORT SINH VIÊN VÀO LỚP HP -->
<div class="modal fade" id="importStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.lophocphan.importStudents', $lopHocPhan->MaLopHP ?? $lopHocPhan->_id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-navy text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-file-excel me-2 text-cyan"></i>Import Sinh Viên Vào Lớp HP</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label-modern fw-bold">Chọn file Excel (.xlsx, .csv)</label>
                        <input type="file" name="file" class="form-control-modern" accept=".xlsx,.csv,.xls" required>
                    </div>
                    <div class="alert alert-info border-0 rounded-3 mb-0 d-flex align-items-center justify-content-between p-3">
                        <div>
                            <i class="fa-solid fa-file-excel text-success me-2 fs-5"></i>
                            <span class="small fw-semibold text-secondary">Tải file Excel mẫu đúng chuẩn</span>
                        </div>
                        <a href="{{ asset('templates/Template_SinhVien_LopHocPhan.xlsx') }}" download class="btn btn-sm btn-outline-primary rounded-pill">
                            <i class="fa-solid fa-download me-1"></i>Tải File Mẫu
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-cyan rounded-pill px-4"><i class="fa-solid fa-upload me-1"></i>Import Ngay</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
