<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class BoMon extends Model
{
    protected $table = 'bo_mon';
    protected $collection = 'bo_mon';

    protected $fillable = ['MaBoMon', 'TenBoMon', 'MoTa'];
}
