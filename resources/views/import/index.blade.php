@extends('layouts.app')

@section('title', 'Data Import Center - Dealership MasterData Hub')

@section('breadcrumb')
    <x-ui.breadcrumb :items="[['label' => 'Import Center']]" />
@endsection

@section('content')
<div class="max-w-5xl mx-auto px-2 py-6" x-data="importHistory()" x-init="fetchStatus()">

    {{-- Page Header --}}
    <div class="mb-8">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Data Import Center</h1>
                <p class="mt-2 text-gray-500 dark:text-slate-400">All data is read from the server folders automatically. No file upload needed.</p>
            </div>
        </div>
    </div>

    {{-- Help Panel --}}
    <x-ui.help-panel>
        <x-slot name="trigger"><span label="How does importing work?"></span></x-slot>
        <p class="mb-2"><strong>Import Pipeline Overview</strong></p>
        <ul class="list-disc pl-5 space-y-1 text-xs">
            <li><strong>DMS Customers</strong> — Reads from <code class="bg-indigo-100 dark:bg-indigo-800 px-1 rounded">data cust/*.xls</code> with advanced deduplication logic via Python pipeline.</li>
            <li><strong>H04 Customers</strong> — Imports all branch H04 Excel files, auto-detecting the source branch from the filename.</li>
            <li><strong>LVS Vehicles</strong> — Merges vehicle data from <code class="bg-indigo-100 dark:bg-indigo-800 px-1 rounded">lvs/*.xls</code> files, matching to existing customers.</li>
            <li><strong>Service History</strong> — Syncs service records from FoxPro DBF files in the <code class="bg-indigo-100 dark:bg-indigo-800 px-1 rounded">vehicle history/</code> directory.</li>
            <li><strong>Suppliers</strong> — Syncs from the single <code class="bg-indigo-100 dark:bg-indigo-800 px-1 rounded">supplier.DBF</code> file using upsert (insert or update).</li>
            <li class="mt-1"><strong>Smart Sync</strong> — Runs the full intelligence pipeline: Recover Ghosts → LVS Import → Backfill Names → Merge Duplicates. Fixes missing names and consolidates duplicate records in one click.</li>
        </ul>
        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Source paths are configured in <code>.env</code> variables: <code>IMPORT_CUSTOMER_DIR</code>, <code>IMPORT_VEHICLE_DIR</code>, <code>IMPORT_SUPPLIER_DBF</code>.</p>
    </x-ui.help-panel>

    {{-- Alerts (also show as toasts) --}}
    @if(session('success'))
        <div id="flash-success" data-message="{{ session('success') }}" class="hidden"></div>
    @endif
    @if(session('error'))
        <div id="flash-error" data-message="{{ session('error') }}" class="hidden"></div>
    @endif

    <div class="mb-6 bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="h-1 bg-gradient-to-r from-indigo-500 via-violet-500 to-purple-600"></div>
        <div class="p-6 flex flex-col lg:flex-row items-center gap-6">
            <div class="w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-600/40 flex-shrink-0">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <div class="flex-grow">
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight mb-2">Full Master Smart Sync</h2>
                <p class="text-sm text-gray-500 dark:text-slate-400 leading-relaxed">
                    Runs the full intelligence pipeline: <span class="font-bold text-indigo-600 dark:text-indigo-400">Recover Ghosts → LVS Import → Backfill Names → Merge Duplicates</span>. 
                    Fixes missing names and consolidates duplicate records in one click.
                </p>
            </div>
            <div class="w-full lg:w-auto">
                <form action="{{ route('import.smart-sync') }}" method="POST" x-data="{ running: false }" @submit.prevent="running = true; startAjaxImport($event.target)">
                    @csrf
                    <button type="submit" :disabled="running"
                        class="w-full lg:px-10 py-3 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white font-bold rounded-xl shadow-lg shadow-indigo-600/30 transition-all flex items-center justify-center gap-2">
                        <svg x-show="running" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <svg x-show="!running" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span x-text="running ? 'PROCESSING...' : 'START MASTER SYNC'"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Live Terminal Log (visible when something is running) --}}
    <div x-show="isRunning" x-transition.opacity class="mb-8 bg-gray-900 rounded-2xl border border-gray-800 shadow-xl overflow-hidden">
        <div class="px-4 py-3 bg-black flex items-center gap-3 border-b border-gray-800">
            <div class="flex gap-1.5">
                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
            </div>
            <span class="text-xs font-mono text-gray-400">Live Sync Log</span>
            <div class="ml-auto flex items-center gap-2 text-xs font-bold text-emerald-400">
                <span class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                SYNC IN PROGRESS
            </div>
        </div>
        <div class="p-4 h-64 overflow-y-auto overflow-x-hidden font-mono text-xs text-gray-300 leading-relaxed max-w-full" id="live-log-container">
            <pre x-text="liveLog" class="whitespace-pre-wrap break-all break-words max-w-full"></pre>
        </div>
    </div>

    {{-- 5 Import Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">



        {{-- CARD 2: DMS Customers (Python pipeline / RTS Code) --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col">
            <div class="p-6 flex-grow">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-11 h-11 bg-indigo-100 dark:bg-indigo-900/40 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-gray-900 dark:text-white">DMS Customers</h3>
                            <span class="text-[9px] font-black bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 px-2 py-0.5 rounded-full uppercase tracking-widest">Python</span>
                        </div>
                        <code class="text-[10px] text-gray-400 dark:text-slate-500 font-mono">/rts_code/data cust/*.xls</code>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-slate-900 rounded-xl p-4 mb-5">
                    <p class="text-xs text-gray-700 dark:text-slate-300 font-semibold mb-1">Advanced RTS Code Import Pipeline</p>
                    <p class="text-[11px] text-gray-400">Uses the Python DMS script with smart deduplication, email/phone validation, and name normalization.</p>
                    <div class="mt-3 flex items-center gap-2">
                        @if($customerFiles->isNotEmpty())
                            <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                            <span class="text-[11px] text-emerald-700 dark:text-emerald-400 font-bold">{{ $customerFiles->count() }} file(s) ready</span>
                        @else
                            <div class="w-2 h-2 bg-red-400 rounded-full"></div>
                            <span class="text-[11px] text-red-500 font-bold">No files found</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="px-6 pb-6">
                <form action="{{ route('import.dms-customers') }}" method="POST" x-data="{ running: false }" @submit="running = true">
                    @csrf
                    <button type="submit" :disabled="{{ $customerFiles->isEmpty() ? 'true' : 'false' }} || running"
                        class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 dark:disabled:bg-slate-700 disabled:cursor-not-allowed text-white font-bold rounded-xl text-sm transition-all flex items-center justify-center gap-2">
                        <svg x-show="running" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <svg x-show="!running" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a1 1 0 001 1h10a1 1 0 001-1v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span x-text="running ? 'Running Python Pipeline...' : 'Run DMS Import'"></span>
                    </button>
                </form>
            </div>
        </div>

        {{-- CARD 3: LVS Vehicles --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col">
            <div class="p-6 flex-grow">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-11 h-11 bg-emerald-100 dark:bg-emerald-900/40 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white">Master Vehicles (LVS)</h3>
                        <code class="text-[10px] text-gray-400 dark:text-slate-500 font-mono">/rts_code/lvs/*.xls</code>
                    </div>
                </div>

                {{-- File list --}}
                <div class="bg-gray-50 dark:bg-slate-900 rounded-xl p-4 mb-5">
                    @if($vehicleFiles->isEmpty())
                        <p class="text-xs text-gray-400 italic text-center py-5">No LVS files found in folder</p>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @foreach($vehicleFiles as $file)
                                @php
                                    preg_match('/(?:per\s+[\d\s\w]+?\s+)(.+?)\(lvs\)/i', $file['name'], $m);
                                    $branch = isset($m[1]) ? trim($m[1]) : $file['name'];
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 rounded-lg text-[11px] font-bold">
                                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full inline-block"></span>
                                    {{ $branch }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                </div>
            </div>
            <div class="px-6 pb-6">
                <form action="{{ route('import.lvs-vehicles') }}" method="POST" x-data="{ running: false }" @submit="running = true">
                    @csrf
                    <button type="submit" :disabled="{{ $vehicleFiles->isEmpty() ? 'true' : 'false' }} || running"
                        class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-300 dark:disabled:bg-slate-700 disabled:cursor-not-allowed text-white font-bold rounded-xl text-sm transition-all flex items-center justify-center gap-2">
                        <svg x-show="running" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <svg x-show="!running" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a1 1 0 001 1h10a1 1 0 001-1v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span x-text="running ? 'Merging Vehicles...' : 'Sync {{ $vehicleFiles->count() }} LVS File(s)'"></span>
                    </button>
                </form>
            </div>
        </div>

        {{-- CARD 3: Supplier DBF --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col">
            <div class="p-6 flex-grow">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-11 h-11 bg-blue-100 dark:bg-blue-900/40 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white">Master Suppliers</h3>
                        <code class="text-[10px] text-gray-400 dark:text-slate-500 font-mono">/rts_code/supplier/supplier.DBF</code>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-slate-900 rounded-xl p-4 mb-5">
                    <div class="flex items-center gap-3">
                        @if($supplierExists)
                            <div class="w-2.5 h-2.5 bg-emerald-500 rounded-full flex-shrink-0"></div>
                            <span class="text-xs font-semibold text-gray-700 dark:text-slate-300">supplier.DBF — ready</span>
                        @else
                            <div class="w-2.5 h-2.5 bg-red-400 rounded-full flex-shrink-0"></div>
                            <span class="text-xs font-semibold text-red-500">supplier.DBF — not found</span>
                        @endif
                    </div>
                    <p class="text-[11px] text-gray-400 mt-2 ml-5">Syncs all supplier records, bank accounts, and contact info from FoxPro.</p>
                </div>
            </div>
            <div class="px-6 pb-6">
                <form action="{{ route('import.suppliers') }}" method="POST" x-data="{ running: false }" @submit="running = true">
                    @csrf
                    <button type="submit" :disabled="{{ $supplierExists ? 'false' : 'true' }} || running"
                        class="w-full py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 dark:disabled:bg-slate-700 disabled:cursor-not-allowed text-white font-bold rounded-xl text-sm transition-all flex items-center justify-center gap-2">
                        <svg x-show="running" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <svg x-show="!running" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span x-text="running ? 'Syncing Suppliers...' : 'Sync Suppliers from DBF'"></span>
                    </button>
                </form>
            </div>
        </div>

        {{-- CARD 4: Service History DBF --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col">
            <div class="p-6 flex-grow">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-11 h-11 bg-amber-100 dark:bg-amber-900/40 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white">Service History</h3>
                        <code class="text-[10px] text-gray-400 dark:text-slate-500 font-mono">/vehicle history/*.DBF</code>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-slate-900 rounded-xl p-4 mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-2.5 h-2.5 bg-amber-400 rounded-full flex-shrink-0"></div>
                        <span class="text-xs font-semibold text-gray-700 dark:text-slate-300">FoxPro DBF files — server-side</span>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-2 ml-5">Refreshes all repair and invoice records from legacy FoxPro databases. This may take several minutes for 1M+ records.</p>
                </div>
            </div>
            <div class="px-6 pb-6">
                <form action="{{ route('import.history') }}" method="POST" x-data="{ running: false }" @submit.prevent="running = true; startAjaxImport($event.target)">
                    @csrf
                    <button type="submit" :disabled="running"
                        class="w-full py-3 bg-amber-600 hover:bg-amber-700 disabled:bg-amber-400 text-white font-bold rounded-xl text-sm transition-all flex items-center justify-center gap-2">
                        <svg x-show="running" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <svg x-show="!running" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span x-text="running ? 'Synchronizing — Please Wait...' : 'Refresh Service History'"></span>
                    </button>
                </form>
            </div>
        </div>

        <div class="md:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="h-1 bg-gradient-to-r from-violet-500 to-indigo-600"></div>
            <div class="p-6 flex flex-col md:flex-row items-center gap-6">
                <div class="w-11 h-11 bg-violet-100 dark:bg-violet-900/40 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                </div>
                <div class="flex-grow">
                    <h3 class="font-bold text-gray-900 dark:text-white">Labour Codes (RTS-Code)</h3>
                    <p class="text-[11px] text-gray-400 mt-1">Rebuilds the standard labour code directory, flat rates, and job descriptions from the Data Operation Excel files.</p>
                </div>
                <div class="w-full md:w-auto flex-shrink-0">
                    <form action="{{ route('import.labour-codes') }}" method="POST" x-data="{ running: false }" @submit.prevent="running = true; startAjaxImport($event.target)">
                        @csrf
                        <button type="submit" :disabled="running"
                            class="w-full md:px-8 py-3 bg-violet-600 hover:bg-violet-700 disabled:bg-gray-300 dark:disabled:bg-slate-700 disabled:cursor-not-allowed text-white font-bold rounded-xl text-sm transition-all flex items-center justify-center gap-2">
                            <svg x-show="running" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            <svg x-show="!running" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span x-text="running ? 'Rebuilding...' : 'Sync Labour Codes'"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>{{-- /grid --}}

    {{-- Recent Activity & Live Log --}}
    <div class="mt-14">

        <h2 class="text-xs font-black text-gray-400 dark:text-slate-500 uppercase tracking-[0.3em] mb-5">Recent Import Activity</h2>
        


        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700 overflow-hidden shadow-sm">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-slate-900/50 border-b border-gray-100 dark:border-slate-700">
                    <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        <th class="px-6 py-3 text-left">Process</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-right">Records</th>
                        <th class="px-6 py-3 text-right">Last Run</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                    <template x-if="Object.keys(statuses).length === 0">
                        <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400 italic text-sm">No import runs recorded yet.</td></tr>
                    </template>
                    <template x-for="(log, type) in statuses" :key="type">
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white" x-text="typeLabel(type)"></td>
                            <td class="px-6 py-4">
                                <span :class="{
                                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400': log.status === 'completed',
                                    'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 animate-pulse': log.status === 'running',
                                    'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400': log.status === 'failed',
                                }" class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-lg" x-text="log.status"></span>
                            </td>
                            <td class="px-6 py-4 text-right font-mono text-xs text-gray-500 dark:text-slate-400">
                                <template x-if="log.total_records">
                                    <span x-text="Number(log.processed_records).toLocaleString() + ' / ' + Number(log.total_records).toLocaleString()"></span>
                                </template>
                                <template x-if="!log.total_records"><span>—</span></template>
                            </td>
                            <td class="px-6 py-4 text-right text-xs text-gray-400 dark:text-slate-500" x-text="formatDate(log.started_at)"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('importHistory', () => ({
        statuses: {},
        pollInterval: null,
        logInterval: null,
        liveLog: 'Waiting for import output...',
        isRunning: false,
        async fetchStatus() {
            try {
                const res = await fetch('{{ route('import.status') }}');
                const newStatuses = await res.json();
                const wasRunning = this.isRunning;
                this.statuses = newStatuses;
                this.isRunning = Object.values(newStatuses).some(s => s.status === 'running');
                
                if (this.isRunning && !this.pollInterval) {
                    await this.fetchLog(); // Immediate first fetch on page load
                    this.pollInterval = setInterval(() => this.fetchStatus(), 3000);
                    this.logInterval  = setInterval(() => this.fetchLog(), 1500);
                } else if (!this.isRunning && this.pollInterval) {
                    clearInterval(this.pollInterval);
                    clearInterval(this.logInterval);
                    this.pollInterval = null;
                    this.logInterval  = null;
                    if (wasRunning) {
                        await this.fetchLog();
                        this.liveLog = this.liveLog + '\n\n✅ Import completed!';
                    }
                }
            } catch (e) { console.error(e); }
        },
        async fetchLog() {
            try {
                const res = await fetch('{{ route('import.log') }}');
                const data = await res.json();
                if (data.log) {
                    this.liveLog = data.log;
                    // Auto scroll to bottom
                    const container = document.getElementById('live-log-container');
                    if (container) container.scrollTop = container.scrollHeight;
                }
            } catch (e) { console.error(e); }
        },
        async startAjaxImport(form) {
            // Immediately show live log panel
            this.isRunning = true;
            this.liveLog = 'Starting import... please wait.';
            if (!this.pollInterval) {
                this.pollInterval = setInterval(() => this.fetchStatus(), 3000);
                this.logInterval  = setInterval(() => this.fetchLog(), 1500);
            }
            // Scroll to live log
            setTimeout(() => {
                const el = document.getElementById('live-log-container');
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 400);

            try {
                const formData = new FormData(form);
                const res = await fetch(form.action, {
                    method: form.method || 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                // Safely parse — only attempt JSON if content-type says so
                const ct = res.headers.get('content-type') || '';
                if (ct.includes('application/json')) {
                    const data = await res.json();
                    if (!res.ok) {
                        this.isRunning = false;
                        alert('Error: ' + (data.message ?? 'Unknown error'));
                    }
                } else if (!res.ok) {
                    this.isRunning = false;
                }
                // Do NOT reload — let polling stream the live log
            } catch (e) {
                console.error('startAjaxImport error:', e);
                // Keep isRunning true — polling will detect actual state
            }
        },
        typeLabel(type) {
            return {
                smart_sync:      'Full Smart Sync (Auto-Merge)',
                customers:       'Customer Data (H04)',
                dms_customers:   'DMS Customers (Python)',
                lvs_vehicles:    'LVS Vehicles',
                service_history: 'Service History',
                suppliers:       'Master Suppliers',
                labour_codes:    'Labour Codes (RTS-Code)',
            }[type] ?? type;
        },
        formatDate(iso) {
            if (!iso) return '—';
            return new Date(iso).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
        }
    }));
});
</script>
@endpush
@endsection
