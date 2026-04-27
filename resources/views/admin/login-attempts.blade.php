@extends('layouts.app')

@section('title', 'Login Attempts')
@section('subtitle', 'Web login history and failed authentication tracking')

@section('content')
<div class="space-y-6">

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Today — Total</p>
            <p class="text-2xl font-bold mt-1 text-slate-700 dark:text-slate-300">{{ number_format($stats['today_total']) }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Today — Successful</p>
            <p class="text-2xl font-bold mt-1 text-emerald-600 dark:text-emerald-400">{{ number_format($stats['today_success']) }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Today — Failed</p>
            <p class="text-2xl font-bold mt-1 text-red-600 dark:text-red-400">{{ number_format($stats['today_failed']) }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.login-attempts.index') }}"
          class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <input type="text" name="email" value="{{ request('email') }}" placeholder="Email"
                   class="rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            <input type="text" name="ip" value="{{ request('ip') }}" placeholder="IP address"
                   class="rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            <select name="success" class="rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                <option value="">All Outcomes</option>
                <option value="1" {{ request('success') === '1' ? 'selected' : '' }}>Successful</option>
                <option value="0" {{ request('success') === '0' ? 'selected' : '' }}>Failed</option>
            </select>
            <div class="flex gap-2">
                <input type="date" name="from" value="{{ request('from') }}"
                       class="flex-1 rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 text-sm">
                <button type="submit" class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">Filter</button>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="font-semibold">Login Attempt Log</h3>
        </div>
        @if($logs->isEmpty())
        <div class="p-12 text-center text-slate-400">No login attempts found.</div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-left">
                        @foreach(['Time', 'Email', 'IP Address', 'Outcome', 'Reason', 'User-Agent'] as $col)
                        <th class="px-4 py-3 font-semibold text-slate-500 text-xs uppercase tracking-wide">{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                    @foreach($logs as $attempt)
                    <tr class="{{ $attempt->success ? '' : 'bg-red-50/50 dark:bg-red-900/10' }} hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-4 py-3 text-slate-500 whitespace-nowrap text-xs">{{ $attempt->created_at->format('d/m/Y H:i:s') }}</td>
                        <td class="px-4 py-3 font-medium">{{ $attempt->email }}</td>
                        <td class="px-4 py-3 font-mono text-slate-600 dark:text-slate-400 text-xs">{{ $attempt->ip_address }}</td>
                        <td class="px-4 py-3">
                            @if($attempt->success)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 text-xs font-medium">✓ Success</span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 text-xs font-medium">✗ Failed</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-500">{{ $attempt->failure_reason ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs text-slate-400 truncate max-w-xs" title="{{ $attempt->user_agent }}">{{ Str::limit($attempt->user_agent, 50) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
