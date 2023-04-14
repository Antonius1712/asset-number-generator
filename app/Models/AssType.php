<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssType extends Model
{
    use HasFactory;

    protected $connection = 'SEA_DEMO';
    protected $table = 'AssType';
}
