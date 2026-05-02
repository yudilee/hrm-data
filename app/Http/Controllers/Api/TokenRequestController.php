<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Admin\ApiTokenController;
use App\Http\Controllers\Controller;
use App\Models\TokenRequest;
use Illuminate\Http\Request;

class TokenRequestController extends Controller
{
    public function showForm()
    {
        $abilities = ApiTokenController::ABILITIES;

        return view('api-request', compact('abilities'));
    }

    public function store(Request $request)
    {
        $abilities = array_keys(ApiTokenController::ABILITIES);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'company' => 'nullable|string|max:255',
            'use_case' => 'required|string|min:20|max:2000',
            'requested_abilities' => 'required|array|min:1',
            'requested_abilities.*' => 'string|in:'.implode(',', $abilities),
        ]);

        $existing = TokenRequest::where('email', $validated['email'])
            ->where('status', 'pending')
            ->exists();

        if ($existing) {
            return response()->json([
                'success' => false,
                'error' => 'You already have a pending request. Please wait for it to be reviewed.',
            ], 422);
        }

        TokenRequest::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Your request has been submitted. You will receive an email once reviewed.',
        ], 201);
    }
}
