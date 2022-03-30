<?php

namespace App\Http\Controllers\Franchise;

use Illuminate\Http\Request;
use App\Http\Resources\EsoResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
use Facade\FlareClient\Http\Response;
use Illuminate\Support\Facades\Validator;

class EsoController extends Controller
{
    public function index(Request $request)
    {
        return      (new EsoResource(eso()))->additional(metaData(true, $request, '1008', 'success', '', '', ''));
    }

    public function updateProfile(ProfileRequest $request)
    {
        $user = eso();
        $user->update($request->all());

        $merge =  merge(['data'=> new EsoResource($user)], metaData(true, $request, '1009', 'Profile updated successfully', '', '', ''));
        return response()->json($merge);
    }
}
