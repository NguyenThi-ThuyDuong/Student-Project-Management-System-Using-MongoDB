@extends('layouts.sinhvien')
@section('title', 'Nộp & Theo Dõi Sản Phẩm Đồ Án')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 text-primary-custom"><i class="fa-solid fa-box-open me-2"></i>Quản Lý Nộp &amp; Chấm Đồ Án</h4>
        <div class="text-muted small">
            <i class="fa-solid fa-users me-1"></i>Nhóm: <strong>{{ $nhom->TenNhom ?? '' }}</strong>
            &nbsp;|&nbsp;
            <i class="fa-solid fa-book me-1"></i>Môn: <strong>{{ $nhom->monHoc->TenMon ?? 'N/A' }}</strong>
        </div>
    </div>

    <div class="d-flex gap-2 align-items-center">
        @if(isset($allNhoms) && $allNhoms->count() > 1)
        <form method="GET" action="{{ route('sinhvien.sanpham.index') }}" class="d-flex gap-2 align-items-center me-2">
            <select name="maNhom" class="form-select form-select-sm shadow-sm" style="border-radius: 20px;" onchange="this.form.submit()">
                @foreach($allNhoms as $n)
                    <option value="{{ (string) $n->_id }}" {{ (string) $nhom->_id === (string) $n->_id ? 'selected' : '' }}>
                        {{ $n->TenNhom }} ({{ $n->monHoc->TenMon ?? '' }})
                    </option>
                @endforeach
            </select>
        </form>
        @endif

        @php
            $isLeader = $nhom->isTruongNhom($sv);
        @endphp

        <button class="btn btn-primary-custom rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fa-solid fa-upload me-2"></i>{{ $sanphams->isNotEmpty() ? 'Cập Nhật / Nộp Thêm' : 'Nộp Sản Phẩm' }}
        </button>
    </div>
</div>

{{-- 1. BẢNG KẾT QUẢ CHẤM ĐIỂM & ĐÁNH GIÁ TỪ GIẢNG VIÊN --}}
@php
    $chamDiem = $nhom->chamDiem;
    $dangKy = $nhom->getDangKyDeTai();
    $deTai = $nhom->getDeTaiDangKy();
    $gvHD = $nhom->getGiangVienHuongDan();
@endphp

@if($chamDiem)
<div class="card card-premium mb-4 border-0 shadow-sm overflow-hidden" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-7 border-end border-white-50">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-white text-success fw-bold rounded-pill px-3 py-2"><i class="fa-solid fa-award me-1"></i>ĐÃ CHẤM ĐIỂM HOÀN TẤT</span>
                    <small class="text-white-50">Chấm ngày: {{ !empty($chamDiem->NgayCham) ? date('d/m/Y', strtotime($chamDiem->NgayCham)) : date('d/m/Y') }}</small>
                </div>
                <h5 class="fw-bold text-white mb-2"><i class="fa-solid fa-graduation-cap me-2"></i>{{ $nhom->getTenDeTaiDangKy() }}</h5>
                <p class="mb-0 text-white-50 small">
                    <i class="fa-solid fa-chalkboard-user me-1"></i>GVHD: <strong>{{ $gvHD->HoTen ?? 'Giảng viên hướng dẫn' }}</strong>
                </p>
                @if(!empty($chamDiem->NhanXet))
                <div class="mt-3 p-3 bg-white bg-opacity-10 rounded-3 border border-white-25">
                    <small class="fw-bold d-block text-white text-uppercase" style="letter-spacing: 0.5px; font-size: 0.75rem;"><i class="fa-solid fa-comment-dots me-1"></i>Nhận xét từ Giảng viên:</small>
                    <div class="fst-italic small mt-1 text-white" style="line-height: 1.5;">"{{ $chamDiem->NhanXet }}"</div>
                </div>
                @endif
            </div>
            <div class="col-md-5 text-center mt-3 mt-md-0">
                <div class="text-white-50 text-uppercase fw-semibold mb-1" style="font-size: 0.8rem;">Điểm Tổng Đồ Án</div>
                <div class="display-3 fw-extrabold text-white mb-2" style="font-weight: 800; letter-spacing: -1px;">
                    {{ number_format((float)($chamDiem->DiemTong ?? 0), 1) }}
                    <span style="font-size: 1.2rem;" class="fw-normal">/10</span>
                </div>
                <div class="d-flex justify-content-center gap-3">
                    <div class="bg-white bg-opacity-20 px-3 py-1 rounded-pill small text-white">
                        Báo cáo: <strong>{{ number_format((float)($chamDiem->DiemBaoCao ?? 0), 1) }}</strong> (50%)
                    </div>
                    <div class="bg-white bg-opacity-20 px-3 py-1 rounded-pill small text-white">
                        Bảo vệ: <strong>{{ number_format((float)($chamDiem->DiemBaoVe ?? 0), 1) }}</strong> (50%)
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@else
<div class="alert alert-light border shadow-sm rounded-4 p-3 mb-4 d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="avatar-lg bg-primary-subtle text-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
            <i class="fa-solid fa-clock-rotate-left fa-lg"></i>
        </div>
        <div>
            <div class="fw-bold text-dark">Trạng thái nộp đồ án: 
                <span class="badge bg-{{ $sanphams->isNotEmpty() ? 'info' : 'warning' }} text-dark px-2 py-1 ms-1">
                    {{ $sanphams->isNotEmpty() ? 'Đã nộp - Đang chờ chấm điểm' : 'Chưa nộp sản phẩm' }}
                </span>
            </div>
            <small class="text-muted">
                @if($deTai && $deTai->HanNopSanPham)
                    <i class="fa-regular fa-calendar-xmark me-1 text-danger"></i>Hạn chót nộp sản phẩm: <strong class="text-danger">{{ date('d/m/Y', strtotime($deTai->HanNopSanPham)) }}</strong>
                @else
                    Hạn chót: Theo lịch thông báo của Khoa/Bộ môn.
                @endif
            </small>
        </div>
    </div>
    @if(!$isLeader)
    <span class="badge bg-light text-muted border px-3 py-2"><i class="fa-solid fa-user-check me-1"></i>Thành viên nhóm</span>
    @endif
