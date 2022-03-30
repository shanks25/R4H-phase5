<?php

namespace App\Http\Controllers\Franchise;

use App\Models\PayorType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PayorTypeCollection;

class PayorTypeController extends Controller
{
    public function index(Request $request)
    {
        $payor_types = PayorType::get();
        return new PayorTypeCollection($payor_types);
    }
}
