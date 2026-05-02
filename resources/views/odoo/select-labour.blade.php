@extends('layouts.app')

@section('title', 'Select Labour Codes')

@section('content')
<div x-data="labourSelector()" class="max-w-6xl mx-auto">
    {{-- Header Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 mb-6">
        <div class="flex items-start justify-between flex-wrap gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Odoo Labour Code Selection</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Select the labour codes to add to the job order</p>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-full text-xs font-semibold">
                    From Odoo
                </span>
            </div>
        </div>

        {{-- Job Info Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
            <div class="bg-slate-50 dark:bg-slate-900/50 rounded-xl p-4">
                <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Job Number</p>
                <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $jobNumber ?: '-' }}</p>
            </div>
            <div class="bg-slate-50 dark:bg-slate-900/50 rounded-xl p-4">
                <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Chassis Number</p>
                <p class="text-sm font-bold font-mono text-slate-900 dark:text-white">{{ $chassis }}</p>
            </div>
            <div class="bg-slate-50 dark:bg-slate-900/50 rounded-xl p-4">
                <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Model Prefix</p>
                <p class="text-sm font-bold font-mono text-emerald-600 dark:text-emerald-400">{{ $modelPrefix }}</p>
            </div>
            <div class="bg-slate-50 dark:bg-slate-900/50 rounded-xl p-4">
                <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Customer</p>
                <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $customerName ?: ($vehicle?->customer?->name ?? '-') }}</p>
            </div>
        </div>

        @if($vehicle)
        <div class="mt-4 p-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl">
            <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-300 text-sm">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span><strong>Vehicle found:</strong> {{ $vehicle->registration_no }} — {{ $vehicle->description }}</span>
            </div>
        </div>
        @endif
    </div>

    {{-- Error Messages --}}
    @if($errors->any())
    <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
        <div class="flex items-center gap-2 text-red-700 dark:text-red-300 text-sm">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ $errors->first() }}</span>
        </div>
    </div>
    @endif

    @if($allCodes->isEmpty())
    {{-- No codes found --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-slate-300 dark:text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">No Labour Codes Found</h3>
        <p class="text-sm text-slate-500 dark:text-slate-400">No labour codes found for model prefix <strong class="font-mono">{{ $modelPrefix }}</strong>.</p>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Please verify the chassis number and try again from Odoo.</p>
    </div>
    @else
    {{-- Selection Form --}}
    <form method="POST" action="{{ route('odoo.labour-select.submit') }}" @submit="submitting = true">
        @csrf
        <input type="hidden" name="job_order_id" value="{{ $jobOrderId }}">
        <input type="hidden" name="job_number" value="{{ $jobNumber }}">
        <input type="hidden" name="callback_url" value="{{ $callbackUrl }}">

        {{-- Toolbar --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 mb-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4 flex-wrap">
                {{-- Search --}}
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" x-model="search" placeholder="Filter codes..."
                        class="pl-9 pr-4 py-2 w-64 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-900 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                </div>

                {{-- Select All / None --}}
                <div class="flex items-center gap-2">
                    <button type="button" @click="selectAll()" class="px-3 py-1.5 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-colors">Select All</button>
                    <button type="button" @click="selectNone()" class="px-3 py-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors">Clear All</button>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-sm text-slate-500 dark:text-slate-400">
                    <span x-text="selectedCount" class="font-bold text-indigo-600 dark:text-indigo-400"></span>
                    of <span class="font-semibold">{{ $allCodes->count() }}</span> selected
                </div>
                <button type="submit" :disabled="selectedCount === 0 || submitting"
                    :class="selectedCount === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:from-indigo-700 hover:to-purple-700 shadow-lg shadow-indigo-500/25'"
                    class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl text-sm transition-all flex items-center gap-2">
                    <svg x-show="submitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span x-text="submitting ? 'Sending to Odoo...' : 'Send to Odoo'"></span>
                    <svg x-show="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </div>
        </div>

        {{-- Labour Code Groups --}}
        <div class="space-y-4">
            @foreach($codes as $groupName => $groupCodes)
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden"
                 x-show="filterGroup('{{ addslashes($groupName) }}')" x-cloak>
                {{-- Group Header --}}
                <div class="px-6 py-4 bg-gradient-to-r from-slate-50 to-white dark:from-slate-800 dark:to-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between cursor-pointer group/header"
                     @click="toggleCollapse('{{ addslashes($groupName) }}')">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center group-hover/header:bg-indigo-200 transition-colors">
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 transition-transform duration-200" 
                                 :class="isCollapsed('{{ addslashes($groupName) }}') ? '-rotate-90' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white">{{ $groupName ?: 'Ungrouped' }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $groupCodes->count() }} labour code{{ $groupCodes->count() > 1 ? 's' : '' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click.stop="selectGroup('{{ addslashes($groupName) }}')"
                            class="px-3 py-1 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-colors">
                            Select Group
                        </button>
                    </div>
                </div>

                {{-- Code List --}}
                <div class="divide-y divide-slate-100 dark:divide-slate-700/50" x-show="!isCollapsed('{{ addslashes($groupName) }}')" x-collapse>
                    @foreach($groupCodes as $code)
                    <label x-show="filterCode({{ json_encode($code->code . ' ' . $code->description) }})"
                           class="flex items-center gap-4 px-6 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors cursor-pointer group">
                        <input type="checkbox" name="selected_codes[]" value="{{ $code->id }}"
                            x-model="selected"
                            class="w-5 h-5 rounded-md border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500 dark:bg-slate-800 transition-colors">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $code->code }}</span>
                                @if($code->labour_key)
                                <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 rounded text-[10px] font-mono">{{ $code->labour_key }}</span>
                                @endif
                            </div>
                            <p class="text-sm text-slate-700 dark:text-slate-300 mt-0.5 truncate">{{ $code->description }}</p>
                        </div>
                        @if($code->time_hours)
                        <div class="text-right shrink-0">
                            <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ number_format($code->time_hours, 2) }}</span>
                            <p class="text-[10px] text-slate-400 uppercase">hours</p>
                        </div>
                        @endif
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        {{-- Bottom Submit Bar (sticky) --}}
        <div x-show="selectedCount > 0" x-transition
            class="sticky bottom-4 mt-6 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center">
                    <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400" x-text="selectedCount"></span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Labour codes selected</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Total estimated hours: <span class="font-bold" x-text="totalHours"></span></p>
                </div>
            </div>
            <button type="submit" :disabled="submitting"
                class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold rounded-xl text-sm transition-all shadow-lg shadow-indigo-500/25 flex items-center gap-2">
                <svg x-show="submitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span x-text="submitting ? 'Sending...' : 'Confirm & Send to Odoo'"></span>
            </button>
        </div>
    </form>
    @endif
</div>

@push('scripts')
<script>
function labourSelector() {
    // Build a map of code IDs to their hours for calculating totals
    const codeHours = @json($allCodes->pluck('time_hours', 'id'));
    // Build a map of code IDs to their group names for group selection
    const codeGroups = @json($allCodes->mapWithKeys(fn($c) => [$c->id => $c->group_name]));

    return {
        search: '',
        selected: [],
        submitting: false,
        collapsedGroups: [],

        isCollapsed(group) {
            return this.collapsedGroups.includes(group);
        },

        toggleCollapse(group) {
            if (this.isCollapsed(group)) {
                this.collapsedGroups = this.collapsedGroups.filter(g => g !== group);
            } else {
                this.collapsedGroups.push(group);
            }
        },

        get selectedCount() {
            return this.selected.length;
        },

        get totalHours() {
            return this.selected.reduce((sum, id) => sum + (parseFloat(codeHours[id]) || 0), 0).toFixed(2);
        },

        filterCode(text) {
            if (!this.search) return true;
            return text.toLowerCase().includes(this.search.toLowerCase());
        },

        filterGroup(groupName) {
            if (!this.search) return true;
            // Show group if any code in it matches the search
            return Object.entries(codeGroups).some(([id, group]) => {
                if (group !== groupName) return false;
                const el = document.querySelector(`input[value="${id}"]`);
                if (!el) return true;
                const label = el.closest('label');
                return label ? label.textContent.toLowerCase().includes(this.search.toLowerCase()) : true;
            });
        },

        selectAll() {
            this.selected = Object.keys(codeHours).map(String);
        },

        selectNone() {
            this.selected = [];
        },

        selectGroup(groupName) {
            const groupIds = Object.entries(codeGroups)
                .filter(([, group]) => group === groupName)
                .map(([id]) => String(id));

            // Toggle: if all are selected, deselect; otherwise select all
            const allSelected = groupIds.every(id => this.selected.includes(id));
            if (allSelected) {
                this.selected = this.selected.filter(id => !groupIds.includes(id));
            } else {
                const newSelected = new Set([...this.selected, ...groupIds]);
                this.selected = [...newSelected];
            }
        },
    };
}
</script>
@endpush
@endsection
