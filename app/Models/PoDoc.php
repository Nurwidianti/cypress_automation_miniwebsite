<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoDoc extends Model
{
    use HasFactory;
    protected $table = 'tbl_po_doc';
    protected $fillable = [
        'namavendor',
        'tanggal',
        'hari',
        'namado',
        'unit',
        'kodecustomer',
        'namapeternak',
        'alamatkandang',
        'notelepon',
        'noteleponppl',
        'jumlahbox',
        'nonvacc',
        'gradedoc',
        'vaksin',
        'plastik',
        'perlakuan',
        'feedgel',
        'keterangan',
    ];
}
