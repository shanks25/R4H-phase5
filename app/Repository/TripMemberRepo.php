<?php
namespace App\Repository;

use App\Models\Member;
use App\Models\MemberAddress;

class TripMemberRepo
{
    public function getMember($request, $leg)
    {
        $leg['member_id'] = $request->member_id;
        $leg = (object) $leg;
        
            
        if ($request->missing('member_id')) {

        // write code to check if ssn belongs to existing member
            $member =Member::where('ssn', $request->ssn)->first();
            if ($member) {
                $member->mobile_no =$request->member_phone_no;
                $member->save();
                $leg->member_id = $member->id;
                goto end; //if member exists this code will exit the if loop
            }

            // fresh member

            $request->merge(
                ['mobile_no'=>$request->member_phone_no,'primary_payor_type'=>$request->payor_type,'primary_payor_id'=>$request->payor_id,'user_id'=>$request->eso_id]
            );
            $member_array=    $request->only(
                ['first_name', 'middle_name', 'last_name', 'dob', 'ssn', 'mobile_no','primary_payor_type','primary_payor_id','user_id']
            );

            $member = Member::create($member_array);
            $leg->member_id =  $member->id;
         
            $pickup_array =  $this->getPickupArray($leg);
          
            $member_pickup_address = $this->addMemberAddress($pickup_array);
            $pickup_member_address_id = $member_pickup_address->id;

            $drop_array = $this->getDropArray($leg);
            $member_drop_address = $this->addMemberAddress($drop_array);
            $drop_member_address_id =$member_drop_address->id ;
        }
        end:

        if (!isset($member)) {
            $member = Member::find($request->member_id);
        }

        $pickup_member_address_id = $leg->pickup_member_address_id  ?? '';
        $drop_member_address_id = $leg->drop_member_address_id ?? '';

        // existing member with new pickupaddress
        if (!isset($leg->pickup_member_address_id)) {
            $pickup_array =  $this->getPickupArray($leg);
            $member_pickup_address = $this->addMemberAddress($pickup_array);
            $pickup_member_address_id = $member_pickup_address->id;
        }

        // existing member with new dropoff address
        if (!isset($leg->drop_member_address_id)) {
            $drop_array =  $this->getDropArray($leg);
            $member_drop_address = $this->addMemberAddress($drop_array);
            $drop_member_address_id =$member_drop_address->id;
        }
 
        $leg->member_id =$leg->member_id;
        $leg->pickup_member_address_id =$pickup_member_address_id;
        $leg->drop_member_address_id =$drop_member_address_id;
        $leg->member_first_name =$member->first_name;
        $leg->member_middle_name =$member->middle_name;
        $leg->member_last_name =$member->last_name;
        $leg->member_last_name =$member->last_name;
        $leg->member_phone_no =$member->mobile_no;
        return ($leg);
        // return ['member'=> $member,'leg'=>$leg] ;
    }

    /**
     * create member address
     *
     * @param [type] $address
     * @return object
     */
    public function addMemberAddress($address)
    {
        return MemberAddress::create($address) ;
    }


    /**
     * get pickup address of trip
     *
     * @param [type] $leg
     * @return array
     */
    public function getPickupArray($leg)
    {
        return  [
            'address_name'=> $leg->pickup_address_type_name,
            'location_type'=> $leg->pickup_location_type,
            'facility_autofill_id'=> $leg->pickup_facility_id,
            'department_name'=> $leg->pickup_department_name,
            'street_address'=> $leg->pickup_address,
            'zipcode'=> $leg->pickup_zip,
            'latitude'=> $leg->pickup_lat,
            'longitude'=> $leg->pickup_lng,
            'member_id'=> $leg->member_id,
            'added_by'=> 'trip_master',
     ] ;
    }

    /**
     * get dropoff address of trip
     *
     * @param [type] $leg
     * @return array
     */
    public function getDropArray($leg)
    {
        return [
        'address_name'=> $leg->drop_address_type_name,
        'location_type'=> $leg->drop_location_type,
        'facility_autofill_id'=> $leg->drop_facility_id,
        'department_name'=> $leg->drop_department_name,
        'street_address'=> $leg->drop_address,
        'zipcode'=> $leg->drop_zip,
        'latitude'=> $leg->drop_lat,
        'longitude'=> $leg->drop_lng,
        'member_id'=> $leg->member_id,
        'added_by'=> 'trip_master',
      ] ;
    }
}
