<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Return information about the currently authenticated API token.
     *
     * Returns the user's name, email, role, and the abilities granted to the current token.
     */
    public function me(Request $request)
    {
        $user  = $request->user();
        $token = $user->currentAccessToken();

        return response()->json([
            'success' => true,
            'data' => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'role'       => $user->role,
                'token' => [
                    'name'       => $token->name,
                    'abilities'  => $token->abilities,
                    'created_at' => $token->created_at,
                    'last_used'  => $token->last_used_at,
                ],
            ],
            'meta' => ['version' => '2.0'],
        ]);
    }

    /**
     * Return API version and health status.
     */
    public function health()
    {
        try {
            DB::connection()->getPdo();
            $dbStatus = 'connected';
        } catch (\Exception $e) {
            $dbStatus = 'error';
        }

        return response()->json([
            'status'    => 'ok',
            'database'  => $dbStatus,
            'app'       => config('app.name'),
            'version'   => '2.0',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
