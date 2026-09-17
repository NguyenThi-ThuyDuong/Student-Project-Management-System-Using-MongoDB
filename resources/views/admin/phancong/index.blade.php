@extends('layouts.admin')
@section('page_title', 'Phân Công Lớp & GVHD')
@section('content')
<div class="page-header-zone mb-3">
    <div>
        <h1 class="fw-bold"><i class="fa-solid fa-sitemap me-2 text-cyan"></i>Phân Công Lớp &amp; Giảng Viên Hướng Dẫn</h1>
        <div class="text-muted small">Quy trình phân công: <span class="fw-bold text-dark">Học kỳ → Lớp (Hành chính / Học phần) → Giảng viên phụ trách</span> (Tự động là GVHD cho sinh viên thuộc Lớp)</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.import.template', 'phancong_hc') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3" title="File mẫu Lớp Hành Chính">
            <i class="fa-solid fa-file-arrow-down me-1 text-cyan"></i>Mẫu Lớp Hành Chính
        </a>
        <a href="{{ route('admin.import.template', 'phancong_hp') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3" title="File mẫu Lớp Học Phần">
            <i class="fa-solid fa-file-arrow-down me-1 text-cyan"></i>Mẫu Lớp Học Phần
        </a>
        <button class="btn btn-navy btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fa-solid fa-file-excel me-1 text-cyan"></i>Import Excel
        </button>
        <button class="btn btn-cyan btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fa-solid fa-plus me-1"></i>Thêm Phân Công
        </button>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show border-0 mb-4" role="alert" style="background: #ECFDF5; border-left: 4px solid #10b981 !important;">
    <i class="fa-solid fa-circle-check me-2 text-success"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show border-0 mb-4" role="alert" style="background: #FEF2F2; border-left: 4px solid #ef4444 !important;">
    <i class="fa-solid fa-circle-exclamation me-2 text-danger"></i>{{ $errors->first() }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('import_result'))
<div class="alert alert-info alert-dismissible fade show border-0 mb-4" role="alert" style="background: #EFF6FF; border-left: 4px solid var(--v-cyan) !important;">
    <i class="fa-solid fa-circle-info me-2 text-cyan"></i>{!! session('import_result') !!}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="alert alert-info border-0 rounded-4 shadow-sm mb-4 p-3 d-flex gap-3 align-items-center" style="background: var(--v-ice-blue);">
    <i class="fa-solid fa-circle-info fs-3 text-cyan"></i>
    <div style="font-size: 0.88rem; color: var(--navy-deep);">
        <strong>Lưu ý nghiệp vụ quan trọng:</strong> Sau khi Giáo vụ thực hiện phân công Giảng viên phụ trách Lớp (Hành chính hoặc Học phần), Giảng viên đó sẽ <strong>tự động trở thành Giảng viên hướng dẫn (GVHD)</strong> cho các sinh viên thuộc Lớp. Quý thầy/cô có thể thực hiện <strong>Đổi phân công</strong> nhiều lần nếu cần thay đổi Giảng viên phụ trách.
    </div>
</div>

@php
    $activeTab = request('tab') == 'hp' || request()->has('page_hp') || request()->has('q_hp') ? 'hp' : 'hc';
@endphp

