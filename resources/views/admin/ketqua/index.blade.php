@extends('layouts.admin')
@section('page_title', 'Tổng Hợp Đề Tài & Kết Quả')

@section('content')
<div class="page-header-zone mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="fw-bold mb-1"><i class="fa-solid fa-square-poll-vertical text-cyan me-2"></i>Kết Quả Đồ Án Toàn Hệ Thống</h1>
        <div class="text-muted small">Bảng tổng hợp điểm số đồ án, đánh giá điểm báo cáo, điểm bảo vệ và xếp loại học tập sinh viên</div>
    </div>
    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="fa-solid fa-print me-1"></i>In / Xuất Báo Cáo
    </button>
</div>

{{-- BANNER STATS SUMMARY --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card card-modern border-0 p-3 bg-gradient-sky text-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-white-50 small fw-bold text-uppercase">Tổng Số Nhóm Đồ Án</span>
                    <h3 class="mb-0 fw-extrabold text-white mt-1">{{ number_format($totalGroups ?? 0) }}</h3>
                </div>
                <div class="rounded-circle bg-white bg-opacity-20 p-3">
                    <i class="fa-solid fa-users-gear fs-3 text-white"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-modern border-0 p-3 bg-gradient-indigo text-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-white-50 small fw-bold text-uppercase">Đã Hoàn Thành Chấm Điểm</span>
                    <h3 class="mb-0 fw-extrabold text-white mt-1">{{ number_format($gradedGroups ?? 0) }}</h3>
                </div>
                <div class="rounded-circle bg-white bg-opacity-20 p-3">
                    <i class="fa-solid fa-square-check fs-3 text-white"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-modern border-0 p-3 bg-gradient-cyan text-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-white-50 small fw-bold text-uppercase">Chưa Có Kết Quả Cuối Kỳ</span>
                    <h3 class="mb-0 fw-extrabold text-white mt-1">{{ number_format($ungradedGroups ?? 0) }}</h3>
                </div>
                <div class="rounded-circle bg-white bg-opacity-20 p-3">
                    <i class="fa-solid fa-hourglass-half fs-3 text-white"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- BỘ LỌC TÌM KIẾM TRỌNG TÂM --}}
