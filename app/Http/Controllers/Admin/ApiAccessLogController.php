<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiAccessLog;
use Illuminate\Http\Request;

class ApiAccessLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ApiAccessLog::query()->orderByDesc('created_at');

        if ($request->filled('token_name')) {
            $query->where('token_name', 'like', '%'.$request->token_name.'%');
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('status')) {
            $s = (int) $request->status;
            match (true) {
                $s === 2 => $query->where('response_status', '>=', 200)->where('response_status', '<', 300),
                $s === 4 => $query->where('response_status', '>=', 400)->where('response_status', '<', 500),
                $s === 5 => $query->where('response_status', '>=', 500),
                default => null,
            };
        }

        if ($request->filled('ip')) {
            $query->where('ip_address', 'like', '%'.$request->ip.'%');
        }

        if ($request->filled('path')) {
            $query->where('path', 'like', '%'.$request->path.'%');
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $logs = $query->paginate(50)->withQueryString();

        // Stats for today
        $today = now()->toDateString();
        $stats = [
            'today_total' => ApiAccessLog::whereDate('created_at', $today)->count(),
            'today_failed' => ApiAccessLog::whereDate('created_at', $today)->where('response_status', '>=', 400)->count(),
            'today_rate' => ApiAccessLog::whereDate('created_at', $today)->where('response_status', 429)->count(),
            'today_ips' => ApiAccessLog::whereDate('created_at', $today)->distinct('ip_address')->count('ip_address'),
        ];

        return view('admin.api-logs', compact('logs', 'stats'));
    }

    public function stats()
    {
        // Last 48 hours by hour
        $hourly = ApiAccessLog::selectRaw(
            "DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as hour, COUNT(*) as total,
             SUM(CASE WHEN response_status >= 400 THEN 1 ELSE 0 END) as failed"
        )
            ->where('created_at', '>=', now()->subHours(48))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Top tokens last 7 days
        $topTokens = ApiAccessLog::selectRaw('token_name, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(7))
            ->whereNotNull('token_name')
            ->groupBy('token_name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Top IPs last 24h
        $topIps = ApiAccessLog::selectRaw('ip_address, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDay())
            ->groupBy('ip_address')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return response()->json(compact('hourly', 'topTokens', 'topIps'));
    }
}
