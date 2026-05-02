<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->orderByDesc('created_at');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('type')) {
            $query->where('auditable_type', 'like', '%'.$request->type.'%');
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $logs = $query->paginate(50)->withQueryString();

        $actions = AuditLog::distinct('action')->pluck('action')->sort()->values();

        return view('admin.audit-logs', compact('logs', 'actions'));
    }

    public function show(int $id)
    {
        $log = AuditLog::with('user')->findOrFail($id);

        return view('admin.audit-log-detail', compact('log'));
    }
}
