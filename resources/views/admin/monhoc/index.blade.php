@extends('layouts.admin')
@section('page_title', 'Quản Lý Môn Học / Học Phần')

@section('content')
<div class="page-header-zone mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="fw-bold mb-1"><i class="fa-solid fa-book-open me-2 text-cyan"></i>Quản Lý Môn Học / Học Phần</h1>
        <div class="text-muted small">Danh mục môn học, số tín chỉ đồ án và bộ môn chuyên môn quản lý</div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.import.template', 'monhoc') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fa-solid fa-file-arrow-down me-1"></i>File Mẫu .xlsx
        </a>
        <button type="button" class="btn btn-navy btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fa-solid fa-file-excel me-1 text-cyan"></i>Import Excel
        </button>
        <a href="{{ route('monhoc.create') }}" class="btn btn-cyan btn-sm rounded-pill px-3">
            <i class="fa-solid fa-plus me-1"></i>Thêm Môn Mới
        </a>
    </div>
</div>

{{-- BỘ LỌC TÌM KIẾM MÔN HỌC --}}
<div class="card-modern mb-4">
    <div class="card-modern-body p-3">
        <form method="GET" action="{{ route('monhoc.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control-modern" placeholder="🔍 Nhập Tên Môn Học hoặc Mã Môn...">
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
                <a href="{{ route('monhoc.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3"><i class="fa-solid fa-rotate me-1"></i>Reset</a>
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
        <span><i class="fa-solid fa-list-check me-2"></i>Danh Sách Môn Học ({{ $monhocs->total() }})</span>
        <span class="badge bg-white text-dark px-3 py-1 rounded-pill">Trang {{ $monhocs->currentPage() }}/{{ $monhocs->lastPage() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-saas-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">STT</th>
                        <th width="15%">Mã Môn</th>
                        <th width="35%">Tên Môn Học / Học Phần</th>
                        <th width="25%">Bộ Môn Quản Lý</th>
                        <th width="10%" class="text-center">Số Tín Chỉ</th>
                        <th width="10%" class="text-end pe-4">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($monhocs as $index => $item)
                    <tr>
                        <td class="text-center fw-bold text-muted">{{ $monhocs->firstItem() + $index }}</td>
                        <td>
                            <span class="badge-indigo text-nowrap"><i class="fa-solid fa-book me-1"></i>{{ $item->MaMon }}</span>
                        </td>
                        <td>
                            <span class="fw-bold text-dark">{{ $item->TenMon }}</span>
                        </td>
                        <td>
                            <span class="badge-cyan text-nowrap"><i class="fa-solid fa-building me-1"></i>{{ $item->boMon->TenBoMon ?? $item->MaBoMon ?? '—' }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border px-3 py-1 rounded-pill"><i class="fa-solid fa-award text-warning me-1"></i>{{ $item->SoTinChi }} tín chỉ</span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('monhoc.edit', $item->_id ?? $item->MaMon) }}" class="btn-action-pill btn-action-edit" title="Chỉnh sửa">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('monhoc.destroy', $item->_id ?? $item->MaMon) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa môn {{ $item->TenMon }}?');">
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
                            Chưa tìm thấy môn học nào.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($monhocs->hasPages())
    <div class="card-footer bg-white border-0 py-3 d-flex justify-content-center">
        {{ $monhocs->appends(request()->query())->links() }}
    </div>
    @endif
</div>

<!-- MODAL IMPORT -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.monhoc.import') }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow-lg">
            @csrf
            <div class="modal-header text-white" style="background: var(--grad-card-head); border-radius: 16px 16px 0 0;">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-file-excel me-2 text-cyan"></i>Import Danh Sách Môn Học</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="small text-muted mb-3">Tải file Excel mẫu `.xlsx` để nhập hàng loạt môn học.</p>
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