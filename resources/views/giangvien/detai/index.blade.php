@extends('layouts.giangvien')
@section('page_title', 'Quản Lý Đề Tài')
@section('content')

<div class="card card-premium shadow-sm mb-4">
    <div class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-bold fs-5 text-primary">
            <i class="fa-solid fa-book-bookmark me-2"></i>Danh Sách Đề Tài Phụ Trách ({{ $detais->total() }})
        </span>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.import.template', 'detais') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-file-arrow-down me-1"></i>File mẫu .xlsx
            </a>
            <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fa-solid fa-file-excel me-1"></i>Import Excel
            </button>
            <a href="{{ route('giangvien.detai.create') }}" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-plus me-1"></i>Thêm đề tài
            </a>
        </div>
    </div>

    {{-- Filter bar --}}
    <div class="card-body bg-light border-bottom p-3">
        <form method="GET" action="{{ route('giangvien.detai.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Học kỳ</label>
                <select name="MaHocKy" class="form-select form-select-sm">
                    <option value="">-- Tất cả học kỳ --</option>
                    @foreach($hockys as $hk)
                        <option value="{{ $hk->MaHocKy }}" {{ (request('MaHocKy') == $hk->MaHocKy || request('MaHocKy') == $hk->_id) ? 'selected' : '' }}>
                            {{ $hk->TenHocKy }} ({{ $hk->NamHoc }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Lớp Học Phần</label>
                <select name="MaLopHP" class="form-select form-select-sm">
                    <option value="">-- Tất cả Lớp HP --</option>
                    @foreach($lopHocPhans as $lhp)
                        <option value="{{ $lhp->_id }}" {{ (request('MaLopHP') == $lhp->_id || request('MaLopHP') == $lhp->MaLopHP) ? 'selected' : '' }}>
                            {{ $lhp->TenLopHP }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Môn học</label>
                <select name="MaMon" class="form-select form-select-sm">
                    <option value="">-- Tất cả môn --</option>
                    @foreach($monhocs as $mh)
                        <option value="{{ $mh->MaMon }}" {{ (request('MaMon') == $mh->MaMon || request('MaMon') == $mh->_id) ? 'selected' : '' }}>
                            {{ $mh->TenMon }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 w-100">
                    <i class="fa-solid fa-filter me-1"></i>Lọc
                </button>
                <a href="{{ route('giangvien.detai.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3" title="Xóa lọc">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="card-body p-4 bg-light-subtle">
        @if(session('import_result'))
        <div class="alert alert-info alert-dismissible fade show mb-4">
            <i class="fa-solid fa-circle-info me-2"></i>
            {!! session('import_result') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        {{-- CARD GRID LAYOUT --}}
        <div class="row row-cols-1 row-cols-md-2 g-4">
            @forelse($detais as $dt)
            <div class="col">
                <div class="card h-100 border-0 shadow-sm rounded-3 hover-shadow transition-all position-relative" style="border-left: 4px solid #2563eb !important;">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            {{-- Header badge & Code --}}
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-{{ $dt->TrangThaiPheDuyet == 'Đã duyệt' ? 'success' : ($dt->TrangThaiPheDuyet == 'Chờ Giáo vụ duyệt' ? 'warning text-dark' : 'danger') }} rounded-pill px-3 py-1">
                                    <i class="fa-solid {{ $dt->TrangThaiPheDuyet == 'Đã duyệt' ? 'fa-circle-check' : ($dt->TrangThaiPheDuyet == 'Chờ Giáo vụ duyệt' ? 'fa-clock' : 'fa-circle-exclamation') }} me-1"></i>
                                    {{ $dt->TrangThaiPheDuyet }}
                                </span>
                                <span class="text-muted small font-monospace">#{{ $dt->MaDeTai ?? substr((string)$dt->_id, -6) }}</span>
                            </div>

                            {{-- Topic Title --}}
                            <h5 class="fw-bold text-dark mb-3" style="line-height: 1.4;">
                                {{ $dt->TenDeTai }}
                            </h5>

                            {{-- Info Badges --}}
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-2 px-2 py-1 small">
                                    <i class="fa-solid fa-graduation-cap me-1"></i>{{ $dt->lopHocPhan->TenLopHP ?? $dt->lop->TenLop ?? 'Chưa gán Lớp HP' }}
                                </span>
                                <span class="badge bg-light text-dark border rounded-2 px-2 py-1 small">
                                    <i class="fa-solid fa-book me-1 text-muted"></i>{{ $dt->monHoc->TenMon ?? $dt->lopHocPhan->monHoc->TenMon ?? '—' }}
                                </span>
                                <span class="badge bg-light text-muted border rounded-2 px-2 py-1 small">
                                    <i class="fa-solid fa-calendar-alt me-1"></i>{{ $dt->hocKy->TenHocKy ?? $dt->lopHocPhan->hocKy->TenHocKy ?? '—' }}
                                </span>
                            </div>

                            {{-- Short Deadlines summary --}}
                            <div class="p-2 bg-light rounded border small d-flex justify-content-between text-muted mb-3">
                                <span><i class="fa-solid fa-user-plus text-success me-1"></i>ĐK: <strong>{{ $dt->HanDangKy ? \Carbon\Carbon::parse($dt->HanDangKy)->format('d/m/Y') : '31/12/2026' }}</strong></span>
                                <span><i class="fa-solid fa-clock text-warning me-1"></i>BC: <strong>{{ $dt->HanBaoCao ? \Carbon\Carbon::parse($dt->HanBaoCao)->format('d/m/Y') : '30/12/2026' }}</strong></span>
                                <span><i class="fa-solid fa-box-archive text-info me-1"></i>SP: <strong>{{ $dt->HanNopSanPham ? \Carbon\Carbon::parse($dt->HanNopSanPham)->format('d/m/Y') : '31/12/2026' }}</strong></span>
                            </div>
                        </div>

                        {{-- Card Footer Action Buttons --}}
                        <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#detailModal{{ $dt->_id }}">
                                <i class="fa-solid fa-eye me-1"></i>Xem chi tiết
                            </button>

                            <div class="d-flex gap-1 align-items-center">
                                @if(auth()->check() && auth()->user()->VaiTro === 'Admin' && $dt->TrangThaiPheDuyet !== 'Đã duyệt')
                                    <form action="{{ route('admin.duyet_detai.approve', $dt->_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Duyệt đề tài này và mở đăng ký cho sinh viên?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-2" title="Phê duyệt đề tài ngay">
                                            <i class="fa-solid fa-check me-1"></i>Duyệt
                                        </button>
                                    </form>
                                @endif
                                <button type="button" class="btn btn-sm btn-light text-secondary rounded-circle" data-bs-toggle="modal" data-bs-target="#uploadDocModal{{ $dt->_id }}" title="Tải lên/đính kèm đề cương">
                                    <i class="fa-solid fa-paperclip"></i>
                                </button>
                                <a href="{{ route('giangvien.detai.edit', $dt->_id) }}" class="btn btn-sm btn-light text-warning rounded-circle" title="Sửa đề tài">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form action="{{ route('giangvien.detai.destroy', $dt->_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xoá đề tài này?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light text-danger rounded-circle" title="Xoá đề tài">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MODAL DETAIL ĐỀ TÀI -->
            <div class="modal fade" id="detailModal{{ $dt->_id }}" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title"><i class="fa-solid fa-circle-info me-2"></i>Chi Tiết Đề Tài</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <h4 class="fw-bold text-primary mb-3">{{ $dt->TenDeTai }}</h4>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <p class="mb-1 text-muted small fw-semibold">LỚP HỌC PHẦN</p>
                                    <p class="fw-semibold">{{ $dt->lopHocPhan->TenLopHP ?? $dt->lop->TenLop ?? '—' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1 text-muted small fw-semibold">MÔN HỌC & HỌC KỲ</p>
                                    <p class="fw-semibold">{{ $dt->monHoc->TenMon ?? $dt->lopHocPhan->monHoc->TenMon ?? '—' }} ({{ $dt->hocKy->TenHocKy ?? $dt->lopHocPhan->hocKy->TenHocKy ?? '—' }})</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1 text-muted small fw-semibold">TRẠNG THÁI PHÊ DUYỆT</p>
                                    <span class="badge bg-{{ $dt->TrangThaiPheDuyet == 'Đã duyệt' ? 'success' : ($dt->TrangThaiPheDuyet == 'Chờ Giáo vụ duyệt' ? 'warning text-dark' : 'danger') }} rounded-pill px-3 py-1">
                                        {{ $dt->TrangThaiPheDuyet }}
                                    </span>
                                    @if(auth()->check() && auth()->user()->VaiTro === 'Admin' && $dt->TrangThaiPheDuyet !== 'Đã duyệt')
                                        <form action="{{ route('admin.duyet_detai.approve', $dt->_id) }}" method="POST" class="d-inline ms-2" onsubmit="return confirm('Duyệt đề tài này?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 py-1">
                                                <i class="fa-solid fa-check me-1"></i>Duyệt ngay
                                            </button>
                                        </form>
                                    @endif
                                    @if($dt->TrangThaiPheDuyet === 'Yêu cầu điều chỉnh' && $dt->LyDoPheDuyet)
                                        <p class="text-danger small mt-1"><i class="fa-solid fa-exclamation-triangle me-1"></i>Lý do: {{ $dt->LyDoPheDuyet }}</p>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1 text-muted small fw-semibold">TRẠNG THÁI ĐĂNG KÝ</p>
                                    <span class="badge bg-info-subtle text-info border rounded-pill px-3 py-1">{{ $dt->TrangThai }}</span>
                                </div>
                            </div>

                            <div class="mb-3 p-3 bg-light rounded border">
                                <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-align-left me-2 text-primary"></i>Mô Tả Chi Tiết:</h6>
                                <p class="mb-0 text-secondary" style="white-space: pre-line;">{{ $dt->MoTa ?: 'Chưa có thông tin mô tả cho đề tài này.' }}</p>
                            </div>

                            <div class="mb-3 p-3 bg-light rounded border">
                                <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-list-check me-2 text-success"></i>Yêu Cầu Cụ Thể:</h6>
                                <p class="mb-0 text-secondary" style="white-space: pre-line;">{{ $dt->YeuCau ?: 'Chưa có yêu cầu cụ thể.' }}</p>
                            </div>

                            <div class="p-3 bg-light rounded border mb-3">
                                <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-calendar-days me-2 text-warning"></i>Thời Hạn Thực Hiện:</h6>
                                <div class="row text-muted small">
                                    <div class="col-md-4"><strong>Hạn Đăng Ký:</strong> {{ $dt->HanDangKy ? \Carbon\Carbon::parse($dt->HanDangKy)->format('d/m/Y') : 'Chưa thiết lập' }}</div>
                                    <div class="col-md-4"><strong>Hạn Báo Cáo:</strong> {{ $dt->HanBaoCao ? \Carbon\Carbon::parse($dt->HanBaoCao)->format('d/m/Y') : 'Chưa thiết lập' }}</div>
                                    <div class="col-md-4"><strong>Hạn Nộp SP:</strong> {{ $dt->HanNopSanPham ? \Carbon\Carbon::parse($dt->HanNopSanPham)->format('d/m/Y') : 'Chưa thiết lập' }}</div>
                                </div>
                            </div>

                            <div class="p-3 bg-primary-subtle rounded border border-primary-subtle d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fa-solid fa-file-lines fa-lg text-primary me-2"></i>
                                    <strong>File Đề Cương / Tài Liệu:</strong>
                                    @if($dt->FileTaiLieu)
                                        <span class="text-success ms-2"><code>{{ basename($dt->FileTaiLieu) }}</code></span>
                                    @else
                                        <span class="text-muted ms-2">Chưa có tệp đính kèm</span>
                                    @endif
                                </div>
                                <div>
                                    @if($dt->FileTaiLieu)
                                        <a href="{{ route('giangvien.detai.downloadTaiLieu', $dt->_id) }}" class="btn btn-sm btn-primary rounded-pill px-3">
                                            <i class="fa-solid fa-download me-1"></i>Tải về
                                        </a>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 ms-1" data-bs-toggle="modal" data-bs-target="#uploadDocModal{{ $dt->_id }}">
                                        <i class="fa-solid fa-upload me-1"></i>Cập nhật File
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="{{ route('giangvien.detai.edit', $dt->_id) }}" class="btn btn-warning text-white rounded-pill px-4">
                                <i class="fa-solid fa-pen me-1"></i>Chỉnh Sửa Đề Tài
                            </a>
                            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MODAL UPLOAD TÀI LIỆU -->
            <div class="modal fade" id="uploadDocModal{{ $dt->_id }}" tabindex="-1">
                <div class="modal-dialog">
                    <form action="{{ route('giangvien.detai.uploadTaiLieu', $dt->_id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title"><i class="fa-solid fa-paperclip me-2"></i>Cập Nhật File Đề Cương / Tài Liệu</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-2"><strong>Đề tài:</strong> {{ $dt->TenDeTai }}</p>
                                @if($dt->FileTaiLieu)
                                    <div class="alert alert-info py-2 small d-flex justify-content-between align-items-center mb-3">
                                        <span><i class="fa-solid fa-file-lines me-1"></i>Đã có file: <code>{{ basename($dt->FileTaiLieu) }}</code></span>
                                        <a href="{{ route('giangvien.detai.downloadTaiLieu', $dt->_id) }}" class="btn btn-sm btn-info text-white">Tải về</a>
                                    </div>
                                @endif
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Chọn tệp (.pdf, .doc, .docx, .zip, .rar - tối đa 20MB)</label>
                                    <input type="file" name="file_tai_lieu" class="form-control" accept=".pdf,.doc,.docx,.zip,.rar" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                                <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fa-solid fa-upload me-1"></i>Lưu Tệp</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="text-center text-muted py-5 card border-0 bg-white">
                    <i class="fa-solid fa-inbox fa-3x mb-3 text-muted opacity-50"></i>
                    <h5>Không tìm thấy đề tài nào phù hợp</h5>
                    <p class="small">Hãy thử lại với bộ lọc khác hoặc tạo đề tài mới.</p>
                </div>
            </div>
            @endforelse
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $detais->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('giangvien.detai.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-file-excel me-2"></i>Import Đề Tài từ Excel/CSV</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning d-flex gap-2 align-items-start">
                        <i class="fa-solid fa-triangle-exclamation mt-1"></i>
                        <div>
                            <strong>Hướng dẫn:</strong> Tải file mẫu, điền dữ liệu đúng định dạng rồi upload lại.<br>
                            Định dạng cột: <code>TenDeTai, MaMon, MaHocKy, MaLop, MoTa, YeuCau, HanDangKy, HanBaoCao, HanNopSanPham</code>
                            <br>
                            <a href="{{ route('admin.import.template', 'detais') }}" class="btn btn-sm btn-outline-success mt-2 rounded-pill px-3">
                                <i class="fa-solid fa-download me-1"></i>Tải file mẫu .xlsx
                            </a>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn file CSV/Excel <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" required accept=".csv,.xlsx">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4">
                        <i class="fa-solid fa-upload me-1"></i>Tải Lên & Import
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection