<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Service History — {{ $chassis }} | RTS Master Data Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        .highlight-match { background-color: rgba(234, 179, 8, 0.3); border-radius: 2px; padding: 0 2px; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-50 min-h-screen">

<div class="max-w-6xl mx-auto px-4 py-8" x-data="serviceHistory()">

    {{-- Header Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
        <div class="flex items-start justify-between flex-wrap gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900">Service History Viewer</h1>
                        <p class="text-sm text-slate-500">Complete service records for this vehicle</p>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-semibold">
                    From Odoo
                </span>
                <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-semibold">
                    Read-Only
                </span>
            </div>
        </div>

        {{-- Vehicle Info Grid --}}
        @if($vehicle)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Chassis Number</p>
                <p class="text-sm font-bold font-mono text-slate-900">{{ $chassis }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Registration</p>
                <p class="text-sm font-bold text-slate-900">{{ $vehicle->registration_no ?? '-' }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Vehicle</p>
                <p class="text-sm font-bold text-slate-900">{{ $vehicle->description ?? '-' }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Customer</p>
                <p class="text-sm font-bold text-slate-900">{{ $vehicle->customer?->name ?? '-' }}</p>
            </div>
        </div>

        {{-- Summary Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-4">
            <div class="bg-indigo-50 rounded-xl p-4 text-center">
                <p class="text-2xl font-extrabold text-indigo-600">{{ $histories->count() }}</p>
                <p class="text-xs font-semibold text-indigo-500 uppercase tracking-wider mt-1">Service Visits</p>
            </div>
            <div class="bg-emerald-50 rounded-xl p-4 text-center">
                <p class="text-2xl font-extrabold text-emerald-600">{{ $histories->sum(fn($h) => $h->labours->count()) }}</p>
                <p class="text-xs font-semibold text-emerald-500 uppercase tracking-wider mt-1">Total Labours</p>
            </div>
            <div class="bg-amber-50 rounded-xl p-4 text-center">
                <p class="text-2xl font-extrabold text-amber-600">{{ $histories->sum(fn($h) => $h->parts->count()) }}</p>
                <p class="text-xs font-semibold text-amber-500 uppercase tracking-wider mt-1">Total Parts</p>
            </div>
            <div class="bg-purple-50 rounded-xl p-4 text-center">
                @php
                    $dates = $histories->filter(fn($h) => $h->DINVN)->pluck('DINVN')->sort();
                    $firstDate = $dates->first();
                    $lastDate = $dates->last();
                @endphp
                <p class="text-lg font-extrabold text-purple-600">
                    @if($firstDate && $lastDate)
                        {{ $firstDate->format('Y') }} — {{ $lastDate->format('Y') }}
                    @else
                        -
                    @endif
                </p>
                <p class="text-xs font-semibold text-purple-500 uppercase tracking-wider mt-1">Date Range</p>
            </div>
        </div>
        @else
        <div class="mt-6 p-8 bg-red-50 border border-red-200 rounded-xl text-center">
            <svg class="w-12 h-12 mx-auto text-red-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <h3 class="text-lg font-bold text-red-800 mb-1">Vehicle Not Found</h3>
            <p class="text-sm text-red-600">No vehicle found with chassis number <strong class="font-mono">{{ $chassis }}</strong>.</p>
            <p class="text-sm text-red-500 mt-1">Please verify the chassis number and try again from Odoo.</p>
        </div>
        @endif
    </div>

    @if($vehicle)
    {{-- Toolbar --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 mb-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4 flex-wrap flex-1">
            {{-- Search Form --}}
            <form method="GET" action="{{ url()->current() }}" class="relative flex-1 max-w-md" id="search-form">
                {{-- Preserve signed URL params --}}
                <input type="hidden" name="chassis" value="{{ request('chassis') }}">
                <input type="hidden" name="exp" value="{{ request('exp') }}">
                <input type="hidden" name="nonce" value="{{ request('nonce') }}">
                <input type="hidden" name="sig" value="{{ request('sig') }}">

                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search labours & parts (e.g. ban, wiper, oli)..."
                    class="w-full pl-9 pr-20 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
                <button type="submit" class="absolute inset-y-0 right-0 flex items-center pr-3 text-emerald-600 hover:text-emerald-800 font-semibold text-xs uppercase tracking-wider">
                    Search
                </button>
            </form>

            @if($search)
            <a href="{{ url()->current() }}?chassis={{ request('chassis') }}&exp={{ request('exp') }}&nonce={{ request('nonce') }}&sig={{ request('sig') }}"
               class="px-3 py-1.5 text-xs font-semibold text-slate-500 hover:bg-slate-100 rounded-lg transition-colors flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Clear search
            </a>
            @endif
        </div>

        {{-- Export Button --}}
        <form method="POST" action="{{ route('odoo.service-history.export') }}">
            @csrf
            <input type="hidden" name="chassis" value="{{ $chassis }}">
            <input type="hidden" name="search" value="{{ $search }}">
            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm transition-colors flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export CSV
            </button>
        </form>
    </div>

    @if($search && $histories->isEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <h3 class="text-lg font-bold text-slate-900 mb-2">No Results Found</h3>
        <p class="text-sm text-slate-500">No service history records match "<strong>{{ $search }}</strong>" for this vehicle.</p>
    </div>
    @elseif($histories->isEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <h3 class="text-lg font-bold text-slate-900 mb-2">No Service History</h3>
        <p class="text-sm text-slate-500">This vehicle has no service history records in the system.</p>
    </div>
    @else

    @if($search)
    <div class="mb-4 px-4 py-3 bg-amber-50 border border-amber-200 rounded-xl flex items-center gap-2 text-sm text-amber-800">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        Showing <strong class="font-bold">{{ $histories->count() }}</strong> service record{{ $histories->count() !== 1 ? 's' : '' }} matching "<strong>{{ $search }}</strong>"
    </div>
    @endif

    {{-- Service History Cards --}}
    <div class="space-y-4">
        @foreach($histories as $index => $history)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden" x-data="{ open: {{ $index < 3 ? 'true' : 'false' }} }">
            {{-- Invoice Header --}}
            <div class="px-6 py-4 bg-gradient-to-r from-slate-50 to-white border-b border-slate-200 flex items-center justify-between cursor-pointer group"
                 @click="open = !open">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center group-hover:bg-indigo-200 transition-colors">
                        <svg class="w-4 h-4 text-indigo-600 transition-transform duration-200" :class="open ? '' : '-rotate-90'"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-sm font-bold font-mono text-indigo-600">{{ $history->CINVN ?? 'N/A' }}</span>
                            @if($history->DINVN)
                            <span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-[10px] font-semibold">{{ $history->DINVN->format('d M Y') }}</span>
                            @endif
                            @if($history->source)
                            <span class="px-2 py-0.5 bg-purple-100 text-purple-600 rounded text-[10px] font-semibold uppercase">{{ $history->source }}</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            @if($history->DRECV)
                                Received: {{ $history->DRECV->format('d M Y') }}
                            @endif
                            @if($history->EKMPOS)
                                &bull; KM: {{ number_format((float)$history->EKMPOS, 0, ',', '.') }}
                            @endif
                            &bull; {{ $history->labours->count() }} labour{{ $history->labours->count() !== 1 ? 's' : '' }}, {{ $history->parts->count() }} part{{ $history->parts->count() !== 1 ? 's' : '' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Invoice Detail --}}
            <div x-show="open" x-collapse>
                <div class="p-6 space-y-6">

                    {{-- Labour Lines --}}
                    @if($history->labours->isNotEmpty())
                    <div>
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/></svg>
                            Labour Lines ({{ $history->labours->count() }})
                        </h4>
                        <div class="overflow-x-auto rounded-xl border border-slate-200">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-slate-50 text-left">
                                        <th class="px-4 py-2.5 font-semibold text-slate-500 text-xs uppercase">Job Code</th>
                                        <th class="px-4 py-2.5 font-semibold text-slate-500 text-xs uppercase">Description</th>
                                        <th class="px-4 py-2.5 font-semibold text-slate-500 text-xs uppercase text-right">Hours</th>
                                        <th class="px-4 py-2.5 font-semibold text-slate-500 text-xs uppercase text-right">Net</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($history->labours as $labour)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-4 py-2.5 font-mono text-xs font-bold text-indigo-600">{{ $labour->CDJOB }}</td>
                                        <td class="px-4 py-2.5 text-slate-700">
                                            @if($search)
                                                {!! preg_replace('/(' . preg_quote($search, '/') . ')/i', '<span class="highlight-match">$1</span>', e($labour->EMJOB)) !!}
                                            @else
                                                {{ $labour->EMJOB }}
                                            @endif
                                        </td>
                                        <td class="px-4 py-2.5 text-right font-mono text-slate-600">{{ $labour->QHOUR ? number_format((float)$labour->QHOUR, 2) : '-' }}</td>
                                        <td class="px-4 py-2.5 text-right font-mono text-slate-600">{{ $labour->NET ? number_format((float)$labour->NET, 0, ',', '.') : '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    {{-- Parts Lines --}}
                    @if($history->parts->isNotEmpty())
                    <div>
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            Parts Used ({{ $history->parts->count() }})
                        </h4>
                        <div class="overflow-x-auto rounded-xl border border-slate-200">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-slate-50 text-left">
                                        <th class="px-4 py-2.5 font-semibold text-slate-500 text-xs uppercase">Part Code</th>
                                        <th class="px-4 py-2.5 font-semibold text-slate-500 text-xs uppercase">Description</th>
                                        <th class="px-4 py-2.5 font-semibold text-slate-500 text-xs uppercase text-right">Qty</th>
                                        <th class="px-4 py-2.5 font-semibold text-slate-500 text-xs uppercase text-right">Price</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($history->parts as $part)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-4 py-2.5 font-mono text-xs font-bold text-amber-600">{{ $part->CPART }}</td>
                                        <td class="px-4 py-2.5 text-slate-700">
                                            @if($search)
                                                {!! preg_replace('/(' . preg_quote($search, '/') . ')/i', '<span class="highlight-match">$1</span>', e($part->EDESC)) !!}
                                            @else
                                                {{ $part->EDESC }}
                                            @endif
                                        </td>
                                        <td class="px-4 py-2.5 text-right font-mono text-slate-600">{{ $part->QRECV ?? 1 }}</td>
                                        <td class="px-4 py-2.5 text-right font-mono text-slate-600">{{ $part->ASPPRC ? number_format((float)$part->ASPPRC, 0, ',', '.') : '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
    @endif

</div>

<script>
function serviceHistory() {
    return {};
}
</script>

{{-- Footer --}}
<div class="max-w-6xl mx-auto px-4 py-6 text-center">
    <p class="text-[10px] uppercase tracking-widest text-slate-400 font-medium">
        RTS Master Data Hub — Service History Viewer
    </p>
</div>

</body>
</html>
