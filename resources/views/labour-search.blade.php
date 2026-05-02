@extends('layouts.app')

@section('title', 'Labour Search (RTS) - Dealership MasterData Hub Data System')

@section('breadcrumb')
    <x-ui.breadcrumb :items="[['label' => 'Labour Code Search']]" />
@endsection

@section('content')
<div class="space-y-6" x-data="labourSearch()">

    <x-ui.help-panel>
        <x-slot name="trigger"><span label="How to use this tool"></span></x-slot>
        <p class="text-xs leading-relaxed">
            <strong>Labour Code Search</strong> looks up labour operations and estimated hours for a specific vehicle model based on the VIN prefix (first 6 characters).
        </p>
        <ul class="list-disc pl-5 mt-1 text-xs space-y-0.5">
            <li><strong>Enter a full VIN</strong> (17 characters) — the system extracts the first 6 digits to identify your vehicle model.</li>
            <li>Results are <strong>grouped by operation group</strong> (left sidebar) with details shown in the right panel.</li>
            <li>Use the <strong>search box above the table</strong> to filter results by labour key, code, or description.</li>
            <li>Click on a <strong>group name</strong> in the sidebar to jump to that section of the table.</li>
        </ul>
    </x-ui.help-panel>
    <!-- Header/Search Section -->
    <div class="rounded-2xl bg-white dark:bg-slate-800 p-8 shadow-sm ring-1 ring-gray-200 dark:ring-slate-700">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">RTS Labour Search</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">Lookup exact labour operations and estimated hours by VIN.</p>
        </div>

        <form @submit.prevent="performSearch" class="flex flex-col sm:flex-row gap-4">
            <div class="relative flex-grow">
                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-400 dark:text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" x-model="chassisInput" id="chassis"
                    class="block w-full pl-11 rounded-xl border-0 py-3.5 px-4 text-gray-900 dark:text-white bg-white dark:bg-slate-900 ring-1 ring-inset ring-gray-300 dark:ring-slate-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-lg font-mono uppercase transition-all"
                    placeholder="ENTER FULL VIN (EG. W1N243...)">
            </div>
            <button type="submit" class="inline-flex justify-center items-center rounded-xl bg-indigo-600 px-8 py-3.5 text-sm font-bold text-white shadow-xl shadow-indigo-200 hover:bg-indigo-500 transition-all active:scale-95 disabled:opacity-50" :disabled="isLoading">
                <span x-show="!isLoading">Perform Lookup</span>
                <span x-show="isLoading" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Searching...
                </span>
            </button>
        </form>

        <div x-show="error" class="mt-4 p-4 rounded-xl bg-red-50 text-red-700 text-sm font-medium" x-text="error" x-cloak></div>
    </div>

    <!-- Results Section -->
    <div x-show="hasSearched && !isLoading" class="space-y-6" x-cloak>
        <!-- Results Summary Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-2">
            <div>
                <h3 class="text-sm font-bold text-gray-500 dark:text-slate-500 uppercase tracking-widest">Search Results</h3>
                <p class="text-gray-900 dark:text-slate-200 font-medium">Found <span class="text-indigo-600 dark:text-indigo-400 font-bold" x-text="results.total_results"></span> operations for model prefix <span class="bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 px-2 py-0.5 rounded font-mono" x-text="results.model_prefix"></span></p>
                <template x-if="results.vehicle_id && !new URLSearchParams(window.location.search).get('compact')">
                    <a :href="'/master-vehicles/' + results.vehicle_id" class="inline-flex items-center gap-2 mt-2 text-sm font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        View Vehicle Profile
                    </a>
                </template>
            </div>
            <div class="relative max-w-xs w-full">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                </div>
                <input type="text" x-model="searchFilter" class="block w-full pl-9 rounded-lg border-0 py-2 text-sm text-gray-900 dark:text-white bg-white dark:bg-slate-800 ring-1 ring-inset ring-gray-200 dark:ring-slate-700 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600" placeholder="Filter results...">
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-6 items-start">
            <!-- Left Col: Groups -->
            <div class="w-full lg:w-96 shrink-0 space-y-2 lg:max-h-[750px] overflow-y-auto pr-2 custom-scrollbar">
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest px-2 mb-2 block font-mono">Operation Groups</label>
                <div class="space-y-1.5">
                    <template x-for="(items, name) in groupedData" :key="name">
                        <button @click="activeGroup = name" 
                            class="w-full text-left px-4 py-4 rounded-xl transition-all flex items-center justify-between group border border-transparent"
                            :class="activeGroup === name ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20' : 'bg-white dark:bg-slate-800 hover:bg-gray-50 dark:hover:bg-slate-700 text-gray-700 dark:text-slate-300 ring-1 ring-gray-200 dark:ring-slate-700'">
                            <span class="text-[11px] font-bold truncate pr-3 uppercase tracking-tight" x-text="name"></span>
                            <span class="shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full"
                                :class="activeGroup === name ? 'bg-indigo-500/50 text-indigo-100' : 'bg-gray-100 dark:bg-slate-900 text-gray-500 dark:text-slate-400 group-hover:bg-indigo-50 dark:group-hover:bg-indigo-900/30 group-hover:text-indigo-600 dark:group-hover:text-indigo-400'" 
                                x-text="items.length"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Right Col: Table -->
            <div class="flex-grow w-full bg-white dark:bg-slate-800 rounded-2xl shadow-sm ring-1 ring-gray-200 dark:ring-slate-700 overflow-hidden flex flex-col lg:h-[750px]">
                <div class="bg-gray-50 dark:bg-slate-900/50 px-6 py-4 border-b border-gray-100 dark:border-slate-700 shrink-0">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white truncate">
                        Details: <span class="text-indigo-600 dark:text-indigo-400" x-text="activeGroup"></span>
                    </h3>
                </div>
                <div class="flex-grow overflow-x-auto overflow-y-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-slate-700">
                        <thead class="bg-gray-50/50 dark:bg-slate-900/80 sticky top-0 z-10 backdrop-blur-md">
                            <tr>
                                <th class="w-24 px-6 py-3 text-left text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest text-right pr-12">Key</th>
                                <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Description</th>
                                <th class="w-24 px-6 py-3 text-right text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Hours</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-slate-700 bg-white dark:bg-slate-800">
                            <template x-if="activeGroup && groupedData[activeGroup]">
                                <template x-for="item in groupedData[activeGroup]" :key="item.id">
                                    <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/20 transition-colors">
                                        <td class="px-6 py-4 text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400 whitespace-nowrap text-right pr-12" x-text="item.labour_key"></td>
                                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-slate-300 leading-relaxed font-semibold italic" x-text="item.description"></td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="inline-flex items-center rounded-lg bg-emerald-50 dark:bg-emerald-900/30 px-3 py-1 text-sm font-bold text-emerald-700 dark:text-emerald-400 ring-1 ring-inset ring-emerald-600/20 dark:ring-emerald-500/30 whitespace-nowrap font-mono" x-text="Number(item.time_hours).toFixed(2)"></span>
                                        </td>
                                    </tr>
                                </template>
                            </template>
                        </tbody>
                    </table>
                    <div x-show="!activeGroup" class="p-20 text-center">
                        <p class="text-gray-400 italic font-medium">Select a group from the list to view operations.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Empty State -->
    <div x-show="!hasSearched && !isLoading" class="py-20 flex flex-col items-center justify-center text-center">
        <div class="w-20 h-20 bg-gray-50 dark:bg-slate-800 rounded-3xl flex items-center justify-center text-gray-300 dark:text-slate-600 mb-6">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Start your lookup</h2>
        <p class="mt-2 text-gray-500 dark:text-slate-400 max-w-sm">Enter a full Chassis Number (VIN) to retrieve all official labour operations and estimated repair times.</p>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('labourSearch', () => ({
            chassisInput: new URLSearchParams(window.location.search).get('chassis') || '',
            isLoading: false,
            hasSearched: false,
            error: null,
            searchFilter: '',
            activeGroup: null,
            results: { model_prefix: '', total_results: 0, vehicle_id: null, data: [] },
            
            init() {
                if (this.chassisInput) {
                    this.performSearch();
                }
            },
            
            get filteredData() {
                let term = this.searchFilter.toLowerCase();
                if(!term) return this.results.data;
                return this.results.data.filter(item => {
                    return (item.description && item.description.toLowerCase().includes(term)) ||
                           (item.group_name && item.group_name.toLowerCase().includes(term)) ||
                           (item.labour_key && item.labour_key.toLowerCase().includes(term));
                });
            },

            get groupedData() {
                let grouped = {};
                this.filteredData.forEach(item => {
                    let name = item.group_name || 'Uncategorized';
                    if (!grouped[name]) {
                        grouped[name] = [];
                    }
                    grouped[name].push(item);
                });
                return grouped;
            },

            async performSearch() {
                if(this.chassisInput.length < 6) {
                    this.error = "Please enter a valid chassis number (at least 6 characters)";
                    return;
                }
                this.isLoading = true;
                this.error = null;
                try {
                    let response = await fetch(`/web-api/labour-codes?chassis_number=${encodeURIComponent(this.chassisInput)}`);
                    let data = await response.json();
                    if(!response.ok) throw new Error(data.error || "Failed to fetch data.");
                    this.results = data;
                    this.hasSearched = true;
                    
                    let sortedGroups = Object.keys(this.groupedData).sort();
                    if(sortedGroups.length > 0) {
                        this.activeGroup = sortedGroups[0];
                    }
                } catch (e) {
                    this.error = e.message;
                } finally {
                    this.isLoading = false;
                }
            }
        }))
    });
</script>
@endpush
@endsection
