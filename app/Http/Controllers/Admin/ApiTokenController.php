<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class ApiTokenController extends Controller
{
    /**
     * Available token abilities for the Master Data Hub API v2.
     */
    public const ABILITIES = [
        'read:customers' => 'Read Customers — list and view master customer records',
        'read:vehicles' => 'Read Vehicles — list and view master vehicle records',
        'read:service-histories' => 'Read Service Histories — list and view service records with labours & parts',
        'read:suppliers' => 'Read Suppliers — list and view master supplier records',
        'read:labour-codes' => 'Read Labour Codes — search and view labour code catalogue',
        'search' => 'Global Search — cross-entity search endpoint',
        '*' => 'Full Access — all current and future abilities',
    ];

    /**
     * Show the API token management page.
     */
    public function index()
    {
        // Eager-load the user for each token so we can display who owns it
        $tokens = PersonalAccessToken::with('tokenable')
            ->where('tokenable_type', User::class)
            ->latest()
            ->get();

        $users = User::orderBy('name')->get(['id', 'name', 'email', 'role']);
        $abilities = self::ABILITIES;

        return view('admin.api-tokens', compact('tokens', 'users', 'abilities'));
    }

    /**
     * Create a new API token.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:100',
            'abilities' => 'required|array|min:1',
            'abilities.*' => 'string|in:'.implode(',', array_keys(self::ABILITIES)),
            'expires_at' => 'nullable|date|after:now',
            'allowed_ips' => 'nullable|string',
            'rate_limit' => 'nullable|integer|min:10|max:1000',
        ]);

        $user = User::findOrFail($validated['user_id']);

        $tokenData = [
            'name' => $validated['name'],
            'abilities' => $validated['abilities'],
            'expires_at' => $validated['expires_at'] ?? null,
            'allowed_ips' => isset($validated['allowed_ips']) && trim($validated['allowed_ips']) !== ''
                ? array_filter(array_map('trim', explode(',', $validated['allowed_ips'])))
                : null,
            'rate_limit' => $validated['rate_limit'] ?? null,
        ];

        $token = $user->createToken(
            $tokenData['name'],
            $tokenData['abilities'],
            $tokenData['expires_at'] ? Carbon::parse($tokenData['expires_at']) : null
        );

        // Store extra fields on the token record
        PersonalAccessToken::find($token->accessToken->id)->update([
            'allowed_ips' => $tokenData['allowed_ips'],
            'rate_limit' => $tokenData['rate_limit'],
        ]);

        AuditLog::record(
            'created',
            ['PersonalAccessToken', $token->accessToken->id],
            [],
            ['name' => $tokenData['name'], 'abilities' => $tokenData['abilities'], 'for_user' => $user->email],
            "API token '{$tokenData['name']}' created for {$user->name}"
        );

        return back()
            ->with('new_token', $token->plainTextToken)
            ->with('new_token_name', $tokenData['name'])
            ->with('success', "Token \"{$tokenData['name']}\" created successfully. Copy it now — it won't be shown again.");
    }

    /**
     * Revoke (delete) a token.
     */
    public function destroy(int $tokenId)
    {
        $token = PersonalAccessToken::findOrFail($tokenId);
        $name = $token->name;

        AuditLog::record(
            'deleted',
            ['PersonalAccessToken', $tokenId],
            ['name' => $name],
            [],
            "API token '{$name}' revoked"
        );

        $token->delete();

        return back()->with('success', "Token \"{$name}\" has been revoked.");
    }
}
