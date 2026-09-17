@extends('layouts.admin')
@section('page_title', 'Danh Sách Sinh Viên')

@section('content')
<div class="page-header-zone mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="fw-bold mb-1"><i class="fa-solid fa-user-graduate me-2 text-cyan"></i>Quản Lý Sinh Viên</h1>
        <div class="text-muted small">Danh sách hồ sơ sinh viên, thông tin lớp hành chính, ngành và trạng thái tài khoản</div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.import.template', 'sinhvien') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fa-solid fa-file-arrow-down me-1"></i>File Mẫu .xlsx
        </a>
        <button type="button" class="btn btn-navy btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fa-solid fa-file-excel me-1 text-cyan"></i>Import Excel
        </button>
        <a href="{{ route('sinhvien.create') }}" class="btn btn-cyan btn-sm rounded-pill px-3">
            <i class="fa-solid fa-plus me-1"></i>Thêm SV Mới
        </a>
    </div>
</div>

{{-- BỘ LỌC TÌM KIẾM SANG TRỌNG --}}
<div class="card-modern mb-4">
    <div class="card-modern-body p-3">
        <form method="GET" action="{{ route('sinhvien.index') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control-modern" placeholder="🔍 Nhập MSSV hoặc Họ tên...">
            </div>
            <div class="col-md-2">
                <select name="MaLop" class="form-select-modern" onchange="this.form.submit()">
                    <option value="">-- Tất cả Lớp --</option>
                    @foreach(\App\Models\Lop::all() as $l)
                        <option value="{{ $l->MaLop }}" {{ request('MaLop') == $l->MaLop ? 'selected' : '' }}>{{ $l->TenLop }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="MaNganh" class="form-select-modern" onchange="this.form.submit()">
                    <option value="">-- Tất cả Ngành --</option>
                    @foreach(\App\Models\Nganh::all() as $n)
                        <option value="{{ $n->MaNganh }}" {{ request('MaNganh') == $n->MaNganh ? 'selected' : '' }}>{{ $n->TenNganh }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="TrangThai" class="form-select-modern" onchange="this.form.submit()">
                    <option value="">-- Trạng thái --</option>
                    <option value="1" {{ request('TrangThai') == '1' ? 'selected' : '' }}>Hoạt động</option>
                    <option value="0" {{ request('TrangThai') == '0' ? 'selected' : '' }}>Đã khóa</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-cyan btn-sm rounded-pill px-3 w-100"><i class="fa-solid fa-filter me-1"></i>Lọc</button>
                <a href="{{ route('sinhvien.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3"><i class="fa-solid fa-rotate me-1"></i>Reset</a>
            </div>
        </form>
    </div>
</div>

@if(session('import_result'))
<div class="alert alert-info alert-dismissible fade show mb-4 border-0 rounded-4" style="background: #EFF6FF; border-left: 4px solid var(--v-cyan) !important;">
    <i class="fa-solid fa-circle-info me-2 text-cyan"></i>{!! session('import_result') !!}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- BẢNG DỮ LIỆU ENTERPRISE SAAS TABLE --}}
