@extends('layouts.app')

@section('title', 'Master Suppliers - Dealership MasterData Hub')

@section('breadcrumb')
<li class="inline-flex items-center">
    <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
    </svg>
    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Master Suppliers</span>
</li>
@endsection

@section('content')
<div class="space-y-6" x-data="{ selected: null }">
    <!-- Header/Search Section -->
    <div class="relative z-50 rounded-2xl bg-white dark:bg-slate-800 p-8 shadow-sm ring-1 ring-gray-200 dark:ring-slate-700">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Master Suppliers</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">Manage and search supplier records across the repository.</p>
            </div>
            <div class="flex items-center gap-3" x-data="{ exportMenu: false }">
                <div class="relative">
                    <button @click="exportMenu = !exportMenu" @click.away="exportMenu = false"
                        class="inline-flex items-center rounded-xl bg-white dark:bg-slate-800 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-slate-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700 transition-all">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a1 1 0 001 1h10a1 1 0 001-1v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Export
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="exportMenu" x-cloak class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-800 rounded-xl shadow-xl ring-1 ring-black ring-opacity-5 dark:ring-slate-700 z-50 py-1 overflow-hidden">
                        <a href="{{ route('export.suppliers', ['format' => 'excel']) }}" class="flex items-center px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                            <svg class="w-4 h-4 mr-3 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Export as Excel
                        </a>
                        <a href="{{ route('export.suppliers', ['format' => 'csv']) }}" class="flex items-center px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                            <svg class="w-4 h-4 mr-3 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Export as CSV
                        </a>
                        
                        <div class="my-1 border-t border-gray-100 dark:border-slate-700"></div>
                        
                        <div class="px-4 py-1.5">
                            <span class="text-xs font-bold text-violet-500 uppercase tracking-wider">Odoo ERP</span>
                        </div>
                        
                        <a href="{{ route('export.odoo-suppliers', request()->except('format')) }}" class="group flex items-center px-4 py-2.5 text-sm text-gray-700 dark:text-slate-300 hover:bg-violet-50 dark:hover:bg-violet-900/20 hover:text-violet-700 dark:hover:text-violet-300 transition-colors">
                            <svg class="w-4 h-4 mr-3 text-violet-400 group-hover:text-violet-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
                            Standard Format
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('master-suppliers.index') }}" class="flex flex-col sm:flex-row gap-4">
            <div class="relative flex-grow">
                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-400 dark:text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                    class="block w-full pl-11 rounded-xl border-0 py-3.5 px-4 text-gray-900 dark:text-white bg-white dark:bg-slate-900 ring-1 ring-inset ring-gray-300 dark:ring-slate-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm transition-all"
                    placeholder="Search by name, code, city, or contact...">
                @if(request('search'))
                <a href="{{ route('master-suppliers.index') }}" class="absolute right-4 top-3.5 text-gray-400 dark:text-slate-500 hover:text-gray-600 dark:hover:text-slate-300">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
                    </svg>
                </a>
                @endif
            </div>
            <button type="submit" class="inline-flex justify-center items-center rounded-xl bg-gray-900 dark:bg-indigo-600 px-8 py-3.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 dark:hover:bg-indigo-500 transition-all">
                Search
            </button>
        </form>
    </div>

    <!-- Main Content Grid -->
    <div class="relative z-0 flex flex-col lg:flex-row gap-8 items-start">
        <!-- Results Table -->
        <div class="flex-1 rounded-2xl bg-white dark:bg-slate-800 shadow-sm ring-1 ring-gray-200 dark:ring-slate-700 overflow-hidden">
            <div class="px-8 py-4 border-b border-gray-100 dark:border-slate-700 flex items-center justify-between bg-gray-50/50 dark:bg-slate-900/50">
                <div class="flex items-center gap-4">
                    <span class="text-sm font-medium text-gray-700 dark:text-slate-300">{{ $suppliers->total() }} Suppliers found</span>
                    <div class="h-4 w-px bg-gray-300 dark:bg-slate-700"></div>
                    <span class="text-sm text-gray-500 dark:text-slate-400">Page {{ $suppliers->currentPage() }} of {{ $suppliers->lastPage() }}</span>
                </div>
            </div>

            <div class="overflow-x-auto min-h-[400px]">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead>
                        <tr class="text-gray-400 dark:text-slate-500 font-medium uppercase tracking-wider text-xs">
                            <th class="px-8 py-5">Code / Name</th>
                            <th class="px-4 py-5">City</th>
                            <th class="px-4 py-5">Contact</th>
                            <th class="px-8 py-5 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                        @forelse($suppliers as $supplier)
                        <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 cursor-pointer transition-colors group"
                            @click="selected = {{ $supplier->toJson() }}"
                            :class="selected && selected.id == {{ $supplier->id }} ? 'bg-indigo-50/50 dark:bg-indigo-900/20' : ''"
                        >
                            <td class="px-8 py-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $supplier->name }}</span>
                                    <span class="text-[10px] font-mono text-indigo-500 dark:text-indigo-400 font-semibold tracking-widest mt-0.5">{{ $supplier->code }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-gray-600 dark:text-slate-300">{{ $supplier->city ?: '-' }}</td>
                            <td class="px-4 py-4 text-gray-600 dark:text-slate-300">{{ $supplier->contact_person ?: '-' }}</td>
                            <td class="px-8 py-4 text-right">
                                <span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-700 px-2.5 py-0.5 text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider ring-1 ring-inset ring-slate-400/20">
                                    {{ $supplier->sync_status }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-8 py-20 text-center text-gray-500">No suppliers found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($suppliers->hasPages())
            <div class="px-8 py-6 border-t border-gray-100 dark:border-slate-700 bg-gray-50/30 dark:bg-slate-900/30">
                <div class="flex items-center justify-between">
                    {{-- Left: records per page --}}
                    <form method="GET" action="{{ route('master-suppliers.index') }}" class="flex items-center gap-2">
                        @foreach(request()->except(['per_page', 'page']) as $k => $v)
                            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                        @endforeach
                        <label class="text-sm text-gray-500 dark:text-slate-400">Show</label>
                        <select name="per_page" onchange="this.form.submit()" class="rounded-lg border-0 py-1.5 px-3 text-sm text-gray-700 dark:text-white bg-white dark:bg-slate-800 ring-1 ring-gray-200 dark:ring-slate-700 focus:ring-2 focus:ring-indigo-500">
                            @foreach([25, 50, 100, 200] as $pp)
                                <option value="{{ $pp }}" {{ $perPage == $pp ? 'selected' : '' }}>{{ $pp }}</option>
                            @endforeach
                        </select>
                        <span class="text-sm text-gray-500 dark:text-slate-400">of <strong class="text-gray-900 dark:text-white">{{ number_format($suppliers->total()) }}</strong> records</span>
                    </form>

                    {{-- Center: page links --}}
                    <div class="flex items-center gap-1">
                        {{-- Prev --}}
                        @if($suppliers->onFirstPage())
                            <span class="px-3 py-1.5 rounded-lg text-sm text-gray-300 dark:text-slate-600 cursor-not-allowed">‹ Prev</span>
                        @else
                            <a href="{{ $suppliers->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg text-sm text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">‹ Prev</a>
                        @endif

                        @foreach($suppliers->getUrlRange(max(1,$suppliers->currentPage()-2), min($suppliers->lastPage(),$suppliers->currentPage()+2)) as $page => $url)
                            @if($page == $suppliers->currentPage())
                                <span class="px-3 py-1.5 rounded-lg text-sm font-bold bg-indigo-600 text-white">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="px-3 py-1.5 rounded-lg text-sm text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">{{ $page }}</a>
                            @endif
                        @endforeach

                        {{-- Next --}}
                        @if($suppliers->hasMorePages())
                            <a href="{{ $suppliers->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-sm text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">Next ›</a>
                        @else
                            <span class="px-3 py-1.5 rounded-lg text-sm text-gray-300 dark:text-slate-600 cursor-not-allowed">Next ›</span>
                        @endif
                    </div>

                    {{-- Right: Go to page --}}
                    <form method="GET" action="{{ route('master-suppliers.index') }}" class="flex items-center gap-2">
                        @foreach(request()->except('page') as $k => $v)
                            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                        @endforeach
                        <label class="text-sm text-gray-500 dark:text-slate-400">Go to</label>
                        <input type="number" name="page" min="1" max="{{ $suppliers->lastPage() }}" value="{{ $suppliers->currentPage() }}"
                            class="w-16 rounded-lg border-0 py-1.5 px-2 text-sm text-center text-gray-700 dark:text-white bg-white dark:bg-slate-800 ring-1 ring-gray-200 dark:ring-slate-700 focus:ring-2 focus:ring-indigo-500">
                        <button type="submit" class="px-3 py-1.5 rounded-lg text-sm bg-gray-900 dark:bg-slate-700 text-white hover:bg-gray-700 dark:hover:bg-slate-600 transition-colors">Go</button>
                        <span class="text-sm text-gray-400">/ {{ $suppliers->lastPage() }}</span>
                    </form>
                </div>
            </div>
            @endif
        </div>

        <!-- Detail Sidebar -->
        <div class="w-full lg:w-96 lg:sticky lg:top-6" 
             x-show="selected" 
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 lg:translate-y-0 lg:translate-x-4"
             x-transition:enter-end="opacity-100 translate-y-0 lg:translate-x-0">
            
            <div class="bg-slate-900 rounded-2xl shadow-2xl overflow-hidden text-white ring-1 ring-white/10">
                <div class="p-8 pb-12 relative overflow-hidden bg-gradient-to-br from-slate-800 to-slate-950">
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-500/20 flex items-center justify-center border border-indigo-400/20">
                                <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <button @click="selected = null" class="p-2 rounded-xl hover:bg-white/10 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <h3 class="text-xl font-bold truncate" x-text="selected?.name"></h3>
                        <p class="text-indigo-400 text-xs font-mono mt-1 tracking-widest" x-text="'CODE: ' + selected?.code"></p>
                    </div>
                    <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-indigo-500/10 rounded-full blur-3xl"></div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-t-3xl -mt-6 p-8 space-y-8 text-gray-900 dark:text-white min-h-[500px]">
                    <div class="space-y-6">
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Contact Person</label>
                            <p class="mt-1 font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                <span x-text="selected?.contact_person || '-'"></span>
                            </p>
                        </div>

                        <div x-show="selected?.phone || selected?.email">
                            <label class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest block mb-2">Communication</label>
                            <div class="space-y-2">
                                <template x-if="selected?.phone">
                                    <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-700">
                                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 7.5V5z"></path></svg>
                                        <span class="text-sm font-mono" x-text="selected?.phone"></span>
                                    </div>
                                </template>
                                <template x-if="selected?.email">
                                    <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-700">
                                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                        <span class="text-sm truncate" x-text="selected?.email"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div>
                            <label class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Office Address</label>
                            <div class="mt-2 p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-700 space-y-1">
                                <p class="text-sm text-gray-700 dark:text-slate-300 leading-relaxed" x-text="selected?.address_1"></p>
                                <p class="text-sm text-gray-700 dark:text-slate-300" x-text="selected?.address_2" x-show="selected?.address_2"></p>
                                <p class="text-sm font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider mt-2" x-text="selected?.city"></p>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-100 dark:border-slate-700">
                            <label class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest block mb-3">Bank Settlement Info</label>
                            <div class="bg-indigo-50 dark:bg-indigo-950/30 rounded-2xl p-5 border border-indigo-100 dark:border-indigo-900/50">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="p-2 rounded-lg bg-indigo-600 text-white">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">Bank Name</p>
                                        <p class="text-sm font-bold text-slate-900 dark:text-white" x-text="selected?.bank_name || 'N/A'"></p>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase">Account Number</p>
                                        <p class="text-lg font-mono font-bold text-indigo-600 dark:text-indigo-400" x-text="selected?.bank_account_no || '-'"></p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase">Account Name</p>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200" x-text="selected?.bank_account_name || '-'"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
