<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PO_Log_Sending_Email extends Model
{
    use HasFactory;

    protected $table = 'PO_Log_Sending_Email';

    protected $fillable = [
        'PID',
        'email_sent',
        'email_subject',
        'email_body',
        'year',
        'month',
        'date',
        'day',
        'time'
    ];
}
