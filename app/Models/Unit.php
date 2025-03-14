<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;
    protected $table = 'units';
    protected $fillable = [
        'kodeunit',
        'namaunit',
        'region',
    ];

    protected $primaryKey = 'kodeunit';
    public $incrementing = false;
    protected $keyType = 'string';
}
