<?php

namespace App\Traits;

use App\Http\Resources\TripListingCollection;
use App\Http\Resources\TripResource;
use App\Models\DriverMaster;
use App\Models\InvoiceMaster;
use App\Models\PayoutPaidMaster;
use App\Models\PayoutPaidTrip;
use App\Models\RelInvoiceItem;
use App\Models\TripLog;
use App\Models\TripMaster;
use App\Models\TripStatusLog;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleDriverFuelDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;

trait TripTrait
{
    use DriverTrait;
    public function tripsPaginationCollection($request)
    {
        // return 123;
        $with_array = [
            'driver:id,name,vehicle_id',
            'vehicle:id,model_no',
            'status:id,status_description',
            'payorTypeNames:id,name',
            'payor:id,name,phone_number',
            'levelOfService:id,name',
            'baselocation:id,name',
            'importFile:id,imported_file',
            'log',
            'member:id,name,email,mobile_no,first_name,middle_name,last_name,gender',
            'zone:zone_id,zipcode', 'zone.zoneName:id,name',
        ];
        $trips = $this->trips($request, $with_array);
        $total_price_sum = decimal2digitNumber($trips->sum('total_price'));
        $request->merge(['total_price_sum' => $total_price_sum]);
        $trips = $trips->paginate(config('Settings.pagination'));
        return  new TripListingCollection($trips);
    }
    public function earningReportTripsCollection($request)
    {
        // return 123;
        $with_array = [
            'driver:id,name,vehicle_id',
            'vehicle:id,model_no',
            'status:id,status_description',
            'payorTypeNames:id,name',
            'payor:id,name,phone_number',
            'levelOfService:id,name',
            'baselocation:id,name',
            'importFile:id,imported_file',
            'log',
            'member:id,name,email,mobile_no,first_name,middle_name,last_name,gender',
            'zone:zone_id,zipcode', 'zone.zoneName:id,name',
        ];
        $trips = $this->trips($request, $with_array);
        if ($request->filled('current_date')) {
            $start_date = Carbon::parse($request->current_date, eso()->timezone)
                ->startOfDay()
                ->setTimezone(config('app.timezone'));
            $trips->whereRaw('concat(trip_master_ut.date_of_service," ", CASE WHEN shedule_pickup_time IS NULL THEN "00:00:00" ELSE trip_master_ut.shedule_pickup_time END) >="' . $start_date . '"');
        }

        if ($request->filled('current_date')) {
            $end_date = Carbon::parse($request->current_date, eso()->timezone)
                ->endOfDay()
                ->setTimezone(config('app.timezone'));
            $trips->WhereRaw('concat(trip_master_ut.date_of_service," ", CASE WHEN shedule_pickup_time IS NULL THEN "00:00:00" ELSE trip_master_ut.shedule_pickup_time END) <="' . $end_date . '"');
        }
        $total_price_sum = decimal2digitNumber($trips->sum('total_price'));
        $request->merge(['total_price_sum' => $total_price_sum]);
        $trips = $trips->get();
        return  new TripListingCollection($trips);
    }
    public function routeReportTripsTripsCollection($request)
    {
        // return 123;
        $with_array = [
            'driver:id,name,vehicle_id',
            'vehicle:id,model_no',
            'status:id,status_description',
            'payorTypeNames:id,name',
            'payor:id,name,phone_number',
            'levelOfService:id,name',
            'baselocation:id,name',
            'importFile:id,imported_file',
            'log',
            'member:id,name,email,mobile_no,first_name,middle_name,last_name,gender',
            'zone:zone_id,zipcode', 'zone.zoneName:id,name',
        ];
        $trips = $this->trips($request, $with_array);
        if ($request->filled('start_date')) {
            $start_date = Carbon::parse($request->start_date, eso()->timezone)
                ->startOfDay()
                ->setTimezone(config('app.timezone'));
            $trips->whereRaw('concat(trip_master_ut.date_of_service," ", CASE WHEN shedule_pickup_time IS NULL THEN "00:00:00" ELSE trip_master_ut.shedule_pickup_time END) >="' . $start_date . '"');
        }

        if ($request->filled('end_date')) {
            $end_date = Carbon::parse($request->end_date, eso()->timezone)
                ->endOfDay()
                ->setTimezone(config('app.timezone'));
            $trips->WhereRaw('concat(trip_master_ut.date_of_service," ", CASE WHEN shedule_pickup_time IS NULL THEN "00:00:00" ELSE trip_master_ut.shedule_pickup_time END) <="' . $end_date . '"');
        }
        if ($request->filled('start_zip')) {
            $start_zip = $request->start_zip;
            $trips->Where('trip_master_ut.pickup_zip', $start_zip);
        }
        if ($request->filled('end_zip')) {
            $end_zip = $request->end_date;
            $trips->Where('trip_master_ut.drop_zip', $end_zip);
        }
        $total_price_sum = decimal2digitNumber($trips->sum('total_price'));
        $request->merge(['total_price_sum' => $total_price_sum]);
        $trips = $trips->get();
        return  new TripListingCollection($trips);
    }
    public function driverReportTripsCollection($request)
    {
        // return 123;
        $with_array = [
            'driver:id,name,vehicle_id',
            'vehicle:id,model_no',
            'status:id,status_description',
            'payorTypeNames:id,name',
            'payor:id,name,phone_number',
            'levelOfService:id,name',
            'baselocation:id,name',
            'importFile:id,imported_file',
            'log',
            'member:id,name,email,mobile_no,first_name,middle_name,last_name,gender',
            'zone:zone_id,zipcode', 'zone.zoneName:id,name',
        ];
        $trips = $this->trips($request, $with_array);
        if ($request->filled('start_date')) {
            $start_date = Carbon::parse($request->start_date, eso()->timezone)
                ->startOfDay()
                ->setTimezone(config('app.timezone'));
            $trips->whereRaw('concat(trip_master_ut.date_of_service," ", CASE WHEN shedule_pickup_time IS NULL THEN "00:00:00" ELSE trip_master_ut.shedule_pickup_time END) >="' . $start_date . '"');
        }

        if ($request->filled('end_date')) {
            $end_date = Carbon::parse($request->end_date, eso()->timezone)
                ->endOfDay()
                ->setTimezone(config('app.timezone'));
            $trips->WhereRaw('concat(trip_master_ut.date_of_service," ", CASE WHEN shedule_pickup_time IS NULL THEN "00:00:00" ELSE trip_master_ut.shedule_pickup_time END) <="' . $end_date . '"');
        }
        if ($request->filled('vehicle_id')) {
            $vehicle_id = $request->vehicle_id;
            $trips->Where('trip_master_ut.vehicle_id', $vehicle_id);
        }
        if ($request->filled('driver_id')) {
            $driver_id = $request->driver_id;
            $trips->Where('trip_master_ut.driver_id', $driver_id);
        }
        $total_price_sum = decimal2digitNumber($trips->sum('total_price'));
        $request->merge(['total_price_sum' => $total_price_sum]);
        $trips = $trips->get();
        return  new TripListingCollection($trips);
    }
    public function tripSingle($request)
    {
        $with_array = [
            'driver:id,name,vehicle_id',
            'driver.vehicle:id,model_no',
            'status:id,status_description',
            'payorTypeNames:id,name',
            'levelOfService:id,name',
            'baselocation:id,name',
            'importFile:id,imported_file',
            'log',
            'member:id,name,email,mobile_no,first_name,middle_name,last_name,gender',
            'zone:zone_id,zipcode', 'zone.zoneName:id,name',
            'countyPickupNames:id,county_name,zip',
            'countyDropNames:id,county_name,zip'
        ];
        $trips = $this->trips($request, $with_array);
        $trips = $trips->first();
        if ($trips) {
            return  new TripResource($trips);
        } else {
            return metaData(false, $request, 20002, '', 502, '', 'Trip Id not found ');
        }
    }
    public function tripsExportCollection($request)
    {
        $with_array = [
            'driver:id,name,vehicle_id',
            'driver.vehicle:id,model_no',
            'status:id,status_description',
            'payorTypeNames:id,name',
            'levelOfService:id,name',
            'baselocation:id,name',
            'importFile:id,imported_file',
            'log',
            'member:id,name,email,mobile_no,first_name,middle_name,last_name,gender',
            'zone:zone_id,zipcode', 'zone.zoneName:id,name',
            'payor'
        ];
        return $this->trips($request, $with_array);
    }
    public function trips($request, $with_array)
    {
        // return $with_array;
        $status = array();
        $driver = array();
        $base_location = array();
        $level_of_service = array();
        $trip_type = array();
        $county_type = array();
        $payor = array();
        $members = array();
        $level_of_services = array();

        $user_timezone = eso()->timezone;
        if ($request->filled('start_date')) {
            $start_date = searchStartDate($request->start_date, $user_timezone);
        }
        if ($request->filled('end_date')) {
            $end_date = searchEndDate($request->end_date, $user_timezone);
        }
        if ($request->filled('status_id')) {
            $status =  json_decode($request->status_id, true);
        }
        if ($request->filled('driver_id')) {
            $driver =  json_decode($request->driver_id, true);
        }
        if ($request->filled('base_location_id')) {
            $base_location =  json_decode($request->base_location_id, true);
        }
        if ($request->filled('level_of_service_id')) {
            $level_of_service =  json_decode($request->level_of_service_id, true);
        }
        if ($request->filled('trip_type')) {
            $trip_type =  json_decode($request->trip_type, true);
        }
        if ($request->filled('county_type')) {
            $county_type =  json_decode($request->county_type, true);
        }

        if ($request->filled('payor_id')) {
            $payor =  json_decode($request->payor_id, true);
        }
        if ($request->filled('trip_id')) {
            $trip_id_request =  $request->trip_id;
        }
        if ($request->filled('member_id')) {
            $members =  json_decode($request->member_id, true);
        }
        if ($request->filled('level_of_service_id')) {
            $level_of_services =  json_decode($request->level_of_service_id, true);
        }

        // DB::enableQueryLog();
        $trips = TripMaster::select(
            'id',
            'driver_id',
            'date_of_service',
            'trip_no',
            'leg_no',
            'appointment_time',
            'pickup_address',
            'shedule_pickup_time',
            'estimated_trip_duration',
            'drop_address',
            'Member_name',
            'level_of_service',
            'vehicle_id',
            'trip_price',
            'notes_or_instruction',
            'base_location_id',
            'status_id',
            'payor_type',
            'payor_id',
            'trip_format',
            'payable_type',
            'payor_signature',
            'master_level_of_service_id',
            'member_phone_no',
            'base_location_id',
            'trip_start_address',
            'pickup_zip',
            'additional_passengers',
            'county_type',
            'importfile_id',
            'estimated_mileage_frombase_location',
            'estimated_trip_distance',
            'adjusted_price',
            'total_price',
            'notes_or_instruction',
            'trip_add_type',
            'drop_of_time',
            'member_id',
            'payout_type'
        );

        if (count($with_array) > 0) {
            foreach ($with_array as $with) {
                $trips = $trips->with($with);
            }
        }
        if ($request->filled('start_date')) {
            $trips =  $trips->whereRaw('concat(date_of_service," ", CASE WHEN shedule_pickup_time IS NULL THEN "00:00:00" ELSE shedule_pickup_time END) >="' . $start_date . '"');
        }
        if ($request->filled('end_date')) {
            $trips =  $trips->WhereRaw('concat(date_of_service," ", CASE WHEN shedule_pickup_time IS NULL THEN "00:00:00" ELSE shedule_pickup_time END) <="' . $end_date . '"');
        }

        if (count($status) > 0) {
            $trips = $trips->whereIn('status_id', $status);
        }

        if (count($driver) > 0) {
            $trips =  $trips->whereIn('driver_id', $driver);
        }

        if (count($base_location) > 0) {
            $trips =  $trips->whereIn('base_location_id', $base_location);
        }

        if (count($level_of_service) > 0) {
            $trips =  $trips->whereIn('base_location_id', $level_of_service);
        }

        if (count($trip_type) > 0) {
            $trips =  $trips->whereIn('trip_format', $trip_type);
        }

        if (count($county_type) > 0) {
            $trips =  $trips->whereIn('county_type', $county_type);
        }
        if ($request->filled('payor_type')) {
            $trips =  $trips->where('payor_type', $request->payor_type);
        }

        if ($request->filled('payor_category')) {
            $trips =  $trips->where('payor_category', $request->payor_category);
        }

        if (count($payor) > 0) {
            $trips =  $trips->whereIn('payor_id', $payor);
        }
        if ($request->filled('trip_id')) {
            $trips =  $trips->where('id', $trip_id_request);
        }
        if ($request->filled('trip_no')) {
            $trips =  $trips->where('trip_no', $request->trip_no);
        }

        if ($request->filled('onboard_status_id')) {
            $trips =  $trips->where('onboard_status', $request->onboard_status_id);
        }
        if (count($members) > 0) {
            $trips =  $trips->whereIn('member_id', $members);
        }
        if (count($level_of_services) > 0) {
            $trips =  $trips->whereIn('master_level_of_service_id', $level_of_services);
        }
        if ($request->filled('keyword_search')) {
            $keyword = $request->keyword_search;
            $allSearchFields =  tripSearchArray();
            $trips = $trips->where(function ($query) use ($allSearchFields, $keyword) {
                $i = 0;
                foreach ($allSearchFields as $k => $v) {
                    if ($i == 0) {
                        $query->where($v, 'LIKE', '%' . $keyword . '%');
                    }
                    $query->orWhere($v, 'LIKE', '%' . $keyword . '%');
                    $i++;
                }
            });
        }
        if (isset($request->day) && $request->day != null && $request->day != '' && $request->day != 'null') {
            $request->day = explode(',', $request->day);
            $trips = $trips->whereIn(DB::raw('weekday(trip_master_ut.date_of_service)'), $request->day);
        }
        $trips = $trips->eso()
            // ->orderBy('group_id', 'DESC')
            ->orderBy('date_of_service', 'DESC')
            ->orderBy('leg_no', 'ASC')
            ->orderBy('shedule_pickup_time', 'DESC')
            ->orderBy('id', 'DESC');
        return $trips;
    }
    public function tripStatusLog($trip_id, $driver_id)
    {
        $logs_time =  new stdClass();
        $logs_time->confirm_time = '';
        $logs_time->start_time = '';
        $logs_time->pickup_time = '';
        $logs_time->member_on_board_time = '';
        $logs_time->no_show_time = '';
        $logs_time->member_cancelled_time = '';
        $logs_time->drop_time = '';
        $custom_logs_array = array();
        $logs = TripStatusLog::where('trip_id', $trip_id)->where('driver_id', $driver_id)->get();
        if (count($logs) > 0) {
            foreach ($logs as $log) { // craeting location is status id
                $custom_logs_array[$log->status] = $log;
            }
            // all  confirm status flag desc =>
            //0 = pending
            // 1 = confirm_by_driver
            //2 = turned_back_by_driver
            //3 = start_trip_to_pickup_location
            //4 = arrived_at_pickup
            //5 = reassign
            //6 = member_on_board
            //7 = no_show
            //8 = member_cancelled  (late cancellation)
            //9 = end_trip
            //10 = Unallocated
            //0 = pending
            //1 = confirm_by_driver
            //2 = turned_back_by_driver
            //3 = start_trip_to_pickup_location
            //4 = arrived_at_pickup
            //5 = reassign
            //6 = member_on_board
            //7 = no_show
            //8 = member_cancelled
            //9 = end_trip (drop of time )
            //10 = Unallocated
            // foreach ($trips as $trip) {
            if (isset($custom_logs_array[1])) { //1 confirm
                $logs_time->confirm_time =   modifyDriverLogTime($custom_logs_array[1]['date_time'], $custom_logs_array[1]['timezone'])->format('Y-m-d H:i:s');
            }
            if (isset($custom_logs_array[3])) { //3 start_trip_to_pickup_location
                $logs_time->start_time =   modifyDriverLogTime($custom_logs_array[3]['date_time'], $custom_logs_array[3]['timezone'])->format('Y-m-d H:i:s');
            }
            if (isset($custom_logs_array[4])) { //4 arrived_at_pickup
                $logs_time->pickup_time =   modifyDriverLogTime($custom_logs_array[4]['date_time'], $custom_logs_array[4]['timezone'])->format('Y-m-d H:i:s');
            }
            if (isset($custom_logs_array[6])) { //6 member_on_board
                $logs_time->member_on_board_time =   modifyDriverLogTime($custom_logs_array[6]['date_time'], $custom_logs_array[6]['timezone'])->format('Y-m-d H:i:s');
            }
            if (isset($custom_logs_array[7])) { //7 no show
                $logs_time->no_show_time =   modifyDriverLogTime($custom_logs_array[7]['date_time'], $custom_logs_array[7]['timezone'])->format('Y-m-d H:i:s');
            }
            if (isset($custom_logs_array[8])) { //8 member cancelled (late cancellation)
                $logs_time->member_cancelled_time =   modifyDriverLogTime($custom_logs_array[8]['date_time'], $custom_logs_array[8]['timezone'])->format('Y-m-d H:i:s');
            }
            if (isset($custom_logs_array[9])) { //9 end_trip (drop of time )
                $logs_time->drop_time =   modifyDriverLogTime($custom_logs_array[9]['date_time'], $custom_logs_array[9]['timezone'])->format('Y-m-d H:i:s');
            }
        }
        return $logs_time;
    }
    public function invoiceUpdate($trip)
    {
        $rel_invoice_item = RelInvoiceItem::where('trip_id', $trip->id)
            ->where('invoice_active_status', 1)
            ->where('provider_remitances_status_id', 1)
            ->whereNull('is_deleted')
            ->first();
        /////// check total paid amount
        $rel_invoice_item = RelInvoiceItem::where('trip_id', $trip->id)
            ->where('invoice_active_status', 1)
            ->where('provider_remitances_status_id', 1)
            ->whereNull('is_deleted')
            ->first();
        ////////////
        // $commission_amount = $rel_invoice_item->commision_amount;
        $total_amount = $rel_invoice_item->invoice_amount;
        $provider_remaining_amount = $rel_invoice_item->remaining_amount;
        $franchise_total_amount = $rel_invoice_item->franchise_amount;
        $franchise_remaining_amount = $rel_invoice_item->remaining_franchise_amount;
        //main invoice update
        $invMaster = InvoiceMaster::where('id', $rel_invoice_item->invoice_id)->first();
        $invMaster->provider_total_amount = $invMaster->provider_total_amount - $total_amount;
        $invMaster->provider_remaining_amount = $invMaster->provider_remaining_amount - $provider_remaining_amount;
        $invMaster->franchise_total_amount = $invMaster->franchise_total_amount - $franchise_total_amount;
        $invMaster->franchise_remaining_amount = $invMaster->franchise_remaining_amount - $franchise_remaining_amount;
        $invMaster->save();
        ////// after invoice update
        // $allready_invoice = RelInvoiceItem::where('trip_id', $trip->id)->where('invoice_active_status', 1)->where('provider_invoice_status_id', 2)->whereNull('is_deleted')->first();


        ////////////////////////////////////////check before invoice amount
        $before_trip_price = $rel_invoice_item->total_amount;
        $difference_amount = $trip->total_price - $before_trip_price;
        $rel_invoice_item->total_amount = $trip->total_price; //round($dtl->trip_price + $dtl->adjusted_price, 2);
        $rel_invoice_item->invoice_amount = $rel_invoice_item->total_amount;
        $allready_invoice = RelInvoiceItem::where('trip_id', $trip->id)->where('invoice_active_status', 2)->where('provider_invoice_status_id', 2)->whereNull('is_deleted')->get();
        $allready_paid = 0;
        if (count($allready_invoice) > 0) {
            foreach ($allready_invoice as $items) {
                $allready_paid += $items->paid_amount;
            }
        }
        if ($allready_paid != 0) {
            $rel_invoice_item->invoice_amount = $rel_invoice_item->total_amount - $allready_paid;
        } else {
            $rel_invoice_item->invoice_amount = $rel_invoice_item->total_amount;
        }
        ///////////////////////////////////////
        $rel_invoice_item->trip_amount = $trip->trip_price;
        $rel_invoice_item->adjusted_price = $trip->adjusted_price;
        $rel_invoice_item->adjusted_price_detail = $trip->adjusted_price_detail;

        $rel_invoice_item->remaining_amount = $rel_invoice_item->invoice_amount; //$relInvoice->total_amount;
        $commission_amount = round(($rel_invoice_item->commision / 100) * $rel_invoice_item->invoice_amount, 2);
        $rel_invoice_item->commision_amount = $commission_amount;
        // franchise amount = after deduct commision amount
        $rel_invoice_item->franchise_amount = round($rel_invoice_item->invoice_amount - $rel_invoice_item->commision_amount, 2);
        $rel_invoice_item->remaining_franchise_amount = $rel_invoice_item->franchise_amount;
        $total_amount = $rel_invoice_item->invoice_amount;
        $franchise_total_amount = $rel_invoice_item->franchise_amount;
        $rel_invoice_item->save();
        // after main invoice update
        $invMaster->provider_total_amount = $invMaster->provider_total_amount + $total_amount;
        $invMaster->provider_remaining_amount = $invMaster->provider_remaining_amount + $total_amount;
        $invMaster->franchise_total_amount = $invMaster->franchise_total_amount + $franchise_total_amount;
        $invMaster->franchise_remaining_amount = $invMaster->franchise_remaining_amount +  $franchise_total_amount;
        $invMaster->save();

        return 1;
    }
    public function updateTripsProfit($tripid)
    {
        ////////////////ars
        // try {
        //     DB::beginTransaction();
        $trips = TripMaster::where('id', $tripid)->first();
        if ($trips->payment_status != 1) {
            $res = $this->calculateDriverPay($trips->driver_id, $tripid);
            $trips->insurance_amount = $res['insurance_amount'];
            $trips->driver_pay = $res['total_pay_to_driver'];
            $trips->save();
        }
        ///////////////
        $tlogs =  TripMaster::select('id', 'driver_id', 'driver_pay', 'vehicle_id', 'insurance_amount', 'date_of_service', 'trip_price')
            ->with('log')
            ->with('vehicle:id,miles_per_gallon')
            ->where('id', $tripid)
            ->first();
        if ($tlogs) {
            $total_price = $tlogs->total_price;
            $trip_id = $tlogs->id;
            $vehicle_id = $tlogs->vehicle_id;
            $driver_pay = $tlogs->driver_pay;
            $insurance_amount = $tlogs->insurance_amount;
            $miles_per_gallon = $tlogs->vehicle->miles_per_gallon;
            $driver_id = $tlogs->driver_id;

            $period3_miles = (float) $tlogs->log->period3_miles ?? 0;
            $period2_miles = (float) $tlogs->log->period2_miles ?? 0;
            $total_mileage = $period2_miles + $period3_miles;

            $total_toll_cost = VehicleDriverFuelDetail::where('trip_id', $trip_id)->where('driver_id', $driver_id)->where('vehicle_id', $vehicle_id)->sum('fuel_cost');
            $total_toll_cost = decimal2digitNumber($total_toll_cost, 2);

            $user_id = $trips->user_id;
            $user_info = User::select('default_fuel_cost', 'default_vehicle_avg')->where('id', $user_id)->first();

            $last_fuel_cost = VehicleDriverFuelDetail::select('fuel_cost', 'unit_in_liter')->where('expense_type', 'Fuel')->where('vehicle_id', $vehicle_id)->orderBy('id', 'desc')->first();
            if ($last_fuel_cost) {
                $fuel_cost = $last_fuel_cost->fuel_cost;
                $unit_in_liter = $last_fuel_cost->unit_in_liter;
                if ($unit_in_liter > 0) {
                    $per_litre_fuel_cost = $fuel_cost / $unit_in_liter;
                } else {
                    $per_litre_fuel_cost = $fuel_cost;
                }
            } else {
                $per_litre_fuel_cost = $user_info->default_fuel_cost;
            }

            if ($miles_per_gallon == 0 || $miles_per_gallon == null) {
                $miles_per_gallon = $user_info->default_vehicle_avg;
            }

            if ($miles_per_gallon == 0 || $miles_per_gallon == null) {
                $total_mile_per_gallon = 0;
            } else {
                $total_mile_per_gallon = ($total_mileage / $miles_per_gallon);
            }

            $fuel_cost = $total_mile_per_gallon * $per_litre_fuel_cost;
            $fuel_cost = decimal2digitNumber($fuel_cost);

            $trip_profit = $total_price - ($driver_pay + $insurance_amount + $fuel_cost + $total_toll_cost);
            $trip_profit = decimal2digitNumber($trip_profit);

            $update_arr = array(
                'fuel_cost' => $fuel_cost,
                'trip_profit' => $trip_profit,
                'cron_update' => 1
            );
            TripMaster::where('id', $trip_id)->update($update_arr);
            // DB::commit();
        }
        return true;
        // } catch (\Exception $e) {
        //     $requests = array('error' => 'helper@updatetripsprofit', 'msg' => 'updatetripsprofit ' . $e->getMessage() . ' ' . $e->getLine());
        //     Log::info($requests);
        //     return $requests;
        // }
        ///////////////
    }
    public function tripPriceUpdateAllOver($trip_id, $new_trip_price = '')
    {
        $trip = TripMaster::select('id', 'adjusted_price', 'trip_price', 'total_price', 'invoice_status', 'pay_type', 'driver_id', 'insurance_type')->where('id', $trip_id)->first();
        ////////////////ars
        // try {
        //     DB::beginTransaction();
        if ($new_trip_price != '') {
            if ($trip) {
                if ($new_trip_price > $trip->total_price) {
                    // if value is greater then add in adjust
                    $adjust_price = $new_trip_price - $trip->total_price;
                    // return $adjust_price;
                    $before_adjust = $trip->adjusted_price ?? 0;
                    $total_adjust_price = $before_adjust + $adjust_price;
                    $trip->adjusted_price = $total_adjust_price;
                    // return $total_adjust_price;
                    $trip->total_price = $new_trip_price;
                } else {
                    // if less then deduct adjust and total
                    if ($trip->adjusted_price > 0) {
                        $adjust = $new_trip_price - $trip->adjusted_price;
                        if ($adjust < 0) {
                            $trip->adjusted_price = 0;
                            $trip->trip_price = $new_trip_price;
                            $trip->total_price = $new_trip_price;
                        } else {
                            $adjust_price = $new_trip_price - $trip->adjusted_price;
                            $before_adjust = $trip->adjusted_price ?? 0;
                            $total_adjust_price = $before_adjust - $adjust_price;
                            $trip->adjusted_price = $total_adjust_price;
                            $trip->total_price = $new_trip_price;
                        }
                    } else {
                        $trip->trip_price = $new_trip_price;
                        $trip->total_price = $new_trip_price;
                    }
                }
                $trip->save();
            }
        }
        if ($trip->payment_status == 1) {
            $this->payoutUpdate($trip);
        }
        $this->updateTripsProfit($trip_id);

        if ($trip->invoice_status == 1) {
            $this->invoiceUpdate($trip);
        }
        return true;
        //     DB::commit();
        //
        // } catch (\Exception $e) {
        //     $requests = array('error' => 'commonLogic@tripPriceUpdateAllOver', 'trip_id' => $trip_id, 'msg' => $e->getMessage());
        //     Log::info($requests);
        //     return false;
        // }
    }
    public function payoutUpdate($trip)
    {
        $paid_trips = PayoutPaidTrip::where('trip_id', $trip->id)->first();
        $paid_master = PayoutPaidMaster::where('id', $paid_trips->payout_paid_master_id)->first();
        $payout_method = $trip->pay_type;
        $insurance_type = $trip->insurance_type;
        $driver_id = $trip->driver_id;

        $total_amount = $paid_master->amount - $paid_trips->trip_price_total;
        $total_driver_pay = $paid_master->total_driver_pay - $paid_trips->driver_pay;
        // $total_company_profit = $paid_master->total_company_profit;
        $total_driver_fees = $paid_master->total_driver_fees - $paid_trips->driver_fees;
        $total_insurance_amount = $paid_master->insurance_amount - $paid_trips->insurance_amount;
        // first update trips
        ////

        if ($payout_method == 2) {
            $hourly_rate = $paid_master->hourly_rate;
            $hourly_over_time_rate = $paid_master->hourly_over_time_rate;
            $over_time_hours_value = $paid_master->over_time_seconds;
            $start_date = date('Y-m-d', strtotime($paid_master->start_date));
            $end_date = date('Y-m-d', strtotime($paid_master->end_date));

            $payout_array = array();
            $payout_array['hourly_rate'] = $hourly_rate;
            $payout_array['over_time_rate_per_hour'] = $hourly_over_time_rate;
            $payout_array['over_time_sec'] = $over_time_hours_value;
            $payout_week = $this->getDriverOverTime($start_date, $end_date, $driver_id, $payout_array);
        }
        $current_insurance_amount = 0;
        if ($payout_method == 1) {
            $given_payout_array = array();
            $paid_flag = 1;
            $res = $this->calculateDriverPay($trip->driver_id, $trip->id, $given_payout_array, $paid_flag);
            /////////
            if ($insurance_type == 1) {
                $total_insurance_amount += $res['insurance_amount'];
                $current_insurance_amount = $res['insurance_amount'];
            }
        } else {
            $payout_array['hourly_rate'] = $hourly_rate;
            $payout_array['over_time_rate_per_hour'] = $hourly_over_time_rate;
            $payout_array['over_time_hours_value'] = $over_time_hours_value;
            $res = $this->calculateDriverPayHour($trip->driver_id, $trip->id, $payout_array);
            if ($insurance_type == 1) {
                $total_insurance_amount += $res['insurance_amount'];
                $current_insurance_amount = $res['insurance_amount'];
            }
        }
        // calculate driver pay
        $total_pay_to_driver = $res['total_pay_to_driver'];
        $insurance_amount = 0;
        if ($insurance_type == 1) {
            $insurance_amount  = $res['insurance_amount'];
        }
        $update_arr = array(
            'driver_pay' => $total_pay_to_driver,
            'insurance_amount' => $insurance_amount,
            // 'insurance_type' => $insurance_type,
        );
        TripMaster::where('id', $trip->id)->update($update_arr);
        //caculate trip pryout
        // updatetripsprofit($trip->id);
        // insert payment log
        // Driverpaymentstatuschangelogs::insert($insert_arr);
        // insert relation trip entries
        // store relation entries
        $total_amount += $res['total_price'];
        $total_driver_pay += $total_pay_to_driver;
        $total_driver_fees += $res['driver_fee'];
        $relation_payout = $paid_trips; //new  PayoutPaidTrips();
        $relation_payout->driver_pay = $total_pay_to_driver;
        $relation_payout->trip_price_total = $res['total_price'];
        $relation_payout->driver_fees = $res['driver_fee'];
        $relation_payout->company_profit = $res['company_profit'];
        $relation_payout->insurance_amount = $current_insurance_amount;

        $relation_payout->save();
        /////////////
        $all_deduction = $paid_master->deduction_amount;
        $all_reimbursement = $paid_master->reimbursement_amount;
        ///////////
        $total_insurance_amount = round($total_insurance_amount, 2);
        $total = $total_driver_pay + $all_reimbursement;
        $total = $total - $all_deduction;
        if ($insurance_type == 1) {
            $total = $total - $total_insurance_amount;
        }
        $total_driver_fees = round($total_driver_fees, 2);
        // $payout_master->reimbursement_amount = $all_reimbursement;
        // $payout_master->deduction_amount = $all_deduction;
        $paid_master->amount = $total_amount;
        $paid_master->total_driver_pay = $total_driver_pay;
        // $paid_master->total_company_profit = $total_company_profit;
        $paid_master->total_company_profit = $total_amount - $total - $total_insurance_amount;
        // $paid_master->trip_count = $total_price_count;
        $paid_master->final_driver_pay = $total;
        // $paid_master->total_miles = $total_miles;
        $paid_master->insurance_amount = $total_insurance_amount;
        $paid_master->insurance_type = $insurance_type;
        $paid_master->total_driver_fees = $total_driver_fees;

        // total
        if ($payout_method == 2) {
            $paid_master->normal_payout = $payout_week['total_normal_time_payout'];
            $paid_master->overtime_payout = $payout_week['total_over_time_payout'];
            $paid_master->total_payout = $payout_week['total_payout'];
            $total = $payout_week['total_payout'] + $all_reimbursement;
            $total = $total - $all_deduction;
            $total_insurance_amount = round($total_insurance_amount, 2);
            if ($insurance_type == 1) {
                $total = $total - $total_insurance_amount;
            }
            $company_profit = $payout_week['all_trip_price'] - $total - $total_insurance_amount;
            $paid_master->total_company_profit = $company_profit;
            $paid_master->final_driver_pay = $total;
            // normal seconds and over time seconds
            $paid_master->total_seconds = $payout_week['total_seconds'];
            $paid_master->normal_working_seconds = $payout_week['normal_working_seconds'];
            //
        }
        $paid_master->save();
        return 1;
    }

