<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class YeuCauDoiMatKhau extends Model
{
    protected $table = 'yeu_cau_doi_mat_khau';
    protected $collection = 'yeu_cau_doi_mat_khau';

    protected $fillable = [
        'TenDangNhap',
        'Email',
        'Role',
        'TrangThai',
        'NgayGui'
    ];
}
