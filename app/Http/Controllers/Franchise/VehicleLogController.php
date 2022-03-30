<?php

namespace App\Http\Controllers\Franchise;


use App\helpers\Downloadshelper;
use App\Http\Controllers\Controller;
use App\Models\DriverMaster;
use App\Models\OdoMeter;
use App\Models\TripMaster;
use App\Models\VehicleMaster;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Vehicle;
use App\Models\VehicleLevelofService;
use Facade\FlareClient\Http\Response;
use App\Http\Resources\VehicleResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\VehicleCollection;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\VehicleStoreRequest;
use Illuminate\Validation\Rule;
use App\Http\Requests\VehicleUpdateRequest;

class VehicleLogController extends Controller
{
	public function indexes()
	{
		$drivers = DriverMaster::where('user_id', auth()->id())
			->select('id', 'name')
			->where('status', '1')
			->orderBy('name', 'asc')->get();
		$vehicles = VehicleMaster::whereHas('driver')->where('status', '1')->select(["id", "model_no as name"])->where('user_id', auth()->id())->orderBy('id', 'desc')->get();
		return view('franchise.reports.vehiclelogs.vehiclelog', compact('drivers', 'vehicles'));

		try {
            $query = DriverMaster::eso()->with('masterLevelservices:id,name','driver');
            $drivers=DriverMaster::filterDriver($request, $query)
            ->latest()->paginate(config('Settings.pagination'));
            return new VehicleCollection($vehicle);
        } catch (\Exception $e) {
            return metaData(false, $request, 30001, '', 502, errorDesc($e), 'Error occured in server side ');
        }
	}

	//  this is only testing function
	public function dev(Request $request)
	{
		$trips =	DB::table('trip_master_ut as tr');
		$trips->leftjoin('trip_logs as tl', 'tl.trip_id', '=', 'tr.id');
		$trips->leftjoin('vehicle_master_ut as vh', 'tr.vehicle_id', '=', 'vh.id');
		$trips->leftjoin('driver_master_ut as dr', 'vh.id', '=', 'dr.vehicle_id');
		$trips->select(
			'tr.date_of_service',
			'tr.vehicle_id',
			'vh.model_no',
			'vh.VIN',
			'dr.name',
			DB::raw('count(*) as trip_count'),
			DB::raw('count(tr.other_driver_id) as other_drivers'),
			DB::raw('sum(tr.additional_passengers) as total_additional_passengers'),
			DB::raw('sum(tr.driver_pay) as driver_payout'),
			DB::raw('sum(tr.insurance_amount) as total_insurace'),
			DB::raw('sum(tr.trip_profit) as trip_profit'),
			DB::raw('sum(tr.total_trip) as total_trip_price'),
			DB::raw('sum(tr.total_trip) as total_trip_price'),
			DB::raw('sum(tl.period2 + tl.period3) as total_time'),
			DB::raw('sum(tl.period2 + tl.period3) as total_time'),
			DB::raw('sum(tl.period2_miles + tl.period3_miles) as total_miles'),
			DB::raw('sum(TIME_TO_SEC(tl.period2) + TIME_TO_SEC(tl.period3)) as time')
		);
		$trips->where('tr.user_id', auth()->id());
		$trips->whereNotNull('tr.vehicle_id');
		$trips->whereIn('tr.status_id', [3, 5, 6]);

		$trips  =	$trips->groupBy('tr.date_of_service', 'tr.vehicle_id')->orderbyDesc('tr.created_at')->get();

		$odometers = OdoMeter::select()
			->select(
				'date_of_service',
				'vehicle_id',
				DB::raw('sum(trip_reading) as reading')
			)
			->groupBy('date_of_service', 'vehicle_id')
			->get();

		foreach ($trips as $key => $trip) {
			$reading = 0;

			foreach ($odometers as $key => $odometer) {

				if ($trip->date == $odometer->date_of_service && $trip->vehicle_id == $odometer->vehicle_id) {
					$reading = $odometer->reading;
				}
			}
			$trip->odometer = $reading;
		}
		return $trips;
	}



	public function index(Request $request)
	{
		$trips = $this->getRecords($request);
		return $trips;
		$i = 1;
		$table = '';
		if ($trips->count()) {
			
		} else {
			$table .= '<tr><td colspan="25">No data found...</td></tr>';
		}
		echo json_encode(array('table' => $table, 'count' => $trips->count()));
		exit;
	}

