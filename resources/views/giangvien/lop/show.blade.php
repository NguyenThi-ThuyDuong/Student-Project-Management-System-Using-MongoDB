@extends('layouts.giangvien')
@section('page_title', 'Chi Tiết Lớp Học Phần: ' . ($lopHP->TenLopHP ?? ''))
@section('content')

<div class="mb-3">
    <a href="{{ route('giangvien.lop.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="fa-solid fa-arrow-left me-1"></i>Quay lại danh sách lớp
    </a>
</div>

<div class="card card-premium mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <span class="badge bg-primary px-3 py-2 rounded-pill mb-2"><i class="fa-solid fa-graduation-cap me-1"></i>Lớp Học Phần Phụ Trách</span>
                <h3 class="fw-bold mb-1 text-primary-custom">{{ $lopHP->TenLopHP }}</h3>
                <p class="text-muted mb-0">
                    <i class="fa-solid fa-book me-1"></i>Môn Học: <strong>{{ $lopHP->monHoc->TenMon ?? 'N/A' }}</strong> ({{ $lopHP->monHoc->SoTinChi ?? 0 }} tín chỉ)
                    &nbsp;|&nbsp;
                    <i class="fa-solid fa-calendar me-1"></i>Học Kỳ: <strong>{{ $lopHP->hocKy->TenHocKy ?? 'N/A' }} ({{ $lopHP->hocKy->NamHoc ?? '' }})</strong>
                </p>
            </div>
            <div class="d-flex gap-2">
                <div class="bg-light p-3 rounded-3 text-center border">
                    <div class="small text-muted">Sĩ Số / Tối Đa</div>
                    <div class="fs-4 fw-bold text-dark">{{ $sinhVienList->count() }} / {{ $lopHP->SiSoToiDa ?? 40 }}</div>
                </div>
                <div class="bg-light p-3 rounded-3 text-center border">
                    <div class="small text-muted">Tổng số nhóm</div>
                    <div class="fs-4 fw-bold text-primary">{{ $nhoms->count() }}</div>
                </div>
                <div class="bg-light p-3 rounded-3 text-center border">
                    <div class="small text-muted">Giới hạn TV/Nhóm</div>
                    <div class="fs-4 fw-bold text-success">{{ $lopHP->SoThanhVienNhomToiDa ?? 5 }} SV</div>
                    <button class="btn btn-link p-0 text-decoration-none text-success small" data-bs-toggle="modal" data-bs-target="#editGroupLimitModal">
                        <i class="fa-solid fa-pen me-1"></i>Thay đổi
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-premium">
    <div class="card-header-premium bg-white border-bottom p-3">
        <ul class="nav nav-pills card-header-pills" id="lopTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill me-2" id="nhom-tab" data-bs-toggle="tab" data-bs-target="#nhom-pane" type="button">
                    <i class="fa-solid fa-users me-1"></i>Danh Sách Nhóm Đồ Án ({{ $nhoms->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill" id="sv-tab" data-bs-toggle="tab" data-bs-target="#sv-pane" type="button">
                    <i class="fa-solid fa-id-card me-1"></i>Danh Sách Sinh Viên Lớp HP ({{ $sinhVienList->count() }})
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
        <div class="tab-content" id="lopTabContent">
            {{-- TAB 1: DANH SÁCH NHÓM ĐỒ ÁN --}}
            <div class="tab-pane fade show active" id="nhom-pane" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4">Tên Nhóm</th>
                                <th>Trưởng Nhóm</th>
                                <th>Môn Học</th>
                                <th>Đề Tài Đăng Ký</th>
                                <th>Thành Viên</th>
                                <th>Trạng Thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($nhoms as $n)
                                <tr>
                                    <td class="px-4 fw-bold text-primary">{{ $n->TenNhom }}</td>
                                    <td>
                                        @php
                                            $leader = $n->thanhVienSVs->firstWhere('VaiTro', 'Trưởng nhóm') 
                                                ?? $n->thanhVienSVs->first() 
                                                ?? null;
                                        @endphp
                                        <div><strong class="text-dark">{{ $leader->HoTen ?? 'Chưa xác định' }}</strong></div>
                                        <small class="text-muted">MSSV: {{ $leader->MaSV ?? $n->TruongNhom ?? 'N/A' }}</small>
                                    </td>
                                    <td>{{ $n->monHoc->TenMon ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $dtTen = $n->getTenDeTaiDangKy();
                                            $dtTrangThai = $n->getTrangThaiDangKy();
                                        @endphp
                                        @if($dtTen !== 'Chưa đăng ký')
                                            <div class="fw-semibold text-dark">{{ $dtTen }}</div>
                                            <span class="badge bg-{{ $dtTrangThai == 'Đã duyệt' ? 'success' : ($dtTrangThai == 'Từ chối' ? 'danger' : 'warning text-dark') }}">
                                                {{ $dtTrangThai }}
                                            </span>
                                        @else
                                            <span class="text-muted italic">Chưa đăng ký đề tài</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($n->ThanhVien ?? [] as $tv)
                                                <span class="badge bg-light text-dark border small">
                                                    {{ $tv['HoTen'] ?? $tv['MaSV'] }}
                                                    @if(($tv['VaiTro'] ?? '') == 'Trưởng nhóm')
                                                        <i class="fa-solid fa-crown text-warning ms-1"></i>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $n->TrangThai == 'Đã hoàn thành' ? 'success' : ($n->TrangThai == 'Đang hoạt động' ? 'info' : 'secondary') }}">
                                            {{ $n->TrangThai }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-users-slash fa-2x mb-2 d-block opacity-50"></i>
                                        Chưa có nhóm đồ án nào được thành lập trong lớp học phần này.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- TAB 2: DANH SÁCH SINH VIÊN LỚP HỌC PHẦN --}}
            <div class="tab-pane fade" id="sv-pane" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4">#</th>
                                <th>MSSV</th>
                                <th>Họ Và Tên</th>
                                <th>Lớp Hành Chính</th>
                                <th>Email</th>
                                <th>Số Điện Thoại</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sinhVienList as $idx => $sv)
                                <tr>
                                    <td class="px-4 text-muted">{{ $idx + 1 }}</td>
                                    <td class="fw-bold text-primary">{{ $sv->MaSV ?? 'N/A' }}</td>
                                    <td class="fw-bold text-dark">{{ $sv->HoTen ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-dark border">
                                            {{ $sv->lop->TenLop ?? $sv->MaLop ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>{{ $sv->Email ?? 'N/A' }}</td>
                                    <td>{{ $sv->SoDienThoai ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-user-slash fa-2x mb-2 d-block opacity-50"></i>
                                        Lớp học phần này chưa có sinh viên nào đăng ký.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL THAY ĐỔI GIỚI HẠN THÀNH VIÊN NHÓM -->
<div class="modal fade" id="editGroupLimitModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form action="{{ route('giangvien.lop.updateLimit', $lopHP->_id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white py-2">
                    <h6 class="modal-title mb-0"><i class="fa-solid fa-users-gear me-2"></i>Quy Định Nhóm</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Số thành viên tối đa / 1 nhóm:</label>
                        <input type="number" name="SoThanhVienNhomToiDa" class="form-control" value="{{ $lopHP->SoThanhVienNhomToiDa ?? 5 }}" min="1" max="20" required>
                    </div>
                    <p class="text-muted small mb-0">
                        <i class="fa-solid fa-circle-info text-info me-1"></i>Sinh viên khi tạo nhóm trong Lớp HP này sẽ bị giới hạn không được vượt quá số lượng trên.
                    </p>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success btn-sm px-3">Lưu thay đổi</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
