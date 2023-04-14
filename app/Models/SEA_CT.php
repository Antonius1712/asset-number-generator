<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SEA_CT extends Model
{
    use HasFactory;

    protected $connection = 'SEA_DEMO';
    protected $table = 'CT';
}