<div class="card card-modern mb-4">
    <div class="card-body p-3">
        <form action="{{ route('admin.ketqua.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <label class="form-label small fw-bold mb-1"><i class="fa-solid fa-calendar-days text-primary me-1"></i>Học Kỳ</label>
                <select name="maHK" class="form-select form-select-sm border-soft" onchange="this.form.submit()">
                    <option value="">-- Tất cả Học Kỳ --</option>
                    @foreach($hocKys as $hk)
                        <option value="{{ $hk->MaHK }}" {{ request('maHK') == $hk->MaHK ? 'selected' : '' }}>
                            {{ $hk->TenHK }} ({{ $hk->NamHoc }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold mb-1"><i class="fa-solid fa-book-open text-primary me-1"></i>Học Phần</label>
                <select name="maMon" class="form-select form-select-sm border-soft" onchange="this.form.submit()">
                    <option value="">-- Tất cả Học Phần --</option>
                    @foreach($monHocs as $mh)
                        <option value="{{ $mh->MaMon }}" {{ request('maMon') == $mh->MaMon ? 'selected' : '' }}>
                            {{ $mh->TenMon }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold mb-1"><i class="fa-solid fa-magnifying-glass text-primary me-1"></i>Từ Khóa</label>
                <input type="text" name="keyword" class="form-select form-select-sm border-soft" placeholder="Tên nhóm, mã nhóm, tên đề tài..." value="{{ request('keyword') }}">
            </div>
            <div class="col-md-2 d-flex gap-1 align-self-end">
                <button type="submit" class="btn btn-cyan btn-sm w-100 fw-bold">
                    <i class="fa-solid fa-filter me-1"></i>Lọc
                </button>
                <a href="{{ route('admin.ketqua.index') }}" class="btn btn-light btn-sm border" title="Xóa bộ lọc">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- BẢNG DỮ LIỆU KẾT QUẢ SAAS TABLE --}}
<div class="card card-modern shadow-sm mb-4">
    <div class="card-modern-header">
        <span><i class="fa-solid fa-square-poll-vertical me-2"></i>Bảng Tổng Hợp Điểm Số Đồ Án ({{ $danhSach->total() }})</span>
        <span class="badge bg-white text-dark px-3 py-1 rounded-pill">Trang {{ $danhSach->currentPage() }}/{{ $danhSach->lastPage() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-saas-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="4%" class="text-center">STT</th>
                        <th width="20%">Nhóm Thực Hiện</th>
                        <th width="26%">Tên Đề Tài Đồ Án</th>
                        <th width="18%">Giảng Viên Hướng Dẫn</th>
                        <th width="8%" class="text-center">Điểm Báo Cáo</th>
                        <th width="8%" class="text-center">Điểm Bảo Vệ</th>
                        <th width="8%" class="text-center">Tổng Điểm</th>
                        <th width="8%" class="text-center pe-3">Xếp Loại</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($danhSach as $key => $item)
                        @php
                            $hasScore = ($item->nhomDoAn && $item->nhomDoAn->chamDiem);
                            $bc = 0; $bv = 0; $tongDiem = 0; $diemHe4 = 0.0;
                            $xepLoai = 'Chưa chấm'; $badgeClass = 'bg-secondary text-white';

                            if ($hasScore) {
                                $bc = floatval($item->nhomDoAn->chamDiem->DiemBaoCao ?? 0);
                                $bv = floatval($item->nhomDoAn->chamDiem->DiemBaoVe ?? 0);
                                $tongDiem = ($bc + $bv) / 2;

                                if ($tongDiem >= 8.5) { $diemHe4 = 4.0; $xepLoai = 'Xuất sắc'; $badgeClass = 'badge-success'; }
                                elseif ($tongDiem >= 8.0) { $diemHe4 = 3.5; $xepLoai = 'Giỏi'; $badgeClass = 'badge-cyan'; }
                                elseif ($tongDiem >= 7.0) { $diemHe4 = 3.0; $xepLoai = 'Khá'; $badgeClass = 'badge-indigo'; }
                                elseif ($tongDiem >= 5.5) { $diemHe4 = 2.0; $xepLoai = 'Trung bình'; $badgeClass = 'badge bg-warning text-dark'; }
                                elseif ($tongDiem >= 4.0) { $diemHe4 = 1.0; $xepLoai = 'Yếu'; $badgeClass = 'badge-danger'; }
                                else { $diemHe4 = 0.0; $xepLoai = 'Kém'; $badgeClass = 'badge-danger'; }
                            }
                        @endphp
                        <tr>
                            <td class="text-center fw-bold text-muted">{{ $danhSach->firstItem() + $key }}</td>
                            <td>
                                <span class="badge-indigo text-nowrap"><i class="fa-solid fa-users me-1"></i>{{ $item->TenNhom ?? 'Nhóm' }}</span>
                                <small class="text-muted text-nowrap d-block mt-1">TN: {{ $item->svTruongNhom->HoTen ?? 'Chưa phân công' }}</small>
                            </td>
                            <td>
                                <span class="fw-bold text-dark d-block" style="line-height: 1.4;" title="{{ $item->deTai->TenDeTai ?? 'Chưa đăng ký' }}">
                                    {{ $item->deTai->TenDeTai ?? 'Chưa đăng ký' }}
                                </span>
                            </td>
                            <td>
                                <span class="small text-dark fw-medium text-nowrap"><i class="fa-solid fa-chalkboard-user text-cyan me-1"></i>{{ $item->giangVien->HoTen ?? 'Chưa phân công' }}</span>
                            </td>
                            <td class="text-center fw-bold">
                                {{ $hasScore ? number_format($bc, 1) : '—' }}
                            </td>
                            <td class="text-center fw-bold">
                                {{ $hasScore ? number_format($bv, 1) : '—' }}
                            </td>
                            <td class="text-center fw-bold text-primary fs-6">
                                {{ $hasScore ? number_format($tongDiem, 1) : '—' }}
                            </td>
                            <td class="text-center pe-3">
                                <span class="{{ $badgeClass }} px-3 py-1 text-nowrap">{{ $xepLoai }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-folder-open fs-1 d-block mb-2 opacity-50"></i>
                                Chưa tìm thấy kết quả đồ án nào phù hợp.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($danhSach->hasPages())
    <div class="card-footer bg-white border-0 py-3 d-flex justify-content-center">
        {{ $danhSach->links() }}
    </div>
    @endif
</div>
@endsection
