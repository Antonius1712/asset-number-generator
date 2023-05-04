<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PO_Asset_Log extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'EPO';
    protected $table = 'PO_Asset_Log';
}
