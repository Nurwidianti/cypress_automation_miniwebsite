<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoPost extends Model
{
    use HasFactory;
    protected $table = 'tb_mo_post';
    protected $fillable = [
        'title',
        'body',
        'file',
        'link',
        'nik',
        'tujuan_nik',
        'tujuan_ap',
        'tujuan_unit',
        'tujuan_jabatan',
        'notifikasi',
        'is_opened',
        'anonym'
    ];
}
