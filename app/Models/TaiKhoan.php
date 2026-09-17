<?php

namespace App\Models;

use MongoDB\Laravel\Auth\User as MongoUser;
use Illuminate\Notifications\Notifiable;

class TaiKhoan extends MongoUser
{
    use Notifiable;

    protected $connection = 'mongodb';
    protected $table = 'tai_khoan';
    protected $collection = 'tai_khoan';

    protected $fillable = [
        'TenDangNhap',
        'MatKhau',
        'VaiTro',
        'TrangThai',
    ];

    protected $hidden = [
        'MatKhau',
    ];

    // Laravel uses password for Auth by default, we map it to MatKhau
    public function getAuthPassword()
    {
        return $this->MatKhau;
    }

    public function sinhVien()
    {
        return $this->hasOne(SinhVien::class, 'MaTK', '_id');
    }

    public function giangVien()
    {
        return $this->hasOne(GiangVien::class, 'MaTK', '_id');
    }
}