	public function export_vehicle(REQUEST $request)
	{

		$trips = $this->getRecords($request);
		$final_array = array();
		$i = 1;

		foreach ($trips as $key => $row) {

			$uti_500 = 0;
			$uti_8 = 0;
			if ($row->total_trip_price) {
				$uti_500 = $row->total_trip_price / 500;
			}
			if ($row->total_time) {
				$uti_8 = $row->total_time / 8;
			}
			$time =  gmdate("H:i:s", $row->time);


			$data = array(
				'sn' => $i++,
				'dateofservice' => $row->date ?? '',
				'model_no' => $row->model_no,
				'VIN' => $row->VIN,
				'name' => $row->name,
				'odometer' => $row->odometer,
				'total_miles' => $row->total_miles,
				'trip_count' => $row->trip_count,
				'time' => $time,
				'trip_price' => $row->total_trip_price,
				'uti_500' => round($uti_500, 2),
				'uti_8' => round($uti_8, 2),
			);

			$final_array[] = $data;
		}
		// return $final_array;
		$headerArr = array("sn", 'dateofservice', 'Vehicle', 'VIN', 'driver name', 'odometer', 'Mileage Driven', 'Trips executed', 'Duration', 'Total Trip Cost', 'Vehicle Utilization (%-Total Trip Price/$500)', 'Vehicle Utilization(%-Total Duration/8)');


		echo Downloadshelper::arrayToCSV($headerArr, $final_array, "vehiclelogs.csv");
	}


	public function getRecords($request)
	{
		$user_timezone_offset = Carbon::now()->timezone(eso()->timezone)->getOffsetString();
		$app_timezone_offset  = Carbon::now()->getOffsetString();

		$trips =	DB::table('trip_master_ut as tr');
		// $trips->leftjoin('trip_logs as tl', 'tl.trip_id', '=', 'tr.id');
		$trips->leftjoin("trip_logs as tl", function ($join) {
			$join->on("tl.trip_id", "=", "tr.id")
				->on("tl.driver_id", "=", "tr.driver_id");
		});
		$trips->leftjoin('vehicle_master_ut as vh', 'tr.vehicle_id', '=', 'vh.id');
		$trips->leftjoin('driver_master_ut as dr', 'tr.driver_id', '=', 'dr.id');
		$trips->select(
			'tr.vehicle_id',
			'vh.model_no',
			'vh.VIN',
			'dr.name',
			DB::raw('DATE(CONVERT_TZ(concat(tr.date_of_service," ", CASE WHEN tr.shedule_pickup_time IS NULL THEN "00:00:00" ELSE tr.shedule_pickup_time END),"' . $app_timezone_offset . '","' . $user_timezone_offset . '")) as date'),
			DB::raw('count(*) as trip_count'),
			// DB::raw('count(tr.other_driver_id) as other_drivers'),
			DB::raw('sum(tr.additional_passengers) as total_additional_passengers'),
			DB::raw('sum(tr.driver_pay) as driver_payout'),
			DB::raw('sum(tr.insurance_amount) as total_insurace'),
			DB::raw('sum(tr.trip_profit) as trip_profit'),
			DB::raw('sum(tr.total_price) as total_trip_price'),
			DB::raw('sum(tl.period2 + tl.period3) as total_time'),
			DB::raw('sum(tl.period2_miles + tl.period3_miles) as total_miles'),
			DB::raw('SUM(TIME_TO_SEC(IFNULL(tl.period2,0)) + TIME_TO_SEC(IFNULL(tl.period3,0)) + IF(tr.trip_format = "2", IFNULL(tl.wait_time_sec,0), 0)) as time'),
			// DB::raw('sum(TIME_TO_SEC(tl.period2) + TIME_TO_SEC(tl.period3)) as time')
		);
		$trips->where('tr.user_id', eso());
		$trips->whereNotNull('tr.vehicle_id');
		$trips->whereIn('tr.status_id', [3, 5, 6]);
		if ($request->filled('start_date')) {
			$start_date = Carbon::parse($request->start_date, eso()->timezone)
				->startOfDay()
				->setTimezone(config('app.timezone'));

			// $trips->where('tr.date_of_service', '>=', $date);
			$trips->whereRaw('concat(tr.date_of_service," ", CASE WHEN shedule_pickup_time IS NULL THEN "00:00:00" ELSE tr.shedule_pickup_time END) >="' . $start_date . '"');
		}

		if ($request->filled('end_date')) {
			$end_date = Carbon::parse($request->end_date, eso()->timezone)
				->endOfDay()
				->setTimezone(config('app.timezone'));
			// $trips->where('tr.date_of_service', '<=', $end_date);
			$trips->WhereRaw('concat(tr.date_of_service," ", CASE WHEN shedule_pickup_time IS NULL THEN "00:00:00" ELSE tr.shedule_pickup_time END) <="' . $end_date . '"');
		}


		if ($request->filled('driver_id')) {
			$trips->where('tr.Driver_id', $request->driver_id);
		}

		if ($request->filled('vehicle_id')) {
			$trips->where('tr.vehicle_id', $request->vehicle_id);
		}
		$trips =	$trips->groupBy('date', 'tr.vehicle_id')->orderbyDesc('tr.created_at')->get();

		$odometers = OdoMeter::select(
				'date_of_service',
				'vehicle_id',
				DB::raw('sum(trip_reading) as reading')
			)
			->groupBy('date_of_service', 'vehicle_id')
			->get();

		foreach ($trips as $key => $trip) {
			$reading = 0;

			foreach ($odometers as $key => $odometer) {

				if ($trip->date == $odometer->date_of_service && $trip->vehicle_id == $odometer->vehicle_id) {
					$reading = $odometer->reading;
				}
			}
			$trip->odometer = $reading;
		}

		return $trips;
	}
}
