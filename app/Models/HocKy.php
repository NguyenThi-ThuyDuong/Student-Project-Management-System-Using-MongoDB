<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class HocKy extends Model
{
    protected $table = 'hoc_ky';
    protected $collection = 'hoc_ky';

    protected $fillable = ['MaHocKy', 'MaHK', 'TenHocKy', 'TenHK', 'NamHoc', 'NgayBatDau', 'NgayKetThuc', 'TrangThai'];

    public function getMaHocKyAttribute($value)
    {
        if (!empty($value)) {
            return (string)$value;
        }
        if (!empty($this->attributes['MaHK'])) {
            return (string)$this->attributes['MaHK'];
        }
        return isset($this->attributes['_id']) ? (string)$this->attributes['_id'] : '';
    }

    public function getMaHKAttribute($value)
    {
        return $this->getMaHocKyAttribute($value);
    }

    public function getTenHocKyAttribute($value)
    {
        return !empty($value) ? $value : ($this->attributes['TenHK'] ?? '');
    }

    public function getTenHKAttribute($value)
    {
        return $this->getTenHocKyAttribute($value);
    }

    public function lopHocPhans()
    {
        return $this->hasMany(LopHocPhan::class, 'MaHocKy', '_id');
    }
}
