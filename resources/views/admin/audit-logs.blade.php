@extends('layouts.app')

@section('title', 'Audit Trail')
@section('subtitle', 'Complete record of all admin actions')

@section('content')
<div class="space-y-6">

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.audit-logs.index') }}"
          class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <select name="action" class="rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                <option value="">All Actions</option>
                @foreach($actions as $action)
                <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ ucfirst($action) }}</option>
                @endforeach
            </select>
            <input type="text" name="type" value="{{ request('type') }}" placeholder="Entity type (User, Token...)"
                   class="rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            <input type="date" name="from" value="{{ request('from') }}"
                   class="rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            <div class="flex gap-2">
                <input type="date" name="to" value="{{ request('to') }}"
                       class="flex-1 rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                <button type="submit" class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">Filter</button>
            </div>
        </div>
    </form>

    {{-- Log Timeline --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="font-semibold">Audit Entries</h3>
            <span class="text-xs text-slate-500">{{ number_format($logs->total()) }} total</span>
        </div>

        @if($logs->isEmpty())
        <div class="p-12 text-center text-slate-400">No audit entries match your filters.</div>
        @else
        <div class="divide-y divide-slate-100 dark:divide-slate-800">
            @foreach($logs as $log)
            @php
            $actionColor = match($log->action) {
                'created'   => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                'updated'   => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                'deleted'   => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                'restored'  => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                'exported'  => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                default     => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400',
            };
            $entityShort = class_basename($log->auditable_type ?? 'Unknown');
            @endphp
            <div x-data="{ open: false }" class="px-5 py-4 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                <div class="flex items-start gap-3">
                    <span class="inline-block px-2 py-0.5 rounded text-xs font-bold {{ $actionColor }} shrink-0 mt-0.5">
                        {{ strtoupper($log->action) }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-slate-800 dark:text-slate-200 font-medium">
                            {{ $log->description ?? "{$entityShort} #{$log->auditable_id}" }}
                        </p>
                        <p class="text-xs text-slate-500 mt-0.5">
                            by <strong>{{ $log->user?->name ?? 'System' }}</strong>
                            &middot; {{ $log->created_at->format('d M Y H:i:s') }}
                            &middot; IP {{ $log->ip_address }}
                        </p>
                    </div>
                    @if($log->old_values || $log->new_values)
                    <button @click="open = !open" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline shrink-0">
                        <span x-text="open ? 'Hide diff' : 'Show diff'"></span>
                    </button>
                    @endif
                </div>

                {{-- Diff View --}}
                @if($log->old_values || $log->new_values)
                <div x-show="open" x-cloak class="mt-3 ml-20 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 text-xs font-mono">
                    @if($log->old_values)
                    <div class="bg-red-50 dark:bg-red-900/20 px-3 py-2 border-b border-slate-200 dark:border-slate-700">
                        <span class="font-bold text-red-600">− Before</span>
                        <pre class="mt-1 text-red-700 dark:text-red-400 whitespace-pre-wrap">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                    @endif
                    @if($log->new_values)
                    <div class="bg-emerald-50 dark:bg-emerald-900/20 px-3 py-2">
                        <span class="font-bold text-emerald-600">+ After</span>
                        <pre class="mt-1 text-emerald-700 dark:text-emerald-400 whitespace-pre-wrap">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                    @endif
                </div>
                @endif
            </div>
            @endforeach
        </div>
        <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
