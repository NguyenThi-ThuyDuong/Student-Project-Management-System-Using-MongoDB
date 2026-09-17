<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class LopHocPhan extends Model
{
    protected $table = 'lop_hoc_phan';
    protected $collection = 'lop_hoc_phan';

    protected $fillable = [
        'MaLopHP',
        'TenLopHP',
        'MaMon',
        'MaHocKy',
        'MaGV',
        'SiSoToiDa',
        'SoThanhVienNhomToiDa',
        'TrangThai',
        'Thu',
        'CaHoc',
        'PhongHoc',
        'DanhSachSinhVien',
    ];

    protected $casts = [
        'DanhSachSinhVien' => 'array',
    ];

    public function getSoThanhVienNhomToiDaAttribute($value)
    {
        return $value ? (int)$value : 5;
    }

    public function monHoc()
    {
        return $this->belongsTo(MonHoc::class, 'MaMon', 'MaMon');
    }

    public function getMonHocModelAttribute()
    {
        if ($this->monHoc) return $this->monHoc;
        return MonHoc::where('MaMon', $this->MaMon)->orWhere('_id', $this->MaMon)->first();
    }

    public function hocKy()
    {
        return $this->belongsTo(HocKy::class, 'MaHocKy', 'MaHocKy');
    }

    public function getHocKyModelAttribute()
    {
        if ($this->hocKy) return $this->hocKy;
        return HocKy::where('MaHocKy', $this->MaHocKy)->orWhere('MaHK', $this->MaHocKy)->orWhere('_id', $this->MaHocKy)->first();
    }

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'MaGV', 'MaGV');
    }

    public function getGiangVienAttribute()
    {
        if (array_key_exists('giangVien', $this->relations) && $this->relations['giangVien']) {
            return $this->relations['giangVien'];
        }
        $val = $this->attributes['MaGV'] ?? null;
        if (!$val) return null;
        return GiangVien::where('MaGV', $val)->orWhere('_id', $val)->orWhere('MaTK', $val)->first();
    }

    public function getGiangVienModelAttribute()
    {
        return $this->giangVien;
    }

    /**
     * Lấy danh sách sinh viên trong Lớp Học Phần
     * Dữ liệu sinh viên được lưu dạng embedded array: DanhSachSinhVien[{MaSV, NgayDangKy}]
     */
    public function getSinhVienIds()
    {
        $ds = $this->DanhSachSinhVien ?? [];
        return collect($ds)->map(fn($item) => $item['MaSV'] ?? $item['sinh_vien_id'] ?? null)->filter()->values();
    }

    /**
     * Kiểm tra sinh viên có trong lớp học phần không
     */
    public function hasSinhVien($maSV)
    {
        $sv = SinhVien::where('MaSV', $maSV)->orWhere('_id', $maSV)->first();
        $targetMaSV = $sv->MaSV ?? (string)$maSV;
        $targetId   = $sv ? (string)$sv->_id : (string)$maSV;

        $ds = $this->DanhSachSinhVien ?? [];
        return collect($ds)->contains(function ($item) use ($targetMaSV, $targetId) {
            $val = (string) ($item['MaSV'] ?? $item['sinh_vien_id'] ?? '');
            return !empty($val) && ($val === $targetMaSV || $val === $targetId);
        });
    }

    /**
     * Thêm sinh viên vào lớp học phần
     */
    public function addSinhVien($maSV, $ngayDangKy = null)
    {
        $sv = SinhVien::where('MaSV', $maSV)->orWhere('_id', $maSV)->first();
        $realMaSV = $sv->MaSV ?? (string)$maSV;

        if ($this->hasSinhVien($realMaSV)) {
            return;
        }

        $ds = $this->DanhSachSinhVien ?? [];
        $ds[] = [
            'MaSV' => $realMaSV,
            'NgayDangKy' => $ngayDangKy ?? now()->toDateTimeString(),
        ];
        $this->update(['DanhSachSinhVien' => array_values($ds)]);
    }

    /**
     * Xóa sinh viên khỏi lớp học phần
     */
    public function removeSinhVien($maSV)
    {
        $sv = SinhVien::where('MaSV', $maSV)->orWhere('_id', $maSV)->first();
        $targetMaSV = $sv->MaSV ?? (string)$maSV;
        $targetId   = $sv ? (string)$sv->_id : (string)$maSV;

        $ds = $this->DanhSachSinhVien ?? [];
        $ds = collect($ds)->reject(function ($item) use ($targetMaSV, $targetId) {
            $val = (string) ($item['MaSV'] ?? $item['sinh_vien_id'] ?? '');
            return empty($val) || $val === $targetMaSV || $val === $targetId;
        })->values()->toArray();

        $this->update(['DanhSachSinhVien' => $ds]);
    }

    /**
     * Lấy đối tượng SinhVien từ embedded data
     */
    public function sinhViens()
    {
        $ids = $this->getSinhVienIds();
        return SinhVien::whereIn('MaSV', $ids)->orWhereIn('_id', $ids)->get();
    }
}
