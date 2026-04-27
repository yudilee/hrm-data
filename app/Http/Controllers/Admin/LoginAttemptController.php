<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginAttempt;
use Illuminate\Http\Request;

class LoginAttemptController extends Controller
{
    public function index(Request $request)
    {
        $query = LoginAttempt::orderByDesc('created_at');

        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        if ($request->filled('ip')) {
            $query->where('ip_address', 'like', '%' . $request->ip . '%');
        }

        if ($request->filled('success')) {
            $query->where('success', $request->success === '1');
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        $logs = $query->paginate(50)->withQueryString();

        $stats = [
            'today_total'    => LoginAttempt::whereDate('created_at', today())->count(),
            'today_success'  => LoginAttempt::whereDate('created_at', today())->where('success', true)->count(),
            'today_failed'   => LoginAttempt::whereDate('created_at', today())->where('success', false)->count(),
        ];

        return view('admin.login-attempts', compact('logs', 'stats'));
    }
}
