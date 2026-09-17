<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class GiangVien extends Model
{
    protected $table = 'giang_vien';
    protected $collection = 'giang_vien';

    protected $fillable = [
        'MaGV', 
        'MaTK', 
        'MaBoMon', 
        'HoTen', 
        'Email', 
        'SoDienThoai', 
        'HocVi', 
        'TenDangNhap', 
        'TrangThai'
    ];

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'MaTK', '_id');
    }

    public function boMon()
    {
        return $this->belongsTo(BoMon::class, 'MaBoMon', 'MaBoMon');
    }

    public function getBoMonAttribute()
    {
        if (array_key_exists('boMon', $this->relations) && $this->relations['boMon']) {
            return $this->relations['boMon'];
        }
        $val = $this->attributes['MaBoMon'] ?? null;
        if (!$val) return null;
        return BoMon::where('MaBoMon', $val)->orWhere('_id', $val)->first();
    }

    public function getBoMonModelAttribute()
    {
        return $this->boMon;
    }

    public function lopHocPhans()
    {
        return $this->hasMany(LopHocPhan::class, 'MaGV', 'MaGV');
    }
}