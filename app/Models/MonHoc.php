<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class MonHoc extends Model
{
    protected $table = 'mon_hoc';
    protected $collection = 'mon_hoc';

    protected $fillable = ['MaMon', 'TenMon', 'MaBoMon', 'SoTinChi', 'MoTa'];

    public function boMon()
    {
        return $this->belongsTo(BoMon::class, 'MaBoMon', 'MaBoMon');
    }

    public function getBoMonModelAttribute()
    {
        if ($this->boMon) return $this->boMon;
        return BoMon::where('MaBoMon', $this->MaBoMon)->orWhere('_id', $this->MaBoMon)->first();
    }

    public function lopHocPhans()
    {
        return $this->hasMany(LopHocPhan::class, 'MaMon', 'MaMon');
    }
}
