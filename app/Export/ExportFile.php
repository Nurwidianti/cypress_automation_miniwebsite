<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\FromArray;

class ExportFile implements FromArray, WithHeadings, WithBatchInserts, WithChunkReading
{
    use Exportable;
    private $myArray;
    private $myHeadings;

    public function __construct($myArray, $myHeadings){
        $this->myArray = $myArray;
        $this->myHeadings = $myHeadings;
    }

    public function array(): array{
        return $this->myArray;
    }

    public function headings(): array{
        return $this->myHeadings;
    }
}
?>