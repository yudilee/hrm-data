<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\TokenRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TokenRequestController extends Controller
{
    public function index()
    {
        $pending = TokenRequest::where('status', 'pending')->latest()->get();
        $reviewed = TokenRequest::whereIn('status', ['approved', 'rejected'])->latest()->paginate(20);

        return view('admin.token-requests', compact('pending', 'reviewed'));
    }

    public function approve(Request $request, TokenRequest $tokenRequest)
    {
        $request->validate([
            'abilities' => 'required|array|min:1',
            'expires_at' => 'nullable|date|after:now',
            'notes' => 'nullable|string|max:500',
        ]);

        // Find or create a user account for the requester
        $user = User::firstOrCreate(
            ['email' => $tokenRequest->email],
            ['name' => $tokenRequest->name, 'password' => bcrypt(\Str::random(32)), 'role' => 'user']
        );

        $abilities = $request->abilities;
        $expiresAt = $request->filled('expires_at') ? Carbon::parse($request->expires_at) : null;

        $token = $user->createToken("External - {$tokenRequest->company}", $abilities, $expiresAt);

        $tokenRequest->update([
            'status' => 'approved',
            'token_id' => $token->accessToken->id,
            'admin_notes' => $request->notes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        AuditLog::record('created', ['PersonalAccessToken', $token->accessToken->id], [], [
            'for' => $tokenRequest->email,
            'abilities' => $abilities,
        ], "Token request approved for {$tokenRequest->email}");

        // Email the plain-text token to the requester
        try {
            Mail::raw(
                "Hello {$tokenRequest->name},\n\n".
                "Your API access request for Master Data Hub has been approved.\n\n".
                "Token: {$token->plainTextToken}\n\n".
                'Abilities granted: '.implode(', ', $abilities)."\n".
                ($expiresAt ? "Expires: {$expiresAt->toDateString()}\n" : "No expiry set.\n").
                "\nStore this token securely — it will NOT be shown again.\n\n".
                'API Base URL: '.config('app.url')."/api/v2\n".
                'Documentation: '.config('app.url')."/docs/api\n\n".
                "Regards,\n".config('app.name'),
                fn ($m) => $m->to($tokenRequest->email, $tokenRequest->name)
                    ->subject('Your Master Data Hub API Token')
            );
        } catch (\Exception $e) {
            Log::error('Failed to send token email: '.$e->getMessage());
        }

        return back()->with('success', "Token request approved and token emailed to {$tokenRequest->email}.");
    }

    public function reject(Request $request, TokenRequest $tokenRequest)
    {
        $request->validate(['notes' => 'nullable|string|max:500']);

        $tokenRequest->update([
            'status' => 'rejected',
            'admin_notes' => $request->notes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        try {
            Mail::raw(
                "Hello {$tokenRequest->name},\n\n".
                "We have reviewed your API access request and are unable to approve it at this time.\n\n".
                ($request->notes ? "Reason: {$request->notes}\n\n" : '').
                "If you have questions, please contact the system administrator.\n\n".
                "Regards,\n".config('app.name'),
                fn ($m) => $m->to($tokenRequest->email, $tokenRequest->name)
                    ->subject('API Access Request Update')
            );
        } catch (\Exception $e) {
            Log::error('Failed to send rejection email', [
                'email' => $tokenRequest->email,
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('success', "Request rejected and {$tokenRequest->email} notified.");
    }
}
