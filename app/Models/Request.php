<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $connection = 'SEA_DEMO';
    protected $table = 'Request';

    public function pVoucher(){
        return $this->hasOne(PVoucher::class, 'Voucher', 'Voucher');
    }
}
