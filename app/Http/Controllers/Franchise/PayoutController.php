<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Http\Resources\PayoutCollection;
use App\Models\PayoutMaster;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    public function index(Request $request)
    {
        $payout = PayoutMaster::orderBy('name', 'ASC')->get();
        return new PayoutCollection($payout);
    }
}
