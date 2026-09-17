@extends('layouts.admin')
@section('page_title', 'Danh Sách Giảng Viên')

@section('content')
<div class="page-header-zone mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="fw-bold mb-1"><i class="fa-solid fa-chalkboard-user me-2 text-cyan"></i>Quản Lý Giảng Viên</h1>
        <div class="text-muted small">Quản lý hồ sơ giảng viên, bộ môn, học vị và quyền khóa tài khoản hệ thống</div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.import.template', 'giangvien') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fa-solid fa-file-arrow-down me-1"></i>File Mẫu .xlsx
        </a>
        <button type="button" class="btn btn-navy btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fa-solid fa-file-excel me-1 text-cyan"></i>Import Excel
        </button>
        <a href="{{ route('giangvien.create') }}" class="btn btn-cyan btn-sm rounded-pill px-3">
            <i class="fa-solid fa-plus me-1"></i>Thêm GV Mới
        </a>
    </div>
</div>

{{-- BỘ LỌC TÌM KIẾM GIẢNG VIÊN --}}
<div class="card-modern mb-4">
    <div class="card-modern-body p-3">
        <form method="GET" action="{{ route('giangvien.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control-modern" placeholder="🔍 Nhập Mã GV, Họ tên hoặc Email...">
            </div>
            <div class="col-md-3">
                <select name="MaBoMon" class="form-select-modern" onchange="this.form.submit()">
                    <option value="">-- Tất cả Bộ Môn --</option>
                    @foreach(\App\Models\BoMon::all() as $bm)
                        <option value="{{ $bm->MaBoMon }}" {{ request('MaBoMon') == $bm->MaBoMon ? 'selected' : '' }}>{{ $bm->TenBoMon }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="HocVi" class="form-select-modern" onchange="this.form.submit()">
                    <option value="">-- Tất cả Học Vị --</option>
                    <option value="Cử nhân" {{ request('HocVi') == 'Cử nhân' ? 'selected' : '' }}>Cử nhân</option>
                    <option value="Thạc sĩ" {{ request('HocVi') == 'Thạc sĩ' ? 'selected' : '' }}>Thạc sĩ</option>
                    <option value="Tiến sĩ" {{ request('HocVi') == 'Tiến sĩ' ? 'selected' : '' }}>Tiến sĩ</option>
                    <option value="Phó Giáo sư" {{ request('HocVi') == 'Phó Giáo sư' ? 'selected' : '' }}>Phó Giáo sư</option>
                    <option value="Giáo sư" {{ request('HocVi') == 'Giáo sư' ? 'selected' : '' }}>Giáo sư</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-cyan btn-sm rounded-pill px-3 w-100"><i class="fa-solid fa-filter me-1"></i>Lọc</button>
                <a href="{{ route('giangvien.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3"><i class="fa-solid fa-rotate me-1"></i>Reset</a>
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

{{-- BẢNG DỮ LIỆU GIẢNG VIÊN SAAS TABLE --}}
<div class="card card-modern shadow-sm">
    <div class="card-modern-header">
        <span><i class="fa-solid fa-list-check me-2"></i>Danh Sách Giảng Viên ({{ $giangviens->total() }})</span>
        <span class="badge bg-white text-dark px-3 py-1 rounded-pill">Trang {{ $giangviens->currentPage() }}/{{ $giangviens->lastPage() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-saas-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">STT</th>
                        <th width="12%">MSGV</th>
                        <th width="22%">Họ và Tên</th>
                        <th width="14%">Học Vị</th>
                        <th width="20%">Bộ Môn Trực Thuộc</th>
                        <th width="15%">Tài Khoản</th>
                        <th width="10%" class="text-center">Trạng Thái</th>
                        <th width="12%" class="text-end pe-4">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($giangviens as $index => $item)
                    @php
                        $boMonModel = $item->boMonModel;
                    @endphp
                    <tr>
                        <td class="text-center fw-bold text-muted">{{ $giangviens->firstItem() + $index }}</td>
                        <td>
                            <span class="badge-indigo text-nowrap"><i class="fa-solid fa-id-badge me-1"></i>{{ $item->MaGV }}</span>
                        </td>
                        <td>
                            <span class="fw-bold text-dark">{{ $item->HoTen }}</span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border text-nowrap"><i class="fa-solid fa-graduation-cap text-cyan me-1"></i>{{ $item->HocVi ?? 'Giảng viên' }}</span>
                        </td>
                        <td>
                            <span class="badge-cyan text-nowrap"><i class="fa-solid fa-building me-1"></i>{{ $boMonModel->TenBoMon ?? ($item->boMon->TenBoMon ?? 'Chưa phân công') }}</span>
                        </td>
                        <td>
                            <span class="small text-muted text-nowrap"><i class="fa-solid fa-user me-1 text-secondary"></i>{{ $item->taiKhoan->TenDangNhap ?? '—' }}</span>
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
                                @if($item->taiKhoan && ($tkId = (string)($item->taiKhoan->_id ?? $item->MaTK ?? '')))
                                <form action="{{ route('admin.taikhoan.toggleLock', $tkId) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn thay đổi trạng thái khóa tài khoản này?');">
                                    @csrf
                                    <button type="submit" class="btn-action-pill" title="{{ $item->taiKhoan->TrangThai ? 'Khóa tài khoản' : 'Mở khóa tài khoản' }}">
                                        <i class="fa-solid {{ $item->taiKhoan->TrangThai ? 'fa-lock text-warning' : 'fa-unlock text-success' }}"></i>
                                    </button>
                                </form>
                                @endif
                                <a href="{{ route('giangvien.edit', $item->MaGV) }}" class="btn-action-pill btn-action-edit" title="Chỉnh sửa">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('giangvien.destroy', $item->MaGV) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa giảng viên này?');">
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
                            <i class="fa-solid fa-chalkboard-user fs-1 d-block mb-2 opacity-50"></i>
                            Chưa có giảng viên nào trong hệ thống.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($giangviens->hasPages())
    <div class="card-footer bg-white border-0 py-3 d-flex justify-content-center">
        {{ $giangviens->appends(request()->query())->links() }}
    </div>
    @endif
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.giangvien.import') }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow-lg">
            @csrf
            <div class="modal-header text-white" style="background: var(--grad-card-head); border-radius: 16px 16px 0 0;">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-file-excel me-2 text-cyan"></i>Import Danh Sách Giảng Viên</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="small text-muted mb-3">Tải file Excel mẫu `.xlsx` để nhập hàng loạt hồ sơ giảng viên chính xác.</p>
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