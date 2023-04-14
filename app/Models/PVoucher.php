<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PVoucher extends Model
{
    use HasFactory;

    protected $connection = 'SEA_DEMO';
    protected $table = 'pVoucher';
}
