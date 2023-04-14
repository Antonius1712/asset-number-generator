<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SEA_Branch extends Model
{
    use HasFactory;

    protected $connection = 'SEA_DEMO';
    protected $table = 'Branch';
}
