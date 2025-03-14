<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogDoc extends Model
{
    use HasFactory;
    protected $table = 'menu_logdoc';
    protected $fillable = [
        'name', 'link',
    ];
}
