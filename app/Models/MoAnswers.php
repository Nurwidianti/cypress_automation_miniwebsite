<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoAnswers extends Model
{
    use HasFactory;
    protected $table = 'tb_mo_answer';
    protected $fillable = [
        'post_id',
        'body',
        'file',
        'link',
        'nik',
        'anonym',
        'is_read'
    ];
}
