<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoDocFilterTemp extends Model
{
    use HasFactory;
    protected $table = 'tbl_po_doc_filter_temp';
    protected $fillable = [
        'nikfilter',
        'tanggalawalfilter',
        'tanggalakhirfilter',
        'vendorfilter',
        'apfilter',
        'unitfilter',
    ];
}