    public function checkLogsGenerate($tripid, $driverid)
    {
        $update_arr = array();
        $trip = TripMaster::select('status_id')->where('id', $tripid)->first();

        try {
            DB::beginTransaction();
            $start_time = TripStatusLog::select('date_time', 'current_lat', 'current_lng', 'timezone')
                ->where('trip_id', $tripid)
                ->where('driver_id', $driverid)
                ->where('status', 3)->get()->toArray();
            if (!empty($start_time)) {
                if ($start_time[0]["timezone"] != '') {
                    $start_time_val = $start_time[0]["date_time"];
                } else {
                    $start_time_val = 'NA';
                }
            } else {
                $start_time_val = 'NA';
            }
            $member_on_board = TripStatusLog::select('date_time', 'current_lat', 'current_lng', 'timezone')
                ->where('trip_id', $tripid)
                ->where('driver_id', $driverid)
                ->where('status', 6)->get()->toArray();

            if (!empty($member_on_board)) {
                if ($member_on_board[0]["timezone"] != '') {
                    $member_on_board_val = $member_on_board[0]["date_time"];
                } else {
                    $member_on_board_val = 'NA';
                }
            } else {
                $member_on_board_val = 'NA';
            }
            $dropoff_time = TripStatusLog::select('date_time', 'current_lat', 'current_lng', 'timezone')
                ->where('trip_id', $tripid)
                ->where('driver_id', $driverid)
                ->where('status', 9)->get()->toArray();

            if (!empty($dropoff_time)) {
                if ($dropoff_time[0]["timezone"] != '') {
                    $dropoff_time_val = $dropoff_time[0]["date_time"];
                } else {
                    $dropoff_time_val = 'NA';
                }
            } else {
                $dropoff_time_val = 'NA';
            }
            $pickup_time = TripStatusLog::select('date_time', 'current_lat', 'current_lng', 'timezone')
                ->where('trip_id', $tripid)
                ->where('driver_id', $driverid)
                ->where('status', 4)->get()->toArray();

            if (!empty($pickup_time)) {
                if ($pickup_time[0]["timezone"] != '') {
                    $pickup_time_val = $pickup_time[0]["date_time"];
                } else {
                    $pickup_time_val = 'NA';
                }
            } else {
                $pickup_time_val = 'NA';
            }

            if ($trip->status_id == '3') {
                if ($start_time_val == 'NA' || $pickup_time_val == 'NA' || $member_on_board_val == 'NA' || $dropoff_time_val == 'NA') {
                    $update_arr = array(
                        'log_status' => 'Missing',
                    );
                    TripMaster::where('id', $tripid)->update($update_arr);
                } elseif ($start_time_val != 'NA' && $pickup_time_val != 'NA' && $member_on_board_val != 'NA' && $dropoff_time_val != 'NA') {
                    $update_arr = array(
                        'log_status' => 'Complete',
                    );
                    TripMaster::where('id', $tripid)->update($update_arr);
                }
            } elseif ($trip->status_id == '5' || $trip->status_id == '6') {
                if ($start_time_val == 'NA' || $pickup_time_val == 'NA') {
                    $update_arr = array(
                        'log_status' => 'Missing',
                    );
                    TripMaster::where('id', $tripid)->update($update_arr);
                } elseif ($start_time_val != 'NA' && $pickup_time_val != 'NA') {
                    $update_arr = array(
                        'log_status' => 'Complete',
                    );
                    TripMaster::where('id', $tripid)->update($update_arr);
                }
            } elseif ($trip->status_id == '13' || $trip->status_id == '8' || $trip->status_id == '2' || $trip->status_id == '9') {
                $update_arr = array(
                    'log_status' => 'NA',
                );
                TripMaster::where('id', $tripid)->update($update_arr);
            } elseif ($trip->status_id == '1' || $trip->status_id == '4' || $trip->status_id == '7') {
                $update_arr = array(
                    'log_status' => 'Scheduled',
                );
                TripMaster::where('id', $tripid)->update($update_arr);
            } elseif ($trip->status_id == '10' || $trip->status_id == '11' || $trip->status_id == '12') {
                $update_arr = array(
                    'log_status' => 'In Progress',
                );
                TripMaster::where('id', $tripid)->update($update_arr);
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            $requests = array('error' => 'helper@checkloggenerate', 'trip_id' => $tripid, 'msg' => 'checkloggenerate ' . $e->getMessage() . ' ' . $e->getLine());
            Log::info($requests);
            return false;
        }
        return true;
    }

    public function driverLogPercent($trip_id, $driver_id)
    {
        try {
            DB::beginTransaction();

            $trip = TripMaster::find($trip_id);
            $log = TripLog::select(
                'id',
                'period2_miles',
                'period3_miles',
                DB::raw('TIME_TO_SEC(period3) as period_3')
            )->where([['trip_id', $trip_id], ['driver_id', $driver_id]])->first();

            $log->estimated_unloaded_miles = $trip->estimated_mileage_frombase_location;
            $log->estimated_loaded_miles = $trip->estimated_trip_distance;
            $log->estimated_trip_duration = $trip->estimated_trip_duration;
            $log->trip_status = $trip->status_id;
            $log->save();

            $percentage_diff_in_unloaded_miles = 0;
            $percentage_diff_in_loaded_miles = 0;
            $percentage_diff_in_trip_duration = 0;

            // for completd trips
            if ($trip->status_id == 3) {
                /* start Unloaded Miles */
                $difference_in_unloaded_miles = $log->estimated_unloaded_miles - $log->period2_miles;
                $percentage_diff_in_unloaded_miles = 0;

                if ($log->estimated_unloaded_miles == $log->period2_miles) {
                    $percentage_diff_in_unloaded_miles = 100;
                } elseif ($difference_in_unloaded_miles <= 0.1 && $difference_in_unloaded_miles >= 0) {
                    $percentage_diff_in_unloaded_miles = 90;
                } elseif ($log->estimated_unloaded_miles) {
                    $percentage_diff_in_unloaded_miles = $log->period2_miles / $log->estimated_unloaded_miles * 100;
                }
                /*end of unloaded Miles*/

                /* start loaded Miles */
                $difference_in_loaded_miles = $log->estimated_loaded_miles - $log->period3_miles;
                $percentage_diff_in_loaded_miles = 0;
                if ($log->estimated_loaded_miles == $log->period3_miles) {
                    $percentage_diff_in_loaded_miles = 100;
                } elseif ($difference_in_loaded_miles <= 0.1 && $difference_in_loaded_miles >= 0) {
                    $percentage_diff_in_loaded_miles = 90;
                } elseif ($log->estimated_loaded_miles) {
                    $percentage_diff_in_loaded_miles = $log->period3_miles / $log->estimated_loaded_miles * 100;
                }

                /*end of loaded Miles*/
                $difference_in_trip_duration = 0;
                $percentage_diff_in_trip_duration = 0;
                if ($log->estimated_trip_duration) {
                    $difference_in_trip_duration = $log->estimated_trip_duration - $log->period_3;
                    $percentage_diff_in_trip_duration = $log->period_3 / $log->estimated_trip_duration * 100;
                }

                $log->diff_in_unloaded_miles = $difference_in_unloaded_miles;
                $log->diffpercent_in_unloaded_miles = round($percentage_diff_in_unloaded_miles, 2);
                $log->diff_in_loaded_miles = $difference_in_loaded_miles;
                $log->diffpercent_in_loaded_miles = round($percentage_diff_in_loaded_miles, 2);
                $log->diff_in_period3_duration = abs($difference_in_trip_duration);
                $log->diffpercent_in_period3_duration = round($percentage_diff_in_trip_duration, 2);
                $log->save();
            }
            /*for no show and late cancellation*/ elseif ($trip->status_id == 6 || $trip->status_id == 5) {
                /* start Unloaded Miles */
                $difference_in_unloaded_miles = $log->estimated_unloaded_miles - $log->period2_miles;
                $percentage_diff_in_unloaded_miles = 0;

                if ($log->estimated_unloaded_miles == $log->period2_miles) {
                    $percentage_diff_in_unloaded_miles = 100;
                } elseif ($difference_in_unloaded_miles <= 0.1 && $difference_in_unloaded_miles >= 0) {
                    $percentage_diff_in_unloaded_miles = 90;
                } elseif ($log->estimated_unloaded_miles) {
                    $percentage_diff_in_unloaded_miles = $log->period2_miles / $log->estimated_unloaded_miles * 100;
                }
                $log->diff_in_unloaded_miles = $difference_in_unloaded_miles;
                $log->diffpercent_in_unloaded_miles = round($percentage_diff_in_unloaded_miles, 2);
                $log->diff_in_loaded_miles = 0;
                $log->diffpercent_in_loaded_miles = 0;;
                $log->diff_in_period3_duration = 0;
                $log->diffpercent_in_period3_duration = 0;
                $log->save();
            } else {
                $log->diff_in_unloaded_miles = 0;
                $log->diffpercent_in_unloaded_miles = 0;
                $log->diff_in_loaded_miles = 0;
                $log->diffpercent_in_loaded_miles = 0;
                $log->diff_in_period3_duration = 0;
                $log->diffpercent_in_period3_duration = 0;
                $log->save();
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            $requests = array('error' => 'helper@driverLogPercent', 'trip_id' => $trip_id, 'msg' => 'driverLogPercent ' . $e->getMessage() . ' ' . $e->getLine());
            Log::info($requests);
            return false;
        }

        return true;
    }
}
