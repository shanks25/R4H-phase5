<?php

namespace App\Http\Controllers\Franchise;

use Carbon\Carbon;
use App\Models\Member;
use App\Models\TripImport;
use App\Models\TripMaster;
use App\Imports\UsersImport;
use App\Models\BaseLocation;
use Illuminate\Http\Request;
use App\Models\MemberAddress;
use Illuminate\Validation\Rule;
use App\Models\Triplevelofservice;
use App\Http\Controllers\Controller;
use App\Http\Resources\TripResource;
use Facades\App\Repository\TripRepo;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\ImportTripRequest;
use Illuminate\Support\Facades\Validator;
use Facades\App\Repository\ImportTrips\Access2Care;

class ImportTripController extends Controller
{
    /**
     * index of import trip
     *
     * @param ImportTripRequest $request
     * @return void
     */
    public function index(ImportTripRequest $request)
    {
        $array =     Excel::toArray(new UsersImport, $request->file('import'));
        $request->merge(['excel'=>$array[0]]);
        return    $trips = collect($array[0]);
 
      
        if ($request->provider_template_id == 1) {
            $trips =  $trips->map(function ($trip, $key) use ($request) {
                $trip['pickup_address'] = concatAddress($trip['pickup_address'], $trip['pickup_city'], $trip['pickup_state'], $trip['pickup_zip']) ;
                $trip['dropoff_address'] = concatAddress($trip['dropoff_address'], $trip['dropoff_city'], $trip['dropoff_state'], $trip['dropoff_zip']) ;
                if (isset($trip['pickup_time']) && trim($trip['pickup_time'])) {
                    $trip['trip_datetime_asper_request_timezone'] = Carbon::parse($trip['pickup_time']);
                } else {
                    $trip['trip_datetime_asper_request_timezone'] = null;
                }


                return $trip;
            });

            $trips =  $trips->groupBy('passenger_name')->values();
            foreach ($trips as $key1 => $legs) {
                //laravel collection sortby asc except for null hence -trip_datetime_asper_request_timezone (negative/minus)
                $legs = $legs->sortBy('-trip_datetime_asper_request_timezone')->values() ;
                $member_array = [] ;
                $group_id =tripGroupId();
                $trips_collection = [];

                foreach ($legs as $key =>  $leg) {
                    $leg = (object) $leg;

                    /* if loop is for 2nd leg or more then return member which was already created in the first leg */
                    if ($key > 0) {
                        $member = $member_array['member']  ;
                    } else {
                        $member = Member::create([
                            'name'=>$leg->passenger_name ,
                            'mobile_no'=>$leg->passenger_phone_number ,
                            'user_id'=>esoId() ,
                            'first_name'=>splitName($leg->passenger_name)['first_name'] ,
                            'middle_name'=>splitName($leg->passenger_name)['middle_name'] ,
                            'last_name'=>splitName($leg->passenger_name)['last_name'] ,
                  ]);
                        $member_array['member'] =  $member ;
                    }

                    $pickup_address = MemberAddress::where([['member_id','=',$member->id], ['street_address','=',$leg->pickup_address]])->first();
                    if (!$pickup_address) {
                        $pickup_address =     $member->addresses()->create([
                            'address_name'=> 'Home',
                            'street_address'=>$leg->pickup_address ,
                            'zipcode'=>$leg->pickup_zip ,
                        ]);
                    }


                    $drop_address = MemberAddress::where([['member_id','=',$member->id], ['street_address','=',$leg->dropoff_address]])->first();
                    if (!$drop_address) {
                        $drop_address =     $member->addresses()->create([
                            'address_name'=> 'Home',
                            'street_address'=>$leg->dropoff_address ,
                            'zipcode'=>$leg->dropoff_zip ,
                        ]);
                    }

                    $leg->shedule_pickup_time = null;
                    $leg->date_of_service = null;
                    if ($leg->trip_datetime_asper_request_timezone) {
                        $trip_date_time = $leg->trip_datetime_asper_request_timezone->setTimezone(config('app.timezone'));
                        $leg->date_of_service =$trip_date_time->format('Y-m-d');
                        $leg->shedule_pickup_time =$trip_date_time->format('H:i');
                    } else {
                        $trip_date_time = '';
                        $leg->date_of_service = $trips_collection[0]->date_of_service;
                        $leg->shedule_pickup_time ='';
                    }
                    

                    $master_level_of_service_id = 0;
                    $level_of_service = 0;
                    if ($leg->service_level) {
                        $leveofservice = Triplevelofservice::where('value', $leg->service_level)->first();
                        if (!$leveofservice) {
                            $leveofservice =    Triplevelofservice::create([
                                                    'name' =>$leg->service_level,
                                                    'value' =>$leg->service_level,
                                                    'user_id' => esoId(),
                                                    'master_id' => 5
                                            ]);
                        }
                        $level_of_service = $leveofservice->value;
                        $master_level_of_service_id = $leveofservice->master_id;
                    }

                    $trip_dateTime = storeDateTime($leg->date_of_service, $leg->shedule_pickup_time, $request->timezone);

                    $trip =    TripMaster::create([

                        'leg_no' =>($key + 1),
                        'user_id' =>esoId(),
                        // 'trip_format' =>$leg->trip_format,
                        'parent_id'=> TripRepo::getParentId($trips_collection, $key),
                        'group_id' =>$group_id ,
                        'trip_import_id' => request('trip_import_id'),
            
                        'trip_no' =>$leg->bolt_trip_id,
                        'notes_or_instruction' =>$leg->pickup_notes,
            
                        'date_of_service' =>$leg->date_of_service,
                        'shedule_pickup_time' =>$leg->shedule_pickup_time ,
                        'week_day' =>$trip_dateTime->dayName,
            
                        'member_id' =>$member->id,
                        'member_name' =>$member->name,
                        'first_name'=>$member->first_name,
                        'first_middle'=>$member->first_middle,
                        'first_last'=>$member->first_last,
                        'member_phone_no'=>$member->mobile_no,
            
            
                        'pickup_address'=>$pickup_address->street_address,
                        'pickup_zip'=>$pickup_address->zipcode,
                        'pickup_member_address_id'=>$pickup_address->id,
            
            
                        'drop_member_address_id'=>$drop_address->id,
                        'drop_address'=>$drop_address->street_address,
                        'drop_zip'=>$drop_address->zipcode,

                        'payor_type' =>$request->payor_type,
                        'payable_type' =>payorTypeModel($request->payor_type),
                        'payor_id' =>$request->provider_id,
            
            
                    ]);


                    $trip_google_details =getGoogleDetails($pickup_address->street_address, $drop_address->street_address);
              
                    $trip->update([
                        'pickup_lat'=>$trip_google_details['pickup_lat'],
                        'pickup_lng'=>$trip_google_details['pickup_lng'],
                        'drop_lat'=>$trip_google_details['dropoff_lat'],
                        'drop_lng'=>$trip_google_details['dropoff_lng'],
                        'estimated_trip_duration'=>$trip_google_details['totalduration'],
                        'estimated_trip_distance'=>$trip_google_details['totalDistance'],
                        // 'appointment_time'=>calculateTimes($leg->shedule_pickup_time, $trip_dateTime, $trip_google_details['totalduration']),
                        
                    ]);

                    $trips_collection[] =  $trip->load(TripRepo::loadTripRelation());
                    $all_recurring_trips_collection[$key1]=  $trips_collection;
                }
            }
    
            $trips = collect($all_recurring_trips_collection)->collapse();
            $collection = TripResource::collection($trips);
    
            return    merge(['data'=>$collection], metaData(true, $request, '1009', count($collection). ' trip added successfully', '', '', ''));
        }


        if ($request->provider_template_id == 2) {
            return    $this->access2Care($trips, $request);
        }
    }



