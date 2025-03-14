<?php

namespace App\Export;

use App\Models\Karyawan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;

class ExcelExport implements FromCollection, WithHeadings, WithDrawings
{
    use Exportable;

    public function __construct(string $unit)
    {
        $this->unit = $unit;
    }

    
    public function collection()
    {
        $karyawan = DB::table('users')
        ->select('nik','name','jabatan','unit','region')
        ->where('unit','=',$this->unit)
        ->get();
        return $karyawan;
    }

    public function headings(): array
    {
        return [
            'NIK',
            'NAMA',
            'JABATAN',
            'UNIT',
            'REGION',
        ];
    }
    
}
