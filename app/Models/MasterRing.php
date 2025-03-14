<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterRing extends Model
{
    use HasFactory;
    protected $table = 'tbl_master_ring_po';
    protected $fillable = [
        'kabupaten',
        'kecamatan',
        'unit',
        'ap',
        'ring',
    ];
}
