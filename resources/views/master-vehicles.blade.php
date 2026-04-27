@extends('layouts.app')

@section('title', 'Master Vehicles - RTS Master Data System')

@section('breadcrumb')
<li class="inline-flex items-center">
    <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
    </svg>
    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Master Vehicles</span>
</li>
@endsection

@section('content')
<div class="space-y-6" x-data="{ customerModal: null }">
    <!-- Header/Search Section -->
    <div class="relative z-50 rounded-2xl bg-white dark:bg-slate-800 p-8 shadow-sm ring-1 ring-gray-200 dark:ring-slate-700">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Master Vehicles</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">Manage and search vehicle records across the entire repository.</p>
            </div>
            <div class="flex items-center gap-3">
                <button class="inline-flex items-center rounded-xl bg-white dark:bg-slate-800 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-slate-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700 transition-all">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a1 1 0 001 1h10a1 1 0 001-1v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Export
                </button>
                <button class="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xl shadow-indigo-200 hover:bg-indigo-500 transition-all active:scale-95">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Vehicle
                </button>
            </div>
        </div>

        <form method="GET" action="{{ route('master-vehicles.index') }}" class="flex flex-col sm:flex-row gap-4">
            <div class="relative flex-grow">
                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-400 dark:text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                    class="block w-full pl-11 rounded-xl border-0 py-3.5 px-4 text-gray-900 dark:text-white bg-white dark:bg-slate-900 ring-1 ring-inset ring-gray-300 dark:ring-slate-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm transition-all"
                    placeholder="Search Registration No, Chassis, Engine, or Description...">
                @if(request('search'))
                <a href="{{ route('master-vehicles.index') }}" class="absolute right-4 top-3.5 text-gray-400 dark:text-slate-500 hover:text-gray-600 dark:hover:text-slate-300">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
                    </svg>
                </a>
                @endif
            </div>
            <button type="submit" class="inline-flex justify-center items-center rounded-xl bg-gray-900 dark:bg-indigo-600 px-8 py-3.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 dark:hover:bg-indigo-500 transition-all">
                Filter
            </button>
        </form>
    </div>

    <!-- Table Section -->
    <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm ring-1 ring-gray-200 dark:ring-slate-700 overflow-hidden">
        <div class="px-8 py-4 border-b border-gray-100 dark:border-slate-700 flex items-center justify-between bg-gray-50/50 dark:bg-slate-900/50">
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-gray-700 dark:text-slate-300">{{ $vehicles->total() }} Vehicles found</span>
                <div class="h-4 w-px bg-gray-300 dark:bg-slate-700"></div>
                <span class="text-sm text-gray-500 dark:text-slate-400">Page {{ $vehicles->currentPage() }} of {{ $vehicles->lastPage() }}</span>
            </div>
        </div>
        
        <div class="overflow-x-auto min-h-[400px]">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead>
                    <tr class="text-gray-400 dark:text-slate-500 font-medium uppercase tracking-wider text-xs">
                        <th class="px-8 py-5">Registration</th>
                        <th class="px-4 py-5">Model/Variant</th>
                        <th class="px-4 py-5">Chassis No</th>
                        <th class="px-4 py-5">Engine No</th>
                        <th class="px-4 py-5 text-right">Customer</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                    @forelse($vehicles as $vehicle)
                    <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition-colors group">
                        <td class="px-8 py-4">
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-900 dark:text-white">{{ $vehicle->registration_no ?: '-' }}</span>
                                <span class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">{{ $vehicle->franc ?: 'N/A' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex flex-col max-w-[200px] overflow-hidden">
                                <span class="font-medium text-gray-700 dark:text-slate-200 truncate">{{ $vehicle->description ?: '-' }}</span>
                                <span class="text-xs text-indigo-500 dark:text-indigo-400 font-semibold uppercase">{{ $vehicle->variant ?: '-' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <a href="{{ route('master-vehicles.show', $vehicle->id) }}"
                               class="inline-flex items-center font-mono text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 group-hover:underline">
                                {{ $vehicle->chassis_no ?: '-' }}
                                <svg class="w-3 h-3 ml-1 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                            </a>
                        </td>
                        <td class="px-4 py-4 font-mono text-gray-500 dark:text-slate-500">{{ $vehicle->engine_no ?: '-' }}</td>
                        <td class="px-8 py-4 text-right">
                           @if($vehicle->customer)
                               <button 
                                   @click="customerModal = JSON.parse($el.dataset.customer)"
                                   data-customer="{{ json_encode([
                                       'id'           => $vehicle->customer->id,
                                       'name'         => $vehicle->customer->name ?? '(No Name)',
                                       'title'        => $vehicle->customer->title,
                                       'email'        => $vehicle->customer->email,
                                       'address'      => $vehicle->customer->full_address,
                                       'telp_1'       => $vehicle->customer->telp_1,
                                       'telp_2'       => $vehicle->customer->telp_2,
                                       'telp_3'       => $vehicle->customer->telp_3,
                                       'telp_4'       => $vehicle->customer->telp_4,
                                       'company'      => $vehicle->customer->company_name,
                                       'source'       => $vehicle->customer->source,
                                       'date_created' => $vehicle->customer->date_created?->format('d M Y'),
                                   ]) }}"
                                   class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-medium">
                                   {{ $vehicle->customer->name ?: '(No Name)' }}
                               </button>
                           @else
                               <span class="text-gray-400 dark:text-slate-600">-</span>
                           @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-8 py-20 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-gray-50 dark:bg-slate-900 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-gray-300 dark:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">No vehicles found</h3>
                                <p class="text-gray-500 dark:text-slate-400 mt-1">Try adjusting your search filters.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($vehicles->hasPages())
        <div class="px-8 py-6 border-t border-gray-100 dark:border-slate-700 bg-gray-50/30 dark:bg-slate-900/30">
            {{ $vehicles->onEachSide(1)->links() }}
        </div>
        @endif
    </div>

    <!-- Customer Detail Modal (Same as before but with glass styling) -->
    <div x-show="customerModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="customerModal = null">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="customerModal = null"></div>

        <!-- Panel -->
        <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg ring-1 ring-gray-900/5 dark:ring-slate-700 overflow-hidden"
             x-show="customerModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 scale-95">

            <!-- Header -->
            <div class="bg-indigo-600 px-8 py-6 text-white pb-10">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-indigo-200 bg-indigo-500/50 px-2 py-0.5 rounded-full" x-text="customerModal?.source === 'vehicle_import' ? 'Legacy System' : 'Standard Master'"></span>
                        <h3 class="mt-3 text-2xl font-bold" x-text="customerModal?.name"></h3>
                        <p class="text-indigo-200 text-sm font-mono mt-1" x-text="'ID: ' + customerModal?.id"></p>
                    </div>
                    <button @click="customerModal = null" class="rounded-xl p-2 text-indigo-100 hover:text-white hover:bg-indigo-500 transition-all">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Body -->
            <div class="px-8 -mt-6">
                <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm ring-1 ring-gray-100 dark:ring-slate-700 p-6 space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div x-show="customerModal?.company">
                            <p class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Company</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white" x-text="customerModal?.company"></p>
                        </div>
                        <div x-show="customerModal?.email">
                            <p class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Email</p>
                            <a :href="'mailto:' + customerModal?.email" class="mt-1 text-sm font-medium text-indigo-600 dark:text-indigo-400" x-text="customerModal?.email"></a>
                        </div>
                    </div>

                    <div x-show="customerModal?.address">
                        <p class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Address</p>
                        <p class="mt-1 text-sm text-gray-700 dark:text-slate-300 leading-relaxed" x-text="customerModal?.address"></p>
                    </div>

                    <div>
                        <p class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Contact Details</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                           <template x-for="i in [1,2,3,4]">
                               <template x-if="customerModal?.['telp_'+i]">
                                   <span class="inline-flex items-center rounded-lg bg-gray-50 dark:bg-slate-800 px-3 py-1.5 text-sm font-mono text-gray-700 dark:text-slate-300 ring-1 ring-inset ring-gray-200 dark:ring-slate-700" x-text="customerModal['telp_'+i]"></span>
                               </template>
                           </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-8 py-6 flex items-center justify-between">
                <a :href="'/master-customers/' + customerModal?.id" class="text-sm font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors">
                    Full Profile &rarr;
                </a>
                <button @click="customerModal = null" class="rounded-xl px-6 py-2.5 bg-gray-900 dark:bg-indigo-600 text-white text-sm font-bold hover:bg-gray-800 dark:hover:bg-indigo-500 transition-all">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
