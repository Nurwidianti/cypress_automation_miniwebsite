<?php

namespace App\Imports;

use App\Models\MasterRing;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class MasterRingImport implements ToCollection
{
    /**
    * @param Collection $collection
    */

    public function collection(Collection $collection)
    {
        $collection->shift();

        foreach ($collection as $row) {
            $data = [
                'kabupaten' => $row[1],
                'kecamatan' => $row[2],
                'unit' => $row[3],
                'ap' => $row[4],
                'ring' => $row[5],
            ];

            if (!empty(array_filter($data))) {
                MasterRing::create($data);
            }
        }
    }
}

