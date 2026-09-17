<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class HocKy extends Model
{
    protected $table = 'hoc_ky';
    protected $collection = 'hoc_ky';

    protected $fillable = ['MaHocKy', 'MaHK', 'TenHocKy', 'TenHK', 'NamHoc', 'NgayBatDau', 'NgayKetThuc', 'TrangThai'];

    public function lopHocPhans()
    {
        return $this->hasMany(LopHocPhan::class, 'MaHocKy', '_id');
    }
}
