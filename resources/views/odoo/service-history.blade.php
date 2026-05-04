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
        body { font-family: 'Inter', sans-serif; font-size: 13px; }
        [x-cloak] { display: none !important; }
        .highlight-match { background-color: rgba(234, 179, 8, 0.3); border-radius: 2px; padding: 0 2px; }
        table th { white-space: nowrap; }
        .selected-row { background-color: rgba(99, 102, 241, 0.1) !important; border-left: 4px solid #6366f1 !important; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-50 min-h-screen">

@php
    $jsonHistories = $histories->map(function($h) {
        return [
            'id' => $h->id,
            'wip_no' => $h->CJOBN,
            'branch' => $h->source,
            'police_no' => $h->CNPOL,
            'chassis_no' => $h->CHASN,
            'receive_date' => $h->DRECV ? $h->DRECV->format('d/m/Y') : '',
            'invoice_no' => $h->CINVN,
            'invoice_date' => $h->DINVN ? $h->DINVN->format('d/m/Y') : '',
            'labour_total' => (float)$h->ALBRS,
            'part_total' => (float)$h->ASPTS,
            'sublet_total' => (float)$h->ASSPS,
            'others_total' => (float)$h->ASUBS + (float)$h->AOTHS1 + (float)$h->AOTHS2,
            'tax' => (float)$h->PTAX,
            'amount' => (float)$h->AMTRS,
            'km_pos' => $h->EKMPOS,
            'labours' => $h->labours->map(fn($l) => [
                'code' => $l->CDJOB,
                'description' => $l->EMJOB,
                'allowed' => (float)$l->QHOUR,
                'net' => (float)$l->NET,
                'taken' => (float)$l->TAKEN
            ]),
            'parts' => $h->parts->map(fn($p) => [
                'code' => $p->CPART,
                'description' => $p->EDESC,
                'qty' => (float)$p->QRECV,
                'price' => (float)$p->ASPPRC,
                'net' => (float)$p->ASPPRC * (float)($p->QRECV ?: 1)
            ])
        ];
    });
@endphp

<div class="max-w-[1600px] mx-auto px-4 py-6" x-data="serviceHistoryViewer(@js($jsonHistories))">

    {{-- Header Card (Same as Image 1) --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 mb-5">
        <div class="flex items-start justify-between flex-wrap gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 leading-tight">Service History Viewer</h1>
                    <p class="text-xs text-slate-500">Complete service records for this vehicle</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-[10px] font-bold uppercase tracking-wider">From Odoo</span>
                <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-[10px] font-bold uppercase tracking-wider">Read-Only</span>
            </div>
        </div>

        @if($vehicle)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
            <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Chassis Number</p>
                <p class="text-sm font-bold font-mono text-slate-900 truncate">{{ $chassis }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Registration</p>
                <p class="text-sm font-bold text-slate-900 truncate">{{ $vehicle->registration_no ?? '-' }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Vehicle</p>
                <p class="text-sm font-bold text-slate-900 truncate">{{ $vehicle->description ?? '-' }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Customer</p>
                <p class="text-sm font-bold text-slate-900 truncate">{{ $vehicle->customer?->name ?? '-' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
            <div class="bg-indigo-50/50 rounded-xl p-3 text-center border border-indigo-100">
                <p class="text-xl font-black text-indigo-600">{{ $histories->count() }}</p>
                <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest">Service Visits</p>
            </div>
            <div class="bg-emerald-50/50 rounded-xl p-3 text-center border border-emerald-100">
                <p class="text-xl font-black text-emerald-600">{{ $histories->sum(fn($h) => $h->labours->count()) }}</p>
                <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest">Total Labours</p>
            </div>
            <div class="bg-amber-50/50 rounded-xl p-3 text-center border border-amber-100">
                <p class="text-xl font-black text-amber-600">{{ $histories->sum(fn($h) => $h->parts->count()) }}</p>
                <p class="text-[10px] font-bold text-amber-500 uppercase tracking-widest">Total Parts</p>
            </div>
            <div class="bg-purple-50/50 rounded-xl p-3 text-center border border-purple-100">
                @php
                    $dates = $histories->filter(fn($h) => $h->DINVN)->pluck('DINVN')->sort();
                    $range = $dates->isNotEmpty() ? $dates->first()->format('Y') . ' — ' . $dates->last()->format('Y') : '-';
                @endphp
                <p class="text-xl font-black text-purple-600">{{ $range }}</p>
                <p class="text-[10px] font-bold text-purple-500 uppercase tracking-widest">Date Range</p>
            </div>
        </div>
        @endif
    </div>

    @if($vehicle)
    {{-- Search & Export Toolbar (Client-Side Search) --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-3 mb-5 flex items-center justify-between gap-4">
        <div class="relative max-w-md flex-1">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <input type="text" x-model="search" placeholder="Search repairs or parts..."
                class="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-xl bg-slate-50 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
        </div>
        
        <form method="POST" action="{{ route('odoo.service-history.export') }}">
            @csrf
            <input type="hidden" name="chassis" value="{{ $chassis }}">
            <input type="hidden" name="search" :value="search">
            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-sm transition-all flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export CSV
            </button>
        </form>
    </div>

    {{-- Master Table: *** All Invoice *** --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">*** All Invoice ***</h3>
            <span class="text-[10px] text-slate-400 font-bold" x-text="`Showing ${filteredHistories.length} results`"></span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-400 font-bold border-b border-slate-200 uppercase tracking-tighter">
                    <tr>
                        <th class="px-3 py-3">WIP NO.</th>
                        <th class="px-3 py-3">BRANCH</th>
                        <th class="px-3 py-3">POLICE NO.</th>
                        <th class="px-3 py-3">CHASSIS NO.</th>
                        <th class="px-3 py-3">RECEIVE DATE</th>
                        <th class="px-3 py-3">INVOICE</th>
                        <th class="px-3 py-3">INVOICE DATE</th>
                        <th class="px-3 py-3 text-right">LABOUR</th>
                        <th class="px-3 py-3 text-right">SPAREPART</th>
                        <th class="px-3 py-3 text-right">SUBLET</th>
                        <th class="px-3 py-3 text-right">OTHERS</th>
                        <th class="px-3 py-3 text-right">SUBTOTAL</th>
                        <th class="px-3 py-3 text-right">TAX</th>
                        <th class="px-3 py-3 text-right">AMOUNT</th>
                        <th class="px-3 py-3">KM POS.</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="h in filteredHistories" :key="h.id">
                        <tr @click="selectInvoice(h)" 
                            :class="selectedId === h.id ? 'selected-row' : 'hover:bg-slate-50'"
                            class="cursor-pointer transition-colors">
                            <td class="px-3 py-3 font-bold" x-text="h.wip_no"></td>
                            <td class="px-3 py-3">
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded-md font-bold text-[10px]" x-text="h.branch"></span>
                            </td>
                            <td class="px-3 py-3 font-bold" x-text="h.police_no"></td>
                            <td class="px-3 py-3 font-mono text-[11px]" x-text="h.chassis_no"></td>
                            <td class="px-3 py-3" x-text="h.receive_date"></td>
                            <td class="px-3 py-3 font-bold text-indigo-600" x-text="h.invoice_no"></td>
                            <td class="px-3 py-3" x-text="h.invoice_date"></td>
                            <td class="px-3 py-3 text-right font-mono" x-text="formatNumber(h.labour_total)"></td>
                            <td class="px-3 py-3 text-right font-mono" x-text="formatNumber(h.part_total)"></td>
                            <td class="px-3 py-3 text-right font-mono" x-text="formatNumber(h.sublet_total)"></td>
                            <td class="px-3 py-3 text-right font-mono" x-text="formatNumber(h.others_total)"></td>
                            <td class="px-3 py-3 text-right font-mono font-bold" x-text="formatNumber(h.labour_total + h.part_total + h.sublet_total + h.others_total)"></td>
                            <td class="px-3 py-3 text-right font-mono" x-text="formatNumber(h.tax)"></td>
                            <td class="px-3 py-3 text-right font-mono font-black text-slate-900" x-text="formatNumber(h.amount)"></td>
                            <td class="px-3 py-3 font-mono" x-text="h.km_pos"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Detail Section (Side-by-side) --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6" x-show="selectedInvoice" x-cloak x-transition>
        
        {{-- Labour Detail --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <h4 class="text-xs font-black text-slate-800 uppercase tracking-widest">
                    Labour Detail Invoice : <span class="text-indigo-600" x-text="selectedInvoice.invoice_no"></span>
                </h4>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-400 font-bold border-b border-slate-200 uppercase tracking-tighter">
                        <tr>
                            <th class="px-4 py-2.5 w-12 text-center">No.</th>
                            <th class="px-4 py-2.5">Description</th>
                            <th class="px-4 py-2.5 text-right">T.Allowed</th>
                            <th class="px-4 py-2.5 text-right">Net Value</th>
                            <th class="px-4 py-2.5 text-right">T.Taken</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(l, i) in selectedInvoice.labours" :key="i">
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-4 py-2.5 text-center text-slate-400 font-bold" x-text="i + 1"></td>
                                <td class="px-4 py-2.5">
                                    <span x-show="l.code" 
                                          :class="isSpecialCode(l.code) ? 'text-purple-500' : 'text-indigo-500'"
                                          class="font-mono font-black block text-[10px] uppercase" x-text="l.code"></span>
                                    <span class="text-slate-700 font-medium" x-html="highlightText(l.description)"></span>
                                </td>
                                <td class="px-4 py-2.5 text-right font-mono" x-text="l.allowed.toFixed(2)"></td>
                                <td class="px-4 py-2.5 text-right font-mono" x-text="formatNumber(l.net)"></td>
                                <td class="px-4 py-2.5 text-right font-mono" x-text="l.taken.toFixed(2)"></td>
                            </tr>
                        </template>
                        <template x-if="selectedInvoice.labours.length === 0">
                            <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400 italic font-medium">No labour records found</td></tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Sparepart Detail --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <h4 class="text-xs font-black text-slate-800 uppercase tracking-widest">
                    Sparepart Detail Invoice : <span class="text-amber-600" x-text="selectedInvoice.invoice_no"></span>
                </h4>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-400 font-bold border-b border-slate-200 uppercase tracking-tighter">
                        <tr>
                            <th class="px-4 py-2.5 w-12 text-center">No.</th>
                            <th class="px-4 py-2.5">Part Number</th>
                            <th class="px-4 py-2.5">Description</th>
                            <th class="px-4 py-2.5 text-right">Qty</th>
                            <th class="px-4 py-2.5 text-right">Price/pcs</th>
                            <th class="px-4 py-2.5 text-right">Net Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(p, i) in selectedInvoice.parts" :key="i">
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-4 py-2.5 text-center text-slate-400 font-bold" x-text="i + 1"></td>
                                <td class="px-4 py-2.5 font-mono font-black text-amber-500 text-[11px]" x-text="p.code"></td>
                                <td class="px-4 py-2.5">
                                    <span class="text-slate-700 font-medium" x-html="highlightText(p.description)"></span>
                                </td>
                                <td class="px-4 py-2.5 text-right font-mono" x-text="p.qty.toFixed(2)"></td>
                                <td class="px-4 py-2.5 text-right font-mono" x-text="formatNumber(p.price)"></td>
                                <td class="px-4 py-2.5 text-right font-mono font-bold" x-text="formatNumber(p.net)"></td>
                            </tr>
                        </template>
                        <template x-if="selectedInvoice.parts.length === 0">
                            <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400 italic font-medium">No sparepart records found</td></tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    @endif

</div>

<script>
function serviceHistoryViewer(initialData) {
    return {
        allHistories: initialData,
        search: '',
        selectedId: initialData.length > 0 ? initialData[0].id : null,
        
        get filteredHistories() {
            if (!this.search) return this.allHistories;
            const term = this.search.toLowerCase();
            return this.allHistories.filter(h => {
                // Search in WIP, Invoice, Branch, Police
                const inHeader = (h.wip_no || '').toLowerCase().includes(term) ||
                                 (h.invoice_no || '').toLowerCase().includes(term) ||
                                 (h.branch || '').toLowerCase().includes(term) ||
                                 (h.police_no || '').toLowerCase().includes(term);
                
                if (inHeader) return true;
                
                // Search in labours
                const inLabours = h.labours.some(l => 
                    (l.description || '').toLowerCase().includes(term) || 
                    (l.code || '').toLowerCase().includes(term)
                );
                
                if (inLabours) return true;
                
                // Search in parts
                const inParts = h.parts.some(p => 
                    (p.description || '').toLowerCase().includes(term) || 
                    (p.code || '').toLowerCase().includes(term)
                );
                
                return inParts;
            });
        },

        get selectedInvoice() {
            return this.allHistories.find(h => h.id === this.selectedId) || null;
        },

        selectInvoice(h) {
            this.selectedId = h.id;
        },

        formatNumber(num) {
            if (num === null || num === undefined) return '0';
            return new Intl.NumberFormat('id-ID').format(num);
        },

        isSpecialCode(code) {
            return ['CUSTOMER', 'NOTES', 'SUN', 'NOTE'].includes(code);
        },

        highlightText(text) {
            if (!this.search || !text) return text;
            const regex = new RegExp(`(${this.search})`, 'gi');
            return text.replace(regex, '<span class="highlight-match">$1</span>');
        }
    };
}
</script>

{{-- Footer --}}
<div class="max-w-6xl mx-auto px-4 py-8 text-center">
    <p class="text-[10px] uppercase tracking-[0.2em] text-slate-400 font-black">
        RTS Master Data Hub — Secure Vehicle Service Audit
    </p>
</div>

</body>
</html>
