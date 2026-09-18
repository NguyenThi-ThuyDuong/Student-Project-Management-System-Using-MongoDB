<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class SinhVien extends Model
{
    protected $table = 'sinh_vien';
    protected $collection = 'sinh_vien';

    protected $fillable = [
        'MaSV', 
        'MaTK', 
        'MaLop', 
        'MaNganh', 
        'KhoaHoc', 
        'NgaySinh', 
        'HoTen', 
        'Email', 
        'SoDienThoai', 
        'TenDangNhap', 
        'TrangThai'
    ];

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'MaTK', '_id');
    }

    public function lop()
    {
        return $this->belongsTo(Lop::class, 'MaLop', 'MaLop');
    }

    public function nganh()
    {
        return $this->belongsTo(Nganh::class, 'MaNganh', 'MaNganh');
    }

    public function getNganhAttribute()
    {
        if (array_key_exists('nganh', $this->relations) && $this->relations['nganh']) {
            return $this->relations['nganh'];
        }
        $val = $this->attributes['MaNganh'] ?? null;
        if ($val) {
            $ng = Nganh::where('MaNganh', $val)->orWhere('_id', $val)->first();
            if ($ng) return $ng;
        }
        $lop = $this->lop;
        if ($lop) {
            if ($lop->nganh) return $lop->nganh;
            $maNganhLop = $lop->MaNganh ?? null;
            if ($maNganhLop) {
                $ng = Nganh::where('MaNganh', $maNganhLop)->orWhere('_id', $maNganhLop)->first();
                if ($ng) return $ng;
            }
        }
        return Nganh::first();
    }

    public function getNganhModelAttribute()
    {
        return $this->nganh;
    }

    public function lopHocPhans()
    {
        return $this->belongsToMany(LopHocPhan::class, null, 'sinh_vien_ids', 'lop_hoc_phan_ids');
    }
}