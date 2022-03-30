<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TripExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;
    public function __construct($trips)
    {
        $this->trips = $trips;
    }
    public function collection()
    {
        return $this->trips;
    }

    public function headings(): array
    {
        return [
            "Date of Service",
            "Trip ID",
            "Trip (Legs)",
            "Appointment Time",
            "Scheduled Pickup Time",
            "Pickup Address",
            "Dropoff Address",
            "Payor Type",
            "Payor Name",
            "Payor Phone Number",
            "Level of Service",
            "Additional Passengers",
            "Driver Name",
            "Base Location",
            "Trip Start Address",
            "County",
            "Estimated Unloaded Mileage from Base Location",
            "Estimated Trip Distance",
            "Actual Unloaded Miles",
            "Actual Loaded Miles (Trip Distance)",
            "Trip Price ($)",
            "Price Adjustment ($)",
            "Total Price ($)",
            "Wait Time",
            "Notes/ Instructions",
            "Status",
            "Timezone"
        ];
    }
    public function map($item): array
    {
        return [
            'date_of_service' => modifyTripDate($item->date_of_service, $item->shedule_pickup_time),
            'trip_no' => $item->trip_no,
            'leg_no' => $item->leg_no,
            'appointment_time' => modifyTripTime($item->date_of_service, $item->appointment_time),
            'shedule_pickup_time' => modifyTripTime($item->date_of_service, $item->shedule_pickup_time),
            'pickup_address' => $item->pickup_address,
            'drop_address' => $item->drop_address,
            'payor_type_names' => $item->payorTypeNames->name ?? '',
            'payor' => $item->payor->name ?? '',
            'payor_phone' => $item->payor->phone_number ?? '',
            'level_of_service' => $item->levelOfService->name ?? '',
            'additional_passengers' => $item->additional_passengers,
            'driver' => $item->driver->name ?? '',
            'baselocation' => $item->baselocation->name ?? '',
            'trip_start_address' => formatPhoneNumber($item->trip_start_address),
            'county_type' => $item->county_type == 1 ? 'Local' : 'Out of County',
            'estimated_mileage_frombase_location' => decimal2digitNumber($item->estimated_mileage_frombase_location),
            'estimated_trip_distance' => $item->estimated_trip_distance,
            'unloaded_miles' => $item->log->period2_miles ?? '',
            'loaded_miles' => $item->log->period3_miles ?? 0,
            'trip_price' => decimal2digitNumber($item->trip_price),
            'adjusted_price' => decimal2digitNumber($item->adjusted_price),
            'total_price' => decimal2digitNumber($item->total_price),
            'wait_time' => $item->trip_format == 4 ? 'Yes' : 'No',
            'notes_or_instruction' => $item->notes_or_instruction,
            'status' => $item->status->status_description ?? '',
            'timezone' => authTimeZone(),

        ];
    }
}
