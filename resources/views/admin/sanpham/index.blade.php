@extends('layouts.admin')
@section('page_title', 'Quản Lý Sản Phẩm Đồ Án')

@section('content')
<div class="page-header-zone mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="fw-bold mb-1"><i class="fa-solid fa-boxes-packing text-cyan me-2"></i>Quản Lý Sản Phẩm Đồ Án</h1>
        <div class="text-muted small">Quản lý các sản phẩm đồ án, báo cáo cuối kỳ và liên kết mã nguồn của sinh viên</div>
    </div>
</div>

{{-- BỘ LỌC TÌM KIẾM SẢN PHẨM --}}
<div class="card card-modern mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.sanpham.index') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <label class="form-label small fw-bold mb-1">Học kỳ</label>
                <select name="MaHocKy" class="form-select form-select-sm border-soft" onchange="this.form.submit()">
                    <option value="">-- Tất cả Học kỳ --</option>
                    @foreach($hockys as $hk)
                        <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>
                            {{ $hk->TenHocKy }} ({{ $hk->NamHoc }})
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label small fw-bold mb-1">Học phần</label>
                <select name="MaMon" class="form-select form-select-sm border-soft" onchange="this.form.submit()">
                    <option value="">-- Tất cả Môn học --</option>
                    @foreach($monhocs as $mh)
                        <option value="{{ $mh->MaMon }}" {{ request('MaMon') == $mh->MaMon ? 'selected' : '' }}>
                            {{ $mh->TenMon }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-bold mb-1">Lớp hành chính</label>
                <select name="MaLop" class="form-select form-select-sm border-soft" onchange="this.form.submit()">
                    <option value="">-- Tất cả Lớp --</option>
                    @foreach($lops as $l)
                        <option value="{{ $l->MaLop }}" {{ request('MaLop') == $l->MaLop ? 'selected' : '' }}>
                            {{ $l->TenLop }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-bold mb-1">Giảng viên hướng dẫn</label>
                <select name="MaGV" class="form-select form-select-sm border-soft" onchange="this.form.submit()">
                    <option value="">-- Tất cả Giảng viên --</option>
                    @foreach($giangviens as $gv)
                        <option value="{{ $gv->MaGV }}" {{ request('MaGV') == $gv->MaGV ? 'selected' : '' }}>
                            {{ $gv->HoTen }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-1 d-flex gap-1 align-self-end">
                <button type="submit" class="btn btn-cyan btn-sm w-100 fw-bold">
                    <i class="fa-solid fa-filter"></i>
                </button>
                <a href="{{ route('admin.sanpham.index') }}" class="btn btn-light btn-sm border" title="Xóa lọc">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- BẢNG SẢN PHẨM ENTERPRISE SAAS TABLE --}}
<div class="card card-modern shadow-sm">
    <div class="card-modern-header">
        <span><i class="fa-solid fa-list-check me-2"></i>Danh Sách Sản Phẩm Đồ Án ({{ $sanphams->total() }})</span>
        <span class="badge bg-white text-dark px-3 py-1 rounded-pill">Trang {{ $sanphams->currentPage() }}/{{ $sanphams->lastPage() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-saas-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">STT</th>
                        <th width="15%">Nhóm Thực Hiện</th>
                        <th width="25%">Đề Tài Đồ Án</th>
                        <th width="15%">Môn Học</th>
                        <th width="15%">Giảng Viên HD</th>
                        <th width="15%">File / Link Đính Kèm</th>
                        <th width="10%" class="text-center">Ngày Nộp</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sanphams as $index => $sp)
                    @php
                        $nhom = $sp->nhomDoAn;
                        $deTai = $nhom->dangKyDeTai->deTai ?? null;
                        $gvName = $deTai->giangVien->HoTen ?? 'Chưa phân công';
                        $fileUrl = $sp->LinkFile && !filter_var($sp->LinkFile, FILTER_VALIDATE_URL) ? asset(ltrim($sp->LinkFile, '/')) : null;
                        $gitUrl = $sp->LinkSourceCode ?? (filter_var($sp->LinkFile, FILTER_VALIDATE_URL) ? $sp->LinkFile : null);
                    @endphp
                    <tr>
                        <td class="text-center fw-bold text-muted">{{ $sanphams->firstItem() + $index }}</td>
                        <td>
                            <span class="badge-indigo me-1"><i class="fa-solid fa-users me-1"></i>{{ $nhom->TenNhom ?? 'Nhóm' }}</span>
                        </td>
                        <td>
                            <span class="fw-bold text-dark text-truncate d-block" style="max-width: 260px;" title="{{ $deTai->TenDeTai ?? 'Đồ án tự do' }}">
                                {{ $deTai->TenDeTai ?? 'Đồ án tự do' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-cyan"><i class="fa-solid fa-book me-1"></i>{{ $deTai->monHoc->TenMon ?? ($nhom->monHoc->TenMon ?? 'N/A') }}</span>
                        </td>
                        <td>
                            <span class="small text-dark fw-medium"><i class="fa-solid fa-chalkboard-user text-cyan me-1"></i>{{ $gvName }}</span>
                        </td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                @if($fileUrl)
                                    <a href="{{ $fileUrl }}" download class="btn btn-sm btn-outline-primary py-1 px-2 rounded-pill small" title="Tải File Sản Phẩm">
                                        <i class="fa-solid fa-download me-1"></i>File
                                    </a>
                                @endif
                                @if($gitUrl)
                                    <a href="{{ $gitUrl }}" target="_blank" class="btn btn-sm btn-dark py-1 px-2 rounded-pill small" title="Xem GitHub">
                                        <i class="fa-brands fa-github me-1"></i>Repo
                                    </a>
                                @endif
                                @if(!$fileUrl && !$gitUrl)
                                    <span class="text-muted small">—</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-center small text-muted">
                            {{ \Carbon\Carbon::parse($sp->NgayNop)->format('d/m/Y') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-box-open fs-1 d-block mb-2 opacity-50"></i>
                            Không tìm thấy sản phẩm đồ án nào phù hợp.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($sanphams->hasPages())
    <div class="card-footer bg-white border-0 py-3 d-flex justify-content-center">
        {{ $sanphams->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
