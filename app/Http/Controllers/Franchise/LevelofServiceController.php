<?php

namespace App\Http\Controllers\Franchise;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\LevelofService;
use App\Http\Controllers\Controller;
use App\Models\MasterLevelOfService;
use App\Http\Resources\EsoCollection;
use App\Http\Resources\LevelofServiceCollection;

class LevelofServiceController extends Controller
{
    public function index(Request $request)
    {
        $level = MasterLevelOfService::get();
        return new LevelofServiceCollection($level);
    }
}