</div>
@endif

{{-- 2. DANH SÁCH SẢN PHẨM & TÀI LIỆU ĐÃ NỘP --}}
<div class="card card-premium shadow-sm border-0 rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-folder-open me-2 text-primary"></i>Danh Sách Sản Phẩm &amp; Tài Liệu Đã Nộp</h6>
        <span class="badge bg-secondary rounded-pill">{{ $sanphams->count() }} tệp/liên kết</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4 py-3">Tên Sản Phẩm / Mã Nguồn</th>
                        <th class="py-3">File Nén / Báo Cáo</th>
                        <th class="py-3">Link GitHub / Drive</th>
                        <th class="py-3">Ngày Nộp</th>
                        <th class="py-3 text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sanphams as $sp)
                    <tr>
                        <td class="px-4 fw-bold text-dark">
                            <i class="fa-solid fa-file-code me-2 text-primary"></i>{{ $sp->TenSanPham }}
                        </td>
                        <td>
                            @php
                                $fileUrl = $sp->LinkFile && !filter_var($sp->LinkFile, FILTER_VALIDATE_URL) ? asset(ltrim($sp->LinkFile, '/')) : null;
                                $gitUrl = $sp->LinkSourceCode ?? (filter_var($sp->LinkFile, FILTER_VALIDATE_URL) ? $sp->LinkFile : null);
                            @endphp
                            @if($fileUrl)
                                <a href="{{ $fileUrl }}" download class="btn btn-sm btn-outline-success rounded-pill px-3">
                                    <i class="fa-solid fa-download me-1"></i>Tải File Đính Kèm
                                </a>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            @if($gitUrl)
                                <a href="{{ $gitUrl }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                                    <i class="fa-brands fa-github me-1"></i>GitHub / Repository
                                </a>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">
                                <i class="fa-regular fa-clock me-1"></i>{{ !empty($sp->NgayNop) ? date('d/m/Y', strtotime($sp->NgayNop)) : date('d/m/Y') }}
                            </small>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-light border rounded-circle text-primary" data-bs-toggle="modal" data-bs-target="#editModal-{{ $sp->_id ?? 'default' }}" title="Cập nhật">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                        </td>
                    </tr>

                    <!-- Modal Sửa sản phẩm -->
                    <div class="modal fade" id="editModal-{{ $sp->_id ?? 'default' }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form action="{{ route('sinhvien.sanpham.update', $sp->_id ?? 'default') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="modal-content rounded-4 border-0">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title fs-6 fw-bold"><i class="fa-solid fa-pen-to-square me-2"></i>Cập Nhật Sản Phẩm Đồ Án</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label text-muted small fw-bold">Tên Dự Án/Sản Phẩm <span class="text-danger">*</span></label>
                                            <input type="text" name="TenSanPham" class="form-control" value="{{ $sp->TenSanPham }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-muted small fw-bold">Tải File Đội Thay Thế (.ZIP, .RAR, .PDF)</label>
                                            <input type="file" name="FileUpLoad" class="form-control" accept=".zip,.rar,.pdf">
                                            <div class="form-text text-muted">Bỏ trống nếu giữ nguyên file cũ.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-muted small fw-bold">Link GitHub Repository / Google Drive</label>
                                            <input type="url" name="LinkFile" class="form-control" value="{{ $gitUrl }}">
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0">
                                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                                        <button type="submit" class="btn btn-primary-custom rounded-pill px-4">Cập Nhật</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <i class="fa-solid fa-box-open fa-3x mb-3 text-black-50 opacity-25"></i>
                            <p class="mb-1 fw-bold">Chưa nộp sản phẩm đồ án nào.</p>
                            <small>Trưởng nhóm hoặc thành viên nhấn nút <strong>"Nộp Sản Phẩm"</strong> ở trên để nộp báo cáo và mã nguồn.</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nộp Mới -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('sinhvien.sanpham.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="MaNhom" value="{{ (string)$nhom->_id }}">
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fs-6 fw-bold"><i class="fa-solid fa-box-open me-2"></i>Nộp Sản Phẩm / Source Code Đồ Án</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Tên Dự Án/Sản Phẩm <span class="text-danger">*</span></label>
                        <input type="text" name="TenSanPham" class="form-control" required placeholder="Ví dụ: Báo cáo cuối kỳ & Source Code Hệ Thống QLĐA">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">1. File Đính Kèm (.ZIP, .RAR, .PDF)</label>
                        <input type="file" name="FileUpLoad" class="form-control" accept=".zip,.rar,.pdf">
                        <div class="form-text text-muted">Dung lượng tối đa 20MB.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">2. Link GitHub Repository HOẶC Google Drive</label>
                        <input type="url" name="LinkFile" class="form-control" placeholder="https://github.com/... hoặc https://drive.google.com/...">
                        <div class="form-text text-muted">Vui lòng chọn ít nhất 1 trong 2 hình thức (tải file hoặc dán link).</div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary-custom rounded-pill px-4">Xác Nhận Nộp</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
