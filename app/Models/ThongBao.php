<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ThongBao extends Model
{
    protected $table = 'thong_bao';
    protected $collection = 'thong_bao';
    public $timestamps = true;

    protected $fillable = [
        'MaTK',
        'MaLop',
        'MaLopHP',
        'TieuDe',
        'NoiDung',
        'FileDinhKem',
        'LoaiThongBao',
        'DuongDan',
        'DaDoc',
        'NgayTao'
    ];

    protected $casts = [
        'DaDoc' => 'boolean',
    ];

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'MaTK', '_id');
    }

    public function lop()
    {
        return $this->belongsTo(Lop::class, 'MaLop', '_id');
    }

    public function lopHocPhan()
    {
        return $this->belongsTo(LopHocPhan::class, 'MaLopHP', '_id');
    }

    public function scopeChuaDoc($query)
    {
        return $query->where('DaDoc', false);
    }
}
