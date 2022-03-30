<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleWithDriverCotroller extends Controller
{
    public function index(Request $request)
    {
        try {
            $vehicle = Vehicle::select('id', 'Year', 'manufacturer', 'model_no', 'vehicle_model_type', 'vin', 'license_plate', 'registration_expiry_date', 'insurance_expiry_date', 'odometer')
                ->where('user_id', $request->eso_id)
                ->with('driver:id,vehicle_id,name,mobile_no,license_expiry,driving_experience')
                ->get();
            $returnData['data'] = $vehicle;
            $dataMeta = [
                'meta' => [
                    'total' => $vehicle->count()
                ],
            ];
            $metaData = metaData(true, $request, '2026');
            $new_merge = merge($metaData, $dataMeta);
            return response()->json(merge($returnData, $new_merge));
        } catch (\Exception $e) {
            return metaData(false, $request, 2026, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
}
