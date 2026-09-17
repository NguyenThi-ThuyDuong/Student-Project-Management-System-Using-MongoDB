<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Nganh extends Model
{
    protected $table = 'nganh';
    protected $collection = 'nganh';

    protected $fillable = ['MaNganh', 'TenNganh', 'MaBoMon', 'MoTa'];

    public function boMon()
    {
        return $this->belongsTo(BoMon::class, 'MaBoMon', 'MaBoMon');
    }

    public function getBoMonModelAttribute()
    {
        if ($this->boMon) return $this->boMon;
        return BoMon::where('MaBoMon', $this->MaBoMon)->orWhere('_id', $this->MaBoMon)->first();
    }
}
