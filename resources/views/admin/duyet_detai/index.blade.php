@extends('layouts.admin')
@section('page_title', 'Giáo Vụ Duyệt Đề Tài Đồ Án')

@section('content')
<div class="page-header-zone mb-4">
    <div>
        <h1 class="fw-bold"><i class="fa-solid fa-file-signature me-2 text-cyan"></i>Giáo Vụ Duyệt Đề Tài Đồ Án</h1>
        <div class="text-muted small">Tiếp nhận, kiểm tra nội dung/đề cương và phê duyệt các đề tài do Giảng viên &amp; Sinh viên đề xuất</div>
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

{{-- THỐNG KÊ NHANH --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <span class="text-muted small d-block">Tổng Số Đề Tài</span>
            <strong class="fs-4 text-dark">{{ $detais->total() }}</strong>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-warning">
            <span class="text-muted small d-block">Chờ Duyệt</span>
            <strong class="fs-4 text-warning">{{ \App\Models\DeTai::where('TrangThaiPheDuyet', 'Chờ Giáo vụ duyệt')->count() }}</strong>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success">
            <span class="text-muted small d-block">Đã Phê Duyệt</span>
            <strong class="fs-4 text-success">{{ \App\Models\DeTai::where('TrangThaiPheDuyet', 'Đã duyệt')->count() }}</strong>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-danger">
            <span class="text-muted small d-block">Từ Chối / Yêu Cầu Chỉnh Sửa</span>
            <strong class="fs-4 text-danger">{{ \App\Models\DeTai::whereIn('TrangThaiPheDuyet', ['Từ chối', 'Yêu cầu điều chỉnh'])->count() }}</strong>
        </div>
    </div>
</div>

<div class="card card-modern">
    <div class="card-modern-header">
        <span class="fw-bold"><i class="fa-solid fa-list-check me-2 text-cyan"></i>Danh Sách Đề Tài Tiếp Nhận</span>
    </div>
    <div class="card-modern-body p-0">
        {{-- BỘ LỌC TÌM KIẾM THEO LỚP & HỌC KỲ --}}
        <div class="p-3 bg-light border-bottom">
            <form action="{{ route('admin.duyet_detai.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <select name="MaHocKy" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                        <option value="">-- Tất cả Học Kỳ --</option>
                        @foreach($hocKies as $hk)
                            <option value="{{ $hk->_id }}" {{ (request('MaHocKy') == (string)$hk->_id || request('MaHocKy') == $hk->MaHocKy) ? 'selected' : '' }}>
                                {{ $hk->TenHocKy ?? $hk->TenHK }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="MaLopHP" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                        <option value="">-- Chọn Lớp Học Phần --</option>
                        @foreach($lopHocPhans as $lhp)
                            <option value="{{ $lhp->_id }}" {{ (request('MaLopHP') == (string)$lhp->_id || request('MaLopHP') == $lhp->MaLopHP) ? 'selected' : '' }}>
                                [{{ $lhp->MaLopHP }}] {{ $lhp->TenLopHP }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="TrangThaiPheDuyet" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                        <option value="">-- Trạng Thái Duyệt --</option>
                        <option value="Chờ Giáo vụ duyệt" {{ request('TrangThaiPheDuyet') == 'Chờ Giáo vụ duyệt' ? 'selected' : '' }}>Chờ Giáo vụ duyệt</option>
                        <option value="Đã duyệt" {{ request('TrangThaiPheDuyet') == 'Đã duyệt' ? 'selected' : '' }}>Đã duyệt</option>
                        <option value="Yêu cầu điều chỉnh" {{ request('TrangThaiPheDuyet') == 'Yêu cầu điều chỉnh' ? 'selected' : '' }}>Yêu cầu điều chỉnh</option>
                        <option value="Từ chối" {{ request('TrangThaiPheDuyet') == 'Từ chối' ? 'selected' : '' }}>Từ chối</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" name="search" class="form-control form-control-sm rounded-pill" placeholder="Tìm tên đề tài..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-cyan btn-sm rounded-pill w-100"><i class="fa-solid fa-filter me-1"></i>Lọc</button>
                    @if(request()->anyFilled(['search', 'TrangThaiPheDuyet', 'LoaiDeTai', 'MaLopHP', 'MaHocKy']))
                        <a href="{{ route('admin.duyet_detai.index') }}" class="btn btn-outline-secondary btn-sm rounded-circle" title="Đặt lại"><i class="fa-solid fa-rotate-left"></i></a>
                    @endif
                </div>
            </form>
        </div>

        @if(request('MaLopHP'))
            @php
                $selectedLhp = $lopHocPhans->first(fn($l) => (string)$l->_id === (string)request('MaLopHP') || $l->MaLopHP === request('MaLopHP'));
            @endphp
            @if($selectedLhp)
            <div class="p-3 bg-cyan-subtle border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <i class="fa-solid fa-graduation-cap me-2 text-cyan"></i>Đang xem đề tài thuộc Lớp HP: 
                    <strong class="text-dark">{{ $selectedLhp->TenLopHP }} ({{ $selectedLhp->MaLopHP }})</strong>
                </div>
                <form action="{{ route('admin.duyet_detai.approveAllInClass') }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn PHÊ DUYỆT TẤT CẢ đề tài chưa duyệt thuộc Lớp Học Phần này?')">
                    @csrf
                    <input type="hidden" name="MaLopHP" value="{{ request('MaLopHP') }}">
                    <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm">
                        <i class="fa-solid fa-check-double me-1"></i>Phê Duyệt Tất Cả Đề Tài Lớp Này
                    </button>
                </form>
            </div>
            @endif
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4">Tên Đề Tài</th>
                        <th>Loại Đề Tài</th>
                        <th>Người Đề Xuất</th>
                        <th>Lớp HP / Môn Học</th>
                        <th>Trạng Thái Duyệt</th>
                        <th class="text-end px-4">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detais as $dt)
                    <tr>
                        <td class="px-4">
                            <strong class="text-dark d-block mb-1">{{ $dt->TenDeTai }}</strong>
                            <small class="text-muted d-block text-truncate" style="max-width: 300px;">{{ $dt->MoTa ?? 'Không có mô tả' }}</small>
                            @if($dt->FileTaiLieu)
                                <a href="{{ asset($dt->FileTaiLieu) }}" target="_blank" class="badge bg-light text-primary border mt-1 text-decoration-none">
                                    <i class="fa-solid fa-paperclip me-1"></i>Xem Đề Cương File
                                </a>
                            @endif
                        </td>
                        <td>
                            @if(($dt->LoaiDeTai ?? '') === 'Sinh viên đề xuất')
                                <span class="badge badge-purple px-2 py-1"><i class="fa-solid fa-user-graduate me-1"></i>SV Đề Xuất</span>
                            @else
                                <span class="badge bg-primary-subtle text-primary border px-2 py-1"><i class="fa-solid fa-user-tie me-1"></i>GV Đề Xuất</span>
                            @endif
                        </td>
                        <td>
                            @if(($dt->LoaiDeTai ?? '') === 'Sinh viên đề xuất' && $dt->nhomDeXuat)
                                <span class="fw-bold text-dark">{{ $dt->nhomDeXuat->TenNhom }}</span><br>
                                <small class="text-muted"><i class="fa-solid fa-users me-1 text-purple"></i>Nhóm Sinh Viên Tự Đề Xuất</small>
                            @elseif(($dt->LoaiDeTai ?? '') === 'Sinh viên đề xuất')
                                <span class="fw-bold text-dark">Nhóm Sinh Viên Tự Đề Xuất</span><br>
                                <small class="text-muted"><i class="fa-solid fa-users me-1 text-purple"></i>Nhóm Sinh Viên</small>
                            @elseif($dt->giangVien)
                                <span class="fw-bold text-dark">{{ $dt->giangVien->HoTen }}</span><br>
                                <small class="text-muted"><i class="fa-solid fa-user-tie me-1 text-primary"></i>Giảng Viên Đề Xuất</small>
                            @elseif($dt->nhomDeXuat)
                                <span class="fw-bold text-dark">{{ $dt->nhomDeXuat->TenNhom }}</span><br>
                                <small class="text-muted"><i class="fa-solid fa-users me-1 text-purple"></i>Nhóm Sinh Viên</small>
                            @else
                                <span class="fw-bold text-dark">Giảng Viên Hướng Dẫn</span><br>
                                <small class="text-muted"><i class="fa-solid fa-user-tie me-1 text-primary"></i>Giảng Viên Khoa</small>
                            @endif
                        </td>
                        <td>
                            @if($dt->lopHocPhan)
                                <span class="fw-bold text-cyan">{{ $dt->lopHocPhan->TenLopHP }}</span><br>
                            @elseif($dt->MaLopHP)
                                <span class="fw-bold text-cyan">Lớp Học Phần {{ $dt->MaLopHP }}</span><br>
                            @else
                                <span class="fw-bold text-cyan">12DHTH01 - Đồ án tốt nghiệp (HK241)</span><br>
                            @endif

                            @if($dt->monHoc)
                                <small class="text-muted"><i class="fa-solid fa-book-open me-1"></i>{{ $dt->monHoc->TenMon }}</small>
                            @elseif($dt->lopHocPhan && $dt->lopHocPhan->monHoc)
                                <small class="text-muted"><i class="fa-solid fa-book-open me-1"></i>{{ $dt->lopHocPhan->monHoc->TenMon }}</small>
                            @else
                                <small class="text-muted"><i class="fa-solid fa-book-open me-1"></i>Đồ án môn học / tốt nghiệp</small>
                            @endif
                        </td>
                        <td>
                            @if($dt->TrangThaiPheDuyet === 'Đã duyệt')
                                <span class="badge bg-success-subtle text-success border px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i>Đã Duyệt</span>
                            @elseif($dt->TrangThaiPheDuyet === 'Từ chối')
                                <span class="badge bg-danger-subtle text-danger border px-2 py-1" title="{{ $dt->LyDoPheDuyet }}"><i class="fa-solid fa-circle-xmark me-1"></i>Từ Chối</span>
                            @elseif($dt->TrangThaiPheDuyet === 'Yêu cầu điều chỉnh')
                                <span class="badge bg-warning-subtle text-warning border px-2 py-1" title="{{ $dt->LyDoPheDuyet }}"><i class="fa-solid fa-triangle-exclamation me-1"></i>Cần Điều Chỉnh</span>
                            @else
                                <span class="badge bg-info-subtle text-info border px-2 py-1"><i class="fa-solid fa-clock me-1"></i>Chờ Phê Duyệt</span>
                            @endif
                        </td>
                        <td class="text-end px-4">
                            <div class="d-inline-flex gap-1">
                                @if($dt->TrangThaiPheDuyet !== 'Đã duyệt')
                                <form action="{{ route('admin.duyet_detai.approve', $dt->_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Duyệt đề tài này và mở đăng ký cho sinh viên?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success rounded-pill px-3" title="Phê duyệt đề tài">
                                        <i class="fa-solid fa-check me-1"></i>Duyệt
                                    </button>
                                </form>
                                @endif

                                <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-2" data-bs-toggle="modal" data-bs-target="#adjustModal{{ $dt->_id }}" title="Yêu cầu điều chỉnh">
                                    <i class="fa-solid fa-pen-ruler"></i>
                                </button>

                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $dt->_id }}" title="Từ chối đề tài">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                            <!-- MODAL YÊU CẦU ĐIỀU CHỈNH -->
                            <div class="modal fade text-start" id="adjustModal{{ $dt->_id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <form action="{{ route('admin.duyet_detai.requestAdjustment', $dt->_id) }}" method="POST">
                                        @csrf
                                        <div class="modal-content">
                                            <div class="modal-header bg-warning text-dark">
                                                <h5 class="modal-title"><i class="fa-solid fa-triangle-exclamation me-2"></i>Yêu Cầu Điều Chỉnh Đề Tài</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <p class="small text-muted mb-2">Đề tài: <strong>{{ $dt->TenDeTai }}</strong></p>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Nội dung yêu cầu điều chỉnh / bổ sung:</label>
                                                    <textarea name="LyDoPheDuyet" class="form-control" rows="4" placeholder="Nhập rõ chi tiết yêu cầu giảng viên / sinh viên chỉnh sửa..." required></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                                                <button type="submit" class="btn btn-warning rounded-pill px-4"><i class="fa-solid fa-paper-plane me-1"></i>Gửi Yêu Cầu</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- MODAL TỪ CHỐI -->
                            <div class="modal fade text-start" id="rejectModal{{ $dt->_id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <form action="{{ route('admin.duyet_detai.reject', $dt->_id) }}" method="POST">
                                        @csrf
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title"><i class="fa-solid fa-circle-xmark me-2"></i>Từ Chối Đề Tài</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <p class="small text-muted mb-2">Đề tài: <strong>{{ $dt->TenDeTai }}</strong></p>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Lý do từ chối đề tài:</label>
                                                    <textarea name="LyDoPheDuyet" class="form-control" rows="4" placeholder="Nhập lý do từ chối không phê duyệt đề tài này..." required></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                                                <button type="submit" class="btn btn-danger rounded-pill px-4"><i class="fa-solid fa-ban me-1"></i>Từ Chối Đề Tài</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-folder-open fa-2x mb-2 d-block opacity-50 text-cyan"></i>
                            Không có đề tài nào phù hợp với bộ lọc.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($detais->hasPages())
        <div class="p-3 border-top d-flex justify-content-center">
            {{ $detais->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>
@endsection
