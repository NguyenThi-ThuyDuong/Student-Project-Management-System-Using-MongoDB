@extends('layouts.admin')
@section('page_title', 'Quản Lý Học Kỳ')

@section('content')
<div class="page-header-zone mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="fw-bold mb-1"><i class="fa-solid fa-calendar-days me-2 text-cyan"></i>Quản Lý Học Kỳ</h1>
        <div class="text-muted small">Danh mục các học kỳ đào tạo và thời gian thực hiện đồ án tốt nghiệp</div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.import.template', 'hocky') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fa-solid fa-file-arrow-down me-1"></i>File Mẫu .xlsx
        </a>
        <button type="button" class="btn btn-navy btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fa-solid fa-file-excel me-1 text-cyan"></i>Import Excel
        </button>
        <a href="{{ route('hocky.create') }}" class="btn btn-cyan btn-sm rounded-pill px-3">
            <i class="fa-solid fa-plus me-1"></i>Thêm Học Kỳ Mới
        </a>
    </div>
</div>

{{-- BỘ LỌC TÌM KIẾM HỌC KỲ --}}
<div class="card-modern mb-4">
    <div class="card-modern-body p-3">
        <form method="GET" action="{{ route('hocky.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control-modern" placeholder="🔍 Nhập Tên Học Kỳ, Năm Học hoặc Mã...">
            </div>
            <div class="col-md-4">
                <select name="NamHoc" class="form-select-modern" onchange="this.form.submit()">
                    <option value="">-- Tất cả Năm Học --</option>
                    @foreach(\App\Models\HocKy::pluck('NamHoc')->unique() as $nh)
                        @if($nh)
                        <option value="{{ $nh }}" {{ request('NamHoc') == $nh ? 'selected' : '' }}>{{ $nh }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-cyan btn-sm rounded-pill px-3 w-100"><i class="fa-solid fa-filter me-1"></i>Lọc</button>
                <a href="{{ route('hocky.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3"><i class="fa-solid fa-rotate me-1"></i>Reset</a>
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
        <span><i class="fa-solid fa-list-check me-2"></i>Danh Sách Học Kỳ ({{ $hockys->total() }})</span>
        <span class="badge bg-white text-dark px-3 py-1 rounded-pill">Trang {{ $hockys->currentPage() }}/{{ $hockys->lastPage() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-saas-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">STT</th>
                        <th width="15%">Mã Học Kỳ</th>
                        <th width="30%">Tên Học Kỳ</th>
                        <th width="20%">Năm Học</th>
                        <th width="10%">Ngày Bắt Đầu</th>
                        <th width="10%">Ngày Kết Thúc</th>
                        <th width="10%" class="text-end pe-4">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hockys as $index => $item)
                    <tr>
                        <td class="text-center fw-bold text-muted">{{ $hockys->firstItem() + $index }}</td>
                        <td>
                            <span class="badge-indigo text-nowrap"><i class="fa-solid fa-calendar me-1"></i>{{ $item->MaHocKy }}</span>
                        </td>
                        <td>
                            <span class="fw-bold text-dark">{{ $item->TenHocKy }}</span>
                        </td>
                        <td>
                            <span class="badge-cyan text-nowrap"><i class="fa-solid fa-clock me-1"></i>{{ $item->NamHoc }}</span>
                        </td>
                        <td>
                            <span class="small text-muted text-nowrap">{{ $item->NgayBatDau ? date('d/m/Y', strtotime($item->NgayBatDau)) : '—' }}</span>
                        </td>
                        <td>
                            <span class="small text-muted text-nowrap">{{ $item->NgayKetThuc ? date('d/m/Y', strtotime($item->NgayKetThuc)) : '—' }}</span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('hocky.edit', $item->_id ?? $item->MaHocKy) }}" class="btn-action-pill btn-action-edit" title="Chỉnh sửa">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('hocky.destroy', $item->_id ?? $item->MaHocKy) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa học kỳ {{ $item->TenHocKy }}?');">
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
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-folder-open fs-1 d-block mb-2 opacity-50"></i>
                            Chưa tìm thấy học kỳ nào.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($hockys->hasPages())
    <div class="card-footer bg-white border-0 py-3 d-flex justify-content-center">
        {{ $hockys->appends(request()->query())->links() }}
    </div>
    @endif
</div>

<!-- MODAL IMPORT -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.hocky.import') }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow-lg">
            @csrf
            <div class="modal-header text-white" style="background: var(--grad-card-head); border-radius: 16px 16px 0 0;">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-file-excel me-2 text-cyan"></i>Import Danh Sách Học Kỳ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="small text-muted mb-3">Tải file Excel mẫu `.xlsx` để nhập hàng loạt học kỳ.</p>
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