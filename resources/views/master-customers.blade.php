@extends('layouts.app')

@section('title', 'Master Customers - Dealership MasterData Hub Data System')

@section('breadcrumb')
<li class="inline-flex items-center">
    <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
    </svg>
    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Master Customers</span>
</li>
@endsection

@section('content')
<div class="space-y-6" x-data="{ 
    selected: null, 
    columns: JSON.parse(localStorage.getItem('mc_cols')) || { email: true, phone: true, location: true, vehicles: true, source: true, type: true, quality: true, created: false, service_count: false, branches: true },
    sortUrl(field) {
        const url = new URL(window.location.href);
        const cur = url.searchParams.get('sort');
        const dir = url.searchParams.get('dir');
        url.searchParams.set('sort', field);
        url.searchParams.set('dir', cur === field && dir === 'asc' ? 'desc' : 'asc');
        return url.toString();
    },
    sortIcon(field) {
        const url = new URL(window.location.href);
        const cur = url.searchParams.get('sort') || 'name';
        const dir = url.searchParams.get('dir') || 'asc';
        if (cur !== field) return '↕';
        return dir === 'asc' ? '↑' : '↓';
    }
}" x-effect="localStorage.setItem('mc_cols', JSON.stringify(columns))">
    <!-- Header/Search Section -->
    <div class="relative z-50 rounded-2xl bg-white dark:bg-slate-800 p-8 shadow-sm ring-1 ring-gray-200 dark:ring-slate-700">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Master Customers</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">Search and manage customer profiles and their associated vehicles.</p>
            </div>
            <div class="flex items-center gap-3" x-data="{ exportMenu: false, colMenu: false }">
                
                <!-- Columns Toggle -->
                <div class="relative">
                    <button @click="colMenu = !colMenu" @click.away="colMenu = false" class="inline-flex items-center rounded-xl bg-white dark:bg-slate-800 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-slate-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700 transition-all">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path></svg>
                        Columns
                    </button>
                    <div x-show="colMenu" x-cloak class="absolute right-0 mt-2 w-52 bg-white dark:bg-slate-800 rounded-xl shadow-xl ring-1 ring-black ring-opacity-5 dark:ring-slate-700 z-50 p-2">
                        @foreach([
                            ['email','email','Email'],
                            ['phone','phone','Phone'],
                            ['location','location','City'],
                            ['type','type','Type'],
                            ['quality','quality','Quality Score'],
                            ['vehicles','vehicles','Vehicles'],
                            ['service_count','service_count','Service Count'],
                            ['branches','branches','Branches Visited'],
                            ['source','source','Source'],
                            ['created','created','Date Created'],
                        ] as [$key,$model,$label])
                        <label class="flex items-center px-3 py-2 hover:bg-gray-50 dark:hover:bg-slate-700 rounded-lg cursor-pointer">
                            <input type="checkbox" x-model="columns.{{ $model }}" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                            <span class="ml-3 text-sm text-gray-700 dark:text-slate-300">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Export Menu -->
                <div class="relative">
                    <button @click="exportMenu = !exportMenu" @click.away="exportMenu = false" class="inline-flex items-center rounded-xl bg-white dark:bg-slate-800 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-slate-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700 transition-all">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a1 1 0 001 1h10a1 1 0 001-1v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Export
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="exportMenu" x-cloak class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-800 rounded-xl shadow-xl ring-1 ring-black ring-opacity-5 dark:ring-slate-700 z-50 py-1 overflow-hidden">
                        <a href="{{ request()->fullUrlWithQuery(['format' => 'excel']) }}" class="flex items-center px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                            <svg class="w-4 h-4 mr-3 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Export as Excel
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['format' => 'csv']) }}" class="flex items-center px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                            <svg class="w-4 h-4 mr-3 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Export as CSV
                        </a>
                        
                        <div class="my-1 border-t border-gray-100 dark:border-slate-700"></div>
                        
                        <div class="px-4 py-1.5">
                            <span class="text-xs font-bold text-violet-500 uppercase tracking-wider">Odoo ERP</span>
                        </div>
                        
                        <a href="{{ route('export.odoo-customers', request()->except('format')) }}" class="group flex items-center px-4 py-2.5 text-sm text-gray-700 dark:text-slate-300 hover:bg-violet-50 dark:hover:bg-violet-900/20 hover:text-violet-700 dark:hover:text-violet-300 transition-colors">
                            <svg class="w-4 h-4 mr-3 text-violet-400 group-hover:text-violet-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
                            Standard Format
                        </a>
                        <a href="{{ route('export.odoo-customers', array_merge(request()->except('format'), ['expanded_branches' => 1])) }}" class="group flex items-center px-4 py-2.5 text-sm text-gray-700 dark:text-slate-300 hover:bg-violet-50 dark:hover:bg-violet-900/20 hover:text-violet-700 dark:hover:text-violet-300 transition-colors">
                            <svg class="w-4 h-4 mr-3 text-violet-400 group-hover:text-violet-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            Expanded Branches
                        </a>
                    </div>
                </div>

                <button class="inline-flex items-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xl shadow-emerald-200 hover:bg-emerald-500 transition-all active:scale-95">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                    Add
                </button>
            </div>
        </div>

        <form method="GET" action="{{ route('master-customers.index') }}" class="flex flex-col gap-4">
            {{-- Row 1: Search + City --}}
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-grow">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-400 dark:text-slate-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        class="block w-full pl-11 rounded-xl border-0 py-3 px-4 text-gray-900 dark:text-white bg-white dark:bg-slate-900 ring-1 ring-inset ring-gray-300 dark:ring-slate-700 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm transition-all"
                        placeholder="Search name, ID, email, phone, company, address...">
                    @if(request('search'))
                    <a href="{{ route('master-customers.index') }}" class="absolute right-4 top-3 text-gray-400 hover:text-gray-600"><svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/></svg></a>
                    @endif
                </div>
                {{-- City Dropdown --}}
                <select name="city" class="w-full sm:w-52 rounded-xl border-0 py-3 px-4 text-gray-900 dark:text-white bg-white dark:bg-slate-900 ring-1 ring-inset ring-gray-300 dark:ring-slate-700 focus:ring-2 focus:ring-indigo-600 sm:text-sm transition-all">
                    <option value="">All Cities</option>
                    @foreach($cities as $city)
                        <option value="{{ $city }}" {{ request('city') === $city ? 'selected' : '' }}>{{ $city }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Row 2: Dropdowns --}}
            <div class="flex flex-wrap gap-3">
                <select name="customer_type" class="rounded-xl border-0 py-2.5 px-4 text-gray-900 dark:text-white bg-white dark:bg-slate-900 ring-1 ring-inset ring-gray-300 dark:ring-slate-700 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    <option value="">All Types</option>
                    <option value="individual" {{ request('customer_type') === 'individual' ? 'selected' : '' }}>Individual</option>
                    <option value="company" {{ request('customer_type') === 'company' ? 'selected' : '' }}>Company</option>
                </select>
                <select name="vehicle_status" class="rounded-xl border-0 py-2.5 px-4 text-gray-900 dark:text-white bg-white dark:bg-slate-900 ring-1 ring-inset ring-gray-300 dark:ring-slate-700 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    <option value="">All Vehicle Status</option>
                    <option value="with_vehicles" {{ request('vehicle_status') === 'with_vehicles' ? 'selected' : '' }}>Has Vehicles</option>
                    <option value="no_vehicles" {{ request('vehicle_status') === 'no_vehicles' ? 'selected' : '' }}>No Vehicles</option>
                </select>
                <select name="quality" class="rounded-xl border-0 py-2.5 px-4 text-gray-900 dark:text-white bg-white dark:bg-slate-900 ring-1 ring-inset ring-gray-300 dark:ring-slate-700 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    <option value="">All Quality</option>
                    <option value="high" {{ request('quality') === 'high' ? 'selected' : '' }}>High (>60)</option>
                    <option value="medium" {{ request('quality') === 'medium' ? 'selected' : '' }}>Medium (21–60)</option>
                    <option value="low" {{ request('quality') === 'low' ? 'selected' : '' }}>Low (≤20)</option>
                </select>
                <select name="visit_period" class="rounded-xl border-0 py-2.5 px-4 text-gray-900 dark:text-white bg-white dark:bg-slate-900 ring-1 ring-inset ring-gray-300 dark:ring-slate-700 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    <option value="">Any Visit Time</option>
                    <option value="1" {{ request('visit_period') === '1' ? 'selected' : '' }}>Visited last 1 Year</option>
                    <option value="2" {{ request('visit_period') === '2' ? 'selected' : '' }}>Visited last 2 Years</option>
                    <option value="3" {{ request('visit_period') === '3' ? 'selected' : '' }}>Visited last 3 Years</option>
                    <option value="5" {{ request('visit_period') === '5' ? 'selected' : '' }}>Visited last 5 Years</option>
                </select>
                <select name="source" class="rounded-xl border-0 py-2.5 px-4 text-gray-900 dark:text-white bg-white dark:bg-slate-900 ring-1 ring-inset ring-gray-300 dark:ring-slate-700 focus:ring-2 focus:ring-indigo-600 sm:text-sm">

                    <option value="">All Sources</option>
                    <optgroup label="Branch Sources">
                        @foreach(['HRMSBY PC','HRMSBY CV','HRMJKT CV','HRMDPS PC','HRMDPS CV','HRMSMG PC','HRMSMG CV'] as $s)
                            <option value="{{ $s }}" {{ request('source') === $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Legacy">
                        <option value="customer_import" {{ request('source') === 'customer_import' ? 'selected' : '' }}>Legacy Master</option>
                        <option value="foxpro_recovery" {{ request('source') === 'foxpro_recovery' ? 'selected' : '' }}>FoxPro Recovery</option>
                        <option value="lvs_recovery" {{ request('source') === 'lvs_recovery' ? 'selected' : '' }}>LVS Recovery</option>
                    </optgroup>
                </select>
                {{-- Multi-Branch checkbox --}}
                <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-gray-700 dark:text-slate-300 self-center px-1">
                    <input type="checkbox" name="multi_branch" value="1"
                        {{ request('multi_branch') == '1' ? 'checked' : '' }}
                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                    Multi-Branch Only
                </label>
                {{-- Origin Branch filter --}}
                <select name="branch_source" class="rounded-xl border-0 py-2.5 px-4 text-gray-900 dark:text-white bg-white dark:bg-slate-900 ring-1 ring-inset ring-gray-300 dark:ring-slate-700 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    <option value="">All Origin Branches</option>
                    @foreach(['HRMSBY PC','HRMSBY CV','HRMJKT CV','HRMDPS PC','HRMDPS CV','HRMSMG PC','HRMSMG CV'] as $b)
                        <option value="{{ $b }}" {{ request('branch_source') === $b ? 'selected' : '' }}>{{ $b }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2 ml-auto">
                    <button type="submit" class="inline-flex items-center rounded-xl bg-gray-900 dark:bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-gray-800 dark:hover:bg-indigo-500 transition-all">Search</button>
                    @if(request()->anyFilled(['search','city','vehicle_status','source','customer_type','quality','visit_period']) || request('multi_branch') == '1' || request()->filled('branch_source'))
                    <a href="{{ route('master-customers.index') }}" class="inline-flex items-center rounded-xl bg-white dark:bg-slate-800 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-slate-300 ring-1 ring-gray-300 dark:ring-slate-700 hover:bg-gray-50 transition-all">Reset</a>
                    @endif
                </div>
            </div>
        </form>
        {{-- Active Filter Chips --}}
        @php
            $activeFilters = array_filter([
                'Search'       => request('search'),
                'City'         => request('city'),
                'Type'         => request('customer_type'),
                'Vehicle'      => request('vehicle_status'),
                'Quality'      => request('quality'),
                'Source'       => request('source'),
                'Latest Visit' => request('visit_period') ? request('visit_period') . ' Years' : null,
                'Multi-Branch' => request('multi_branch') == '1' ? 'Yes' : null,
                'Origin Branch' => request('branch_source') ?: null,
            ]);
        @endphp
        @if(count($activeFilters))

        <div class="flex flex-wrap gap-2 pt-2">
            @foreach($activeFilters as $label => $val)
            @php
                $param = match($label) {
                    'Search'       => 'search',
                    'City'         => 'city',
                    'Type'         => 'customer_type',
                    'Vehicle'      => 'vehicle_status',
                    'Quality'      => 'quality',
                    'Source'       => 'source',
                    'Latest Visit' => 'visit_period',
                    'Multi-Branch' => 'multi_branch',
                    'Origin Branch' => 'branch_source',
                    default        => strtolower($label)
                };
            @endphp
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-xs font-semibold ring-1 ring-indigo-200 dark:ring-indigo-700">
                {{ $label }}: {{ $val }}
                <a href="{{ request()->fullUrlWithQuery([$param => null]) }}" class="hover:text-indigo-900 dark:hover:text-white">&times;</a>
            </span>
            @endforeach

        </div>
        @endif
    </div>

    <!-- Main Content Grid -->
    <div class="relative z-0 flex flex-col lg:flex-row gap-8 items-start">
        
        <!-- Results Table -->
        <div class="flex-1 rounded-2xl bg-white dark:bg-slate-800 shadow-sm ring-1 ring-gray-200 dark:ring-slate-700 overflow-hidden">
            <div class="px-8 py-4 border-b border-gray-100 dark:border-slate-700 flex items-center justify-between bg-gray-50/50 dark:bg-slate-900/50">
                <div class="flex items-center gap-4">
                    <span class="text-sm font-medium text-gray-700 dark:text-slate-300">{{ $customers->total() }} Customers found</span>
                    <div class="h-4 w-px bg-gray-300 dark:bg-slate-700"></div>
                    <span class="text-sm text-gray-500 dark:text-slate-400">Page {{ $customers->currentPage() }} of {{ $customers->lastPage() }}</span>
                </div>
            </div>

            <div class="overflow-x-auto min-h-[400px]">
                @php
                    $sort = request('sort', 'name');
                    $dir  = request('dir', 'asc');
                    $sortArrow = function($field) use ($sort, $dir) {
                        if ($sort !== $field) return '<span class="text-gray-300 dark:text-slate-600">↕</span>';
                        return $dir === 'asc'
                            ? '<span class="text-indigo-500">↑</span>'
                            : '<span class="text-indigo-500">↓</span>';
                    };
                    $sortHref = function($field) use ($sort, $dir) {
                        $newDir = ($sort === $field && $dir === 'asc') ? 'desc' : 'asc';
                        return request()->fullUrlWithQuery(['sort' => $field, 'dir' => $newDir]);
                    };
                @endphp
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="sticky top-0 z-10 bg-gray-50/95 dark:bg-slate-900/95 backdrop-blur-sm shadow-sm">
                        <tr class="text-gray-400 dark:text-slate-500 font-medium uppercase tracking-wider text-xs">
                            <th class="px-6 py-4 hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors">
                                <a href="{{ $sortHref('name') }}" class="flex items-center gap-1.5">Name / Company {!! $sortArrow('name') !!}</a>
                            </th>
                            <th class="px-4 py-4 hover:bg-gray-100 dark:hover:bg-slate-800" x-show="columns.type">
                                <a href="{{ $sortHref('customer_type') }}" class="flex items-center gap-1.5">Type {!! $sortArrow('customer_type') !!}</a>
                            </th>
                            <th class="px-4 py-4 hover:bg-gray-100 dark:hover:bg-slate-800" x-show="columns.email">
                                <a href="{{ $sortHref('email') }}" class="flex items-center gap-1.5">Email {!! $sortArrow('email') !!}</a>
                            </th>
                            <th class="px-4 py-4 hover:bg-gray-100 dark:hover:bg-slate-800" x-show="columns.phone">
                                <a href="{{ $sortHref('telp_1') }}" class="flex items-center gap-1.5">Phone {!! $sortArrow('telp_1') !!}</a>
                            </th>
                            <th class="px-4 py-4 hover:bg-gray-100 dark:hover:bg-slate-800" x-show="columns.location">City</th>
                            <th class="px-4 py-4 hover:bg-gray-100 dark:hover:bg-slate-800" x-show="columns.quality">
                                <a href="{{ $sortHref('data_quality_score') }}" class="flex items-center gap-1.5">Quality {!! $sortArrow('data_quality_score') !!}</a>
                            </th>
                            <th class="px-4 py-4 text-center hover:bg-gray-100 dark:hover:bg-slate-800" x-show="columns.vehicles">
                                <a href="{{ $sortHref('vehicles_count') }}" class="flex items-center justify-center gap-1.5">Vehicles {!! $sortArrow('vehicles_count') !!}</a>
                            </th>
                            <th class="px-4 py-4 text-center hover:bg-gray-100 dark:hover:bg-slate-800" x-show="columns.service_count">
                                <a href="{{ $sortHref('service_count') }}" class="flex items-center justify-center gap-1.5">Services {!! $sortArrow('service_count') !!}</a>
                            </th>
                            <th class="px-4 py-4 hover:bg-gray-100 dark:hover:bg-slate-800" x-show="columns.branches">Branches Visited</th>
                            <th class="px-4 py-4 hover:bg-gray-100 dark:hover:bg-slate-800" x-show="columns.created">
                                <a href="{{ $sortHref('date_created') }}" class="flex items-center gap-1.5">Created {!! $sortArrow('date_created') !!}</a>
                            </th>
                            <th class="px-6 py-4 text-right hover:bg-gray-100 dark:hover:bg-slate-800" x-show="columns.source">
                                <a href="{{ $sortHref('source') }}" class="flex items-center justify-end gap-1.5">Source {!! $sortArrow('source') !!}</a>
                            </th>
                        </tr>
                        {{-- Inline column toggle row --}}
                        <tr class="border-t border-gray-100 dark:border-slate-800 bg-gray-50/60 dark:bg-slate-900/60">
                            {{-- Name always visible – locked --}}
                            <td class="px-6 py-2">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 ring-1 ring-indigo-200 dark:ring-indigo-700 select-none">
                                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                    Name
                                </span>
                            </td>
                            {{-- Type --}}
                            <td class="px-4 py-2">
                                <button @click="columns.type = !columns.type"
                                    :class="columns.type ? 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 ring-indigo-200 dark:ring-indigo-700' : 'bg-gray-100 dark:bg-slate-800 text-gray-400 dark:text-slate-500 ring-gray-200 dark:ring-slate-700 opacity-50'"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold ring-1 transition-all hover:opacity-100 cursor-pointer select-none">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Type
                                </button>
                            </td>
                            {{-- Email --}}
                            <td class="px-4 py-2">
                                <button @click="columns.email = !columns.email"
                                    :class="columns.email ? 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 ring-indigo-200 dark:ring-indigo-700' : 'bg-gray-100 dark:bg-slate-800 text-gray-400 dark:text-slate-500 ring-gray-200 dark:ring-slate-700 opacity-50'"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold ring-1 transition-all hover:opacity-100 cursor-pointer select-none">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Email
                                </button>
                            </td>
                            {{-- Phone --}}
                            <td class="px-4 py-2">
                                <button @click="columns.phone = !columns.phone"
                                    :class="columns.phone ? 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 ring-indigo-200 dark:ring-indigo-700' : 'bg-gray-100 dark:bg-slate-800 text-gray-400 dark:text-slate-500 ring-gray-200 dark:ring-slate-700 opacity-50'"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold ring-1 transition-all hover:opacity-100 cursor-pointer select-none">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Phone
                                </button>
                            </td>
                            {{-- City --}}
                            <td class="px-4 py-2">
                                <button @click="columns.location = !columns.location"
                                    :class="columns.location ? 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 ring-indigo-200 dark:ring-indigo-700' : 'bg-gray-100 dark:bg-slate-800 text-gray-400 dark:text-slate-500 ring-gray-200 dark:ring-slate-700 opacity-50'"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold ring-1 transition-all hover:opacity-100 cursor-pointer select-none">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    City
                                </button>
                            </td>
                            {{-- Quality --}}
                            <td class="px-4 py-2">
                                <button @click="columns.quality = !columns.quality"
                                    :class="columns.quality ? 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 ring-indigo-200 dark:ring-indigo-700' : 'bg-gray-100 dark:bg-slate-800 text-gray-400 dark:text-slate-500 ring-gray-200 dark:ring-slate-700 opacity-50'"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold ring-1 transition-all hover:opacity-100 cursor-pointer select-none">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Quality
                                </button>
                            </td>
                            {{-- Vehicles --}}
                            <td class="px-4 py-2 text-center">
                                <button @click="columns.vehicles = !columns.vehicles"
                                    :class="columns.vehicles ? 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 ring-indigo-200 dark:ring-indigo-700' : 'bg-gray-100 dark:bg-slate-800 text-gray-400 dark:text-slate-500 ring-gray-200 dark:ring-slate-700 opacity-50'"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold ring-1 transition-all hover:opacity-100 cursor-pointer select-none">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Vehicles
                                </button>
                            </td>
                            {{-- Services --}}
                            <td class="px-4 py-2 text-center">
                                <button @click="columns.service_count = !columns.service_count"
                                    :class="columns.service_count ? 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 ring-indigo-200 dark:ring-indigo-700' : 'bg-gray-100 dark:bg-slate-800 text-gray-400 dark:text-slate-500 ring-gray-200 dark:ring-slate-700 opacity-50'"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold ring-1 transition-all hover:opacity-100 cursor-pointer select-none">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Services
                                </button>
                            </td>
                            {{-- Branches --}}
                            <td class="px-4 py-2">
                                <button @click="columns.branches = !columns.branches"
                                    :class="columns.branches ? 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 ring-indigo-200 dark:ring-indigo-700' : 'bg-gray-100 dark:bg-slate-800 text-gray-400 dark:text-slate-500 ring-gray-200 dark:ring-slate-700 opacity-50'"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold ring-1 transition-all hover:opacity-100 cursor-pointer select-none">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Branches
                                </button>
                            </td>
                            {{-- Created --}}
                            <td class="px-4 py-2">
                                <button @click="columns.created = !columns.created"
                                    :class="columns.created ? 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 ring-indigo-200 dark:ring-indigo-700' : 'bg-gray-100 dark:bg-slate-800 text-gray-400 dark:text-slate-500 ring-gray-200 dark:ring-slate-700 opacity-50'"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold ring-1 transition-all hover:opacity-100 cursor-pointer select-none">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Created
                                </button>
                            </td>
                            {{-- Source --}}
                            <td class="px-6 py-2 text-right">
                                <button @click="columns.source = !columns.source"
                                    :class="columns.source ? 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 ring-indigo-200 dark:ring-indigo-700' : 'bg-gray-100 dark:bg-slate-800 text-gray-400 dark:text-slate-500 ring-gray-200 dark:ring-slate-700 opacity-50'"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold ring-1 transition-all hover:opacity-100 cursor-pointer select-none">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Source
                                </button>
                            </td>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                        @forelse($customers as $customer)
                        @php
                            $score  = $customer->data_quality_score ?? 0;
                            $qColor = $score > 60 ? 'bg-emerald-500' : ($score > 20 ? 'bg-amber-400' : 'bg-red-400');
                            $dotColor = $score > 60 ? 'bg-emerald-400' : ($score > 20 ? 'bg-amber-400' : 'bg-red-400');
                            $phone  = $customer->telp_1;
                            $waNum  = preg_replace('/[^0-9]/', '', $phone ?? '');
                            if (str_starts_with($waNum, '0')) $waNum = '62' . substr($waNum, 1);
                            $isWa   = strlen($waNum) >= 10;
                            $src    = $customer->source;
                        @endphp
                        <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 cursor-pointer transition-colors group"
                            :class="selected && selected.id == {{ $customer->id }} ? 'bg-indigo-50/50 dark:bg-indigo-900/20' : ''"
                            data-customer="{{ json_encode([
                                'id'              => $customer->id,
                                'name'            => $customer->name ?? '(No Name)',
                                'title'           => $customer->title,
                                'company'         => $customer->company_name,
                                'email'           => $customer->email,
                                'full_address'    => $customer->full_address,
                                'telp_1'          => $customer->telp_1,
                                'telp_2'          => $customer->telp_2,
                                'telp_3'          => $customer->telp_3,
                                'telp_4'          => $customer->telp_4,
                                'source'          => $customer->source,
                                'quality'         => $customer->data_quality_score,
                                'customer_type'   => $customer->customer_type,
                                'service_count'   => $customer->service_count ?? 0,
                                'vehicles_count'  => $customer->vehicles_count,
                                'branches_visited'=> $customer->branches_visited ?? [],
                                'vehicles'        => $customer->vehicles->map(fn($v) => [
                                    'id' => $v->id,
                                    'registration_no' => $v->registration_no,
                                    'description'     => $v->description,
                                    'chassis_no'      => $v->chassis_no,
                                ])->toArray(),
                            ]) }}"
                            @click="selected = JSON.parse($el.dataset.customer)"
                        >
                            {{-- Name --}}
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $dotColor }}"></span>
                                    <div class="flex flex-col min-w-0">
                                        <span class="font-bold text-gray-900 dark:text-white truncate max-w-[180px]">{{ $customer->name ?: '(No Name)' }}</span>
                                        @if($customer->company_name)
                                        <span class="text-xs text-gray-400 dark:text-slate-500 truncate max-w-[180px]">{{ $customer->company_name }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            {{-- Type --}}
                            <td class="px-4 py-3.5" x-show="columns.type">
                                @if($customer->customer_type === 'company')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 text-[10px] font-bold ring-1 ring-blue-200 dark:ring-blue-700 uppercase tracking-wide">Company</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-slate-400 text-[10px] font-bold uppercase tracking-wide">Individual</span>
                                @endif
                            </td>
                            {{-- Email --}}
                            <td class="px-4 py-3.5" x-show="columns.email">
                                @if($customer->email)
                                    <a href="mailto:{{ $customer->email }}" class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm" @click.stop>{{ $customer->email }}</a>
                                @else
                                    <span class="text-gray-300 dark:text-slate-700">—</span>
                                @endif
                            </td>
                            {{-- Phone --}}
                            <td class="px-4 py-3.5 font-mono text-xs" x-show="columns.phone">
                                @if($phone)
                                    <div class="flex items-center gap-1.5">
                                        <a href="tel:{{ $phone }}" class="text-gray-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400" @click.stop>{{ $phone }}</a>
                                        @if($isWa)
                                        <a href="https://wa.me/{{ $waNum }}" target="_blank" class="text-emerald-500 hover:text-emerald-600 flex-shrink-0" title="WhatsApp" @click.stop>
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.136.561 4.14 1.535 5.878L0 24l6.305-1.524A11.94 11.94 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.96 0-3.8-.535-5.376-1.464l-.385-.228-3.985.963.984-3.896-.253-.4A9.956 9.956 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
                                        </a>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-300 dark:text-slate-700">—</span>
                                @endif
                            </td>
                            {{-- City --}}
                            <td class="px-4 py-3.5 text-gray-600 dark:text-slate-400 text-sm" x-show="columns.location">{{ $customer->address_5 ?: ($customer->address_4 ?: ($customer->address_3 ?: '—')) }}</td>

                            {{-- Quality --}}
                            <td class="px-4 py-3.5" x-show="columns.quality">
                                <div class="flex items-center gap-2 min-w-[80px]">
                                    <div class="flex-1 h-1.5 bg-gray-100 dark:bg-slate-700 rounded-full overflow-hidden">
                                        <div class="{{ $qColor }} h-full rounded-full" style="width:{{ $score }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-500 dark:text-slate-400 w-6 text-right">{{ $score }}</span>
                                </div>
                            </td>
                            {{-- Vehicles --}}
                            <td class="px-4 py-3.5 text-center" x-show="columns.vehicles">
                                @if($customer->vehicles_count > 0)
                                    <span class="inline-flex items-center rounded-full bg-indigo-50 dark:bg-indigo-900/30 px-2.5 py-0.5 text-xs font-bold text-indigo-700 dark:text-indigo-400 ring-1 ring-inset ring-indigo-600/20">{{ $customer->vehicles_count }}</span>
                                @else
                                    <span class="text-gray-300 dark:text-slate-700 text-xs">0</span>
                                @endif
                            </td>
                            {{-- Service Count --}}
                            <td class="px-4 py-3.5 text-center" x-show="columns.service_count">
                                @if(($customer->service_count ?? 0) > 0)
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 dark:bg-emerald-900/20 px-2.5 py-0.5 text-xs font-bold text-emerald-700 dark:text-emerald-400 ring-1 ring-inset ring-emerald-600/20">{{ $customer->service_count }}</span>
                                @else
                                    <span class="text-gray-300 dark:text-slate-700 text-xs">0</span>
                                @endif
                            </td>
                            {{-- Branches Visited --}}
                            <td class="px-4 py-3.5" x-show="columns.branches">
                                <div class="flex flex-wrap gap-1 max-w-[180px]">
                                    @forelse($customer->branches_visited ?? [] as $branch)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-slate-300 ring-1 ring-inset ring-gray-200 dark:ring-slate-600">
                                            {{ $branch }}
                                        </span>
                                    @empty
                                        <span class="text-gray-300 dark:text-slate-700 text-xs">—</span>
                                    @endforelse
                                </div>
                            </td>
                            {{-- Date Created --}}
                            <td class="px-4 py-3.5 text-gray-500 dark:text-slate-400 text-xs" x-show="columns.created">
                                {{ $customer->date_created?->format('d M Y') ?? '—' }}
                            </td>
                            {{-- Source --}}
                            <td class="px-6 py-3.5 text-right" x-show="columns.source">
                                @if(str_starts_with($src, 'HRM'))
                                    <span class="inline-flex items-center rounded-full bg-violet-50 dark:bg-violet-900/10 px-2 py-0.5 text-[10px] font-bold text-violet-700 dark:text-violet-400 ring-1 ring-inset ring-violet-600/20 uppercase">{{ $src }}</span>
                                @elseif($src === 'customer_import')
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 dark:bg-emerald-900/10 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:text-emerald-400 ring-1 ring-inset ring-emerald-600/20 uppercase">Master</span>
                                @elseif(str_contains($src, 'recovery'))
                                    <span class="inline-flex items-center rounded-full bg-amber-50 dark:bg-amber-900/10 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:text-amber-400 ring-1 ring-inset ring-amber-600/20 uppercase">Recovery</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-slate-700 px-2 py-0.5 text-[10px] font-bold text-gray-600 dark:text-slate-400 uppercase">{{ $src ?: 'Legacy' }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-14 h-14 bg-gray-50 dark:bg-slate-900 rounded-full flex items-center justify-center">
                                        <svg class="w-7 h-7 text-gray-300 dark:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
                                    </div>
                                    <p class="text-gray-500 dark:text-slate-400 font-medium">No customers found</p>
                                    <p class="text-sm text-gray-400 dark:text-slate-500">Try adjusting your filters.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Odoo-style Pagination --}}
            <div class="px-6 py-4 border-t border-gray-100 dark:border-slate-700 bg-gray-50/30 dark:bg-slate-900/30 flex flex-wrap items-center justify-between gap-4">
                {{-- Left: per-page + total --}}
                <form method="GET" action="{{ route('master-customers.index') }}" class="flex items-center gap-3" id="per-page-form">
                    @foreach(request()->except(['per_page','page']) as $k => $v)
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach
                    <label class="text-sm text-gray-500 dark:text-slate-400">Show</label>
                    <select name="per_page" onchange="this.form.submit()" class="rounded-lg border-0 py-1.5 px-3 text-sm text-gray-700 dark:text-white bg-white dark:bg-slate-800 ring-1 ring-gray-200 dark:ring-slate-700 focus:ring-2 focus:ring-indigo-500">
                        @foreach([20, 50, 100, 200] as $pp)
                            <option value="{{ $pp }}" {{ $perPage == $pp ? 'selected' : '' }}>{{ $pp }}</option>
                        @endforeach
                    </select>
                    <span class="text-sm text-gray-500 dark:text-slate-400">of <strong class="text-gray-900 dark:text-white">{{ number_format($customers->total()) }}</strong> records</span>
                </form>

                {{-- Center: page links --}}
                <div class="flex items-center gap-1">
                    {{-- Prev --}}
                    @if($customers->onFirstPage())
                        <span class="px-3 py-1.5 rounded-lg text-sm text-gray-300 dark:text-slate-600 cursor-not-allowed">‹ Prev</span>
                    @else
                        <a href="{{ $customers->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg text-sm text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">‹ Prev</a>
                    @endif

                    @foreach($customers->getUrlRange(max(1,$customers->currentPage()-2), min($customers->lastPage(),$customers->currentPage()+2)) as $page => $url)
                        @if($page == $customers->currentPage())
                            <span class="px-3 py-1.5 rounded-lg text-sm font-bold bg-indigo-600 text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="px-3 py-1.5 rounded-lg text-sm text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">{{ $page }}</a>
                        @endif
                    @endforeach

                    {{-- Next --}}
                    @if($customers->hasMorePages())
                        <a href="{{ $customers->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-sm text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">Next ›</a>
                    @else
                        <span class="px-3 py-1.5 rounded-lg text-sm text-gray-300 dark:text-slate-600 cursor-not-allowed">Next ›</span>
                    @endif
                </div>

                {{-- Right: Go to page --}}
                <form method="GET" action="{{ route('master-customers.index') }}" class="flex items-center gap-2">
                    @foreach(request()->except('page') as $k => $v)
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach
                    <label class="text-sm text-gray-500 dark:text-slate-400">Go to</label>
                    <input type="number" name="page" min="1" max="{{ $customers->lastPage() }}" value="{{ $customers->currentPage() }}"
                        class="w-16 rounded-lg border-0 py-1.5 px-2 text-sm text-center text-gray-700 dark:text-white bg-white dark:bg-slate-800 ring-1 ring-gray-200 dark:ring-slate-700 focus:ring-2 focus:ring-indigo-500">
                    <button type="submit" class="px-3 py-1.5 rounded-lg text-sm bg-gray-900 dark:bg-slate-700 text-white hover:bg-gray-700 dark:hover:bg-slate-600 transition-colors">Go</button>
                    <span class="text-sm text-gray-400">/ {{ $customers->lastPage() }}</span>
                </form>
            </div>
        </div>


        <!-- Detail Sidebar -->
        <div class="w-full lg:w-96 lg:sticky lg:top-6"
             x-show="selected"
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 lg:translate-y-0 lg:translate-x-4"
             x-transition:enter-end="opacity-100 translate-y-0 lg:translate-x-0">

            <div class="bg-indigo-900 rounded-2xl shadow-2xl overflow-hidden text-white">
                <!-- Profile Header -->
                <div class="p-8 pb-12 relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-500/30 flex items-center justify-center border border-indigo-400/30">
                                    <svg class="w-6 h-6 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                                <span class="text-[10px] font-bold uppercase tracking-widest text-indigo-300 bg-indigo-500/30 px-2 py-0.5 rounded-full" x-text="selected?.customer_type === 'company' ? 'Company' : 'Individual'"></span>
                            </div>
                            <button @click="selected = null" class="p-2 rounded-xl hover:bg-white/10 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <h3 class="text-xl font-bold truncate" x-text="selected?.name"></h3>
                        <p class="text-indigo-300 text-sm font-mono mt-1" x-text="'ID: ' + selected?.id"></p>
                        <!-- Quality Score Bar -->
                        <div class="mt-4">
                            <div class="flex justify-between text-xs text-indigo-300 mb-1">
                                <span>Data Quality</span>
                                <span x-text="(selected?.quality ?? 0) + '%'"></span>
                            </div>
                            <div class="h-1.5 bg-indigo-800 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500"
                                     :class="(selected?.quality ?? 0) > 60 ? 'bg-emerald-400' : ((selected?.quality ?? 0) > 20 ? 'bg-amber-400' : 'bg-red-400')"
                                     :style="'width:' + (selected?.quality ?? 0) + '%'"></div>
                            </div>
                        </div>
                    </div>
                    <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-indigo-500/20 rounded-full blur-3xl"></div>
                </div>

                <!-- Info Sections -->
                <div class="bg-white dark:bg-slate-800 rounded-t-3xl -mt-6 p-6 space-y-6 text-gray-900 dark:text-white min-h-[400px]">
                    <!-- Stats row -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-xl p-3 text-center">
                            <p class="text-2xl font-extrabold text-indigo-700 dark:text-indigo-400" x-text="selected?.vehicles_count ?? 0"></p>
                            <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider mt-0.5">Vehicles</p>
                        </div>
                        <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-xl p-3 text-center">
                            <p class="text-2xl font-extrabold text-emerald-700 dark:text-emerald-400" x-text="selected?.service_count ?? 0"></p>
                            <p class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider mt-0.5">Services</p>
                        </div>
                    </div>

                    <!-- Branches Visited -->
                    <div x-show="selected?.branches_visited?.length > 0">
                        <label class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest block mb-2">Branches Visited</label>
                        <div class="flex flex-wrap gap-1.5">
                            <template x-for="branch in selected?.branches_visited" :key="branch">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-violet-50 dark:bg-violet-900/20 text-violet-700 dark:text-violet-300 text-[10px] font-bold ring-1 ring-inset ring-violet-200 dark:ring-violet-700 uppercase tracking-wide" x-text="branch"></span>
                            </template>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <div x-show="selected?.company">
                            <label class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Company</label>
                            <p class="mt-1 font-semibold text-gray-900 dark:text-white" x-text="selected?.company"></p>
                        </div>
                        <div x-show="selected?.email">
                            <label class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Email</label>
                            <a :href="'mailto:' + selected?.email" class="block mt-1 font-medium text-indigo-600 dark:text-indigo-400 hover:underline text-sm" x-text="selected?.email"></a>
                        </div>
                        <div x-show="selected?.full_address">
                            <label class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Address</label>
                            <p class="mt-1 text-sm text-gray-600 dark:text-slate-300 leading-relaxed" x-text="selected?.full_address"></p>
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest block mb-3">Phones</label>
                        <div class="flex flex-col gap-2">
                           <template x-for="i in [1,2,3,4]">
                               <template x-if="selected?.['telp_'+i]">
                                   <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-slate-900 border border-gray-100 dark:border-slate-700 group hover:border-indigo-200 dark:hover:border-indigo-500 transition-colors">
                                       <svg class="w-4 h-4 text-gray-400 group-hover:text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 7.5V5z"/></svg>
                                       <a :href="'tel:' + selected['telp_'+i]" class="text-sm font-mono font-medium text-gray-700 dark:text-slate-200" x-text="selected['telp_'+i]"></a>
                                   </div>
                               </template>
                           </template>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Registered Vehicles</label>
                            <span class="bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-[10px] font-bold px-2 py-0.5 rounded-full" x-text="selected?.vehicles_count"></span>
                        </div>
                        <div class="space-y-2">
                            <template x-for="v in selected?.vehicles" :key="v.id">
                                <a :href="'/master-vehicles/' + v.id" class="flex items-center justify-between p-3 rounded-xl bg-gray-50/50 dark:bg-slate-900/50 border border-gray-100 dark:border-slate-700 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:border-indigo-200 dark:hover:border-indigo-800 transition-all group">
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-indigo-700 dark:group-hover:text-indigo-400" x-text="v.registration_no || '(No Plate)'"></p>
                                        <p class="text-[10px] font-mono text-gray-400 dark:text-slate-500 mt-0.5" x-text="v.chassis_no"></p>
                                    </div>
                                    <svg class="w-4 h-4 text-gray-300 dark:text-slate-700 group-hover:text-indigo-500 transition-all transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </template>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-slate-700">
                        <a :href="'/master-customers/' + selected?.id" class="block w-full py-3.5 rounded-2xl bg-gray-900 dark:bg-indigo-600 text-white text-center font-bold text-sm hover:bg-gray-800 dark:hover:bg-indigo-500 transition-all">
                            Full Profile Details &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
