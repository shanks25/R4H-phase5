<?php

namespace App\Http\Controllers\Franchise;

use App\Models\Crm;
use App\Models\Member;
use App\Models\Facility;
use App\Models\PayorType;
use Illuminate\Http\Request;
use App\Models\MemberAddress;
use App\Models\ProviderMaster;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\MemberResource;
use App\Http\Resources\MemberCollection;
use App\Http\Requests\MemberStoreRequest;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\MemberUpdateRequest;
use App\Http\Resources\MemberTripResource;
use App\Http\Resources\MemberListCollection;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $members =  Member::eso()->limit(200)->get();
        return new MemberCollection($members);
    }

    public function searchMember(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'last_name' => 'required',
        ]);

        if ($validator->fails()) {
            $metaData = metaData('false', $request, '1002', '', '400', '', $validator->messages());
            return response()->json($metaData, 200);
        }
        $member = Member::eso()->where('last_name', 'like', '%' .$request->last_name.'%')->paginate(config('Settings.pagination')) ;
        return new MemberCollection($member);
    }

    public function memberAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'member_id' => [ 'required', Rule::exists('members_master', 'id')->where('user_id', esoId())->whereNull('deleted_at')]
        ]);

        if ($validator->fails()) {
            $metaData = metaData('false', $request, '1003', '', '400', '', $validator->messages());
            return response()->json($metaData, 200);
        }

        $member =  Member::eso()->with('addresses:id,member_id,address_name,location_type,facility_autofill_id,street_address,zipcode', 'addresses.facility:id,name')
          ->with('primaryPayor:id,name')
          ->with('masterLevelOfService:id,name')
          ->find($request->member_id) ;
           
        return new MemberTripResource($member);
    }

    public function memberList(Request $request)
    {
        $query =Member::eso()->with('trips', 'address.state')
        ->with('masterLevelOfService:id,name');
        $members=Member::filterMember($request, $query)
        ->latest()->paginate(config('Settings.pagination')) ;
        return new MemberListCollection($members);
    }
    
    public function show(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
        'id' => ['required', Rule::exists('members_master', 'id,deleted_at,NULL')->where('user_id', esoId())]],
            [
            'id.required' => 'ID is required.',
            'id.exists' => 'Invalid ID',
            
        ]
        );
        if ($validator->fails()) {
            return $metaData = metaData('false', $request, '30022', '', '502', '', $validator->messages());
        }
        
        try {
            $member=Member::where('id', $request->id)
                ->with('address.state', 'addresses', 'primaryPayor', 'primaryfacility', 'secondaryfacility', 'primaryprovider', 'secondaryprovider', 'payor', 'primaryDepartment', 'secondaryDepartment', 'categories')
                ->with('masterLevelOfService:id,name')
                ->first();
          
            $metaData= metaData(true, $request, '30022', 'success', 200, '');
            return (new MemberResource($member))->additional($metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, 30022, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }

    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
         'id' =>  ['required', Rule::exists('members_master', 'id,deleted_at,NULL')->where('user_id', esoId())]
          ], [
            'id.required' => 'ID is required.',
            'id.exists' => 'Invalid ID',
            
        ]);

        if ($validator->fails()) {
            return metaData('false', $request, '30021', '', '502', '', $validator->messages());
        }

        try {
            if ($member=Member::find($request->id)) {
                $member->delete();
                $metaData=metaData(true, $request, '30021', 'success', 200, '');
                return  merge($metaData, ['data'=>['deleted_id'=>$request->id]]);
            } else {
                return metaData(false, $request, '30021', '', 502, '', 'Invalid vehicle ID ');
            }
        } catch (\Exception $e) {
            return metaData(false, $request, '30021', '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }

    public function store(MemberStoreRequest $request)
    {
        $name = $request->first_name;
        if ($request->filled('middle_name')) {
            $name .= ' ' . $request->middle_name;
        }
        $name .= ' ' . $request->last_name;
          
       
        try {
            $request-> merge(['name' =>$name]);
            
            $exceptData=array('address_type', 'state_id', 'street_address', 'zipcode', 'location_type', 'facility_autofill', 'latitude', 'longitude', 'department', 'eso_id');
           
            $member=Member::Create($request->except($exceptData));
        
            foreach ($request->address_type as $key => $address_type) {
                $member_address = new MemberAddress;
                $member_address->member_id = $member->id;
                $member_address->address_type = $address_type;
                $member_address->street_address = $request->street_address[$key];
                $member_address->latitude = $request->latitude[$key];
                $member_address->longitude = $request->longitude[$key];
                $member_address->state_id = $request->state_id[$key];
                $member_address->zipcode = $request->zipcode[$key];
                $member_address->location_type = $request->location_type[$key];
                $member_address->department = $request->department[$key];
                $member_address->facility_autofill_id = $request->facility_autofill[$key];
                $member_address->save();
            }


          
            $metaData= metaData(true, $request, '30022', 'success', 200, '');
            return (new MemberResource($member))->additional($metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, 30022, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }


    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required', Rule::exists('members_master', 'id,deleted_at,NULL')->where('user_id', esoId())]
                
        ], [
            'id.required' => 'ID is required.',
            'id.exists' => 'Invalid ID',
            
        ]);
        if ($validator->fails()) {
            return $metaData = metaData('false', $request, '30024', '', '502', '', $validator->messages());
        }
        
        try {
            $member=Member::where('id', $request->id)
                ->with('addresses', 'primaryPayor', 'primaryfacility', 'secondaryfacility', 'primaryprovider', 'secondaryprovider', 'payor', 'primaryDepartment', 'secondaryDepartment', 'categories')
                ->with('masterLevelOfService:id,name')
                ->first();
            //return $member;
            $metaData= metaData(true, $request, '30024', 'success', 200, '');
            return  merge(['data' => $member], $metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, 30024, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }

    public function update(MemberUpdateRequest $request)
    {
        $name = $request->first_name;
        if ($request->filled('middle_name')) {
            $name .= ' ' . $request->middle_name;
        }
        $name .= ' ' . $request->last_name;
          
       
        try {
            $request-> merge(['name' =>$name]);
            $request-> merge(['updated_at' =>now()]);
            $exceptData=array('address_type', 'state_id', 'street_address', 'zipcode', 'location_type', 'facility_autofill', 'latitude', 'longitude', 'department', 'eso_id','address_id');
            $member=Member::find($request->id);
            $member->update($request->except($exceptData));
           
        
            foreach ($request->address_type as $key => $address_type) {
                if ($request->address_id[$key]) {
                    $member_address = MemberAddress::find($request->address_id[$key]);
                } else {
                    $member_address = new MemberAddress;
                }

                $member_address = new MemberAddress;
                $member_address->member_id = $member->id;
                $member_address->address_type = $address_type;
                $member_address->street_address = $request->street_address[$key];
                $member_address->latitude = $request->latitude[$key];
                $member_address->longitude = $request->longitude[$key];
                $member_address->state_id = $request->state_id[$key];
                $member_address->zipcode = $request->zipcode[$key];
                $member_address->location_type = $request->location_type[$key];
                $member_address->department = $request->department[$key];
                $member_address->facility_autofill_id = $request->facility_autofill[$key];
                $member_address->save();
            }


          
            $metaData= metaData(true, $request, '30025', 'success', 200, '');
            return (new MemberResource($member))->additional($metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, 30025, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }

    public function autoFillAddress(Request $request)
    {
        if ($request->payor_type_id == 3) {
            $payors = ProviderMaster::with('departments')->find($request->id);
        } elseif ($request->payor_type_id == 2) {
            $payors = Facility::with('departments')->find($request->id);
        } else {
            $payors = Crm::with('departments')->find($request->id);
        }
        $metaData= metaData(true, $request, '30023', 'success', 200, '');
       
        return  merge(['data' =>$payors], $metaData);
    }
}
