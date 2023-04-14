<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PO_Info extends Model
{
    use HasFactory;

    protected $connection = 'EPO';
    protected $table = 'PO_Info';
}
