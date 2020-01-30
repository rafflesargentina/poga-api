<?php

namespace Raffles\Modules\Poga\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PagareExport implements FromArray, WithHeadings
{
    protected $pagares;

    public function __construct(array $pagares)
    {
        $this->pagares = $pagares;
    }

    public function headings(): array
    {
        return $this->pagares ? array_merge(array_keys($this->pagares[0]), ['Observación']) : []; 
    }

    public function array(): array
    {
        return $this->pagares;
    }
}
