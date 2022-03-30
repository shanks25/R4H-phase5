<?php

namespace App\Http\Controllers\Franchise;

use App\Models\DriverZones;
use App\Models\DriverMaster;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\TripMaster;
use App\Models\TripPayoutDetail;
use App\Models\Driverleavedetails;
use App\Http\Controllers\Controller;
use App\Models\DriverLevelofService;
use App\Http\Resources\DriverResource;
use App\Http\Resources\DriverCollection;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\AvailabilityRequest;
use App\Http\Requests\DriverUpdateRequest;
use App\Http\Resources\DriverListResource;
use App\Http\Resources\DriverViewResource;
use App\Http\Requests\DriverPersonalRequest;
use App\Http\Resources\DriverListCollection;
use App\Models\DriverIdentificationDocuments;
use App\Http\Requests\DriverCredentialRequest;
use App\Http\Resources\DriverLeavesCollection;
use App\Http\Requests\DriverWorkProfileRequest;
use App\Http\Requests\DriverProfessionalRequest;

class DriverPayoutController extends Controller
{
    public function driverPayout(Request $request){
        $data = TripMaster::eso()->select('*')->with('tripPayoutDetail','driverServiceRate')->get();
        return $data;
    }
}
