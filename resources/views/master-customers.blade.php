@extends('layouts.app')

@section('title', 'Master Customers - RTS Master Data System')

@section('breadcrumb')
<li class="inline-flex items-center">
    <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
    </svg>
    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Master Customers</span>
</li>
@endsection

@section('content')
<div class="space-y-6" x-data="{ selected: null }">
    <!-- Header/Search Section -->
    <div class="rounded-2xl bg-white dark:bg-slate-800 p-8 shadow-sm ring-1 ring-gray-200 dark:ring-slate-700">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Master Customers</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">Search and manage customer profiles and their associated vehicles.</p>
            </div>
            <div class="flex items-center gap-3">
                <button class="inline-flex items-center rounded-xl bg-white dark:bg-slate-800 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-slate-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700 transition-all">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a1 1 0 001 1h10a1 1 0 001-1v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Export
                </button>
                <button class="inline-flex items-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xl shadow-emerald-200 hover:bg-emerald-500 transition-all active:scale-95">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                    Add Customer
                </button>
            </div>
        </div>

        <form method="GET" action="{{ route('master-customers.index') }}" class="flex flex-col sm:flex-row gap-4">
            <div class="relative flex-grow">
                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-400 dark:text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                    class="block w-full pl-11 rounded-xl border-0 py-3.5 px-4 text-gray-900 dark:text-white bg-white dark:bg-slate-900 ring-1 ring-inset ring-gray-300 dark:ring-slate-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm transition-all"
                    placeholder="Search by name, ID, email, phone, or address...">
                @if(request('search'))
                <a href="{{ route('master-customers.index') }}" class="absolute right-4 top-3.5 text-gray-400 dark:text-slate-500 hover:text-gray-600 dark:hover:text-slate-300">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
                    </svg>
                </a>
                @endif
            </div>
            <select name="source" class="rounded-xl border-0 py-3.5 px-4 text-gray-900 dark:text-white bg-white dark:bg-slate-900 ring-1 ring-inset ring-gray-300 dark:ring-slate-700 focus:ring-2 focus:ring-indigo-600 sm:text-sm transition-all">
                <option value="">All Sources</option>
                <option value="customer_import" {{ request('source') === 'customer_import' ? 'selected' : '' }}>Real Customers</option>
                <option value="vehicle_import" {{ request('source') === 'vehicle_import' ? 'selected' : '' }}>Legacy / Placeholder</option>
            </select>
            <button type="submit" class="inline-flex justify-center items-center rounded-xl bg-gray-900 dark:bg-indigo-600 px-8 py-3.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 dark:hover:bg-indigo-500 transition-all">
                Search
            </button>
        </form>
    </div>

    <!-- Main Content Grid -->
    <div class="flex flex-col lg:flex-row gap-8 items-start">
        
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
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead>
                        <tr class="text-gray-400 dark:text-slate-500 font-medium uppercase tracking-wider text-xs">
                            <th class="px-8 py-5">Name / Company</th>
                            <th class="px-4 py-5">Location</th>
                            <th class="px-4 py-5 text-center">Vehicles</th>
                            <th class="px-4 py-5 text-right">Source</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                        @forelse($customers as $customer)
                        <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 cursor-pointer transition-colors group"
                            :class="selected && selected.id == {{ $customer->magic_cust }} ? 'bg-indigo-50/50 dark:bg-indigo-900/20' : ''"
                            data-customer="{{ json_encode([
                                'id'           => $customer->magic_cust,
                                'name'         => $customer->name ?? '(No Name)',
                                'title'        => $customer->title,
                                'company'      => $customer->company_name,
                                'email'        => $customer->email,
                                'full_address' => $customer->full_address,
                                'telp_1'       => $customer->telp_1,
                                'telp_2'       => $customer->telp_2,
                                'telp_3'       => $customer->telp_3,
                                'telp_4'       => $customer->telp_4,
                                'source'       => $customer->source,
                                'vehicles_count' => $customer->vehicles_count,
                                'vehicles'     => $customer->vehicles->map(fn($v) => [
                                    'magic' => $v->magic,
                                    'registration_no' => $v->registration_no,
                                    'description'     => $v->description,
                                    'chassis_no'      => $v->chassis_no,
                                ])->toArray(),
                            ]) }}"
                            @click="selected = JSON.parse($el.dataset.customer)"
                        >
                            <td class="px-8 py-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $customer->name ?: '(No Name)' }}</span>
                                    <span class="text-xs text-gray-400 dark:text-slate-500 mt-0.5 truncate max-w-[200px]">{{ $customer->company_name ?: '' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-gray-600 dark:text-slate-300">{{ $customer->address_5 ?: '-' }}</td>
                            <td class="px-4 py-4 text-center">
                                @if($customer->vehicles_count > 0)
                                    <span class="inline-flex items-center rounded-full bg-indigo-50 dark:bg-indigo-900/30 px-2.5 py-0.5 text-xs font-bold text-indigo-700 dark:text-indigo-400 ring-1 ring-inset ring-indigo-600/20 dark:ring-indigo-500/30">
                                        {{ $customer->vehicles_count }}
                                    </span>
                                @else
                                    <span class="text-gray-300 dark:text-slate-700">0</span>
                                @endif
                            </td>
                            <td class="px-8 py-4 text-right">
                                @if($customer->source === 'customer_import')
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 text-xs font-bold text-emerald-700 dark:text-emerald-400 ring-1 ring-inset ring-emerald-600/20 dark:ring-emerald-500/30 tracking-wider uppercase">Master</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-amber-50 dark:bg-amber-900/30 px-2 py-0.5 text-xs font-bold text-amber-700 dark:text-amber-400 ring-1 ring-inset ring-amber-600/20 dark:ring-amber-500/30 tracking-wider uppercase">Legacy</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-8 py-20 text-center">No customers found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($customers->hasPages())
            <div class="px-8 py-6 border-t border-gray-100 dark:border-slate-700 bg-gray-50/30 dark:bg-slate-900/30">
                {{ $customers->links() }}
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
            
            <div class="bg-indigo-900 rounded-2xl shadow-2xl overflow-hidden text-white">
                <!-- Profile Header -->
                <div class="p-8 pb-12 relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-500/30 flex items-center justify-center border border-indigo-400/30">
                                <svg class="w-6 h-6 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <button @click="selected = null" class="p-2 rounded-xl hover:bg-white/10 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <h3 class="text-xl font-bold truncate" x-text="selected?.name"></h3>
                        <p class="text-indigo-300 text-sm font-mono mt-1" x-text="'ID: ' + selected?.id"></p>
                    </div>
                    <!-- Decorative Circle -->
                    <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-indigo-500/20 rounded-full blur-3xl"></div>
                </div>

                <!-- Info Sections -->
                <div class="bg-white dark:bg-slate-800 rounded-t-3xl -mt-6 p-8 space-y-8 text-gray-900 dark:text-white min-h-[400px]">
                    
                    <div class="grid grid-cols-1 gap-6">
                        <div x-show="selected?.company">
                            <label class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Company</label>
                            <p class="mt-1 font-semibold text-gray-900 dark:text-white" x-text="selected?.company"></p>
                        </div>
                        <div x-show="selected?.email">
                            <label class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Email Address</label>
                            <a :href="'mailto:' + selected?.email" class="block mt-1 font-medium text-indigo-600 dark:text-indigo-400 hover:underline" x-text="selected?.email"></a>
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
                                       <svg class="w-4 h-4 text-gray-400 dark:text-slate-500 group-hover:text-indigo-500 dark:group-hover:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 7.5V5z"></path>
                                       </svg>
                                       <a :href="'tel:' + selected['telp_'+i]" class="text-sm font-mono font-medium text-gray-700 dark:text-slate-200" x-text="selected['telp_'+i]"></a>
                                   </div>
                               </template>
                           </template>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <label class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Registered Vehicles</label>
                            <span class="bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-[10px] font-bold px-2 py-0.5 rounded-full" x-text="selected?.vehicles_count"></span>
                        </div>
                        <div class="space-y-3">
                            <template x-for="v in selected?.vehicles" :key="v.magic">
                                <a :href="'/master-vehicles/' + v.magic" class="flex items-center justify-between p-4 rounded-2xl bg-gray-50/50 dark:bg-slate-900/50 border border-gray-100 dark:border-slate-700 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:border-indigo-200 dark:hover:border-indigo-800 transition-all group">
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-indigo-700 dark:group-hover:text-indigo-400" x-text="v.registration_no"></p>
                                        <p class="text-[10px] font-mono text-gray-400 dark:text-slate-500 mt-0.5" x-text="v.chassis_no"></p>
                                    </div>
                                    <svg class="w-4 h-4 text-gray-300 dark:text-slate-700 group-hover:text-indigo-500 dark:group-hover:text-indigo-400 transition-all transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </template>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-gray-100 dark:border-slate-700">
                        <a :href="'/master-customers/' + selected?.id" class="block w-full py-4 rounded-2xl bg-gray-900 dark:bg-indigo-600 text-white text-center font-bold text-sm hover:bg-gray-800 dark:hover:bg-indigo-500 transition-all shadow-xl shadow-gray-200 dark:shadow-none">
                            Full Profile Details &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
