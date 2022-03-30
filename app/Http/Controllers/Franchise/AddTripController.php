<?php

namespace App\Http\Controllers\Franchise;

use App\Models\TripMaster;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\TripResource;
use Facades\App\Repository\TripRepo;
use App\Http\Requests\AddTripRequest;
use Facades\App\Repository\TripMemberRepo;

class AddTripController extends Controller
{

    /**
     * add trip function
     *trip_format  1=normal,2=return,3=will,4=wait
     * @param AddTripRequest $request
     * @return object
     */
     
    public function index(AddTripRequest $request)
    {
        $trip_meta = $request->except('legs') ;
        $trips_collection = [];

        foreach ($request->legs as $key => $leg) {
            $leg = (array) TripMemberRepo::getMember($request, $leg);
   
            $leg['group_id'] =$request->group_id;
            $data =  merge($trip_meta, $leg);
            $data['leg_no'] = ($key+1);
            $data['parent_id'] = TripRepo::getParentId($trips_collection, $key);
            
            $trip=  TripMaster::create($data);
            $trips_collection[] =  $trip->load(TripRepo::loadTripRelation());
        }
        $collection = TripResource::collection($trips_collection);
        return    merge(['data'=>$collection], metaData(true, $request, '1009', 'trip added successfully', '', '', ''));
    }

 

    public function getTripNo(Request $request)
    {
        return  merge(['data'=> ['trip_no' => todaysTrip()]], metaData(true, $request, '1009', 'success', '', '', ''));
    }
}
