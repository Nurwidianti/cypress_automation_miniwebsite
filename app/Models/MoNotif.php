<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoNotif extends Model
{
    use HasFactory;
    protected $table = 'tb_mo_notif';
    protected $fillable = [
        'post_id',
        'pengirim',
        'nik_pengirim',
        'nik_tujuan',
        'type'
    ];
}
