<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KontakTelp extends Model
{
    use HasFactory;
    protected $table = 'tblkontak';
    protected $fillable = [
        'unit',
        'ap',
        'jabatan',
        'nama',
        'nowa',
        'nik',
    ];
}
