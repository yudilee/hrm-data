<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Admin\ApiTokenController as AdminApiTokenController;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class ApiTokenController extends Controller
{
    /**
     * Show the user's API token management page.
     */
    public function index()
    {
        // Fetch only tokens belonging to the authenticated user
        $tokens = PersonalAccessToken::where('tokenable_type', get_class(auth()->user()))
            ->where('tokenable_id', auth()->id())
            ->latest()
            ->get();

        // Get abilities, excluding the '*' full access ability
        $abilities = array_filter(AdminApiTokenController::ABILITIES, function ($key) {
            return $key !== '*';
        }, ARRAY_FILTER_USE_KEY);

        return view('user.api-tokens', compact('tokens', 'abilities'));
    }

    /**
     * Create a new API token for the user.
     */
    public function store(Request $request)
    {
        // Get allowed abilities (excluding '*')
        $allowedAbilities = array_filter(array_keys(AdminApiTokenController::ABILITIES), function ($val) {
            return $val !== '*';
        });

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'abilities' => 'required|array|min:1',
            'abilities.*' => 'string|in:'.implode(',', $allowedAbilities),
            'expires_at' => 'nullable|date|after:now',
        ]);

        $user = auth()->user();

        $tokenData = [
            'name' => $validated['name'],
            'abilities' => $validated['abilities'],
            'expires_at' => $validated['expires_at'] ?? null,
        ];

        $token = $user->createToken(
            $tokenData['name'],
            $tokenData['abilities'],
            $tokenData['expires_at'] ? Carbon::parse($tokenData['expires_at']) : null
        );

        AuditLog::record(
            'created',
            ['PersonalAccessToken', $token->accessToken->id],
            [],
            ['name' => $tokenData['name'], 'abilities' => $tokenData['abilities'], 'for_user' => $user->email],
            "API token '{$tokenData['name']}' created by user {$user->name}"
        );

        return back()
            ->with('new_token', $token->plainTextToken)
            ->with('new_token_name', $tokenData['name'])
            ->with('success', "Token \"{$tokenData['name']}\" created successfully. Copy it now — it won't be shown again.");
    }

    /**
     * Revoke (delete) a user's own token.
     */
    public function destroy(int $tokenId)
    {
        $token = PersonalAccessToken::where('tokenable_type', get_class(auth()->user()))
            ->where('tokenable_id', auth()->id())
            ->findOrFail($tokenId);

        $name = $token->name;

        AuditLog::record(
            'deleted',
            ['PersonalAccessToken', $tokenId],
            ['name' => $name],
            [],
            "API token '{$name}' revoked by user ".auth()->user()->name
        );

        $token->delete();

        return back()->with('success', "Token \"{$name}\" has been revoked.");
    }
}
