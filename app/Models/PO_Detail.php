<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PO_Detail extends Model
{
    use HasFactory;

    protected $connection = 'EPO';
    protected $table = 'PO_Detail';

    public function PO_Header()
    {
        return $this->belongsTo('PO_Header');
    } 

    public function PO_Asset(){
        return $this->hasMany(PO_Asset::class, 'PID_Detail', 'PID');
    }

    public function branch(){
        return $this->hasOne(SEA_Branch::class, 'Branch', 'AssBranch');
    }

    public function CT(){
        return $this->hasOne(SEA_CT::class, 'CT', 'AssDepartment');
    }
}