<div class="card card-modern shadow-sm">
    <div class="card-modern-header">
        <span><i class="fa-solid fa-list-check me-2"></i>Danh Sách Sinh Viên ({{ $sinhviens->total() }})</span>
        <span class="badge bg-white text-dark px-3 py-1 rounded-pill">Trang {{ $sinhviens->currentPage() }}/{{ $sinhviens->lastPage() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-saas-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="4%" class="text-center">STT</th>
                        <th width="10%">MSSV</th>
                        <th width="20%">Họ và Tên</th>
                        <th width="16%">Lớp Hành Chính</th>
                        <th width="20%">Ngành Học</th>
                        <th width="18%">Email Liên Hệ</th>
                        <th width="12%" class="text-center">Trạng Thái</th>
                        <th width="10%" class="text-end pe-4">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sinhviens as $index => $item)
                    @php
                        $lopModel = $item->lopModel;
                        $nganhModel = $item->nganhModel;
                    @endphp
                    <tr>
                        <td class="text-center fw-bold text-muted">{{ $sinhviens->firstItem() + $index }}</td>
                        <td>
                            <span class="badge-indigo text-nowrap"><i class="fa-solid fa-id-card me-1"></i>{{ $item->MaSV }}</span>
                        </td>
                        <td>
                            <a href="{{ route('sinhvien.show', $item->_id) }}" class="fw-bold text-dark text-decoration-none hover-cyan">
                                {{ $item->HoTen }}
                            </a>
                        </td>
                        <td>
                            <span class="badge-cyan text-nowrap"><i class="fa-solid fa-users-rectangle me-1"></i>{{ $lopModel->TenLop ?? ($item->lop->TenLop ?? 'Chưa xếp lớp') }}</span>
                        </td>
                        <td>
                            <span class="text-dark fw-medium small">{{ $nganhModel->TenNganh ?? ($item->nganh->TenNganh ?? '—') }}</span>
                        </td>
                        <td>
                            <span class="small text-muted text-nowrap" title="{{ $item->Email }}"><i class="fa-solid fa-envelope me-1 text-cyan"></i>{{ $item->Email }}</span>
                        </td>
                        <td class="text-center">
                            @if($item->taiKhoan && $item->taiKhoan->TrangThai)
                                <span class="badge-success text-nowrap"><i class="fa-solid fa-circle-check me-1"></i>Hoạt động</span>
                            @else
                                <span class="badge-danger text-nowrap"><i class="fa-solid fa-lock me-1"></i>Đã khóa</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('sinhvien.show', $item->_id) }}" class="btn-action-pill btn-action-view" title="Xem chi tiết">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('sinhvien.edit', $item->_id) }}" class="btn-action-pill btn-action-edit" title="Chỉnh sửa">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('admin.sinhvien.toggleStatus', $item->_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn thay đổi trạng thái tài khoản sinh viên này?')">
                                    @csrf
                                    <button type="submit" class="btn-action-pill border-0 {{ $item->taiKhoan && $item->taiKhoan->TrangThai ? 'bg-warning bg-opacity-10 text-warning' : 'bg-success bg-opacity-10 text-success' }}" title="{{ $item->taiKhoan && $item->taiKhoan->TrangThai ? 'Khóa tài khoản' : 'Mở khóa tài khoản' }}">
                                        <i class="fa-solid {{ $item->taiKhoan && $item->taiKhoan->TrangThai ? 'fa-lock' : 'fa-lock-open' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('sinhvien.destroy', $item->_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa sinh viên này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action-pill btn-action-delete" title="Xóa">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-user-slash fs-1 d-block mb-2 opacity-50"></i>
                            Chưa tìm thấy sinh viên nào trong hệ thống.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($sinhviens->hasPages())
    <div class="card-footer bg-white border-0 py-3 d-flex justify-content-center">
        {{ $sinhviens->appends(request()->query())->links() }}
    </div>
    @endif
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.sinhvien.import') }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow-lg">
            @csrf
            <div class="modal-header text-white" style="background: var(--grad-card-head); border-radius: 16px 16px 0 0;">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-file-excel me-2 text-cyan"></i>Import Danh Sách Sinh Viên</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="small text-muted mb-3">Tải file Excel mẫu `.xlsx` để nhập hàng loạt hồ sơ sinh viên chính xác.</p>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Chọn File Excel (.xlsx, .xls)</label>
                    <input type="file" name="excel_file" class="form-control" accept=".xlsx, .xls" required>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-light btn-sm rounded-pill px-3 border" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-cyan btn-sm rounded-pill px-4"><i class="fa-solid fa-upload me-1"></i>Tải Lên & Nhập</button>
            </div>
        </form>
    </div>
</div>
@endsection