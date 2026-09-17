@extends('layouts.giangvien')
@section('title', 'Chấm Điểm Đồ Án Cuối Kỳ')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 text-primary-custom"><i class="fa-solid fa-gavel me-2"></i>Chấm Điểm Đồ Án Cuối Kỳ</h4>
        <small class="text-muted">Đánh giá sản phẩm, điểm báo cáo, điểm bảo vệ và tổng kết kết quả học phần</small>
    </div>
    <span class="badge bg-primary rounded-pill px-3 py-2"><i class="fa-solid fa-layer-group me-1"></i>{{ $nhoms->count() }} Nhóm đồ án</span>
</div>

<div class="row">
    @forelse($nhoms as $nhom)
    <div class="col-md-6 mb-4">
        <div class="card card-premium h-100 shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <span class="fw-bold text-dark"><i class="fa-solid fa-users text-primary me-2"></i>{{ $nhom->TenNhom }}</span>
                    <div class="small text-muted mt-1"><i class="fa-solid fa-book me-1"></i>{{ $nhom->monHoc->TenMon ?? '—' }}</div>
                </div>
                @if($nhom->chamDiem)
                <span class="badge bg-success rounded-pill px-3 py-2">
                    <i class="fa-solid fa-check-circle me-1"></i>Điểm Tổng: <strong>{{ number_format((float)($nhom->chamDiem->DiemTong ?? 0), 1) }}</strong>
                </span>
                @else
                <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                    <i class="fa-solid fa-hourglass-half me-1"></i>Chưa chấm điểm
                </span>
                @endif
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.75rem;"><i class="fa-solid fa-graduation-cap me-1"></i>Đề Tài Đăng Ký:</small>
                    <div class="fw-bold text-primary mt-1">{{ $nhom->getTenDeTaiDangKy() }}</div>
                </div>

                <div class="mb-3">
                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.75rem;"><i class="fa-solid fa-box-archive me-1 text-primary"></i>Sản Phẩm &amp; Tài Liệu Đã Nộp:</small>
                    <div class="mt-2">
                    @forelse($nhom->sanPhams as $sp)
                        <div class="border rounded-3 p-3 mb-2 bg-light shadow-2xs border-start border-4 border-primary">
                            <div class="fw-bold text-dark mb-1 small">
                                <i class="fa-solid fa-file-lines text-warning me-2"></i>{{ $sp->TenSanPham }}
                                @if($sp->NgayNop)
                                <span class="badge bg-white text-muted border ms-2 fw-normal" style="font-size: 0.7rem;">
                                    <i class="fa-regular fa-clock me-1"></i>{{ \Carbon\Carbon::parse($sp->NgayNop)->format('d/m/Y') }}
                                </span>
                                @endif
                            </div>
                            
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                {{-- 1. File đính kèm --}}
                                @if($sp->LinkFile && !filter_var($sp->LinkFile, FILTER_VALIDATE_URL))
                                    @php
                                        $filePath = asset(ltrim($sp->LinkFile, '/'));
                                    @endphp
                                    <a href="{{ $filePath }}" target="_blank" download class="btn btn-sm btn-outline-success rounded-pill px-3 py-1" style="font-size: 0.78rem;">
                                        <i class="fa-solid fa-download me-1"></i>Tải File Báo Cáo / Code (.ZIP/.PDF)
                                    </a>
                                @elseif($sp->LinkFile && filter_var($sp->LinkFile, FILTER_VALIDATE_URL))
                                    <a href="{{ $sp->LinkFile }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1" style="font-size: 0.78rem;">
                                        <i class="fa-brands fa-github me-1"></i>Xem Link Repository / Drive
                                    </a>
                                @endif

                                {{-- 2. Link Source Code --}}
                                @if($sp->LinkSourceCode)
                                    <a href="{{ $sp->LinkSourceCode }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1" style="font-size: 0.78rem;">
                                        <i class="fa-brands fa-github me-1"></i>GitHub Source Code
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-warning border-0 text-dark small py-2 text-center mb-2 rounded-3">
                            <i class="fa-solid fa-circle-exclamation me-1"></i>Nhóm chưa nộp sản phẩm đồ án nào.
                        </div>
                    @endforelse
                    </div>
                </div>
                
                <hr class="my-3">
                
                <form action="{{ route('giangvien.chamdiem.store', (string)$nhom->_id) }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label text-muted small fw-bold">Điểm Báo Cáo (50%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.1" min="0" max="10" name="DiemBaoCao" class="form-control" value="{{ $nhom->chamDiem->DiemBaoCao ?? '' }}" required placeholder="0 - 10">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small fw-bold">Điểm Bảo Vệ (50%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.1" min="0" max="10" name="DiemBaoVe" class="form-control" value="{{ $nhom->chamDiem->DiemBaoVe ?? '' }}" required placeholder="0 - 10">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Nhận Xét / Đánh Giá Cuối Kỳ</label>
                        <textarea name="NhanXet" class="form-control" rows="2" placeholder="Nhập nhận xét tổng quan về đồ án của nhóm...">{{ $nhom->chamDiem->NhanXet ?? '' }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary-custom w-100 rounded-pill">
                        <i class="fa-solid fa-check me-2"></i>{{ $nhom->chamDiem ? 'Cập Nhật Kết Quả Chấm Điểm' : 'Lưu Điểm & Thông Báo Cho Nhóm' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-info text-center py-5 rounded-4 shadow-sm">
            <i class="fa-solid fa-folder-open fa-3x mb-3 text-info opacity-50"></i>
            <h5 class="fw-bold mb-1">Chưa tìm thấy nhóm đồ án nào</h5>
            <p class="mb-0 text-muted">Hiện chưa có nhóm nào được phân công hướng dẫn hoặc phụ trách trong các lớp học phần.</p>
        </div>
    </div>
    @endforelse
</div>
@endsection
