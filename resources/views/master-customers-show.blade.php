@extends('layouts.app')

@section('title', ($customer->name ?: 'Customer Detail') . ' - RTS Master Data System')

@section('breadcrumb')
<li class="inline-flex items-center">
    <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
    </svg>
    <a href="{{ route('master-customers.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-indigo-600 md:ml-2">Master Customers</a>
</li>
<li class="inline-flex items-center">
    <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
    </svg>
    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Customer Detail</span>
</li>
@endsection

@section('content')
<div class="space-y-8">
    <!-- Hero Header -->
    <div class="rounded-3xl bg-white dark:bg-slate-800 p-8 shadow-sm ring-1 ring-gray-200 dark:ring-slate-700 lg:p-12 relative overflow-hidden">
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-8">
            <div class="space-y-4">
                <div class="flex items-center gap-4">
                    <span class="inline-flex items-center rounded-xl bg-emerald-50 dark:bg-emerald-900/30 px-3 py-1 text-xs font-bold text-emerald-700 dark:text-emerald-400 ring-1 ring-inset ring-emerald-600/20 dark:ring-emerald-500/30 uppercase tracking-widest">
                        Customer Profile
                    </span>
                    <span class="h-4 w-px bg-gray-200 dark:bg-slate-700"></span>
                    <span class="text-xs font-mono text-gray-400 dark:text-slate-500">{{ $customer->magic_cust }}</span>
                    @if($customer->source === 'vehicle_import')
                        <span class="inline-flex items-center rounded-lg bg-amber-50 dark:bg-amber-900/30 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:text-amber-400 ring-1 ring-inset ring-amber-600/20 dark:ring-amber-500/30 uppercase">Legacy record</span>
                    @endif
                </div>
                <div>
                    <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ $customer->title ?: '' }} {{ $customer->name ?: '(No Name)' }}</h1>
                    <p class="mt-2 text-xl text-gray-500 dark:text-slate-400 font-medium">{{ $customer->company_name ?: 'Private Individual' }}</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <button class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white dark:bg-slate-800 px-6 py-4 text-sm font-bold text-gray-700 dark:text-slate-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit Profile
                </button>
                <button class="inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-6 py-4 text-sm font-bold text-white shadow-xl shadow-indigo-200 dark:shadow-none hover:bg-indigo-500 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Register New Vehicle
                </button>
            </div>
        </div>
        <!-- Abstract Background Shape -->
        <div class="absolute -right-20 -top-20 w-80 h-80 bg-emerald-50 dark:bg-emerald-900/20 rounded-full blur-3xl opacity-50"></div>
    </div>

    <!-- Info Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Customer Details -->
        <div class="lg:col-span-2 space-y-8">
            <div class="rounded-2xl bg-white dark:bg-slate-800 p-8 shadow-sm ring-1 ring-gray-200 dark:ring-slate-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-8 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-slate-900 flex items-center justify-center text-gray-500 dark:text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    Contact & Address
                </h3>
                
                <div class="space-y-8">
                    @if($customer->full_address)
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Full Billing Address</label>
                        <p class="text-lg text-gray-900 dark:text-white leading-relaxed font-medium">{{ $customer->full_address }}</p>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-8 gap-x-12">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Email Address</label>
                            <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400">{{ $customer->email ?: '-' }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Customer ID (Magic)</label>
                            <p class="text-lg font-mono font-bold text-gray-900 dark:text-white">{{ $customer->magic_cust }}</p>
                        </div>
                    </div>

                    <div class="pt-8 border-t border-gray-100 dark:border-slate-700">
                        <label class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest block mb-4">Phone Numbers</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            @foreach(['telp_1', 'telp_2', 'telp_3', 'telp_4'] as $tel)
                                @if($customer->$tel)
                                    <div class="p-4 rounded-xl bg-gray-50 dark:bg-slate-900 border border-gray-100 dark:border-slate-700 flex items-center gap-3 transition-colors">
                                        <svg class="w-4 h-4 text-emerald-500 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1.01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 7.5V5z"></path>
                                        </svg>
                                        <span class="text-sm font-mono font-bold text-gray-700 dark:text-slate-200">{{ $customer->$tel }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assets Section -->
            <div class="rounded-2xl bg-white dark:bg-slate-800 p-8 shadow-sm ring-1 ring-gray-200 dark:ring-slate-700">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1"></path>
                            </svg>
                        </div>
                        Registered Vehicles
                    </h3>
                    <span class="bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 text-xs font-bold px-3 py-1 rounded-full">{{ $customer->vehicles->count() }} Total</span>
                </div>

                @if($customer->vehicles->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($customer->vehicles as $vehicle)
                    <a href="{{ route('master-vehicles.show', $vehicle->magic) }}" class="flex items-center justify-between p-6 rounded-2xl bg-gray-50 dark:bg-slate-900 border border-gray-100 dark:border-slate-700 hover:bg-white dark:hover:bg-slate-800 hover:border-indigo-300 dark:hover:border-indigo-500 hover:shadow-xl dark:shadow-none hover:shadow-indigo-100 transition-all group">
                        <div class="space-y-1">
                            <p class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $vehicle->registration_no ?: '(No Plate)' }}</p>
                            <p class="text-sm text-gray-500 dark:text-slate-400 font-medium">{{ $vehicle->description ?: 'Unknown Model' }}</p>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="text-[10px] font-mono font-bold text-gray-400 dark:text-slate-500 bg-white dark:bg-slate-800 px-1.5 py-0.5 rounded border border-gray-100 dark:border-slate-700">VIN: {{ substr($vehicle->chassis_no, 0, 8) }}...</span>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-300 group-hover:text-indigo-500 transform group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                    @endforeach
                </div>
                @else
                <div class="text-center py-12 rounded-2xl border-2 border-dashed border-gray-100 dark:border-slate-700">
                    <p class="text-gray-400 dark:text-slate-500 italic">No vehicles registered to this customer.</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar Info: Stats & Activity -->
        <div class="space-y-8">
            <!-- Account Summary Card -->
            <div class="rounded-2xl bg-indigo-900 p-8 text-white shadow-xl dark:shadow-none relative overflow-hidden">
                <div class="relative z-10">
                    <h3 class="text-lg font-bold mb-6 text-indigo-200">Account Summary</h3>
                    <div class="space-y-6">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-indigo-300 font-medium">Customer Since</span>
                            <span class="text-sm font-bold text-white">{{ $customer->date_created ? $customer->date_created->format('d M Y') : 'Unknown' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-indigo-300 font-medium">Data Source</span>
                            <span class="text-sm font-bold text-white uppercase">{{ $customer->source ?: 'MASTER' }}</span>
                        </div>
                        <div class="pt-6 border-t border-indigo-800">
                            <div class="flex items-center text-emerald-400 gap-2 font-bold">
                                <span class="relative flex h-3 w-3">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                                </span>
                                Account Active
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Abstract Shape -->
                <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-indigo-700/50 rounded-full blur-3xl opacity-50"></div>
            </div>

            <!-- Activity Log -->
            <div class="rounded-2xl bg-white dark:bg-slate-800 p-8 shadow-sm ring-1 ring-gray-200 dark:ring-slate-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Recent Activity
                </h3>
                <div class="space-y-6">
                    <div class="flex gap-4">
                        <div class="flex flex-col items-center">
                            <div class="w-3 h-3 rounded-full bg-indigo-500 shadow-[0_0_8px_rgba(99,102,241,0.4)]"></div>
                            <div class="w-0.5 h-full bg-gray-100 dark:bg-slate-700"></div>
                        </div>
                        <div class="pb-4">
                            <p class="text-xs font-bold text-gray-900 dark:text-white">Record Initialized</p>
                            <p class="text-[10px] text-gray-400 dark:text-slate-500 mt-1 uppercase tracking-wider">System Auto-Import • Mar 2026</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="rounded-2xl bg-gray-50 dark:bg-slate-900/50 p-6 border border-gray-100 dark:border-slate-800">
                <h4 class="text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest mb-4">Internal Actions</h4>
                <div class="grid grid-cols-1 gap-1">
                    <button class="flex items-center gap-3 p-3 text-sm font-semibold text-gray-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors w-full text-left">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                        </svg>
                        Download PDF Report
                    </button>
                    <button class="flex items-center gap-3 p-3 text-sm font-semibold text-gray-600 dark:text-slate-400 hover:text-red-600 dark:hover:text-red-400 transition-colors w-full text-left">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Flag for Review
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
