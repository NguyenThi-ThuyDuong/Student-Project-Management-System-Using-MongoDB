@extends('layouts.giangvien')
@section('page_title', 'Quản Lý & Duyệt Báo Cáo Tiến Độ')
@section('content')

<!-- BỘ LỌC PHÂN CẤP 5 TẦNG: HỌC KỲ -> LỚP HỌC PHẦN -> NHÓM -> ĐỀ TÀI -> TIẾN ĐỘ -->
<div class="card card-premium mb-4 shadow-sm">
    <div class="card-body p-3">
        <form action="{{ route('giangvien.baocao.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1"><i class="fa-regular fa-calendar-check text-primary me-1"></i>Học Kỳ:</label>
                <select name="MaHocKy" class="form-select border-primary rounded-pill small" onchange="this.form.submit()">
                    <option value="">-- Tất cả học kỳ --</option>
                    @foreach($hocKies as $hk)
                        <option value="{{ $hk->_id }}" {{ request('MaHocKy') == (string)$hk->_id ? 'selected' : '' }}>
                            {{ $hk->TenHocKy ?? $hk->TenHK }} ({{ $hk->NamHoc }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted mb-1"><i class="fa-solid fa-graduation-cap text-primary me-1"></i>Lớp Học Phần:</label>
                <select name="MaLopHP" class="form-select border-primary rounded-pill small" onchange="this.form.submit()">
                    <option value="">-- Tất cả Lớp Học Phần --</option>
                    @foreach($lopHocPhans as $lhp)
                        <option value="{{ $lhp->_id }}" {{ (request('MaLopHP') == (string)$lhp->_id || request('MaLopHP') == $lhp->MaLopHP) ? 'selected' : '' }}>
                            [{{ $lhp->TenLopHP }}] - {{ $lhp->monHoc->TenMon ?? 'Môn học' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1"><i class="fa-solid fa-filter text-primary me-1"></i>Trạng Thái Báo Cáo:</label>
                <select name="TrangThaiBaoCao" class="form-select border-primary rounded-pill small" onchange="this.form.submit()">
                    <option value="">-- Tất cả báo cáo --</option>
                    <option value="chu_a_nhan_xet" {{ request('TrangThaiBaoCao') == 'chu_a_nhan_xet' ? 'selected' : '' }}>⏳ Chưa nhận xét</option>
                    <option value="da_nhan_xet" {{ request('TrangThaiBaoCao') == 'da_nhan_xet' ? 'selected' : '' }}>✓ Đã nhận xét</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <a href="{{ route('giangvien.baocao.index') }}" class="btn btn-light rounded-pill w-100 small mt-3">
                    <i class="fa-solid fa-rotate-left me-1"></i>Đặt lại
                </a>
            </div>
        </form>
    </div>
</div>

<!-- KHU VỰC CẢNH BÁO CHẬM TIẾN ĐỘ -->
@if($chamTienDoNhoms->isNotEmpty())
<div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
    <div class="d-flex align-items-center mb-2">
        <i class="fa-solid fa-triangle-exclamation fa-2x text-danger me-3"></i>
        <div>
            <h6 class="fw-bold mb-0 text-danger"><i class="fa-solid fa-bell me-1"></i>CẢNH BÁO: Phát hiện {{ $chamTienDoNhoms->count() }} Nhóm Đang CHẬM TIẾN ĐỘ / QUÁ HẠN BÁO CÁO!</h6>
            <p class="mb-0 small text-dark">Các nhóm dưới đây chưa gửi báo cáo hoặc đã quá hạn nộp báo cáo mốc tiến độ theo quy định.</p>
        </div>
    </div>
    <div class="table-responsive bg-white rounded-3 p-2 border border-danger-subtle mt-2">
        <table class="table table-sm table-hover align-middle mb-0 small">
            <thead class="table-danger text-danger">
                <tr>
                    <th>Tên Nhóm</th>
                    <th>Đề Tài Đăng Ký</th>
                    <th>Lý Do Cảnh Báo</th>
                    <th class="text-end">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                @foreach($chamTienDoNhoms as $cn)
                <tr>
                    <td class="fw-bold text-dark">{{ $cn->TenNhom }}</td>
                    <td class="text-primary">{{ $cn->deTaiDangKy->TenDeTai ?? 'Chưa rõ đề tài' }}</td>
                    <td><span class="badge bg-danger text-white">{{ $cn->lyDoChamTienDo }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('giangvien.thongbao.index') }}" class="btn btn-xs btn-outline-danger rounded-pill px-2">
                            <i class="fa-solid fa-paper-plane me-1"></i>Gửi Nhắc Nhở
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- DANH SÁCH BÁO CÁO TIẾN ĐỘ CỦA SINH VIÊN -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold text-primary mb-0"><i class="fa-solid fa-list-check me-2"></i>Danh Sách Mốc Tiến Độ Đã Nộp ({{ $baocaos->count() }})</h5>
    <span class="badge bg-info text-dark rounded-pill px-3">Quản lý theo phân cấp Học Kỳ &rarr; Lớp HP &rarr; Nhóm</span>
</div>

@if($baocaos->isNotEmpty())
    <div class="row g-4">
        @foreach($baocaos as $bc)
            @php
                $nhomObj = $bc->nhom ?? null;
                $tenNhom = $nhomObj ? $nhomObj->TenNhom : 'Nhóm đồ án';
                $maBc = (string) (is_array($bc) ? ($bc['MaBaoCao'] ?? $bc['_id'] ?? '') : ($bc->MaBaoCao ?? $bc->_id ?? ''));
                $nhanXets = collect(is_array($bc) ? ($bc['NhanXet'] ?? []) : ($bc->NhanXet ?? []));
                $daNhanXet = $nhanXets->isNotEmpty();
                $noiDungBc = is_array($bc) ? ($bc['NoiDung'] ?? '') : ($bc->NoiDung ?? '');
                $fileBc = is_array($bc) ? ($bc['FileBaoCao'] ?? '') : ($bc->FileBaoCao ?? '');
                $lanBc = is_array($bc) ? ($bc['LanBaoCao'] ?? 1) : ($bc->LanBaoCao ?? 1);
                $ngayNop = is_array($bc) ? ($bc['NgayNop'] ?? '') : ($bc->NgayNop ?? '');
            @endphp
            <div class="col-12">
                <div class="card card-premium shadow-sm border-0">
                    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom flex-wrap gap-2">
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-2 rounded-pill fs-6">
                                <i class="fa-solid fa-users me-1"></i> {{ $tenNhom }}
                            </span>
                            <span class="badge bg-primary fw-bold px-3 py-2 rounded-pill">
                                <i class="fa-solid fa-flag me-1"></i> Mốc Tiến Độ Lần {{ $lanBc }}
                            </span>
                            @if(!empty($bc->lopHocPhan))
                                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill small">
                                    <i class="fa-solid fa-graduation-cap me-1 text-primary"></i>Lớp: {{ $bc->lopHocPhan->TenLopHP }}
                                </span>
                            @endif
                            @if(!empty($ngayNop))
                                <span class="text-muted small ms-2">
                                    <i class="fa-regular fa-clock me-1"></i> Nộp ngày: {{ date('d/m/Y', strtotime($ngayNop)) }}
                                </span>
                            @endif
                        </div>
                        <div>
                            @if($daNhanXet)
                                <span class="badge bg-success-subtle text-success border border-success px-3 py-2 rounded-pill fw-bold">
                                    <i class="fa-solid fa-circle-check me-1"></i> Đã Đánh Giá / Nhận Xét ({{ $nhanXets->count() }})
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-dark border border-warning px-3 py-2 rounded-pill fw-bold">
                                    <i class="fa-solid fa-hourglass-half me-1"></i> Chờ Giảng Viên Đánh Giá
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="card-body p-4">
                        @if(!empty($bc->deTai))
                            <div class="mb-3">
                                <strong class="text-dark small"><i class="fa-solid fa-book text-primary me-1"></i>Đề Tài:</strong>
                                <span class="text-primary fw-bold ms-1">{{ $bc->deTai->TenDeTai }}</span>
                            </div>
                        @endif

                        <div class="mb-3 p-3 bg-light rounded-3 border-start border-4 border-primary">
                            <h6 class="fw-bold text-dark mb-2">
                                <i class="fa-solid fa-align-left me-2 text-primary"></i>Nội dung báo cáo cập nhật tiến độ:
                            </h6>
                            <p class="mb-0 text-secondary" style="white-space: pre-line;">{{ $noiDungBc }}</p>
                        </div>

                        <!-- Tải file hoặc Github Link -->
                        @if(!empty($fileBc))
                            @php
                                $isUrlBc = filter_var($fileBc, FILTER_VALIDATE_URL) || str_starts_with($fileBc, 'http://') || str_starts_with($fileBc, 'https://');
                                $targetUrlBc = $isUrlBc ? $fileBc : asset(str_starts_with($fileBc, '/') ? $fileBc : '/' . $fileBc);
                            @endphp
                            <div class="mb-3">
                                <span class="fw-bold text-dark small me-2"><i class="fa-solid fa-paperclip me-1 text-muted"></i>Tài liệu / Sản phẩm đính kèm:</span>
                                @if($isUrlBc)
                                    <a href="{{ $fileBc }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 me-2 shadow-sm">
                                        <i class="fa-brands fa-github me-1"></i> Link Github / Online Document
                                    </a>
                                @else
                                    <a href="{{ $targetUrlBc }}" download class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 me-2 shadow-sm">
                                        <i class="fa-solid fa-download me-1"></i> Tải File Báo Cáo Tiến Độ
                                    </a>
                                @endif
                            </div>
                        @endif

                        <!-- Danh sách nhận xét của Giảng viên -->
                        @if($daNhanXet)
                            <div class="mt-4 pt-3 border-top">
                                <h6 class="fw-bold text-secondary mb-3">
                                    <i class="fa-solid fa-comments me-2 text-success"></i>Lịch sử nhận xét &amp; Xác nhận của Giảng viên:
                                </h6>
                                <div class="timeline-huit ps-2">
                                    @foreach($nhanXets as $nx)
                                        @php
                                            $noiDungNx = is_array($nx) ? ($nx['NoiDung'] ?? '') : ($nx->NoiDung ?? '');
                                            $ngayNx = is_array($nx) ? ($nx['NgayNhanXet'] ?? '') : ($nx->NgayNhanXet ?? '');
                                        @endphp
                                        <div class="timeline-item mb-3">
                                            <div class="d-flex align-items-start gap-3">
                                                <div class="badge bg-success rounded-circle p-2 text-white">
                                                    <i class="fa-solid fa-user-check"></i>
                                                </div>
                                                <div class="bg-light p-3 rounded-3 flex-grow-1 border">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span class="fw-bold text-dark small">Giảng viên Phản hồi</span>
                                                        @if($ngayNx)
                                                            <span class="text-muted small"><i class="fa-regular fa-clock me-1"></i>{{ date('d/m/Y', strtotime($ngayNx)) }}</span>
                                                        @endif
                                                    </div>
                                                    <p class="mb-0 text-dark small" style="white-space: pre-line;">{{ $noiDungNx }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="card-footer bg-light py-3 px-4 text-end border-top">
                        <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#nxModal{{ $maBc }}">
                            <i class="fa-solid fa-pen-to-square me-1"></i> {{ $daNhanXet ? 'Thêm Nhận Xét Mới' : 'Viết Nhận Xét &amp; Đánh Giá' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- MODAL NHẬN XÉT -->
            <div class="modal fade" id="nxModal{{ $maBc }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <form action="{{ route('giangvien.baocao.nhanxet', $maBc) }}" method="POST">
                        @csrf
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title fw-bold">
                                    <i class="fa-solid fa-comment-dots me-2"></i>Nhận Xét Tiến Độ Lần {{ $lanBc }} - {{ $tenNhom }}
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4 text-start">
                                <div class="p-3 bg-light rounded-3 mb-3 border">
                                    <p class="mb-1 fw-bold small text-muted">Trích yếu tiến độ sinh viên nộp:</p>
                                    <p class="mb-0 small text-dark">{{ Str::limit($noiDungBc, 140) }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-dark fw-bold small">
                                        Nội dung nhận xét &amp; đánh giá xác nhận mốc <span class="text-danger">*</span>
                                    </label>
                                    <textarea name="NoiDung" class="form-control" rows="4" required placeholder="Nhập nhận xét, hướng dẫn hoàn thiện hoặc yêu cầu bổ sung cho nhóm sinh viên..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer bg-light px-4 py-3">
                                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                                <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fa-solid fa-paper-plane me-1"></i>Lưu Nhận Xét</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="card card-premium p-5 text-center shadow-sm">
        <i class="fa-solid fa-list-check fa-4x text-muted mb-3 opacity-50"></i>
        <h5 class="fw-bold text-dark mb-1">Chưa có báo cáo tiến độ nào phù hợp</h5>
        <p class="text-muted small mb-0">Thay đổi bộ lọc Học kỳ hoặc Lớp Học Phần ở trên để xem các mốc báo cáo tiến độ của sinh viên.</p>
    </div>
@endif

@endsection
