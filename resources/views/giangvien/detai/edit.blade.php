@extends('layouts.giangvien')
@section('page_title', 'Sửa Đề Tài')
@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">

<div class="card card-premium">
    <div class="card-header-premium">
        <i class="fa-solid fa-pen-to-square me-2 text-warning"></i>Cập Nhật Đề Tài
        <small class="text-muted ms-2">#{{ $detai->MaDeTai ?? substr((string)$detai->_id, -6) }}</small>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('giangvien.detai.update', $detai->_id) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')

            {{-- Tên đề tài --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Tên Đề Tài <span class="text-danger">*</span></label>
                <input type="text" name="TenDeTai" class="form-control @error('TenDeTai') is-invalid @enderror"
                       value="{{ old('TenDeTai', $detai->TenDeTai) }}" required>
                @error('TenDeTai')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Môn học, Học kỳ, Lớp --}}
            {{-- Lớp học phần --}}
            <div class="mb-3 p-3 bg-light rounded border border-primary-subtle">
                <label class="form-label fw-bold text-primary"><i class="fa-solid fa-graduation-cap me-1"></i>Lớp Học Phần (Lớp Tín Chỉ) <span class="text-danger">*</span></label>
                <select name="MaLopHP" id="select_MaLopHP" class="form-select border-primary @error('MaLopHP') is-invalid @enderror" required onchange="onLopHPSelectChange(this)">
                    <option value="">— Chọn Lớp Học Phần —</option>
                    @foreach($lopHocPhans as $lhp)
                        <option value="{{ $lhp->_id }}" data-mamon="{{ $lhp->MaMon }}" data-mahocky="{{ $lhp->MaHocKy }}" {{ (old('MaLopHP', $detai->MaLopHP) == $lhp->_id || old('MaLopHP', $detai->MaLopHP) == $lhp->MaLopHP) ? 'selected' : '' }}>
                            {{ $lhp->TenLopHP }} — {{ $lhp->monHoc->TenMon ?? '' }} ({{ $lhp->hocKy->TenHocKy ?? '' }} - GV: {{ $lhp->giangVien->HoTen ?? 'Chưa gán' }})
                        </option>
                    @endforeach
                </select>
                @error('MaLopHP')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Môn Học</label>
                    <select name="MaMon" id="select_MaMon" class="form-select @error('MaMon') is-invalid @enderror">
                        <option value="">— Chọn Môn Học —</option>
                        @foreach($monhocs as $m)
                            <option value="{{ $m->MaMon }}" {{ old('MaMon', $detai->MaMon) == $m->MaMon || old('MaMon', $detai->MaMon) == $m->_id ? 'selected' : '' }}>
                                {{ $m->TenMon }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Học Kỳ</label>
                    <select name="MaHocKy" id="select_MaHocKy" class="form-select @error('MaHocKy') is-invalid @enderror">
                        <option value="">— Chọn Học Kỳ —</option>
                        @foreach($hockys as $hk)
                            <option value="{{ $hk->MaHocKy }}" {{ old('MaHocKy', $detai->MaHocKy) == $hk->MaHocKy || old('MaHocKy', $detai->MaHocKy) == $hk->MaHK || old('MaHocKy', $detai->MaHocKy) == $hk->_id ? 'selected' : '' }}>
                                {{ $hk->TenHocKy }} ({{ $hk->NamHoc }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <script>
            function onLopHPSelectChange(selectEl) {
                const opt = selectEl.options[selectEl.selectedIndex];
                const maMon = opt.getAttribute('data-mamon');
                const maHocKy = opt.getAttribute('data-mahocky');
                if (maMon) {
                    const mSel = document.getElementById('select_MaMon');
                    if (mSel) mSel.value = maMon;
                }
                if (maHocKy) {
                    const hkSel = document.getElementById('select_MaHocKy');
                    if (hkSel) hkSel.value = maHocKy;
                }
            }
            </script>

            {{-- Trạng thái --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Trạng Thái</label>
                <select name="TrangThai" class="form-select">
                    <option value="Đang mở đăng ký" {{ $detai->TrangThai == 'Đang mở đăng ký' ? 'selected' : '' }}>Đang mở đăng ký</option>
                    <option value="Đã đăng ký"      {{ $detai->TrangThai == 'Đã đăng ký'      ? 'selected' : '' }}>Đã đăng ký</option>
                    <option value="Đã đóng"          {{ $detai->TrangThai == 'Đã đóng'          ? 'selected' : '' }}>Đã đóng</option>
                </select>
            </div>

            {{-- Deadlines --}}
            <div class="p-3 bg-light rounded-3 border mb-3">
                <p class="fw-semibold mb-2"><i class="fa-solid fa-calendar-days me-2 text-primary"></i>Cài đặt Thời hạn</p>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label small fw-semibold">Hạn Đăng Ký <span class="text-danger">*</span></label>
                        <input type="date" name="HanDangKy" class="form-control @error('HanDangKy') is-invalid @enderror"
                               value="{{ old('HanDangKy', $detai->HanDangKy ? \Carbon\Carbon::parse($detai->HanDangKy)->format('Y-m-d') : '') }}" required>
                        @error('HanDangKy')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label small fw-semibold">Hạn Nộp Báo Cáo <span class="text-danger">*</span></label>
                        <input type="date" name="HanBaoCao" class="form-control @error('HanBaoCao') is-invalid @enderror"
                               value="{{ old('HanBaoCao', $detai->HanBaoCao ? \Carbon\Carbon::parse($detai->HanBaoCao)->format('Y-m-d') : '') }}" required>
                        @error('HanBaoCao')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label small fw-semibold">Hạn Nộp Sản Phẩm <span class="text-danger">*</span></label>
                        <input type="date" name="HanNopSanPham" class="form-control @error('HanNopSanPham') is-invalid @enderror"
                               value="{{ old('HanNopSanPham', $detai->HanNopSanPham ? \Carbon\Carbon::parse($detai->HanNopSanPham)->format('Y-m-d') : '') }}" required>
                        @error('HanNopSanPham')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- Mô tả & Yêu cầu --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Mô Tả</label>
                <textarea name="MoTa" rows="3" class="form-control">{{ old('MoTa', $detai->MoTa) }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Yêu Cầu Cụ Thể</label>
                <textarea name="YeuCau" rows="3" class="form-control">{{ old('YeuCau', $detai->YeuCau) }}</textarea>
            </div>

            {{-- File tài liệu đính kèm / Đề cương --}}
            <div class="mb-3 p-3 bg-light rounded-3 border">
                <label class="form-label fw-bold text-dark"><i class="fa-solid fa-paperclip me-1 text-primary"></i>Cập nhật File Đề Cương / Tài Liệu Đính Kèm</label>
                @if($detai->FileTaiLieu)
                    <div class="alert alert-info py-2 small d-flex justify-content-between align-items-center mb-2">
                        <span><i class="fa-solid fa-file-lines me-1"></i>File hiện tại: <code>{{ basename($detai->FileTaiLieu) }}</code></span>
                        <a href="{{ route('giangvien.detai.downloadTaiLieu', $detai->_id) }}" class="btn btn-sm btn-info text-white rounded-pill px-3">
                            <i class="fa-solid fa-download me-1"></i>Tải về
                        </a>
                    </div>
                @endif
                <input type="file" name="file_tai_lieu" class="form-control" accept=".pdf,.doc,.docx,.zip,.rar">
                <div class="form-text small text-muted">Chọn file mới nếu muốn thay thế tệp đề cương hiện tại (Định dạng: .pdf, .docx, .zip - Tối đa 20MB).</div>
            </div>

            <div class="text-end mt-4 d-flex gap-2 justify-content-end">
                <a href="{{ route('giangvien.detai.index') }}" class="btn btn-light rounded-pill px-4">Huỷ</a>
                <button type="submit" class="btn btn-warning rounded-pill px-4 text-white">
                    <i class="fa-solid fa-floppy-disk me-1"></i>Cập Nhật
                </button>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection