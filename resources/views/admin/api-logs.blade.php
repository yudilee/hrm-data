@extends('layouts.app')

@section('title', 'API Access Logs')
@section('subtitle', 'All API requests made to the Master Data Hub')

@section('content')
<div class="space-y-6" x-data="apiLogs()">

    {{-- Stats Row --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @php
        $statCards = [
            ['label' => 'Total Today',      'value' => $stats['today_total'],  'color' => 'indigo'],
            ['label' => 'Failed Auth (4xx)', 'value' => $stats['today_failed'], 'color' => 'amber'],
            ['label' => 'Rate Limited',     'value' => $stats['today_rate'],   'color' => 'orange'],
            ['label' => 'Unique IPs',       'value' => $stats['today_ips'],    'color' => 'emerald'],
        ];
        @endphp
        @foreach($statCards as $card)
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ $card['label'] }}</p>
            <p class="text-2xl font-bold mt-1 text-{{ $card['color'] }}-600 dark:text-{{ $card['color'] }}-400">{{ number_format($card['value']) }}</p>
        </div>
        @endforeach
    </div>

    {{-- Chart --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5">
        <h3 class="font-semibold text-slate-800 dark:text-slate-200 mb-3">Requests — Last 48 Hours</h3>
        <canvas id="api-chart" height="80"></canvas>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.api-logs.index') }}"
          class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5">
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
            <input type="text" name="token_name" value="{{ request('token_name') }}" placeholder="Token name"
                   class="rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            <input type="text" name="ip" value="{{ request('ip') }}" placeholder="IP address"
                   class="rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            <input type="text" name="path" value="{{ request('path') }}" placeholder="Path /api/v2/..."
                   class="rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            <select name="status" class="rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                <option value="">All Status</option>
                <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>2xx Success</option>
                <option value="4" {{ request('status') == '4' ? 'selected' : '' }}>4xx Client Error</option>
                <option value="5" {{ request('status') == '5' ? 'selected' : '' }}>5xx Server Error</option>
            </select>
            <input type="date" name="from" value="{{ request('from') }}"
                   class="rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            <div class="flex gap-2">
                <input type="date" name="to" value="{{ request('to') }}"
                       class="flex-1 rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                <button type="submit" class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors">Filter</button>
            </div>
        </div>
    </form>

    {{-- Logs Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="font-semibold text-slate-800 dark:text-slate-200">Access Log</h3>
            <span class="text-xs text-slate-500">{{ number_format($logs->total()) }} records</span>
        </div>

        @if($logs->isEmpty())
        <div class="p-12 text-center text-slate-400">No log entries match your filters.</div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-left">
                        @foreach(['Time', 'Method', 'Path', 'Status', 'ms', 'Token', 'IP', 'User-Agent'] as $col)
                        <th class="px-4 py-2.5 font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                    @foreach($logs as $log)
                    @php
                    $statusColor = match(true) {
                        $log->response_status >= 500 => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                        $log->response_status >= 400 => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                        $log->response_status >= 300 => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                        default                      => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                    };
                    @endphp
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-4 py-2.5 text-slate-500 whitespace-nowrap">{{ $log->created_at->format('d/m H:i:s') }}</td>
                        <td class="px-4 py-2.5 font-mono font-bold text-slate-700 dark:text-slate-300">{{ $log->method }}</td>
                        <td class="px-4 py-2.5 font-mono text-slate-600 dark:text-slate-400 max-w-xs truncate">{{ $log->path }}</td>
                        <td class="px-4 py-2.5">
                            <span class="inline-block px-1.5 py-0.5 rounded text-xs font-bold {{ $statusColor }}">{{ $log->response_status }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-slate-500">{{ $log->response_time_ms }}</td>
                        <td class="px-4 py-2.5 font-mono text-indigo-600 dark:text-indigo-400">{{ $log->token_name ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-slate-500 font-mono whitespace-nowrap">{{ $log->ip_address }}</td>
                        <td class="px-4 py-2.5 text-slate-400 truncate max-w-xs" title="{{ $log->user_agent }}">{{ Str::limit($log->user_agent, 40) }}</td>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
function apiLogs() { return {}; }

fetch('{{ route('admin.api-logs.stats') }}')
    .then(r => r.json())
    .then(data => {
        const labels  = data.hourly.map(h => h.hour.substring(5, 16));
        const totals  = data.hourly.map(h => h.total);
        const failed  = data.hourly.map(h => h.failed);

        new Chart(document.getElementById('api-chart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    { label: 'Total', data: totals, backgroundColor: 'rgba(99,102,241,0.5)', borderColor: 'rgb(99,102,241)', borderWidth: 1 },
                    { label: 'Failed', data: failed, backgroundColor: 'rgba(245,158,11,0.5)', borderColor: 'rgb(245,158,11)', borderWidth: 1 },
                ],
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
            },
        });
    })
    .catch(() => {});
</script>
@endpush
@endsection