    /**
     * access 2 care
     *
     * @param collection $trips
     * @param Request $request
     * @return void
     */
    public function access2Care($trips, $request)
    {
        $trips =  Access2Care::modifyExcel($trips);

        $trips =  $trips->groupBy('name')->values();
        $all_recurring_trips_collection = [];
        foreach ($trips as $key1 => $legs) {
            $legs = $legs->sortBy('leg_no')->values() ;
            $group_id =tripGroupId();
            $trips_collection = [];
            $member_array = [] ;

            foreach ($legs as $key =>  $leg) {
                $leg = (object) $leg;

                
                // get member by creating new one or fetch from existing one
                $member =  Access2Care::member($leg, $key, $member_array, $request);
                $member_array['member'] = $member ;
            
                // store member pickup and dropoff address
                $addresses =  Access2Care::memberAddresses($member, $leg);
                $pickup_address = $addresses['pickup_address'];
                $drop_address = $addresses['drop_address'];
             

                $trip_dateTime = storeDateTime($leg->date_of_service, $leg->appointment_time, $request->timezone);


                $baselocation = BaseLocation::whereRaw("find_in_set('" . $pickup_address->zipcode . "', zipcode)")->eso()->first();
                if (!$baselocation) {
                    $baselocation = BaseLocation::where("is_default_location", 1)->eso()->first();
                }
                 
                // what if we are importing trip and base location is not added for the ESO
                if ($baselocation) {
                    $base_distance = googleMatrix($baselocation->address, $pickup_address->street_address);
                }
                

               
                $trip =  Access2Care::createTrip($key, $trip_dateTime, $leg, $member, $trips_collection, $group_id, $pickup_address, $drop_address, $request);


                // move this to dispatch
                $trip_google_details =getGoogleDetails($pickup_address->street_address, $drop_address->street_address);
              
                $trip->update([
                    'pickup_lat'=>$trip_google_details['pickup_lat'],
                    'pickup_lng'=>$trip_google_details['pickup_lng'],
                    'drop_lat'=>$trip_google_details['dropoff_lat'],
                    'drop_lng'=>$trip_google_details['dropoff_lng'],
                    'estimated_trip_duration'=>$trip_google_details['totalduration'],
                    'estimated_trip_distance'=>$trip_google_details['totalDistance'],
                    'shedule_pickup_time'=>calculateTimes($leg->appointment_time, $trip_dateTime, $trip_google_details['totalduration']),

                ]);

                $trips_collection[] =  $trip->load(TripRepo::loadTripRelation());
                $all_recurring_trips_collection[$key1]=  $trips_collection;
            }
        }

        $trips = collect($all_recurring_trips_collection)->collapse();
        $collection = TripResource::collection($trips);

        return    merge(['data'=>$collection], metaData(true, $request, '1009', count($collection). ' trip added successfully', '', '', ''));
    }

    /*     public function updateGoogleData(Request $request)
        {
          return  ;
        } */
}
