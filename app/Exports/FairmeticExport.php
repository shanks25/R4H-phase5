<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FairmeticExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;
    public function __construct($res)
    {
        $this->res = $res;
    }
    public function collection()
    {
        return $this->res;
    }

    public function headings(): array
    {
        return [
            "Driver Id",
            "Period",
            "Start Time",
            "End Time"
        ];
    }
    public function map($item): array
    {
        return [
            'date_of_service' => modifyTripDate($item->date_of_service, $item->shedule_pickup_time),
            'driver_id' => $item->driver_id,
            'period' => $item->period,
            'start_timestamp' => $item->start_timestamp,
            'end_timestamp' => $item->end_timestamp,
        ];
    }
}
