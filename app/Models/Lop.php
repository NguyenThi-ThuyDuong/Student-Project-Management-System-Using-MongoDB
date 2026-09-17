<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Lop extends Model
{
    protected $table = 'lop';
    protected $collection = 'lop';

    protected $fillable = ['MaLop', 'TenLop', 'MaNganh', 'MaHocKy', 'KhoaHoc', 'SiSo'];

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
        if (!$val) return null;
        return Nganh::where('MaNganh', $val)->orWhere('_id', $val)->first();
    }

    public function getNganhModelAttribute()
    {
        return $this->nganh;
    }

    public function hocKy()
    {
        return $this->belongsTo(HocKy::class, 'MaHocKy', 'MaHocKy');
    }
}
