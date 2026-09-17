<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class DeTai extends Model
{
    protected $table = 'de_tai';
    protected $collection = 'de_tai';

    protected $fillable = [
        'MaDeTai', 'MaDT', 'MaTK', 'MaMon', 'MaLop', 'MaLopHP', 'MaHocKy',
        'TenDeTai', 'MoTa', 'YeuCau', 'FileTaiLieu',
        'TrangThai', 'TrangThaiPheDuyet', 'LyDoPheDuyet', 'LoaiDeTai', 'NhomTuDeXuat_id',
        'HanDangKy', 'HanBaoCao', 'HanNopSanPham', 'NgayTao'
    ];

    public function getTrangThaiPheDuyetAttribute($value)
    {
        return $value ?? 'Đã duyệt';
    }

    public function getMaDeTaiAttribute($value)
    {
        if (!empty($value)) return $value;
        if (!empty($this->attributes['MaDT'])) return $this->attributes['MaDT'];
        return 'DT-' . strtoupper(substr((string)$this->_id, -5));
    }

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'MaTK', '_id');
    }

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'MaTK', 'MaTK');
    }

    public function getGiangVienAttribute()
    {
        if (array_key_exists('giangVien', $this->relations) && $this->relations['giangVien']) {
            return $this->relations['giangVien'];
        }

        $maTK = $this->attributes['MaTK'] ?? null;
        if ($maTK && $maTK !== 'sample_tk_id') {
            $gv = GiangVien::where('_id', $maTK)
                ->orWhere('MaTK', $maTK)
                ->orWhere('MaGV', $maTK)
                ->first();
            if ($gv) return $gv;

            $tk = TaiKhoan::find($maTK);
            if ($tk) {
                $gv = GiangVien::where('MaTK', (string) $tk->_id)
                    ->orWhere('_id', (string) $tk->_id)
                    ->orWhere('Email', 'like', $tk->TenDangNhap . '%')
                    ->first();
                if ($gv) return $gv;
            }
        }

        $maLHP = $this->attributes['MaLopHP'] ?? null;
        if ($maLHP) {
            $lhp = LopHocPhan::where('_id', $maLHP)->orWhere('MaLopHP', $maLHP)->first();
            if ($lhp) {
                $gv = $lhp->getGiangVienModelAttribute();
                if ($gv) return $gv;
            }
        }

        return GiangVien::first();
    }

    public function monHoc()
    {
        return $this->belongsTo(MonHoc::class, 'MaMon', '_id');
    }

    public function getMonHocAttribute()
    {
        $val = $this->attributes['MaMon'] ?? null;
        if (!$val) return null;
        return MonHoc::where('MaMon', $val)->orWhere('_id', $val)->first();
    }

    public function lop()
    {
        return $this->belongsTo(Lop::class, 'MaLop', '_id');
    }

    public function lopHocPhan()
    {
        return $this->belongsTo(LopHocPhan::class, 'MaLopHP', '_id');
    }

    public function getLopHocPhanAttribute()
    {
        $val = $this->attributes['MaLopHP'] ?? null;
        if (!$val) return null;
        return LopHocPhan::where('_id', $val)->orWhere('MaLopHP', $val)->first();
    }

    public function hocKy()
    {
        return $this->belongsTo(HocKy::class, 'MaHocKy', '_id');
    }

    public function getHocKyAttribute()
    {
        $val = $this->attributes['MaHocKy'] ?? null;
        if (!$val) return null;
        return HocKy::where('MaHocKy', $val)->orWhere('MaHK', $val)->orWhere('_id', $val)->first();
    }

    /**
     * Tìm các nhóm đã đăng ký đề tài này (qua embedded DangKyDeTai trong NhomDoAn)
     */
    public function getNhomDangKy()
    {
        $keys = array_values(array_unique(array_filter([
            (string) $this->_id,
            (string) ($this->MaDeTai ?? ''),
            (string) ($this->attributes['MaDeTai'] ?? ''),
            (string) ($this->attributes['MaDT'] ?? ''),
        ])));
        return NhomDoAn::whereIn('DangKyDeTai.MaDeTai', $keys)->get();
    }
}