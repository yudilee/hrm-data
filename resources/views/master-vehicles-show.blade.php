@extends('layouts.app')

@section('title', ($vehicle->registration_no ?: 'Vehicle Detail') . ' - Dealership MasterData Hub Data System')

@section('breadcrumb')
@if($vehicle->customer)
<li class="inline-flex items-center">
    <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
    </svg>
    <a href="{{ route('master-customers.show', $vehicle->customer->id) }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-indigo-600 md:ml-2">{{ $vehicle->customer->name ?: 'Customer Profile' }}</a>
</li>
@else
<li class="inline-flex items-center">
    <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
    </svg>
    <a href="{{ route('master-vehicles.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-indigo-600 md:ml-2">Master Vehicles</a>
</li>
@endif
<li class="inline-flex items-center">
    <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
    </svg>
    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Vehicle Detail</span>
</li>
@endsection

@section('content')
<div class="space-y-8" x-data="{ showLabourDrawer: false }">
    <!-- Hero Header -->
    <div class="rounded-3xl bg-white dark:bg-slate-800 p-8 shadow-sm ring-1 ring-gray-200 dark:ring-slate-700 lg:p-12 relative overflow-hidden">
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-8">
            <div class="space-y-4">
                <div class="flex items-center gap-4">
                    <span class="inline-flex items-center rounded-xl bg-indigo-50 dark:bg-indigo-900/30 px-3 py-1 text-xs font-bold text-indigo-700 dark:text-indigo-400 ring-1 ring-inset ring-indigo-600/20 dark:ring-indigo-500/30 uppercase tracking-widest">
                        Vehicle Profile
                    </span>
                    <span class="h-4 w-px bg-gray-200 dark:bg-slate-700"></span>
                    <span class="text-xs font-mono text-gray-400 dark:text-slate-500">{{ $vehicle->id }}</span>
                </div>
                <div>
                    <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ $vehicle->registration_no ?: '(No Plate)' }}</h1>
                    <p class="mt-2 text-xl text-gray-500 dark:text-slate-400 font-medium">{{ $vehicle->description ?: 'Unknown Model' }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="bg-gray-100 dark:bg-slate-900 text-gray-700 dark:text-slate-300 px-3 py-1 rounded-lg text-sm font-bold">{{ $vehicle->franc ?: 'N/A' }}</span>
                    <span class="bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 px-3 py-1 rounded-lg text-sm font-bold">{{ $vehicle->variant ?: 'Standard' }}</span>
                </div>
            </div>

                @if($vehicle->chassis_no)
                <div class="flex flex-col gap-3">
                    <button @click="showLabourDrawer = true" 
                       class="inline-flex items-center justify-center gap-3 rounded-2xl bg-indigo-600 px-8 py-5 text-lg font-bold text-white shadow-2xl shadow-indigo-200 hover:bg-indigo-500 transition-all active:scale-95 group">
                        <svg class="w-6 h-6 text-indigo-200 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Lookup RTS Labour Codes
                    </button>
                    <p class="text-center text-xs text-gray-400">Identify exact labor times via VIN prefix</p>
                </div>
                @endif
            </div>
            <!-- Abstract Background Shape -->
            <div class="absolute -right-20 -top-20 w-80 h-80 bg-indigo-50 dark:bg-indigo-900/20 rounded-full blur-3xl opacity-50"></div>
        </div>

        <!-- Info Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Technical Details -->
            <div class="lg:col-span-2 space-y-8">
                <div class="rounded-2xl bg-white dark:bg-slate-800 p-8 shadow-sm ring-1 ring-gray-200 dark:ring-slate-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-8 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-slate-900 flex items-center justify-center text-gray-500 dark:text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        Technical Specifications
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-8 gap-x-12">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Chassis Number (VIN)</label>
                            <div class="flex items-center gap-2" x-data="{ copied: false }">
                                <p class="text-lg font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $vehicle->chassis_no ?: '-' }}</p>
                                @if($vehicle->chassis_no)
                                <button @click="navigator.clipboard.writeText('{{ $vehicle->chassis_no }}'); copied = true; setTimeout(() => copied = false, 2000)" class="text-gray-400 hover:text-indigo-600 transition-colors focus:outline-none" title="Copy VIN">
                                    <svg x-show="!copied" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                    <svg x-show="copied" class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </button>
                                @endif
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Engine Number</label>
                            <p class="text-lg font-mono font-bold text-gray-900 dark:text-white">{{ $vehicle->engine_no ?: '-' }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">MHL Number</label>
                            <p class="text-lg font-mono font-medium text-gray-700 dark:text-slate-300">{{ $vehicle->mhl_number ?: '-' }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Status / Condition</label>
                            <div>
                                @if($vehicle->status === 'C') 
                                    <span class="inline-flex items-center rounded-lg bg-gray-100 dark:bg-slate-900 px-3 py-1 text-sm font-bold text-gray-500 dark:text-slate-400">CLOSED</span>
                                @else 
                                    <span class="inline-flex items-center rounded-lg bg-green-50 dark:bg-green-900/30 px-3 py-1 text-sm font-bold text-green-700 dark:text-green-400 ring-1 ring-inset ring-green-600/20 dark:ring-green-500/30">ACTIVE ({{ $vehicle->status ?: 'OK' }})</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-12 pt-8 border-t border-gray-100 dark:border-slate-700 flex flex-wrap gap-12">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase">Registration Date</p>
                                <p class="font-bold text-gray-900 dark:text-white">{{ $vehicle->reg_date ? $vehicle->reg_date->format('d M Y') : '-' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase">Last Service</p>
                                <p class="font-bold text-gray-900 dark:text-white">{{ $vehicle->last_service_date ? $vehicle->last_service_date->format('d M Y') : '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Service History Interactive Log -->
                <div class="rounded-2xl bg-white dark:bg-slate-900 ring-1 ring-gray-200 dark:ring-slate-800 p-8 shadow-sm" x-data="{ search: '', expanded: null }">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                        <h3 class="text-lg font-bold flex items-center gap-2 text-gray-900 dark:text-white">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Service History
                            <span class="bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-400 py-0.5 px-2 rounded-full text-xs ml-2">{{ count($vehicle->serviceHistories ?? []) }}</span>
                        </h3>
                        <div class="relative w-full sm:w-64">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <input type="text" x-model="search" placeholder="Search repairs or parts..." class="block w-full pl-9 rounded-xl border-0 py-2 px-3 text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-slate-800 ring-1 ring-inset ring-gray-300 dark:ring-slate-700 focus:ring-2 focus:ring-indigo-600 transition-all">
                        </div>
                    </div>

                    <div class="space-y-4 max-h-[600px] overflow-y-auto pr-2">
                        @forelse(optional($vehicle->serviceHistories)->sortByDesc('DRECV') ?? [] as $history)
                        <div x-data="{ 
                                textContent: '{{ strtolower(htmlspecialchars($history->CINVN . ' ' . $history->CNPOL . ' ' . collect($history->labours)->pluck('description')->join(' ') . ' ' . collect($history->parts)->pluck('description')->join(' '))) }}' 
                             }" 
                             x-show="search === '' || textContent.includes(search.toLowerCase())"
                             class="bg-gray-50 dark:bg-slate-800/50 rounded-xl p-4 border border-gray-100 dark:border-slate-700 transition-colors">
                            
                            <div class="flex items-start justify-between cursor-pointer" @click="expanded = expanded === {{ $history->id }} ? null : {{ $history->id }}">
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $history->CINVN }}</p>
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ str_starts_with($history->source, 'HRM') ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' }}">
                                            {{ $history->source }}
                                        </span>
                                        @if($history->CNPOL)
                                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-mono font-bold border {{ $history->CNPOL !== $vehicle->registration_no ? 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-800/50' : 'bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-slate-400 border-gray-200 dark:border-slate-700' }}" title="Plate used during service">
                                                {{ $history->CNPOL }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">
                                        {{ $history->DRECV ? $history->DRECV->format('d M Y') : 'Unknown Date' }} • KMs: {{ number_format($history->EKMPOS) }}
                                    </p>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 transform transition-transform" :class="expanded === {{ $history->id }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>

                            <div x-show="expanded === {{ $history->id }}" x-collapse class="mt-4 pt-4 border-t border-gray-200 dark:border-slate-700 space-y-4">
                                @if(count($history->labours) > 0)
                                <div>
                                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Labour</h4>
                                    <ul class="space-y-1">
                                        @foreach($history->labours as $labour)
                                            <li class="text-sm text-gray-700 dark:text-slate-300 flex justify-between">
                                                <span>{{ $labour->description ?: $labour->code }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                                
                                @if(count($history->parts) > 0)
                                <div>
                                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Parts</h4>
                                    <ul class="space-y-1">
                                        @foreach($history->parts as $part)
                                            <li class="text-sm text-gray-700 dark:text-slate-300 flex justify-between">
                                                <span>{{ $part->description ?: $part->code }}</span>
                                                <span class="text-gray-500 text-xs">x{{ $part->quantity }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                                
                                @if(count($history->labours) == 0 && count($history->parts) == 0)
                                    <p class="text-sm text-gray-500 italic">No detailed labour or parts available.</p>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8 text-gray-500 text-sm">
                            No service history available for this vehicle.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Sidebar Info: Customer -->
            <div class="space-y-8">
                <div class="rounded-2xl bg-white dark:bg-slate-800 p-8 shadow-sm ring-1 ring-gray-200 dark:ring-slate-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Primary Owner
                    </h3>

                    @if($vehicle->customer)
                        <div class="space-y-6">
                            <div class="p-6 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-100 dark:border-indigo-800">
                                <h4 class="text-lg font-bold text-indigo-900 dark:text-indigo-100 leading-tight">
                                    {{ $vehicle->customer->title ?: '' }} {{ $vehicle->customer->name ?: '(No Name)' }}
                                </h4>
                                <p class="text-sm font-medium text-indigo-700/70 dark:text-indigo-300 mt-1 uppercase tracking-wider text-xs">{{ $vehicle->customer->company_name ?: '' }}</p>
                            </div>

                            <div class="space-y-4">
                                @if($vehicle->customer->full_address)
                                    <div class="flex gap-3">
                                        <svg class="w-5 h-5 text-gray-400 dark:text-slate-500 shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <p class="text-sm text-gray-600 dark:text-slate-300 leading-relaxed">{{ $vehicle->customer->full_address }}</p>
                                    </div>
                                @endif
 
                                <div class="space-y-2">
                                    @foreach(['telp_1', 'telp_2', 'telp_3', 'telp_4'] as $tel)
                                        @if($vehicle->customer->$tel)
                                            <a href="tel:{{ $vehicle->customer->$tel }}" class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors group">
                                                <svg class="w-4 h-4 text-gray-300 dark:text-slate-600 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1.01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 7.5V5z"></path>
                                                </svg>
                                                <span class="text-sm font-mono font-medium text-gray-700 dark:text-slate-200">{{ $vehicle->customer->$tel }}</span>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
 
                            <a href="{{ route('master-customers.show', $vehicle->customer->id) }}" class="block w-full py-4 rounded-2xl bg-gray-900 dark:bg-indigo-600 text-white text-center font-bold text-sm hover:bg-gray-800 dark:hover:bg-indigo-500 transition-all shadow-xl shadow-gray-200 dark:shadow-none">
                                Full Profile Details
                            </a>
                        </div>
                    </div>
                @else
                    <div class="text-center py-12 text-gray-400 italic">
                        No owner assigned.
                    </div>
                @endif
            </div>

            <!-- Quick Actions -->
            <div class="rounded-2xl bg-indigo-50 dark:bg-indigo-900/10 p-6 border border-indigo-100 dark:border-indigo-900/30">
                <h4 class="text-xs font-bold text-indigo-700 dark:text-indigo-400 uppercase tracking-widest mb-4">Quick Actions</h4>
                <div class="grid grid-cols-1 gap-2">
                    <button class="flex items-center gap-3 p-3 text-sm font-semibold text-gray-700 dark:text-slate-300 hover:text-indigo-700 dark:hover:text-indigo-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Information
                    </button>
                    <button class="flex items-center gap-3 p-3 text-sm font-semibold text-gray-700 dark:text-slate-300 hover:text-indigo-700 dark:hover:text-indigo-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                        </svg>
                        Share Vehicle Record
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Slide-Over Drawer for Labour Search -->
    <div x-show="showLabourDrawer" class="relative z-50" aria-labelledby="slide-over-title" role="dialog" aria-modal="true" x-cloak>
        <div x-show="showLabourDrawer" x-transition.opacity class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm transition-opacity" @click="showLabourDrawer = false"></div>
        <div class="fixed inset-0 overflow-hidden">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10 sm:pl-16">
                    <div x-show="showLabourDrawer" 
                         x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700" 
                         x-transition:enter-start="translate-x-full" 
                         x-transition:enter-end="translate-x-0" 
                         x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700" 
                         x-transition:leave-start="translate-x-0" 
                         x-transition:leave-end="translate-x-full" 
                         class="pointer-events-auto w-screen max-w-4xl">
                        <div class="flex h-full flex-col bg-white dark:bg-slate-900 shadow-2xl">
                            <div class="px-4 py-6 sm:px-6 bg-gray-50 dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 flex items-center justify-between">
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white" id="slide-over-title">RTS Labour Search</h2>
                                <button type="button" class="rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" @click="showLabourDrawer = false">
                                    <span class="sr-only">Close panel</span>
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="relative flex-1">
                                <iframe src="{{ route('labour-search') }}?chassis={{ urlencode($vehicle->chassis_no) }}&compact=1" class="w-full h-full border-0"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        let rv = [];
        try { rv = JSON.parse(localStorage.getItem('recently_viewed')) || []; } catch(e) {}
        rv = rv.filter(i => i.url !== window.location.pathname);
        rv.unshift({
            name: '{{ addslashes($vehicle->registration_no ?: "Vehicle " . $vehicle->id) }}',
            url: window.location.pathname,
            type: 'vehicle'
        });
        if(rv.length > 5) rv.pop();
        localStorage.setItem('recently_viewed', JSON.stringify(rv));
    });
</script>
@endpush
@endsection
