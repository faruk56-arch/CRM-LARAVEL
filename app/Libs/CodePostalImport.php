<?php

namespace App\Libs;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class CodePostalImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        return $rows;
    }
}
