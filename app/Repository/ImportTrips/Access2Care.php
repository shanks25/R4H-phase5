<?php
namespace App\Repository\ImportTrips;

use App\Models\Member;
use App\Models\TripMaster;
use App\Models\MemberAddress;
use App\Models\ProviderMaster;
use App\Models\ProviderTemplate;
use Facades\App\Repository\TripRepo;

class Access2Care
{
    public function modifyExcel($trips)
    {
        return    $trips->map(function ($trip, $key) {
            $last_word = substr($trip['trip_number'], -1);

            $trip['main_trip'] = substr($trip['trip_number'], 0, -1);  //  removing last T/R from the trip number
            $trip['trip_format'] = $trip['appointment_time'] == 'Will Call' ? 3 : 1  ; // 3 is for will call and 1 is for normal
            $trip['appointment_time'] = $trip['appointment_time'] != 'Will Call' ? $trip['appointment_time'] : null ;
            $trip['date_of_service'] =dateConvertToYMD($trip['trip_date']);
            // "T" will always be the first leg and other legs will be 2 which i am incrementing in foreach loop
            $trip['leg_no'] = $last_word == 'T' ? 1 : 2 ;
            $trip['member_dob'] = dateConvertToYMD($trip['member_dob']) ;
            $trip['mobile_no'] = getNumber($trip['member_phone']) ;
            $trip['trip_import_id'] = request('trip_import_id') ;
            
            return $trip;
        });
    }

    /**
     * return member by creating new or getting from parent leg
     *
     * @param Request $request
     * @return object
     */
    public function member($leg, $key, $member_array, $request)
    {
        /* if loop is for 2nd leg or more then return member which was already created in the first leg */
        if ($key > 0) {
            return    $member = $member_array['member']  ;
        }
        return    $member = Member::create([
                'dob'=>$leg->member_dob ,
                'name'=>$leg->name ,
                'mobile_no'=>$leg->mobile_no ,
                'phone_number'=>$leg->mobile_no ,
                'user_id'=>esoId() ,
                'first_name'=>splitName($leg->name)['first_name'] ,
                'middle_name'=>splitName($leg->name)['middle_name'] ,
                'last_name'=>splitName($leg->name)['last_name'] ,
                'primary_payor_type'=>3 ,
                'primary_payor_id'=>$request->provider_id ,
            ]);
    }

    public function memberAddresses($member, $leg)
    {
        $joined_pickup_address =concat($leg->pickup_address, $leg->pickup_address_2);
        $joined_drop_address = concat($leg->destination_address, $leg->destination_address_2);

        $pickup_address = MemberAddress::where([['member_id','=',$member->id], ['street_address','=',$joined_pickup_address]])->first();
        if (!$pickup_address) {
            $pickup_address =     $member->addresses()->create([
            'address_name'=> 'Home',
            'street_address'=>$joined_pickup_address ,
            'zipcode'=> extractZipcode($joined_pickup_address),
        ]);
        }

        $drop_address = MemberAddress::where([['member_id','=',$member->id], ['street_address','=',$joined_drop_address]])->first();
        if (!$drop_address) {
            $drop_address = $member->addresses()->create([
                'street_address'=>$joined_drop_address ,
                'zipcode'=> extractZipcode($joined_drop_address),
                'address_name'=> $leg->destination_name ?? 'address1',
          ]);
        }
        return ['pickup_address'=>$pickup_address,'drop_address'=>$drop_address] ;
    }

    /**
     * import trip function
     *
     * @param [type] $key - foreach key
     * @param [type] $trip_dateTime -
     * @param [type] $leg
     * @return object
     */
    public function createTrip($key, $trip_dateTime, $leg, $member, $trips_collection, $group_id, $pickup_address, $drop_address, $request)
    {
        // $common_array = [  ];
        $trip =    TripMaster::create([
            'user_id' =>esoId(),
            'group_id' =>$group_id ,
            'leg_no' =>($key + 1),
          
            'trip_format' =>$leg->trip_format,
            'parent_id'=> TripRepo::getParentId($trips_collection, $key),
            
            'trip_import_id' => request('trip_import_id'),

            'payor_type' =>$request->payor_type,
            'payable_type' =>payorTypeModel($request->payor_type),
            'payor_id' =>$request->provider_id,

            'trip_no' =>$leg->trip_number,
            'pickup_county'=>$leg->pickup_county,
            'notes_or_instruction' =>$leg->provider_notes,

            'date_of_service' =>$trip_dateTime->format('Y-m-d'),
            'appointment_time' =>$leg->appointment_time ?  $trip_dateTime->format('H:i') : null,
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


        ]);
        
        
        return  $trip;
    }
}
