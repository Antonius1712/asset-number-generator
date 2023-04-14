<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PO_Header extends Model
{
    use HasFactory;

    protected $connection = 'EPO';
    protected $table = 'PO_Header';

    public function PO_Detail(){
        return $this->hasMany(PO_Detail::class, 'PID_Header', 'PID');
    }
}
