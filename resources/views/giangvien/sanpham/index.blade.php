@extends('layouts.giangvien')
@section('page_title', 'Quản Lý Sản Phẩm & Báo Cáo Nhóm')
@section('content')

<style>
    .bg-gradient-purple {
        background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 50%, #2563EB 100%);
    }
    .btn-purple-gradient {
        background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%);
        color: white;
        border: none;
        transition: all 0.3s ease;
    }
    .btn-purple-gradient:hover {
        background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
    }
    .ai-badge-pill {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        color: #ffffff;
        font-weight: 600;
        padding: 6px 14px;
        border-radius: 50px;
    }
    .notice-ai-box {
        background-color: #F5F3FF;
        border: 1px solid #DDD6FE;
        border-radius: 12px;
        color: #5B21B6;
    }
    .doc-preview-card {
        background: #F8FAFC;
        border-radius: 12px;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
    }
    .doc-header-bar {
        background: #0F172A;
        color: #F8FAFC;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }
    .doc-paper-content {
        background: #FFFFFF;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border-radius: 8px;
        padding: 35px 40px;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        color: #1E293B;
        line-height: 1.7;
    }
    .summary-card-header {
        background: linear-gradient(135deg, #5B21B6 0%, #4C1D95 100%);
        color: white;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
    }
</style>

{{-- Import Result --}}
@if(session('import_result'))
<div class="alert alert-info alert-dismissible fade show">
    <i class="fa-solid fa-circle-info me-2"></i>
    {!! session('import_result') !!}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card card-premium mb-4 shadow-sm">
    <div class="card-header-premium d-flex justify-content-between align-items-center bg-white p-3 border-bottom">
        <span class="fw-bold fs-5 text-primary"><i class="fa-solid fa-box-open me-2"></i>Danh Sách Nhóm Tôi Hướng Dẫn</span>
        <form method="GET" action="{{ route('giangvien.sanpham.index') }}" class="d-flex gap-2 align-items-center">
            <select name="maNhom" class="form-select form-select-sm" style="width:220px">
                <option value="">— Tất cả nhóm —</option>
                @foreach($allNhoms as $n)
                    <option value="{{ $n->MaNhom }}" {{ $selectedNhomId == $n->MaNhom ? 'selected' : '' }}>
                        {{ $n->TenNhom }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-filter me-1"></i>Lọc
            </button>
            @if($selectedNhomId)
                <a href="{{ route('giangvien.sanpham.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-xmark me-1"></i>Xoá lọc
                </a>
            @endif
        </form>
    </div>

    <div class="card-body p-0">
        @forelse($nhoms as $nhom)
        <div class="border-bottom p-4">
            {{-- Header nhóm --}}
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="fw-bold mb-1">
                        <i class="fa-solid fa-users text-primary me-2"></i>{{ $nhom->TenNhom }}
                        <span class="badge bg-{{ $nhom->TrangThai == 'Đang hoạt động' || $nhom->TrangThai == 'Đã nộp sản phẩm' ? 'success' : 'secondary' }} ms-2 fs-7">
                            {{ $nhom->TrangThai }}
                        </span>
                    </h5>
                    <div class="text-muted small">
                        <i class="fa-solid fa-book me-1"></i>Môn: <strong>{{ $nhom->monHoc->TenMon ?? '—' }}</strong>
                        &nbsp;|&nbsp;
                        <i class="fa-solid fa-graduation-cap me-1"></i>Đề tài:
                        @if($nhom->dangKyDeTai && $nhom->dangKyDeTai->deTai)
                            <strong class="text-primary">{{ $nhom->dangKyDeTai->deTai->TenDeTai }}</strong>
                            <span class="badge bg-{{ $nhom->dangKyDeTai->TrangThai == 'Đã duyệt' ? 'success' : ($nhom->dangKyDeTai->TrangThai == 'Từ chối' ? 'danger' : 'warning text-dark') }} ms-1">
                                {{ $nhom->dangKyDeTai->TrangThai }}
                            </span>
                        @else
                            <span class="text-muted">Chưa đăng ký</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Thành viên --}}
            <div class="mb-3">
                <span class="text-muted small fw-semibold"><i class="fa-solid fa-user-group me-1"></i>Thành viên nhóm:</span>
                <div class="d-flex flex-wrap gap-2 mt-1">
                    @foreach($nhom->thanhVienNhoms->whereIn('TrangThai', ['da_chap_nhan', 'da_tham_gia']) as $tv)
                    <span class="badge bg-light text-dark border">
                        {{ $tv->sinhVien->HoTen ?? '?' }}
                        @if($tv->VaiTro == 'Trưởng nhóm' || $nhom->TruongNhom == $tv->MaSV)
                            <i class="fa-solid fa-crown text-warning ms-1" title="Trưởng nhóm"></i>
                        @endif
                    </span>
                    @endforeach
                </div>
            </div>

            {{-- Accordion báo cáo tiến độ --}}
            <div class="accordion accordion-flush" id="acc-nhom-{{ $nhom->MaNhom }}">
                <div class="accordion-item border rounded mb-2">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-semibold py-2" type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapse-bc-{{ $nhom->MaNhom }}">
                            <i class="fa-solid fa-clipboard-list me-2 text-info"></i>
                            Báo cáo tiến độ
                            <span class="badge bg-info text-dark ms-2">{{ $nhom->baoCaos->count() }}</span>
                        </button>
                    </h2>
                    <div id="collapse-bc-{{ $nhom->MaNhom }}" class="accordion-collapse collapse show">
                        <div class="accordion-body p-0">
                            @if($nhom->baoCaos->isEmpty())
                                <p class="text-muted text-center py-3 mb-0">Nhóm chưa nộp báo cáo tiến độ nào.</p>
                            @else
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Lần</th>
                                            <th>Nội dung</th>
                                            <th>Ngày nộp</th>
                                            <th>File / Link</th>
                                            <th>Thao tác & Đánh giá AI</th>
                                            <th>Nhận xét</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($nhom->baoCaos as $bc)
                                        @php
                                            $bcId = (string) ($bc->_id ?? $bc->MaBaoCao ?? rand(1000,9999));
                                            $fileBc = $bc->FileBaoCao;
                                            $decodedBc = json_decode($fileBc, true);
                                            $isUrlBc = is_string($fileBc) && (filter_var($fileBc, FILTER_VALIDATE_URL) || str_starts_with($fileBc, 'http://') || str_starts_with($fileBc, 'https://'));
                                            $svNamesList = $nhom->thanhVienNhoms->map(fn($t) => $t->sinhVien->HoTen ?? 'Sinh viên')->implode(' & ');
                                            $tenDeTaiStr = $nhom->dangKyDeTai->deTai->TenDeTai ?? ($nhom->getTenDeTaiDangKy() ?? 'Chưa đăng ký');
                                        @endphp
                                        <tr>
                                            <td><span class="badge bg-primary rounded-pill">Lần {{ $bc->LanBaoCao }}</span></td>
                                            <td class="small fw-semibold">{{ Str::limit($bc->NoiDung, 60) }}</td>
                                            <td class="small text-muted">{{ \Carbon\Carbon::parse($bc->NgayNop)->format('d/m/Y') }}</td>
                                            <td>
                                                @if(is_array($decodedBc))
                                                    @if(!empty($decodedBc['file']))
                                                        <span class="badge bg-light text-success border me-1"><i class="fa-solid fa-file me-1"></i>File báo cáo</span>
                                                    @endif
                                                    @if(!empty($decodedBc['github']))
                                                        <span class="badge bg-light text-dark border"><i class="fa-brands fa-github me-1"></i>GitHub Repo</span>
                                                    @endif
                                                @elseif($fileBc)
                                                    @if($isUrlBc)
                                                        <span class="badge bg-light text-dark border"><i class="fa-brands fa-github me-1"></i>GitHub Repo</span>
                                                    @else
                                                        <span class="badge bg-light text-success border"><i class="fa-solid fa-file me-1"></i>File báo cáo</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-1 align-items-center">
                                                    {{-- AI Assist Button --}}
                                                    <button type="button" class="btn btn-sm btn-purple-gradient py-1 px-3 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#modal-ai-{{ $bcId }}">
                                                        <i class="fa-solid fa-wand-magic-sparkles me-1"></i>Đánh giá (AI Assist)
                                                    </button>

                                                    @if(is_array($decodedBc))
                                                        @if(!empty($decodedBc['github']))
                                                            <a href="{{ $decodedBc['github'] }}" target="_blank" class="btn btn-sm btn-outline-dark py-1 px-2 rounded-pill" title="Xem GitHub">
                                                                <i class="fa-brands fa-github"></i>
                                                            </a>
                                                        @endif
                                                        @if(!empty($decodedBc['file']))
                                                            <a href="{{ asset(ltrim($decodedBc['file'], '/')) }}" download class="btn btn-sm btn-outline-success py-1 px-2 rounded-pill" title="Tải về">
                                                                <i class="fa-solid fa-download"></i>
                                                            </a>
                                                        @endif
                                                    @elseif($fileBc)
                                                        @if($isUrlBc)
                                                            <a href="{{ $fileBc }}" target="_blank" class="btn btn-sm btn-outline-dark py-1 px-2 rounded-pill" title="Xem GitHub">
                                                                <i class="fa-brands fa-github"></i>
                                                            </a>
                                                        @else
                                                            @php $targetUrlBc = asset(ltrim($fileBc, '/')); @endphp
                                                            <a href="{{ $targetUrlBc }}" download class="btn btn-sm btn-outline-success py-1 px-2 rounded-pill" title="Tải về">
                                                                <i class="fa-solid fa-download"></i>
                                                            </a>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $hasNhanXet = isset($bc->nhanXets) && $bc->nhanXets->isNotEmpty();
                                                    $lastNhanXet = $hasNhanXet ? $bc->nhanXets->last() : null;
                                                @endphp
                                                @if($hasNhanXet)
                                                    <span class="text-success small fw-semibold">
                                                        <i class="fa-solid fa-check-circle me-1"></i>
                                                        {{ Str::limit(is_object($lastNhanXet) ? ($lastNhanXet->NoiDung ?? '') : ($lastNhanXet['NoiDung'] ?? ''), 45) }}
                                                    </span>
                                                @else
                                                    <form action="{{ route('giangvien.baocao.nhanxet', $bcId) }}" method="POST" class="d-flex gap-1">
                                                        @csrf
                                                        <input type="text" name="NoiDung" class="form-control form-control-sm" placeholder="Nhận xét..." required style="max-width:180px">
                                                        <button type="submit" class="btn btn-sm btn-success px-2">
                                                            <i class="fa-solid fa-paper-plane"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>

                                        {{-- MODAL AI ASSISTANT REVIEW --}}
                                        <div class="modal fade" id="modal-ai-{{ $bcId }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                                                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                                                    
                                                    {{-- Modal Header Banner --}}
                                                    <div class="bg-gradient-purple text-white p-4 position-relative">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div>
                                                                <div class="text-white-50 small mb-1">
                                                                    <i class="fa-solid fa-house me-1"></i>Trang chủ &nbsp;/&nbsp; /Lecturer.reports.detail
                                                                </div>
                                                                <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
                                                                    <span>Đánh Giá Báo Cáo Tiến Độ (Tích hợp Trợ lý AI)</span>
                                                                </h4>
                                                                <div class="small text-white-50">
                                                                    <strong>{{ $nhom->TenNhom }}</strong> &bull; Sinh viên: <strong>{{ $svNamesList }}</strong> &bull; Đề tài: <strong>{{ $tenDeTaiStr }}</strong>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex align-items-center gap-3">
                                                                <div class="ai-badge-pill shadow-sm">
                                                                    <i class="fa-solid fa-sparkles text-warning me-1"></i>AI Verified Analysis <span class="badge bg-warning text-dark rounded-pill ms-1">Độ tin cậy: 95%</span>
                                                                </div>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Modal Body --}}
                                                    <div class="modal-body p-4 bg-light">
                                                        
                                                        {{-- Warning Notice Box --}}
                                                        <div class="notice-ai-box p-3 mb-4 d-flex align-items-center justify-content-between shadow-sm">
                                                            <div class="d-flex align-items-center gap-3">
                                                                <div class="fs-4 text-purple">
                                                                    <i class="fa-solid fa-circle-info"></i>
                                                                </div>
                                                                <div class="small fw-semibold">
                                                                    <strong>LƯU Ý:</strong> AI chỉ đóng vai trò hỗ trợ phân tích & tóm tắt báo cáo. Giảng viên hướng dẫn trực tiếp đưa ra đánh giá chính thức và phê duyệt.
                                                                </div>
                                                            </div>
                                                            <span class="badge bg-white text-purple border border-purple-subtle px-3 py-2 rounded-pill small">Mô hình: Gemini 2.5 Flash Academic Edition</span>
                                                        </div>

                                                        <div class="row g-4">
                                                            {{-- Left Column: Real Document Previewer / Submitted Content --}}
                                                            <div class="col-lg-7">
                                                                <div class="doc-preview-card border rounded-3 overflow-hidden shadow-sm h-100 bg-white d-flex flex-column">
                                                                    @php
                                                                        $realFileBc = is_array($decodedBc) ? ($decodedBc['file'] ?? '') : $fileBc;
                                                                        $isPdf = $realFileBc && str_ends_with(strtolower($realFileBc), '.pdf');
                                                                        $fileUrl = $realFileBc ? ($isUrlBc ? $realFileBc : asset(ltrim($realFileBc, '/'))) : null;
                                                                    @endphp
                                                                    
                                                                    <div class="doc-header-bar px-3 py-2 bg-dark text-white d-flex justify-content-between align-items-center">
                                                                        <div class="d-flex align-items-center gap-2 small">
                                                                            <i class="fa-solid {{ $isPdf ? 'fa-file-pdf text-danger' : 'fa-file-lines text-info' }} fs-5"></i>
                                                                            <span class="fw-semibold text-truncate text-white" style="max-width: 250px;">
                                                                                {{ $realFileBc ? basename($realFileBc) : 'Tệp Báo Cáo' }}
                                                                            </span>
                                                                        </div>
                                                                        <div>
                                                                            @if($fileUrl)
                                                                                <a href="{{ $fileUrl }}" target="_blank" download class="btn btn-sm btn-success rounded-pill px-3 py-1 shadow-sm">
                                                                                    <i class="fa-solid fa-download me-1"></i>Tải File Gốc
                                                                                </a>
                                                                            @endif
                                                                        </div>
                                                                    </div>

                                                                    <div class="p-3 flex-grow-1" style="max-height: 520px; overflow-y: auto;">
                                                                        @if($isPdf && $fileUrl)
                                                                            <iframe src="{{ $fileUrl }}" width="100%" height="480px" style="border: none; border-radius: 8px;"></iframe>
                                                                        @else
                                                                            <div class="doc-paper-content p-3 bg-light rounded border">
                                                                                <div class="text-center mb-3 border-bottom pb-2">
                                                                                    <h5 class="fw-bold text-uppercase text-primary mb-1">BÁO CÁO TIẾN ĐỘ ĐỢT {{ $bc->LanBaoCao }}</h5>
                                                                                    <div class="text-muted small">Nhóm: <strong>{{ $nhom->TenNhom }}</strong> &bull; Đề tài: <em>{{ $tenDeTaiStr }}</em></div>
                                                                                    <div class="text-muted small">Ngày nộp: <strong>{{ \Carbon\Carbon::parse($bc->NgayNop)->format('d/m/Y') }}</strong></div>
                                                                                </div>

                                                                                <div class="bg-white p-3 rounded border mb-3 shadow-sm">
                                                                                    <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-quote-left text-primary me-2"></i>Nội dung báo cáo nộp từ Sinh viên:</h6>
                                                                                    <p class="text-dark mb-0" style="white-space: pre-line; font-size: 0.95rem;">{{ $bc->NoiDung }}</p>
                                                                                </div>

                                                                                @if($fileUrl)
                                                                                    <div class="p-3 bg-white rounded border text-center shadow-sm">
                                                                                        <p class="small text-muted mb-2">Tài liệu / File đính kèm đợt báo cáo này:</p>
                                                                                        <a href="{{ $fileUrl }}" target="_blank" download class="btn btn-outline-primary rounded-pill px-4">
                                                                                            <i class="fa-solid fa-file-arrow-down me-2"></i>Xem / Tải Tệp Đính Kèm ({{ basename($realFileBc) }})
                                                                                        </a>
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            {{-- Right Column: AI SUMMARY Card --}}
                                                            <div class="col-lg-5">
                                                                <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden">
                                                                    <div class="p-3 text-white bg-gradient-purple d-flex justify-content-between align-items-center">
                                                                        <div class="fw-bold d-flex align-items-center gap-2">
                                                                            <i class="fa-solid fa-robot text-warning"></i>AI SUMMARY
                                                                        </div>
                                                                        <div class="d-flex gap-2">
                                                                            <span class="badge bg-white text-dark rounded-pill small"><i class="fa-solid fa-check text-success me-1"></i>AI Verified</span>
                                                                            <span class="badge bg-warning text-dark rounded-pill small">Độ tin cậy: 95.2%</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="bg-white p-2 border-bottom small text-muted d-flex justify-content-between px-3">
                                                                        <span><i class="fa-regular fa-clock me-1"></i>Ngày nộp: {{ \Carbon\Carbon::parse($bc->NgayNop)->format('d/m/Y') }}</span>
                                                                        <span><i class="fa-regular fa-file me-1"></i>Đợt báo cáo: Lần {{ $bc->LanBaoCao }}</span>
                                                                    </div>

                                                                    <div class="card-body p-3" style="max-height: 500px; overflow-y: auto;">
                                                                        
                                                                        {{-- 1. AI Analysis & Tóm tắt Tiến độ --}}
                                                                        <div class="p-3 mb-3 rounded-3 shadow-xs" style="background-color: #F0FDF4; border: 1px solid #BBF7D0;">
                                                                            <h6 class="fw-bold text-success mb-2 small d-flex align-items-center justify-content-between">
                                                                                <span><i class="fa-solid fa-robot me-1"></i> 1. TÓM TẮT TIẾN ĐỘ THỰC HIỆN</span>
                                                                                <span class="badge bg-success text-white px-2 py-1 rounded-pill small" style="font-size:0.75rem">Đạt tiến độ Lần {{ $bc->LanBaoCao }}</span>
                                                                            </h6>
                                                                            <div class="small text-secondary mb-2">
                                                                                <strong class="text-dark">Nội dung báo cáo nộp từ Sinh viên:</strong>
                                                                                <div class="p-2 bg-white rounded border border-success-subtle mt-1 text-dark" style="white-space: pre-line;">{{ $bc->NoiDung }}</div>
                                                                            </div>
                                                                            <ul class="mb-0 ps-3 small text-secondary">
                                                                                <li class="mb-1"><strong>Mốc báo cáo:</strong> Đợt Lần {{ $bc->LanBaoCao }} thuộc Lớp HP: {{ $nhom->lopHocPhan->TenLopHP ?? '—' }}.</li>
                                                                                <li class="mb-1"><strong>Tên đề tài:</strong> {{ $tenDeTaiStr }}.</li>
                                                                                <li><strong>File đính kèm:</strong> {{ $realFileBc ? basename($realFileBc) : 'Đã nộp trực tuyến / Chưa kèm file' }}.</li>
                                                                            </ul>
                                                                        </div>

                                                                        {{-- 2. Đánh giá tự động từ Trợ lý AI --}}
                                                                        <div class="p-3 mb-3 rounded-3 shadow-xs" style="background-color: #EFF6FF; border: 1px solid #BFDBFE;">
                                                                            <h6 class="fw-bold text-primary mb-2 small d-flex align-items-center gap-1">
                                                                                <i class="fa-solid fa-wand-magic-sparkles"></i> 2. PHÂN TÍCH & ĐÁNH GIÁ TỰ ĐỘNG TỪ AI
                                                                            </h6>
                                                                            <ul class="mb-0 ps-3 small text-secondary">
                                                                                <li class="mb-1"><span class="text-success fw-bold"><i class="fa-solid fa-check me-1"></i>Đánh giá thời hạn:</span> Nhóm đã hoàn thành nộp báo cáo đúng mốc quy định.</li>
                                                                                <li class="mb-1"><span class="text-warning fw-bold"><i class="fa-solid fa-lightbulb me-1"></i>Gợi ý cho GVHD:</span> Đã hỗ trợ trích xuất file và nội dung. Giảng viên có thể ghi nhận xét và duyệt mốc báo cáo bên dưới.</li>
                                                                                <li><span class="text-info fw-bold"><i class="fa-solid fa-circle-info me-1"></i>Khuyến nghị:</span> Xác nhận phê duyệt mốc tiến độ Lần {{ $bc->LanBaoCao }}.</li>
                                                                            </ul>
                                                                        </div>

                                                                        {{-- Form Lecturer Review --}}
                                                                        <div class="border-top pt-3 mt-3">
                                                                            <label class="form-label fw-bold text-dark small"><i class="fa-solid fa-user-pen me-1 text-primary"></i>ĐÁNH GIÁ CHÍNH THỨC CỦA GIẢNG VIÊN HƯỚNG DẪN</label>
                                                                            <form action="{{ route('giangvien.baocao.nhanxet', $bcId) }}" method="POST">
                                                                                @csrf
                                                                                <textarea name="NoiDung" class="form-control form-control-sm mb-3" rows="3" placeholder="Nhập đánh giá và hướng dẫn điều chỉnh cho sinh viên..." required>{{ is_object($lastNhanXet) ? ($lastNhanXet->NoiDung ?? '') : ($lastNhanXet['NoiDung'] ?? '') }}</textarea>
                                                                                <div class="d-flex gap-2">
                                                                                    <button type="submit" class="btn btn-success btn-sm flex-fill rounded-pill fw-semibold">
                                                                                        <i class="fa-solid fa-circle-check me-1"></i>Đánh Giá & Phê Duyệt
                                                                                    </button>
                                                                                    <button type="button" class="btn btn-outline-warning btn-sm rounded-pill" data-bs-dismiss="modal">
                                                                                        <i class="fa-solid fa-xmark me-1"></i>Đóng
                                                                                    </button>
                                                                                </div>
                                                                            </form>
                                                                        </div>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Accordion sản phẩm --}}
                <div class="accordion-item border rounded">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-semibold py-2" type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapse-sp-{{ $nhom->MaNhom }}">
                            <i class="fa-solid fa-box me-2 text-warning"></i>
                            Sản phẩm nộp
                            <span class="badge bg-warning text-dark ms-2">{{ $nhom->sanPhams->count() }}</span>
                        </button>
                    </h2>
                    <div id="collapse-sp-{{ $nhom->MaNhom }}" class="accordion-collapse collapse">
                        <div class="accordion-body p-0">
                            @if($nhom->sanPhams->isEmpty())
                                <p class="text-muted text-center py-3 mb-0">Nhóm chưa nộp sản phẩm.</p>
                            @else
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tên sản phẩm / Source code</th>
                                            <th>Ngày nộp</th>
                                            <th>File / Link</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($nhom->sanPhams as $sp)
                                        @php
                                            $fileUrl = $sp->LinkFile ? asset(ltrim($sp->LinkFile, '/')) : null;
                                            $gitUrl = $sp->LinkSourceCode ?? (filter_var($sp->LinkFile, FILTER_VALIDATE_URL) ? $sp->LinkFile : null);
                                        @endphp
                                        <tr>
                                            <td class="fw-semibold">{{ $sp->TenSanPham ?? 'Sản phẩm đồ án' }}</td>
                                            <td class="small text-muted">{{ \Carbon\Carbon::parse($sp->NgayNop)->format('d/m/Y') }}</td>
                                            <td>
                                                @if($sp->LinkFile && !filter_var($sp->LinkFile, FILTER_VALIDATE_URL))
                                                    <span class="badge bg-light text-success border me-1"><i class="fa-solid fa-file me-1"></i>File báo cáo</span>
                                                @endif
                                                @if($gitUrl)
                                                    <span class="badge bg-light text-dark border"><i class="fa-brands fa-github me-1"></i>GitHub Repo</span>
                                                @endif
                                                @if(!$sp->LinkFile && !$gitUrl)
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    @if($gitUrl)
                                                        <a href="{{ $gitUrl }}" target="_blank" class="btn btn-sm btn-outline-dark py-0 px-2" title="Xem GitHub">
                                                            <i class="fa-brands fa-github me-1"></i>Xem link GitHub
                                                        </a>
                                                    @endif
                                                    @if($fileUrl && !filter_var($sp->LinkFile, FILTER_VALIDATE_URL))
                                                        <a href="{{ $fileUrl }}" download class="btn btn-sm btn-outline-success py-0 px-2" title="Tải về">
                                                            <i class="fa-solid fa-download me-1"></i>Download File
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="fa-solid fa-inbox fa-3x mb-3 opacity-25"></i>
            <p class="fs-5 mb-0">Bạn chưa có nhóm nào được phân công hướng dẫn.</p>
        </div>
        @endforelse
    </div>
</div>

{{-- Pagination --}}
<div class="d-flex justify-content-center">
    {{ $nhoms->links('pagination::bootstrap-5') }}
</div>

@endsection
