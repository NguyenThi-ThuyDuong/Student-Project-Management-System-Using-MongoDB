@extends('layouts.admin')
@section('page_title', 'Quản Lý Ngành Học')

@section('content')
<div class="page-header-zone mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="fw-bold mb-1"><i class="fa-solid fa-graduation-cap me-2 text-cyan"></i>Quản Lý Ngành Học</h1>
        <div class="text-muted small">Danh mục các ngành học đào tạo thuộc bộ môn quản lý trong nhà trường</div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.import.template', 'nganh') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fa-solid fa-file-arrow-down me-1"></i>File Mẫu .xlsx
        </a>
        <button type="button" class="btn btn-navy btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fa-solid fa-file-excel me-1 text-cyan"></i>Import Excel
        </button>
        <a href="{{ route('nganh.create') }}" class="btn btn-cyan btn-sm rounded-pill px-3">
            <i class="fa-solid fa-plus me-1"></i>Thêm Ngành Mới
        </a>
    </div>
</div>

{{-- BỘ LỌC TÌM KIẾM NGÀNH HỌC --}}
<div class="card-modern mb-4">
    <div class="card-modern-body p-3">
        <form method="GET" action="{{ route('nganh.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control-modern" placeholder="🔍 Nhập Tên Ngành, Mã Ngành hoặc Mô Tả...">
            </div>
            <div class="col-md-4">
                <select name="MaBoMon" class="form-select-modern" onchange="this.form.submit()">
                    <option value="">-- Tất cả Bộ Môn --</option>
                    @foreach(\App\Models\BoMon::all() as $bm)
                        <option value="{{ $bm->MaBoMon }}" {{ request('MaBoMon') == $bm->MaBoMon ? 'selected' : '' }}>{{ $bm->TenBoMon }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-cyan btn-sm rounded-pill px-3 w-100"><i class="fa-solid fa-filter me-1"></i>Lọc</button>
                <a href="{{ route('nganh.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3"><i class="fa-solid fa-rotate me-1"></i>Reset</a>
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

<div class="card card-modern shadow-sm">
    <div class="card-modern-header">
        <span><i class="fa-solid fa-list-check me-2"></i>Danh Sách Ngành Học ({{ $nganhs->total() }})</span>
        <span class="badge bg-white text-dark px-3 py-1 rounded-pill">Trang {{ $nganhs->currentPage() }}/{{ $nganhs->lastPage() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-saas-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">STT</th>
                        <th width="15%">Mã Ngành</th>
                        <th width="30%">Tên Ngành Học</th>
                        <th width="25%">Bộ Môn Phụ Trách</th>
                        <th width="15%">Mô Tả</th>
                        <th width="10%" class="text-end pe-4">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($nganhs as $index => $item)
                    <tr>
                        <td class="text-center fw-bold text-muted">{{ $nganhs->firstItem() + $index }}</td>
                        <td>
                            <span class="badge-indigo text-nowrap"><i class="fa-solid fa-graduation-cap me-1"></i>{{ $item->MaNganh }}</span>
                        </td>
                        <td>
                            <span class="fw-bold text-dark">{{ $item->TenNganh }}</span>
                        </td>
                        <td>
                            <span class="badge-cyan text-nowrap"><i class="fa-solid fa-building me-1"></i>{{ $item->boMon->TenBoMon ?? $item->MaBoMon ?? '—' }}</span>
                        </td>
                        <td>
                            <span class="text-muted small text-truncate d-inline-block" style="max-width: 250px;">{{ $item->MoTa ?? '—' }}</span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('nganh.edit', $item->_id ?? $item->MaNganh) }}" class="btn-action-pill btn-action-edit" title="Chỉnh sửa">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('nganh.destroy', $item->_id ?? $item->MaNganh) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa ngành {{ $item->TenNganh }}?');">
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
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-folder-open fs-1 d-block mb-2 opacity-50"></i>
                            Chưa tìm thấy ngành học nào.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($nganhs->hasPages())
    <div class="card-footer bg-white border-0 py-3 d-flex justify-content-center">
        {{ $nganhs->appends(request()->query())->links() }}
    </div>
    @endif
</div>

<!-- MODAL IMPORT -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.nganh.import') }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow-lg">
            @csrf
            <div class="modal-header text-white" style="background: var(--grad-card-head); border-radius: 16px 16px 0 0;">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-file-excel me-2 text-cyan"></i>Import Danh Sách Ngành Học</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="small text-muted mb-3">Tải file Excel mẫu `.xlsx` để nhập hàng loạt ngành học.</p>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Chọn File Excel (.xlsx, .csv)</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.csv,.xls" required>
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