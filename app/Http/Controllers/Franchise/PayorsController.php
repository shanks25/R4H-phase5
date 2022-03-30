<?php

namespace App\Http\Controllers\Franchise;

use App\Models\Crm;
use App\Models\Facility;
use Illuminate\Http\Request;
use App\Models\ProviderMaster;
use App\Http\Controllers\Controller;
use App\Http\Resources\ImportNamesCollection;
use App\Http\Resources\PayorsCollection;
use Illuminate\Support\Facades\Validator;

class PayorsController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payor_type_id' => 'required|exists:payor_types,id',
        ]);

        if ($validator->fails()) {
            $metaData = metaData('false', $request, '10006', '', '502', '', $validator->messages());
            return response()->json($metaData, 200);
        }

        if ($request->payor_type_id == 3) {
            $payors = ProviderMaster::select('id', 'name', 'address', 'zipcode')->eso();
        } elseif ($request->payor_type_id == 2) {
            $payors = Facility::select('id', 'name', 'street_address', 'zipcode')->eso();
        } else {
            $payors = Crm::select('id', 'name', 'street_address', 'zipcode')->eso()->where('type', $request->payor_type_id);
        }
        return new PayorsCollection($payors->get());
    }

    public function importNames(Request $request)
    {
        $payors = ProviderMaster::select('id', 'name', 'logo')
            ->where('template', '>', '0')
            ->eso()
            ->orderBy('name', 'ASC')
            ->get();
        return new ImportNamesCollection($payors);
    }
}
