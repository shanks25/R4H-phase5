<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;

class EnsureTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     */
    public function handle(Request $request, Closure $next)
    {
        $error = 0 ;
        if ($request->bearerToken() != env('NODE_TOKEN')) {
            $error = 1 ;
            $msg = 'Invalid Authorization header';
        }

        $user = User::find($request->eso_id);
        if (!$user) {
            $msg =  'Invalid eso Id';
            if ($request->filled('eso_id')) {
            }
            $error = 1 ;
        }

        if ($error) {
            $metaData = metaData('false', $request, '1000', '', '400', '', $msg);
            return response()->json($metaData, 401);
        }
        $request =  $request->merge(['user_id'=>$request->eso_id]);
        return $next($request);
    }
}
