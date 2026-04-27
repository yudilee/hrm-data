@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'API Sync Health & Overview')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 flex justify-between items-center">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">API Health Status</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Real-time statistics of records synchronized with external systems.</p>
        </div>
        <div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-300">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                System Online
            </span>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach(['Master Customers' => $stats['customers'], 'Master Vehicles' => $stats['vehicles'], 'Service History' => $stats['service_history']] as $label => $data)
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="p-5 border-b border-slate-100 dark:border-slate-700/50 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
                <h3 class="font-semibold text-slate-800 dark:text-slate-200">{{ $label }}</h3>
                <span class="text-xs font-semibold text-slate-500 bg-white dark:bg-slate-900 px-2 py-1 rounded-md border border-slate-200 dark:border-slate-700">{{ number_format($data['total']) }} Total</span>
            </div>
            <div class="p-5 grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider mb-1">Synced</p>
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($data['synced']) }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider mb-1">Failed</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($data['failed']) }}</p>
                </div>
                <div class="col-span-2 pt-4 mt-2 border-t border-slate-100 dark:border-slate-700/50">
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider mb-1">Pending (Unsynced)</p>
                    <p class="text-xl font-bold text-slate-700 dark:text-slate-300">{{ number_format($data['pending']) }}</p>
                </div>
            </div>
            <!-- Progress Bar -->
            <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-700">
                @php
                    $percent = $data['total'] > 0 ? ($data['synced'] / $data['total']) * 100 : 0;
                @endphp
                <div class="h-1.5 bg-emerald-500 transition-all duration-500" style="width: {{ $percent }}%"></div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
