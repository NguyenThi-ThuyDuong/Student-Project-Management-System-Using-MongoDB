@extends($layout)
@section('title', 'Quản Lý Thông Báo')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-primary-custom"><i class="fa-solid fa-bullhorn me-2"></i>Quản Lý Thông Báo</h4>
    <button class="btn btn-success btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="fa-solid fa-plus me-2"></i>Đăng Thông Báo Mới
    </button>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card card-premium">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4">Ngày đăng</th>
                    <th>Đối tượng nhận</th>
                    <th>Tiêu đề</th>
                    <th>Nội dung</th>
                    <th class="text-end px-4">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($thongbaos as $tb)
                <tr>
                    <td class="px-4 text-muted small"><i class="fa-regular fa-clock me-1"></i> {{ date('d/m/Y', strtotime($tb->NgayTao)) }}</td>
                    <td>
                        @if($tb->lopHocPhan)
                            <span class="badge bg-info text-white"><i class="fa-solid fa-graduation-cap me-1"></i>Lớp HP: {{ $tb->lopHocPhan->TenLopHP }}</span>
                        @elseif($tb->lop)
                            <span class="badge bg-primary text-white"><i class="fa-solid fa-users-rectangle me-1"></i>Lớp HC: {{ $tb->lop->TenLop }}</span>
                        @else
                            <span class="badge bg-secondary">Tất cả lớp / Toàn trường</span>
                        @endif
                    </td>
                    <td class="fw-bold">{{ $tb->TieuDe }}</td>
                    <td>
                        <div>{{ Str::limit($tb->NoiDung, 80) }}</div>
                        <div class="mt-1 d-flex flex-wrap gap-1">
                            @if(!empty($tb->FileDinhKem))
                                <a href="{{ asset($tb->FileDinhKem) }}" target="_blank" class="badge bg-light text-primary border text-decoration-none" title="Tải xuống file đính kèm">
                                    <i class="fa-solid fa-paperclip me-1"></i>File đính kèm
                                </a>
                            @endif
                            @if(!empty($tb->DuongDan))
                                <a href="{{ $tb->DuongDan }}" target="_blank" class="badge bg-light text-cyan border text-decoration-none" title="Truy cập liên kết">
                                    <i class="fa-solid fa-link me-1"></i>Đường dẫn liên kết
                                </a>
                            @endif
                        </div>
                    </td>
                    <td class="text-end px-4">
                        @if(($tb->MaTK ?? $tb->NguoiGui ?? '') == (string)Auth::user()->_id || ($tb->MaTK ?? '') == (Auth::user()->MaTK ?? '') || strtolower(Auth::user()->VaiTro ?? '') === 'admin')
                            @php
                                $tbId = (string) ($tb->_id ?? $tb->MaThongBao);
                                $destroyRoute = strtolower(Auth::user()->VaiTro ?? '') === 'admin' ? route('thongbao.destroy', $tbId) : route('giangvien.thongbao.destroy', $tbId);
                                $updateRoute = strtolower(Auth::user()->VaiTro ?? '') === 'admin' ? route('thongbao.update', $tbId) : route('giangvien.thongbao.update', $tbId);
                                $currentTarget = '';
                                if (!empty($tb->MaLopHP)) {
                                    $currentTarget = 'lhp_' . $tb->MaLopHP;
                                } elseif (!empty($tb->MaLop)) {
                                    $currentTarget = 'lh_' . $tb->MaLop;
                                }
                            @endphp

                            <!-- Nút Sửa -->
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-circle me-1" 
                                    data-bs-toggle="modal" data-bs-target="#editModal{{ $tbId }}" title="Sửa thông báo">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>

                            <!-- Nút Xóa -->
                            <form action="{{ $destroyRoute }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa thông báo này?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Xóa thông báo"><i class="fa-solid fa-trash"></i></button>
                            </form>

                            <!-- Modal Chỉnh Sửa Thông Báo -->
                            <div class="modal fade text-start" id="editModal{{ $tbId }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <form action="{{ $updateRoute }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title"><i class="fa-solid fa-pen-to-square me-2"></i>Chỉnh Sửa Thông Báo</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3 text-start">
                                                    <label class="form-label text-muted small fw-bold">
                                                        @if(strtolower(Auth::user()->VaiTro ?? '') === 'admin')
                                                            Gửi tới đối tượng <span class="text-danger">*</span>
                                                        @else
                                                            Gửi tới Lớp phụ trách <span class="text-danger">*</span>
                                                        @endif
                                                    </label>
                                                    <select name="Target" class="form-select">
                                                        <option value="" {{ empty($currentTarget) ? 'selected' : '' }}>
                                                            @if(strtolower(Auth::user()->VaiTro ?? '') === 'admin')
                                                                -- Gửi Tất Cả (Toàn hệ thống / Sinh viên & Giảng viên) --
                                                            @else
                                                                -- Tất cả các lớp phụ trách --
                                                            @endif
                                                        </option>
                                                        @if(isset($lopHocPhans) && $lopHocPhans->isNotEmpty())
                                                            <optgroup label="🎓 Lớp Học Phần (Lớp Tín Chỉ)">
                                                                @foreach($lopHocPhans as $lhp)
                                                                    <option value="lhp_{{ $lhp->MaLopHP }}" {{ $currentTarget === ('lhp_' . $lhp->MaLopHP) ? 'selected' : '' }}>
                                                                        Lớp HP: {{ $lhp->TenLopHP }} ({{ $lhp->monHoc->TenMon ?? '' }})
                                                                    </option>
                                                                @endforeach
                                                            </optgroup>
                                                        @endif
                                                        @if(isset($lops) && $lops->isNotEmpty())
                                                            <optgroup label="🏫 Lớp Hành Chính">
                                                                @foreach($lops as $l)
                                                                    <option value="lh_{{ $l->MaLop }}" {{ $currentTarget === ('lh_' . $l->MaLop) ? 'selected' : '' }}>
                                                                        Lớp HC: {{ $l->TenLop }}
                                                                    </option>
                                                                @endforeach
                                                            </optgroup>
                                                        @endif
                                                    </select>
                                                </div>
                                                <div class="mb-3 text-start">
                                                    <label class="form-label text-muted small fw-bold">Tiêu đề thông báo <span class="text-danger">*</span></label>
                                                    <input type="text" name="TieuDe" class="form-control" value="{{ $tb->TieuDe }}" required placeholder="Nhập tiêu đề...">
                                                </div>
                                                <div class="mb-3 text-start">
                                                    <label class="form-label text-muted small fw-bold">Nội dung <span class="text-danger">*</span></label>
                                                    <textarea name="NoiDung" class="form-control" rows="5" required placeholder="Nhập nội dung chi tiết...">{{ $tb->NoiDung }}</textarea>
                                                </div>
                                                <div class="mb-3 text-start">
                                                    <label class="form-label text-muted small fw-bold">Đường dẫn liên kết (Link tùy chọn)</label>
                                                    <input type="url" name="DuongDan" class="form-control" value="{{ $tb->DuongDan }}" placeholder="https://example.com/tai-lieu-lien-quan">
                                                </div>
                                                <div class="mb-3 text-start">
                                                    <label class="form-label text-muted small fw-bold">File đính kèm (tùy chọn)</label>
                                                    @if(!empty($tb->FileDinhKem))
                                                        <div class="mb-2 p-2 bg-light border rounded-3 d-flex justify-content-between align-items-center">
                                                            <a href="{{ asset($tb->FileDinhKem) }}" target="_blank" class="small fw-bold text-primary text-decoration-none">
                                                                <i class="fa-solid fa-paperclip me-1"></i>File hiện tại
                                                            </a>
                                                            <div class="form-check form-switch mb-0">
                                                                <input class="form-check-input" type="checkbox" name="delete_file" value="1" id="delFile{{ $tbId }}">
                                                                <label class="form-check-label small text-danger" for="delFile{{ $tbId }}">Xóa file</label>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    <input type="file" name="file" class="form-control">
                                                    <small class="text-muted">Chọn file mới để tải lên hoặc thay thế file cũ (Tối đa 10MB)</small>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                                                <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fa-solid fa-floppy-disk me-1"></i>Lưu Thay Đổi</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @else
                            <span class="badge bg-secondary">Từ Ban Quản Trị</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">Bạn chưa đăng thông báo nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Thêm Thông Báo -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        @php
            $storeRoute = strtolower(Auth::user()->VaiTro ?? '') === 'admin' ? route('thongbao.store') : route('giangvien.thongbao.store');
        @endphp
        <form action="{{ $storeRoute }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-pen-to-square me-2"></i>Đăng Thông Báo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">
                            @if(strtolower(Auth::user()->VaiTro ?? '') === 'admin')
                                Gửi tới đối tượng <span class="text-danger">*</span>
                            @else
                                Gửi tới Lớp phụ trách <span class="text-danger">*</span>
                            @endif
                        </label>
                        <select name="Target" class="form-select">
                            <option value="">
                                @if(strtolower(Auth::user()->VaiTro ?? '') === 'admin')
                                    -- Gửi Tất Cả (Toàn hệ thống / Sinh viên & Giảng viên) --
                                @else
                                    -- Tất cả các lớp phụ trách --
                                @endif
                            </option>
                            @if(isset($lopHocPhans) && $lopHocPhans->isNotEmpty())
                                <optgroup label="🎓 Lớp Học Phần (Lớp Tín Chỉ)">
                                    @foreach($lopHocPhans as $lhp)
                                        <option value="lhp_{{ $lhp->MaLopHP }}">Lớp HP: {{ $lhp->TenLopHP }} ({{ $lhp->monHoc->TenMon ?? '' }})</option>
                                    @endforeach
                                </optgroup>
                            @endif
                            @if(isset($lops) && $lops->isNotEmpty())
                                <optgroup label="🏫 Lớp Hành Chính">
                                    @foreach($lops as $l)
                                        <option value="lh_{{ $l->MaLop }}">Lớp HC: {{ $l->TenLop }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Tiêu đề thông báo <span class="text-danger">*</span></label>
                        <input type="text" name="TieuDe" class="form-control" required placeholder="Nhập tiêu đề...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Nội dung <span class="text-danger">*</span></label>
                        <textarea name="NoiDung" class="form-control" rows="5" required placeholder="Nhập nội dung chi tiết..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Đường dẫn liên kết (Link tùy chọn)</label>
                        <input type="url" name="DuongDan" class="form-control" placeholder="https://example.com/tai-lieu-lien-quan">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">File đính kèm (tùy chọn)</label>
                        <input type="file" name="file" class="form-control">
                        <small class="text-muted">Hỗ trợ PDF, Word, Excel, ZIP, Ảnh... (Tối đa 10MB)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Đăng Thông Báo</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