<div class="card-modern">
    <div class="card-modern-header">
        <ul class="nav nav-tabs border-0 gap-2 mb-0" id="phanCongTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab == 'hc' ? 'active bg-white text-dark shadow-sm' : 'text-white' }} rounded-pill px-4 fw-bold" id="hc-tab" data-bs-toggle="tab" data-bs-target="#hc-pane" type="button">
                    <i class="fa-solid fa-building-user me-2 text-cyan"></i>Lớp Hành Chính ({{ $phancongs->total() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab == 'hp' ? 'active bg-white text-dark shadow-sm' : 'text-white' }} rounded-pill px-4 fw-bold" id="hp-tab" data-bs-toggle="tab" data-bs-target="#hp-pane" type="button">
                    <i class="fa-solid fa-graduation-cap me-2 text-cyan"></i>Lớp Học Phần / Đồ Án ({{ $lophocphans->total() }})
                </button>
            </li>
        </ul>
    </div>
    <div class="card-modern-body p-0">
        <div class="tab-content" id="phanCongTabContent">
            {{-- TAB 1: PHÂN CÔNG LỚP HÀNH CHÍNH --}}
            <div class="tab-pane fade {{ $activeTab == 'hc' ? 'show active' : '' }}" id="hc-pane" role="tabpanel">
                {{-- BỘ LỌC SEARCH LỚP HÀNH CHÍNH --}}
                <div class="p-3 bg-light border-bottom">
                    <form action="{{ route('phancong.index') }}" method="GET" class="row g-2 align-items-center">
                        <input type="hidden" name="tab" value="hc">
                        <div class="col-md-5">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                                <input type="text" name="q_hc" class="form-control border-start-0 ps-0" placeholder="Tìm theo Mã/Tên Lớp, Tên/Mã Giảng viên..." value="{{ request('q_hc') }}">
                            </div>
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-cyan btn-sm rounded-pill px-3"><i class="fa-solid fa-filter me-1"></i>Lọc Dữ Liệu</button>
                            @if(request()->filled('q_hc'))
                            <a href="{{ route('phancong.index', ['tab' => 'hc']) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3"><i class="fa-solid fa-rotate-left me-1"></i>Đặt lại</a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4">Mã PC</th>
                                <th>Giảng Viên Chủ Nhiệm &amp; GVHD</th>
                                <th>Lớp Hành Chính</th>
                                <th>Học Kỳ Phụ Trách</th>
                                <th>Ngày Phân Công</th>
                                <th class="text-end px-4">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($phancongs as $pc)
                            <tr>
                                <td class="px-4 fw-bold text-muted">#{{ $pc->MaPhanCong ?? $pc->_id }}</td>
                                <td>
                                    <span class="fw-bold text-dark">{{ $pc->giangVien->HoTen ?? 'N/A' }}</span><br>
                                    <small class="text-muted"><i class="fa-solid fa-chalkboard-user me-1 text-cyan"></i>{{ $pc->giangVien->boMon->TenBoMon ?? 'Bộ môn N/A' }}</small>
                                </td>
                                <td>
                                    <span class="badge-cyan px-3 py-2 rounded-pill fw-bold">
                                        <i class="fa-solid fa-users-rectangle me-1"></i>{{ $pc->lop->TenLop ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>{{ $pc->hocKy->TenHocKy ?? 'HK1 2026–2027' }}</td>
                                <td>{{ date('d/m/Y', strtotime($pc->NgayPhanCong ?? now())) }}</td>
                                <td class="text-end px-4">
                                    <div class="d-inline-flex gap-1 align-items-center">
                                        <button type="button" class="btn btn-sm btn-outline-cyan rounded-pill px-3 btn-assign-hc me-1"
                                                data-lop-id="{{ $pc->MaLop }}"
                                                data-hk-id="{{ $pc->MaHocKy }}"
                                                data-gv-id="{{ $pc->MaGV }}">
                                            <i class="fa-solid fa-user-pen me-1"></i>Đổi GV
                                        </button>
                                        <form action="{{ route('phancong.destroy', $pc->_id ?? $pc->MaPhanCong) }}" method="POST" class="d-inline form-delete">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-light text-danger rounded-circle btn-delete" title="Xóa phân công" style="width: 34px; height: 34px;">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-folder-open fa-2x mb-2 d-block opacity-50 text-cyan"></i>
                                    Chưa có dữ liệu phân công Lớp Hành chính nào.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($phancongs->hasPages())
                <div class="p-3 border-top d-flex justify-content-center">
                    {{ $phancongs->appends(request()->except('page_hc'))->links('pagination::bootstrap-5') }}
                </div>
                @endif
            </div>

            {{-- TAB 2: PHÂN CÔNG LỚP HỌC PHẦN --}}
            <div class="tab-pane fade {{ $activeTab == 'hp' ? 'show active' : '' }}" id="hp-pane" role="tabpanel">
                {{-- BỘ LỌC SEARCH LỚP HỌC PHẦN --}}
                <div class="p-3 bg-light border-bottom">
                    <form action="{{ route('phancong.index') }}" method="GET" class="row g-2 align-items-center">
                        <input type="hidden" name="tab" value="hp">
                        <div class="col-md-5">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                                <input type="text" name="q_hp" class="form-control border-start-0 ps-0" placeholder="Tìm theo Mã Lớp HP, Tên Lớp HP, Giảng viên..." value="{{ request('q_hp') }}">
                            </div>
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-cyan btn-sm rounded-pill px-3"><i class="fa-solid fa-filter me-1"></i>Lọc Dữ Liệu</button>
                            @if(request()->filled('q_hp'))
                            <a href="{{ route('phancong.index', ['tab' => 'hp']) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3"><i class="fa-solid fa-rotate-left me-1"></i>Đặt lại</a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4">Mã Lớp HP</th>
                                <th>Tên Lớp Học Phần</th>
                                <th>Học Phần</th>
                                <th>Học Kỳ</th>
                                <th>Giảng Viên Phụ Trách</th>
                                <th class="text-end px-4">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lophocphans as $lhp)
                            <tr>
                                <td class="px-4 fw-bold text-cyan">#{{ $lhp->MaLopHP }}</td>
                                <td class="fw-bold text-dark">{{ $lhp->TenLopHP }}</td>
                                <td>{{ $lhp->monHoc->TenMon ?? 'Đồ án chuyên ngành' }}</td>
                                <td>{{ $lhp->hocKy->TenHocKy ?? 'HK1 2026–2027' }}</td>
                                <td>
                                    @if($lhp->giangVien)
                                        <span class="fw-bold text-dark">{{ $lhp->giangVien->HoTen }}</span><br>
                                        <small class="text-muted"><i class="fa-solid fa-chalkboard-user me-1 text-cyan"></i>{{ $lhp->giangVien->boMon->TenBoMon ?? 'Bộ môn N/A' }}</small>
                                    @else
                                        <span class="badge-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>Chưa phân công</span>
                                    @endif
                                </td>
                                <td class="text-end px-4">
                                    <div class="d-inline-flex gap-1 align-items-center">
                                        <button type="button" class="btn btn-sm btn-cyan rounded-pill px-3 btn-assign-hp" 
                                                data-lhp-id="{{ $lhp->MaLopHP ?? $lhp->_id }}" 
                                                data-lhp-name="{{ $lhp->TenLopHP }}"
                                                data-gv-id="{{ $lhp->MaGV }}">
                                            <i class="fa-solid fa-user-pen me-1"></i>{{ $lhp->MaGV ? 'Đổi GV' : 'Phân công GV' }}
                                        </button>
                                        @if($lhp->MaGV)
                                        <form action="{{ route('admin.phancong.unassign_lhp', $lhp->MaLopHP ?? $lhp->_id) }}" method="POST" class="d-inline form-delete">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-light text-warning rounded-circle btn-delete" title="Hủy phân công" style="width: 34px; height: 34px;">
                                                <i class="fa-solid fa-user-minus"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-folder-open fa-2x mb-2 d-block opacity-50 text-cyan"></i>
                                    Chưa có Lớp Học Phần nào trong hệ thống.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($lophocphans->hasPages())
                <div class="p-3 border-top d-flex justify-content-center">
                    {{ $lophocphans->appends(request()->except('page_hp'))->links('pagination::bootstrap-5') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Thêm Phân Công -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('phancong.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-navy text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-user-plus me-2 text-cyan"></i>Phân Công Lớp &amp; Giảng Viên Phụ Trách</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label-modern fw-bold">Chọn Loại Phân Công <span class="text-danger">*</span></label>
                        <div class="d-flex gap-4 p-3 rounded-3" style="background: var(--v-ice-blue);">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="radio" name="LoaiPhanCong" id="loaiHC" value="lop_hanh_chinh" checked style="accent-color: var(--v-cyan);">
                                <label class="form-check-label fw-bold text-dark cursor-pointer" for="loaiHC">
                                    Lớp Hành Chính (GV Chủ Nhiệm / GVHD)
                                </label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="radio" name="LoaiPhanCong" id="loaiHP" value="lop_hoc_phan" style="accent-color: var(--v-cyan);">
                                <label class="form-check-label fw-bold text-dark cursor-pointer" for="loaiHP">
                                    Lớp Học Phần / Đồ Án
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Nhóm Lớp Hành Chính --}}
                    <div id="groupLopHC">
                        <div class="mb-3">
                            <label class="form-label-modern fw-bold">Chọn Lớp Hành Chính <span class="text-danger">*</span></label>
                            <select name="MaLop" id="selectMaLop" class="form-select-modern">
                                <option value="">-- Chọn lớp hành chính --</option>
                                @foreach($lops as $lop)
                                <option value="{{ $lop->MaLop }}">{{ $lop->TenLop }} ({{ $lop->KhoaHoc }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-modern fw-bold">Chọn Học Kỳ Phụ Trách <span class="text-danger">*</span></label>
                            <select name="MaHocKy" id="selectMaHocKy" class="form-select-modern">
                                <option value="">-- Chọn học kỳ --</option>
                                @foreach($hockys as $hk)
                                <option value="{{ $hk->MaHocKy }}">{{ $hk->TenHocKy }} ({{ $hk->NamHoc }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Nhóm Lớp Học Phần --}}
                    <div id="groupLopHP" class="d-none">
                        <div class="mb-3">
                            <label class="form-label-modern fw-bold">Chọn Lớp Học Phần <span class="text-danger">*</span></label>
                            <select name="MaLopHP" id="selectMaLopHP" class="form-select-modern">
                                <option value="">-- Chọn lớp học phần --</option>
                                @foreach($lophocphans as $lhp)
                                <option value="{{ $lhp->MaLopHP }}">{{ $lhp->TenLopHP }} ({{ $lhp->monHoc->TenMon ?? '' }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-modern fw-bold">Chọn Giảng Viên Phụ Trách &amp; Hướng Dẫn <span class="text-danger">*</span></label>
                        <select name="MaGV" id="selectMaGV" class="form-select-modern" required>
                            <option value="">-- Chọn giảng viên --</option>
                            @foreach($giangviens as $gv)
                            <option value="{{ $gv->MaGV }}">{{ $gv->HoTen }} ({{ $gv->boMon->TenBoMon ?? 'N/A' }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-cyan rounded-pill px-4"><i class="fa-solid fa-floppy-disk me-1"></i>Xác Nhận Phân Công</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('admin.phancong.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-navy text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-file-excel me-2 text-cyan"></i>Import Phân Công Lớp (Excel/CSV)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label-modern fw-bold">Chọn file Excel (.xlsx, .csv)</label>
                        <input type="file" name="file" class="form-control-modern" accept=".xlsx,.csv,.xls" required>
                    </div>
                    <div class="alert alert-info d-flex gap-3 align-items-center rounded-3">
                        <i class="fa-solid fa-circle-info text-cyan fs-3 me-1"></i>
                        <div>
                            <strong>Tải file mẫu Excel phù hợp với loại phân công:</strong>
                            <div class="mt-2 d-flex flex-wrap gap-3">
                                <a href="{{ route('admin.import.template', 'phancong_hc') }}" class="fw-bold text-cyan text-decoration-none">
                                    <i class="fa-solid fa-file-excel me-1"></i>File mẫu Lớp Hành Chính (.xlsx)
                                </a>
                                <a href="{{ route('admin.import.template', 'phancong_hp') }}" class="fw-bold text-cyan text-decoration-none">
                                    <i class="fa-solid fa-file-excel me-1"></i>File mẫu Lớp Học Phần (.xlsx)
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-cyan rounded-pill px-4"><i class="fa-solid fa-upload me-1"></i>Tải Lên &amp; Import</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const loaiHC = document.getElementById('loaiHC');
        const loaiHP = document.getElementById('loaiHP');
        const groupLopHC = document.getElementById('groupLopHC');
        const groupLopHP = document.getElementById('groupLopHP');
        const selectMaLop = document.getElementById('selectMaLop');
        const selectMaHocKy = document.getElementById('selectMaHocKy');
        const selectMaLopHP = document.getElementById('selectMaLopHP');
        const selectMaGV = document.getElementById('selectMaGV');

        function toggleLoai() {
            if (loaiHP.checked) {
                groupLopHC.classList.add('d-none');
                groupLopHP.classList.remove('d-none');
                selectMaLop.removeAttribute('required');
                selectMaHocKy.removeAttribute('required');
                selectMaLopHP.setAttribute('required', 'required');
            } else {
                groupLopHC.classList.remove('d-none');
                groupLopHP.classList.add('d-none');
                selectMaLop.setAttribute('required', 'required');
                selectMaHocKy.setAttribute('required', 'required');
                selectMaLopHP.removeAttribute('required');
            }
        }

        loaiHC.addEventListener('change', toggleLoai);
        loaiHP.addEventListener('change', toggleLoai);
        toggleLoai();

        document.querySelectorAll('.btn-assign-hc').forEach(button => {
            button.addEventListener('click', function() {
                const lopId = this.getAttribute('data-lop-id');
                const hkId = this.getAttribute('data-hk-id');
                const gvId = this.getAttribute('data-gv-id');
                
                loaiHC.checked = true;
                toggleLoai();
                
                if (selectMaLop) selectMaLop.value = lopId || '';
                if (selectMaHocKy) selectMaHocKy.value = hkId || '';
                if (selectMaGV) selectMaGV.value = gvId || '';
                
                const addModal = new bootstrap.Modal(document.getElementById('addModal'));
                addModal.show();
            });
        });

        document.querySelectorAll('.btn-assign-hp').forEach(button => {
            button.addEventListener('click', function() {
                const lhpId = this.getAttribute('data-lhp-id');
                const gvId = this.getAttribute('data-gv-id');
                
                loaiHP.checked = true;
                toggleLoai();
                
                if (selectMaLopHP) selectMaLopHP.value = lhpId || '';
                if (selectMaGV) selectMaGV.value = gvId || '';
                
                const addModal = new bootstrap.Modal(document.getElementById('addModal'));
                addModal.show();
            });
        });

        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const form = this.closest('form');
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Xác nhận xóa?',
                        text: 'Bạn có chắc chắn muốn thực hiện thao tác này?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#0EA5E9',
                        cancelButtonColor: '#6B7280',
                        confirmButtonText: 'Đồng ý',
                        cancelButtonText: 'Hủy'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                } else {
                    if (confirm('Bạn có chắc chắn muốn thực hiện thao tác này?')) {
                        form.submit();
                    }
                }
            });
        });
    });
</script>
@endsection
