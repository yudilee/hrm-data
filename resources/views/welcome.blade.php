@extends('layouts.app')

@section('title', 'Dashboard - Dealership MasterData Hub Data System')

@section('breadcrumb')
<li class="inline-flex items-center">
    <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
    </svg>
    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Dashboard</span>
</li>
@endsection

@section('content')
<div class="space-y-8" x-data="labourSearch()">
    <!-- Welcome Header -->
    <div class="relative overflow-hidden rounded-2xl bg-indigo-600 p-8 text-white shadow-xl shadow-indigo-200">
        <div class="relative z-10">
            <h1 class="text-3xl font-bold tracking-tight">Welcome back!</h1>
            <p class="mt-2 text-indigo-100 max-w-xl">
                Ready to manage your Master Data? Here's a quick overview of your system's health and statistics.
            </p>
        </div>
        <!-- Decorative SVG -->
        <svg class="absolute right-0 top-0 h-full w-1/3 text-indigo-500 opacity-20" fill="currentColor" viewBox="0 0 100 100" preserveAspectRatio="none">
            <path d="M0 100 C 20 0 50 0 100 100 Z"></path>
        </svg>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl bg-white dark:bg-slate-800 p-6 shadow-sm ring-1 ring-gray-100 dark:ring-slate-700 transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-slate-400 uppercase">Total Vehicles</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_vehicles'] ?? 0) }}</p>
                </div>
                <div class="rounded-lg bg-indigo-50 dark:bg-indigo-900/30 p-3 text-indigo-600 dark:text-indigo-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100 transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-slate-400 uppercase">Active Customers</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['active_customers'] ?? 0) }}</p>
                </div>
                <div class="rounded-lg bg-emerald-50 dark:bg-emerald-900/30 p-3 text-emerald-600 dark:text-emerald-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100 transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-slate-400 uppercase">Labour Ops</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_labour_ops'] ?? 0) }}</p>
                </div>
                <div class="rounded-lg bg-blue-50 dark:bg-blue-900/30 p-3 text-blue-600 dark:text-blue-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100 transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-slate-400 uppercase">System Status</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">Online</p>
                </div>
                <div class="rounded-lg bg-indigo-50 dark:bg-indigo-900/30 p-3 text-indigo-600 dark:text-indigo-400">
                    <div class="flex h-6 w-6 items-center justify-center">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-indigo-500"></span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Section -->
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- Labour Quick Search -->
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl bg-white dark:bg-slate-800 p-8 shadow-sm ring-1 ring-gray-200 dark:ring-slate-700" id="labour-search-section">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">RTS Labour Quick Search</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-slate-400 italic">Enter full VIN to lookup exact labour operations</p>
                    </div>
                </div>
                
                <form @submit.prevent="performSearch" class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-grow">
                        <label for="chassis" class="sr-only">Chassis Number</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400 dark:text-slate-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" id="chassis" x-model="chassisInput" class="block w-full pl-10 rounded-xl border-0 py-4 text-gray-900 dark:text-white bg-white dark:bg-slate-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-lg uppercase" placeholder="eg. W1N243...">
                        </div>
                    </div>
                    <button type="submit" class="inline-flex justify-center items-center rounded-xl bg-indigo-600 px-8 py-4 text-md font-bold text-white shadow-xl shadow-indigo-200 hover:bg-indigo-500 transition-all active:scale-95 disabled:opacity-50" :disabled="isLoading">
                        <span x-show="!isLoading">Lookup</span>
                        <span x-show="isLoading">Searching...</span>
                    </button>
                </form>

                <div x-show="error" class="mt-4 p-4 rounded-xl bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 text-sm font-medium" x-text="error" x-cloak></div>

                <!-- Search Results in Dashboard -->
                <div x-show="hasSearched && !isLoading" class="mt-8 border-t border-gray-100 pt-8" x-cloak>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-gray-900 dark:text-white">Found <span class="text-indigo-600 dark:text-indigo-400" x-text="results.total_results"></span> operations for <span x-text="results.model_prefix"></span></h3>
                        <a :href="'{{ route('labour-search') }}?chassis=' + encodeURIComponent(chassisInput)" class="text-sm font-bold text-indigo-600 dark:text-indigo-400">Open Full Search &rarr;</a>
                    </div>
                    <div class="overflow-hidden rounded-xl border border-gray-100 dark:border-slate-700">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 dark:bg-slate-900/50 text-gray-500 dark:text-slate-400">
                                <tr>
                                    <th class="px-4 py-2">Code</th>
                                    <th class="px-4 py-2">Description</th>
                                    <th class="px-4 py-2 text-right">Hours</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-slate-700">
                                <template x-for="item in results.data.slice(0, 5)" :key="item.id">
                                    <tr>
                                        <td class="px-4 py-3 font-mono text-xs text-indigo-600 dark:text-indigo-400" x-text="item.labour_key"></td>
                                        <td class="px-4 py-3 truncate max-w-[200px] text-gray-700 dark:text-slate-300" x-text="item.description"></td>
                                        <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white" x-text="Number(item.time_hours).toFixed(2)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Vehicles Table -->
            <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm ring-1 ring-gray-200 dark:ring-slate-700 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-100 dark:border-slate-700 flex items-center justify-between bg-gray-50/50 dark:bg-slate-900/50">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Recently Updated Vehicles</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-gray-400 dark:text-slate-500 font-medium text-xs uppercase tracking-wider">
                                <th class="px-8 py-4">VIN / Chassis</th>
                                <th class="px-4 py-4">Model</th>
                                <th class="px-4 py-4">Reg No.</th>
                                <th class="px-8 py-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                            @foreach($recentVehicles as $v)
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-900/50 transition-colors">
                                <td class="px-8 py-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $v->chassis_no ?: '-' }}</td>
                                <td class="px-4 py-4 text-gray-600 dark:text-slate-300 truncate max-w-[150px]">{{ $v->description }}</td>
                                <td class="px-4 py-4">
                                    <span class="bg-gray-100 dark:bg-slate-900 px-2 py-1 rounded text-xs font-bold font-mono text-gray-700 dark:text-slate-300">{{ $v->registration_no ?: '-' }}</span>
                                </td>
                                <td class="px-8 py-4 text-right">
                                    <a href="{{ route('master-vehicles.show', $v->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-bold">View &rarr;</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar / Right Col -->
        <div class="space-y-6">
            <div class="rounded-2xl bg-white dark:bg-slate-800 p-8 shadow-sm ring-1 ring-gray-200 dark:ring-slate-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Quick Links</h3>
                <div class="grid grid-cols-1 gap-3">
                    <a href="{{ route('master-vehicles.index') }}" class="flex items-center p-4 rounded-xl border border-gray-100 dark:border-slate-700 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:border-indigo-100 dark:hover:border-indigo-800 transition-all group">
                        <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-slate-900 flex items-center justify-center text-indigo-600 dark:text-indigo-400 group-hover:bg-white dark:group-hover:bg-slate-800 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1"></path>
                            </svg>
                        </div>
                        <span class="ml-3 font-bold text-gray-700 dark:text-slate-200">Master Vehicles</span>
                    </a>
                    <a href="{{ route('master-customers.index') }}" class="flex items-center p-4 rounded-xl border border-gray-100 dark:border-slate-700 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 hover:border-emerald-100 dark:hover:border-emerald-800 transition-all group">
                        <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-slate-900 flex items-center justify-center text-emerald-600 dark:text-emerald-400 group-hover:bg-white dark:group-hover:bg-slate-800 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <span class="ml-3 font-bold text-gray-700 dark:text-slate-200">Master Customers</span>
                    </a>
                </div>
            </div>

            <div class="rounded-2xl bg-indigo-900 p-8 text-white shadow-xl relative overflow-hidden">
                <div class="relative z-10">
                    <h3 class="text-lg font-bold mb-4 text-indigo-200">System Activity</h3>
                    <div class="space-y-4">
                        <div class="flex gap-3">
                            <div class="w-1 bg-indigo-400 rounded"></div>
                            <div>
                                <p class="text-sm font-medium">Sync Active</p>
                                <p class="text-xs text-indigo-300">RTS Repository Online</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-indigo-800 rounded-full blur-2xl opacity-50"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('labourSearch', () => ({
            chassisInput: '',
            isLoading: false,
            hasSearched: false,
            error: null,
            results: { model_prefix: '', total_results: 0, data: [] },
            
            init() {
                const urlParams = new URLSearchParams(window.location.search);
                const prefilledChassis = urlParams.get('chassis');
                const action = urlParams.get('action');

                if (prefilledChassis) {
                    this.chassisInput = prefilledChassis;
                    this.performSearch();
                }

                if (action === 'search' || prefilledChassis) {
                    this.$nextTick(() => {
                        document.getElementById('chassis')?.focus();
                        document.getElementById('labour-search-section')?.scrollIntoView({ behavior: 'smooth' });
                    });
                }
            },
            
            async performSearch() {
                if(this.chassisInput.length < 6) {
                    this.error = "Enter at least 6 characters of VIN";
                    return;
                }
                this.isLoading = true;
                this.error = null;
                try {
                    let response = await fetch(`/api/labour-codes?chassis_number=${encodeURIComponent(this.chassisInput)}`);
                    let data = await response.json();
                    if(!response.ok) throw new Error(data.error || "Fetch failed");
                    this.results = data;
                    this.hasSearched = true;
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
