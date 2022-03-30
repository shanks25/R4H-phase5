<?php

namespace App\Http\Controllers\Franchise;

use App\Exports\TripExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TripMaster;
use App\Traits\TripTrait;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class TripExportController extends Controller
{
    use TripTrait;
    public function index(Request $request)
    {
        // return $request->all_export;
        $trip_ids = array();
        $all_export = 0;
        if ($request->filled('all_export')) {
            $all_export =  $request->all_export;
        }
        // return $all_export;
        if ($all_export != 1) {
            if ($request->filled('trip_ids')) {
                $check_array =  is_array(json_decode($request->trip_ids, true));
                if (!$check_array) {
                    return metaData(false, $request, 20006, '', 400, '', 'Invalid trip_ids');
                }
                if (count(json_decode($request->trip_ids, true)) <= 0) {
                    return metaData(false, $request, 20006, '', 400, '', 'Invalid trip_ids');
                }
                $trip_ids =  json_decode($request->trip_ids, true);
            } else {
                return metaData(false, $request, 20006, '', 400, '', 'trip_ids is required');
            }
        }

        try {
            // return $request;
            $trips = $this->tripsExportCollection($request);
            if ($all_export != 1) {
                $trips = $trips->whereIn('id', $trip_ids);
            }
            $trips = $trips->get();
            // if (count($trips) > 0) {
            //     return metaData(false, $request, 2014, '', 502, '', 'no data found ');
            // }
            Excel::store(new TripExport($trips), 'public/export_trip/trips_export_' . eso()->id . '.csv', 'local');
            $url = asset('storage/export_trip/' . 'trips_export_' . eso()->id . '.csv');
            $data['url'] = $url;
            $main['data'] = $data;

            return   merge($main, metaData(true, $request, 2014, 'success', 200, '', ''));
        } catch (\Exception $e) {
            return metaData(false, $request, 2014, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
}
