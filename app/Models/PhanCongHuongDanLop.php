<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class PhanCongHuongDanLop extends Model
{
    protected $table = 'phan_cong_huong_dan_lop';
    protected $collection = 'phan_cong_huong_dan_lop';

    protected $fillable = ['MaPhanCong', 'MaGV', 'MaLop', 'MaHocKy', 'NgayPhanCong'];

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

    public function lop()
    {
        return $this->belongsTo(Lop::class, 'MaLop', 'MaLop');
    }

    public function getLopAttribute()
    {
        if (array_key_exists('lop', $this->relations) && $this->relations['lop']) {
            return $this->relations['lop'];
        }
        $val = $this->attributes['MaLop'] ?? null;
        if (!$val) return null;
        return Lop::where('MaLop', $val)->orWhere('_id', $val)->first();
    }

    public function getLopModelAttribute()
    {
        return $this->lop;
    }

    public function hocKy()
    {
        return $this->belongsTo(HocKy::class, 'MaHocKy', 'MaHocKy');
    }

    public function getHocKyAttribute()
    {
        if (array_key_exists('hocKy', $this->relations) && $this->relations['hocKy']) {
            return $this->relations['hocKy'];
        }
        $val = $this->attributes['MaHocKy'] ?? null;
        if (!$val) return null;
        return HocKy::where('MaHocKy', $val)->orWhere('MaHK', $val)->orWhere('_id', $val)->first();
    }

    public function getHocKyModelAttribute()
    {
        return $this->hocKy;
    }
}
