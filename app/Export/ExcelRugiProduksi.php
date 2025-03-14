<?php

namespace App\Export;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\FromQuery;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExcelRugiProduksi implements FromView, ShouldAutoSize, WithEvents {

    /* public function __construct(string $keyword){
        $this->keyword = $keyword;
    } */

    public function view(): View{
        $sql = DB::select("SELECT kodeunit, region,
                        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=1),'') AS jan,
                        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=2),'') AS feb,
                        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=3),'') AS mar,
                        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=4),'') AS apr,
                        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=5),'') AS mei,
                        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=6),'') AS jun,
                        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=7),'') AS jul,
                        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=8),'') AS agu,
                        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=9),'') AS sep,
                        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=10),'') AS okt,
                        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=11),'') AS nov,
                        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=12),'') AS des,
                        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND YEAR(tgldocfinal)=2022),'') AS cum,
                        IFNULL((SELECT COUNT(nomrhpprugi) FROM table_rhpp WHERE unit=a.kodeunit AND nomrhpprugi > 0),0)/(SELECT COUNT(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND ciawal > 0)*100 AS flok_rugi
                        FROM units a");
        return view('dashboard.produksi.excelRugiProduksi', ['data' => $sql]);
    }

    public function registerEvents(): array
        {
        return [
            AfterSheet::class    => function(AfterSheet $event) 
            {

                       $cellRange = 'A1:G1'; // All headers
                       $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setName('Calibri');
               $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(14);

        
            },
              ];
       }
    
}
