@extends('layouts.giangvien')
@section('page_title', 'Duyệt Đăng Ký Đề Tài')
@section('content')

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card card-premium bg-warning bg-opacity-10 border-warning border-opacity-25 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-25 p-3 text-warning">
                    <i class="fa-solid fa-clock-rotate-left fa-2x"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0 small">Đề Xuất Chờ Duyệt</h6>
                    <h4 class="fw-bold mb-0 text-dark">{{ $dangkys->filter(fn($d) => in_array($d->TrangThai, ['Chờ duyệt', 'Chờ Giáo vụ duyệt']))->count() }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-premium bg-success bg-opacity-10 border-success border-opacity-25 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-25 p-3 text-success">
                    <i class="fa-solid fa-circle-check fa-2x"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0 small">Đã Phê Duyệt</h6>
                    <h4 class="fw-bold mb-0 text-dark">{{ $dangkys->filter(fn($d) => $d->TrangThai == 'Đã duyệt')->count() }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-premium bg-danger bg-opacity-10 border-danger border-opacity-25 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-danger bg-opacity-25 p-3 text-danger">
                    <i class="fa-solid fa-circle-xmark fa-2x"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0 small">Từ Chối / Yêu Cầu Sửa</h6>
                    <h4 class="fw-bold mb-0 text-dark">{{ $dangkys->filter(fn($d) => $d->TrangThai == 'Từ chối')->count() }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-premium shadow-sm">
    <div class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
        <span class="fw-bold text-primary fs-6">
            <i class="fa-solid fa-list-check me-2"></i>Danh Sách Đăng Ký &amp; Đề Xuất Đề Tài Dành Cho Giảng Viên
        </span>
        <span class="badge bg-primary-subtle text-primary border rounded-pill px-3">Tổng cộng: {{ $dangkys->total() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4">Thông Tin Nhóm</th>
                        <th width="32%">Đề Tài Đăng Ký &amp; File Đề Cương</th>
                        <th>Lớp Học Phần</th>
                        <th>Ngày Đăng Ký</th>
                        <th>Trạng Thái</th>
                        <th class="text-center">Thao Tác Duyệt</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dangkys as $dk)
                    <tr>
                        <td class="px-4">
                            <strong class="text-dark d-block fs-6">{{ $dk->nhomDoAn->TenNhom ?? 'Chưa đặt tên' }}</strong>
                            <small class="text-muted d-block">Mã nhóm: <code>{{ $dk->nhomDoAn->MaNhom ?? $dk->nhomDoAn->_id }}</code></small>
                            <div class="mt-1">
                                @foreach($dk->thanhVienSVs as $tv)
                                    <span class="badge bg-light text-dark border small me-1 mb-1">
                                        {{ $tv->HoTen ?? $tv->MaSV }}
                                        @if($dk->nhomDoAn && $dk->nhomDoAn->isTruongNhom($tv))
                                            <i class="fa-solid fa-crown text-warning ms-1" title="Trưởng nhóm"></i>
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td>
                            @if($dk->deTai)
                                <div class="fw-bold text-primary mb-1">{{ $dk->deTai->TenDeTai }}</div>
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    @if($dk->deTai->LoaiDeTai === 'Sinh viên đề xuất')
                                        <span class="badge bg-warning text-dark border border-warning"><i class="fa-solid fa-lightbulb me-1"></i>Tự đề xuất</span>
                                    @else
                                        <span class="badge bg-info text-dark"><i class="fa-solid fa-chalkboard-user me-1"></i>GV Khởi tạo</span>
                                    @endif

                                    @if(!empty($dk->deTai->FileTaiLieu))
                                        <a href="{{ asset($dk->deTai->FileTaiLieu) }}" target="_blank" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-1 small text-nowrap">
                                            <i class="fa-solid fa-file-pdf me-1"></i>Xem File Đề Cương
                                        </a>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted italic">Mã ĐT: {{ $dk->getDangKyDeTai()['MaDeTai'] ?? 'N/A' }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $dk->lopHocPhan->TenLopHP ?? 'Chưa phân lớp' }}</span>
                            @if($dk->lopHocPhan && $dk->lopHocPhan->monHoc)
                                <small class="d-block text-muted">Môn: {{ $dk->lopHocPhan->monHoc->TenMon }}</small>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted"><i class="fa-regular fa-calendar me-1"></i>{{ !empty($dk->NgayDangKy) ? date('d/m/Y', strtotime($dk->NgayDangKy)) : '---' }}</small>
                        </td>
                        <td>
                            @if(in_array($dk->TrangThai, ['Chờ duyệt', 'Chờ Giáo vụ duyệt']))
                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="fa-solid fa-clock me-1"></i>Chờ duyệt</span>
                            @elseif($dk->TrangThai == 'Đã duyệt')
                                <span class="badge bg-success px-3 py-2 rounded-pill"><i class="fa-solid fa-check me-1"></i>Đã duyệt</span>
                            @else
                                <span class="badge bg-danger px-3 py-2 rounded-pill"><i class="fa-solid fa-xmark me-1"></i>Từ chối</span>
                                @if(!empty($dk->LyDoTuChoi))
                                    <small class="d-block text-danger italic mt-1" style="max-width: 180px;">"{{ $dk->LyDoTuChoi }}"</small>
                                @endif
                            @endif
                        </td>
                        <td class="text-center">
                            @if(in_array($dk->TrangThai, ['Chờ duyệt', 'Chờ Giáo vụ duyệt']))
                                <div class="d-flex justify-content-center gap-1">
                                    <form action="{{ route('giangvien.duyet.update', $dk->nhomDoAn->_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận ĐỒNG Ý PHÊ DUYỆT đăng ký đề tài cho nhóm này?');">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="TrangThai" value="Đã duyệt">
                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm">
                                            <i class="fa-solid fa-check me-1"></i>Duyệt
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#tuChoiModal{{ $dk->nhomDoAn->_id }}">
                                        <i class="fa-solid fa-xmark me-1"></i>Từ Chối
                                    </button>
                                </div>

                                <!-- MODAL TỪ CHỐI ĐỀ TÀI -->
                                <div class="modal fade" id="tuChoiModal{{ $dk->nhomDoAn->_id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <form action="{{ route('giangvien.duyet.update', $dk->nhomDoAn->_id) }}" method="POST">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="TrangThai" value="Từ chối">
                                            <div class="modal-content text-start">
                                                <div class="modal-header bg-danger text-white">
                                                    <h6 class="modal-title fw-bold"><i class="fa-solid fa-triangle-exclamation me-1"></i>Từ Chối Đề Tài Nhóm {{ $dk->nhomDoAn->TenNhom }}</h6>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <label class="form-label fw-bold text-dark">Lý do từ chối (Gửi tới Sinh viên):</label>
                                                    <textarea name="LyDoTuChoi" class="form-control" rows="3" placeholder="Ví dụ: Đề tài chưa rõ phạm vi, file đề cương cần bổ sung thêm kiến trúc..." required></textarea>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="btn btn-danger rounded-pill px-4">Xác Nhận Từ Chối</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted small">Đã hoàn tất</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="fa-solid fa-inbox fa-3x text-muted mb-3 d-block opacity-50"></i>
                            Hiện tại chưa có nhóm nào đăng ký đề tài hoặc gửi đề xuất mới trong các lớp của bạn.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($dangkys->hasPages())
    <div class="card-footer bg-white p-3">
        {{ $dangkys->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection