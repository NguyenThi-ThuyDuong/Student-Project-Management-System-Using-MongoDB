@extends('layouts.sinhvien')
@section('page_title', 'Báo Cáo Tiến Độ Đồ Án')
@section('content')

<!-- BỘ CHỌN LỚP & NHÓM ĐỒ ÁN (NẾU HỌC NHIỀU MÔN/LỚP) -->
@if(isset($allNhoms) && $allNhoms->count() > 1)
<div class="card card-premium mb-4 shadow-sm">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('sinhvien.baocao.index') }}" class="d-flex align-items-center flex-wrap gap-2 m-0">
            <label class="fw-bold text-dark text-nowrap mb-0 me-2">
                <i class="fa-solid fa-layer-group text-primary me-2"></i>Chọn Nhóm Đồ Án / Lớp HP:
            </label>
            <select name="maNhom" class="form-select border-primary fw-bold text-primary rounded-pill flex-grow-1" onchange="this.form.submit()" style="max-width: 580px;">
                @foreach($allNhoms as $n)
                    @php
                        $nDk = $n->getDangKyDeTai();
                        $nDt = $n->deTaiDangKy ?? ($n->DangKyDeTai->deTai ?? $n->getDeTaiDangKy());
                        $nApproved = $nDk && in_array($nDk['TrangThai'] ?? '', ['Đã duyệt', 'Đã duyệt đề tài']);
                    @endphp
                    <option value="{{ $n->_id }}" {{ (string)$nhom->_id === (string)$n->_id ? 'selected' : '' }}>
                        [{{ $n->TenNhom }}] — {{ $n->lopHocPhan->TenLopHP ?? 'Lớp HP' }} @if($nApproved) (✓ Đã duyệt đề tài: {{ $nDt->TenDeTai ?? '' }}) @else (⚠ Chưa có đề tài được duyệt) @endif
                    </option>
                @endforeach
            </select>
        </form>
    </div>
</div>
@endif

@php
    $dkArray = $nhom->getDangKyDeTai();
    $dangKyObj = $nhom->DangKyDeTai;
    $trangThaiDangKy = is_array($dkArray) ? ($dkArray['TrangThai'] ?? '') : ($dangKyObj->TrangThai ?? '');
    $isApprovedTopic = in_array($trangThaiDangKy, ['Đã duyệt', 'Đã duyệt đề tài']);
    $deTai = $nhom->deTaiDangKy ?? ($dangKyObj->deTai ?? $nhom->getDeTaiDangKy());
    $isHetHanBaoCao = $deTai && $deTai->HanBaoCao && date('Y-m-d') > $deTai->HanBaoCao;
    $cntBaoCao = $baocaos->count();
    $progressPercent = min(100, $cntBaoCao * 20);
    $isLeader = $nhom->isTruongNhom($sv);
@endphp

<!-- KHUNG TỔNG QUAN NHÓM & ĐỀ TÀI -->
<div class="card card-premium mb-4 border-0 shadow-sm overflow-hidden">
    <div class="card-header bg-primary bg-gradient text-white p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <span class="badge bg-white text-primary rounded-pill px-3 py-1 mb-2 fw-bold small">
                    <i class="fa-solid fa-graduation-cap me-1"></i>{{ $nhom->lopHocPhan->TenLopHP ?? 'Lớp HP' }} - {{ $nhom->monHoc->TenMon ?? 'Môn học' }}
                </span>
                <h4 class="fw-bold mb-1"><i class="fa-solid fa-folder-tree me-2 text-warning"></i>Báo Cáo Tiến Độ: {{ $nhom->TenNhom }}</h4>
                <p class="mb-0 text-white-50 small">Mã nhóm: <code>{{ $nhom->MaNhom ?? $nhom->_id }}</code> | Giảng viên hướng dẫn: <strong>{{ $nhom->getGiangVienHuongDan()->HoTen ?? ($deTai->giangVien->HoTen ?? 'Chưa phân công') }}</strong></p>
            </div>
            <div>
                @if($isLeader)
                    @if(!$isApprovedTopic)
                        <button class="btn btn-light rounded-pill px-4 shadow-sm" disabled>
                            <i class="fa-solid fa-lock text-warning me-2"></i>Chờ Duyệt Đề Tài
                        </button>
                    @elseif($isHetHanBaoCao)
                        <button class="btn btn-secondary rounded-pill px-4 shadow-sm" disabled>
                            <i class="fa-solid fa-lock text-danger me-2"></i>Đã Đóng Hạn Báo Cáo
                        </button>
                    @else
                        <button class="btn btn-warning text-dark fw-bold rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="fa-solid fa-cloud-arrow-up me-2"></i>Nộp Báo Cáo Tiến Độ Mới
                        </button>
                    @endif
                @else
                    <span class="badge bg-white text-dark rounded-pill px-3 py-2 border shadow-sm">
                        <i class="fa-solid fa-user-check text-success me-1"></i>Thành viên nhóm (Chỉ Trưởng nhóm có quyền nộp)
                    </span>
                @endif
            </div>
        </div>
    </div>
    <div class="card-body p-4 bg-white">
        <!-- Đề tài đang thực hiện -->
        <div class="p-3 bg-light rounded-3 border mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                <strong class="text-dark"><i class="fa-solid fa-book-bookmark text-primary me-2"></i>Đề Tài Đang Thực Hiện:</strong>
                @if($isApprovedTopic)
                    <span class="badge bg-success-subtle text-success border border-success rounded-pill px-3"><i class="fa-solid fa-circle-check me-1"></i>Đã Phê Duyệt Chức Thức</span>
                @else
                    <span class="badge bg-warning-subtle text-dark border border-warning rounded-pill px-3"><i class="fa-solid fa-clock me-1"></i>Chờ Giảng Viên Duyệt</span>
                @endif
            </div>
            <h5 class="fw-bold text-primary mb-2">{{ $deTai->TenDeTai ?? 'Chưa đăng ký đề tài chính thức' }}</h5>
            @if($deTai)
                <div class="d-flex flex-wrap gap-3 small text-muted">
                    <span><i class="fa-solid fa-tag me-1 text-primary"></i>Loại: {{ $deTai->LoaiDeTai ?? 'Đề tài môn học' }}</span>
                    <span><i class="fa-regular fa-calendar-check me-1 text-danger"></i>Hạn chót báo cáo: {{ $deTai->HanBaoCao ? date('d/m/Y', strtotime($deTai->HanBaoCao)) : '31/12/2026' }}</span>
                    @if(!empty($deTai->FileTaiLieu))
                        <a href="{{ asset($deTai->FileTaiLieu) }}" target="_blank" class="text-primary text-decoration-none fw-bold">
                            <i class="fa-solid fa-file-pdf me-1"></i>Xem File Đề Cương Chi Tiết
                        </a>
                    @endif
                </div>
            @endif
        </div>

        <!-- Thanh tổng quan tiến độ hoàn thành -->
        <div>
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="fw-bold small text-dark"><i class="fa-solid fa-chart-line text-primary me-1"></i>Mức độ hoàn thành khối lượng đồ án:</span>
                <span class="fw-bold text-primary small">{{ $progressPercent }}% (Đã nộp {{ $cntBaoCao }}/5 mốc)</span>
            </div>
            <div class="progress rounded-pill style-progress" style="height: 12px; background: #e2e8f0;">
                <div class="progress-bar bg-gradient-primary rounded-pill progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $progressPercent }}%;"></div>
            </div>
        </div>
    </div>
</div>

@if(!$isApprovedTopic)
<div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4">
    <div class="d-flex align-items-center">
        <i class="fa-solid fa-triangle-exclamation fa-2x text-warning me-3"></i>
        <div>
            <h6 class="fw-bold mb-1">Nhóm của bạn chưa có đề tài được duyệt!</h6>
            <p class="mb-0 small text-dark">Vui lòng chọn đề tài trong danh sách hoặc tự nộp Đề xuất đề tài riêng để Giảng viên phê duyệt trước khi cập nhật tiến độ.</p>
        </div>
    </div>
</div>
@endif

<!-- QUY TRÌNH CÁC GIAI ĐOẠN MỐC TIẾN ĐỘ -->
<div class="card card-premium mb-4 shadow-sm">
    <div class="card-header bg-white p-3 border-bottom">
        <h6 class="fw-bold text-primary mb-0"><i class="fa-solid fa-route me-2"></i>Quy Trình Các Giai Đoạn Nộp Báo Cáo Tiến Độ</h6>
    </div>
    <div class="card-body p-4">
        <div class="row g-3 text-center">
            @for($i = 1; $i <= 5; $i++)
                @php
                    $bcStage = $baocaos->firstWhere('LanBaoCao', $i);
                    $isDone = !empty($bcStage);
                    $hasNx = $isDone && !empty($bcStage->NhanXet);
                @endphp
                <div class="col-md-2-4 col-6">
                    <div class="p-3 rounded-4 border {{ $isDone ? 'border-success bg-success bg-opacity-10' : 'border-light bg-light' }} h-100">
                        <div class="rounded-circle {{ $isDone ? 'bg-success text-white' : 'bg-secondary text-white' }} d-inline-flex align-items-center justify-content-center mb-2" style="width: 40px; height: 40px;">
                            @if($isDone) <i class="fa-solid fa-check"></i> @else {{ $i }} @endif
                        </div>
                        <h6 class="fw-bold small mb-1">Giai Đoạn {{ $i }}</h6>
                        <small class="d-block text-muted" style="font-size: 0.75rem;">
                            @if($i == 1) Đề cương & Khảo sát
                            @elseif($i == 2) Phân tích & CSDL
                            @elseif($i == 3) Backend & Frontend
                            @elseif($i == 4) Kiểm thử & Sửa lỗi
                            @else Hoàn thiện & Nộp SP @endif
                        </small>
                        <div class="mt-2">
                            @if($hasNx)
                                <span class="badge bg-success rounded-pill small">✓ Đã đánh giá</span>
                            @elseif($isDone)
                                <span class="badge bg-warning text-dark rounded-pill small">⏳ Chờ nhận xét</span>
                            @else
                                <span class="badge bg-secondary rounded-pill small">Chưa nộp</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endfor
        </div>
    </div>
</div>

<!-- NỘI DUNG LỊCH SỬ CÁC MỐC TIẾN ĐỘ ĐÃ NỘP -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold text-primary mb-0"><i class="fa-solid fa-clock-rotate-left me-2"></i>Lịch Sử Báo Cáo Tiến Độ Đồ Án ({{ $cntBaoCao }} Lần Nộp)</h5>
    @if($isLeader && $isApprovedTopic && !$isHetHanBaoCao)
        <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fa-solid fa-plus me-1"></i>Thêm Báo Cáo Mốc Mới
        </button>
    @endif
</div>

@if($baocaos->isEmpty())
    <div class="card card-premium text-center p-5 shadow-sm">
        <i class="fa-solid fa-file-circle-xmark fa-4x text-muted mb-3 opacity-25"></i>
        <h5 class="fw-bold text-secondary">Chưa có báo cáo tiến độ nào được nộp</h5>
        <p class="text-muted small mb-3">Nhóm bạn chưa thực hiện nộp báo cáo cập nhật tiến độ cho đồ án này.</p>
        @if($isLeader && $isApprovedTopic && !$isHetHanBaoCao)
            <div>
                <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fa-solid fa-upload me-2"></i>Nộp Báo Cáo Đầu Tiên
                </button>
            </div>
        @endif
    </div>
@else
    <div class="row g-4">
        @foreach($baocaos as $bc)
            @php
                $lanBc = is_array($bc) ? ($bc['LanBaoCao'] ?? 1) : ($bc->LanBaoCao ?? 1);
                $ngayNopBc = is_array($bc) ? ($bc['NgayNop'] ?? '') : ($bc->NgayNop ?? '');
                $fileBc = is_array($bc) ? ($bc['FileBaoCao'] ?? '') : ($bc->FileBaoCao ?? '');
                $noiDungBc = is_array($bc) ? ($bc['NoiDung'] ?? '') : ($bc->NoiDung ?? '');
                $nhanXetArr = is_array($bc) ? ($bc['NhanXet'] ?? []) : ($bc->NhanXet ?? []);
                $stBc = is_array($bc) ? ($bc['TrangThai'] ?? '') : ($bc->TrangThai ?? '');
                if (empty($stBc)) {
                    $stBc = !empty($nhanXetArr) ? 'Đã nhận xét' : 'Chờ nhận xét';
                }
            @endphp
            <div class="col-12">
                <div class="card card-premium shadow-sm border-0">
                    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom flex-wrap gap-2">
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <span class="badge bg-primary fw-bold px-3 py-2 rounded-pill fs-6">
                                <i class="fa-solid fa-flag me-1"></i> Mốc Tiến Độ Giai Đoạn Lần {{ $lanBc }}
                            </span>
                            @if($ngayNopBc)
                                <span class="text-muted small">
                                    <i class="fa-regular fa-clock me-1"></i> Ngày nộp: <strong>{{ date('d/m/Y', strtotime($ngayNopBc)) }}</strong>
                                </span>
                            @endif
                        </div>
                        <div>
                            @if($stBc == 'Đã nhận xét')
                                <span class="badge bg-success-subtle text-success border border-success px-3 py-2 rounded-pill fw-bold">
                                    <i class="fa-solid fa-circle-check me-1"></i> Giảng Viên Đã Nhận Xét ({{ count($nhanXetArr) }})
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-dark border border-warning px-3 py-2 rounded-pill fw-bold">
                                    <i class="fa-solid fa-hourglass-half me-1"></i> Chờ Giảng Viên Phản Hồi
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="mb-3 p-3 bg-light rounded-3 border-start border-4 border-primary">
                            <h6 class="fw-bold text-dark mb-2">
                                <i class="fa-solid fa-align-left me-2 text-primary"></i>Nội dung khối lượng công việc đã hoàn thành:
                            </h6>
                            <p class="mb-0 text-secondary" style="white-space: pre-line;">{{ $noiDungBc }}</p>
                        </div>

                        <!-- Tải file hoặc Github Link -->
                        @if(!empty($fileBc))
                            @php
                                $isUrlBc = filter_var($fileBc, FILTER_VALIDATE_URL) || str_starts_with($fileBc, 'http://') || str_starts_with($fileBc, 'https://');
                                $targetUrlBc = $isUrlBc ? $fileBc : asset(ltrim($fileBc, '/'));
                            @endphp
                            <div class="mb-3 p-3 bg-white rounded-3 border">
                                <span class="fw-bold text-dark me-2"><i class="fa-solid fa-paperclip me-1 text-primary"></i>File / Đường dẫn sản phẩm đính kèm:</span>
                                @if($isUrlBc)
                                    <a href="{{ $fileBc }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 shadow-sm">
                                        <i class="fa-brands fa-github me-1"></i> Mở Liên Kết Online / GitHub
                                    </a>
                                @else
                                    <a href="{{ $targetUrlBc }}" download class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 shadow-sm">
                                        <i class="fa-solid fa-download me-1"></i> Tải File Báo Cáo Tiến Độ
                                    </a>
                                @endif
                            </div>
                        @endif

                        <!-- Khung Phản hồi & Nhận xét của Giảng viên -->
                        @if(!empty($nhanXetArr))
                            <div class="mt-4 pt-3 border-top">
                                <h6 class="fw-bold text-success mb-3">
                                    <i class="fa-solid fa-comments me-2"></i>Đánh Giá &amp; Hướng Dẫn Từ Giảng Viên Hướng Dẫn:
                                </h6>
                                <div class="ps-2">
                                    @foreach($nhanXetArr as $nx)
                                        @php
                                            $date = !empty($nx['NgayNhanXet']) ? date('d/m/Y', strtotime($nx['NgayNhanXet'])) : '';
                                            $noiDungNx = is_array($nx) ? ($nx['NoiDung'] ?? '') : ($nx->NoiDung ?? '');
                                        @endphp
                                        <div class="bg-success bg-opacity-10 p-3 rounded-3 mb-2 border border-success border-opacity-25">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="fw-bold text-success"><i class="fa-solid fa-user-check me-1"></i>Giảng viên hướng dẫn</span>
                                                @if($date)
                                                    <span class="text-muted small"><i class="fa-regular fa-clock me-1"></i>{{ $date }}</span>
                                                @endif
                                            </div>
                                            <p class="mb-0 text-dark small" style="white-space: pre-line;">{{ $noiDungNx }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

<!-- MODAL NỘP BÁO CÁO TIẾN ĐỘ MỚI -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('sinhvien.baocao.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="MaNhom" value="{{ $nhom->_id }}">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-cloud-arrow-up me-2"></i>Nộp Báo Cáo Tiến Độ Đồ Án</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 rounded-3 mb-3 small">
                        <i class="fa-solid fa-circle-info me-1"></i>Sinh viên nộp báo cáo cập nhật tiến độ theo từng giai đoạn làm đồ án. File đính kèm sẽ được gửi tới Giảng viên hướng dẫn để xem và nhận xét.
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Chọn Giai Đoạn / Mốc Tiến Độ <span class="text-danger">*</span></label>
                            <select name="LanBaoCao" class="form-select border-primary fw-bold" required>
                                <option value="1" {{ $cntBaoCao == 0 ? 'selected' : '' }}>Giai đoạn 1: Nộp &amp; Cập nhật Đề cương chi tiết</option>
                                <option value="2" {{ $cntBaoCao == 1 ? 'selected' : '' }}>Giai đoạn 2: Báo cáo Tiến độ Lần 1 (Phân tích &amp; CSDL)</option>
                                <option value="3" {{ $cntBaoCao == 2 ? 'selected' : '' }}>Giai đoạn 3: Báo cáo Tiến độ Lần 2 (Backend &amp; Frontend)</option>
                                <option value="4" {{ $cntBaoCao == 3 ? 'selected' : '' }}>Giai đoạn 4: Báo cáo Tiến độ Lần 3 (Kiểm thử &amp; Sửa lỗi)</option>
                                <option value="5" {{ $cntBaoCao >= 4 ? 'selected' : '' }}>Giai đoạn 5: Nộp Sản Phẩm Hoàn Thiện Cuối Kỳ</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nhóm Nộp Báo Cáo</label>
                            <input type="text" class="form-control bg-light" value="{{ $nhom->TenNhom }}" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tóm Tắt Nội Dung Công Việc Đã Hoàn Thành <span class="text-danger">*</span></label>
                        <textarea name="NoiDung" class="form-control" rows="4" placeholder="Ví dụ: Đã thiết kế xong CSDL MongoDB, hoàn thành module Đăng nhập & Phân quyền, tích hợp API..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">File Báo Cáo Tiến Độ Đính Kèm (.pdf, .docx, .zip)</label>
                        <input type="file" name="FileUpLoad" class="form-control" accept=".pdf,.doc,.docx,.zip,.rar">
                        <div class="form-text small text-muted">Tải lên tệp báo cáo chi tiết (Tối đa 20MB).</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Hoặc Điền Liên Kết GitHub / Tài Liệu Online (Nếu có)</label>
                        <input type="url" name="FileBaoCao" class="form-control" placeholder="https://github.com/user/repository hoặc link Google Drive...">
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold"><i class="fa-solid fa-paper-plane me-1"></i>Xác Nhận Nộp Báo Cáo</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.col-md-2-4 {
    flex: 0 0 auto;
    width: 20%;
}
@media (max-width: 768px) {
    .col-md-2-4 {
        width: 50%;
    }
}
</style>

@endsection
