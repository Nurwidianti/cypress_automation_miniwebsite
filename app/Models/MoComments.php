<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoComments extends Model
{
    use HasFactory;
    protected $table = 'tb_mo_comment';
    protected $fillable = [
        'answer_id',
        'body',
        'file',
        'link',
        'nik',
        'anonym',
        'is_read'
    ];
}
