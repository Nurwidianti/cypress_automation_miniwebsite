<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogMasterDoc extends Model
{
    use HasFactory;
    protected $table = 'table_sclog_rekap_pembelian_doc';
    protected $fillable = [
        'tanggal', 
        'po',
        'flok',
        'vendor',
        'produk',
        'qty',
        'harga_beli',
        'subsidi',
        'value_total_beli',
        'transport',
        'value_total_transport',
        'value_total_transaksi',
        'armada',
        'surat_jalan',
        'unit',
        'ap',
        'kode_vendor',
        'bf_broker',
        'pbm',
    ];
}
