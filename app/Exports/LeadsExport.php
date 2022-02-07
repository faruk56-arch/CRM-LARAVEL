<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;

class LeadsExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct($keys, $leads)
    {
        $this->keys = $keys;
        $this->leads = $leads;
    }


    public function view(): \Illuminate\Contracts\View\View
    {
        return view('exports', [
            'keys' => $this->keys,
            'leads' => $this->leads
        ]);
    }
}
