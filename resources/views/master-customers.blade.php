<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Master Customers - RTS Repository</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full">
<div class="min-h-full">

    <!-- Header -->
    <header class="bg-indigo-600 pb-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between border-b border-indigo-500 border-opacity-25">
                <div class="flex items-center gap-6">
                    <div class="flex items-center">
                        <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.492-3.053c.217-.266.35-.595.394-.945l.44-3.52 3.14-3.14a2.652 2.652 0 00-3.75-3.75l-3.14 3.14-3.52.44c-.35.044-.68.177-.945.394l-3.053 2.492.001.001M11.42 15.17L7.5 19.5 4.5 21 3 19.5l1.5-3 4.33-3.92M8.25 15.75l-1.5 1.5M10.5 13.5l-2.25 2.25"/>
                        </svg>
                        <span class="ml-3 text-xl font-bold text-white tracking-tight">RTS Labour Repository</span>
                    </div>
                    <nav class="flex space-x-4">
                        <a href="{{ route('home') }}" class="text-indigo-100 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Labour Search</a>
                        <a href="{{ route('master-vehicles.index') }}" class="text-indigo-100 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Master Vehicles</a>
                        <a href="{{ route('master-customers.index') }}" class="bg-indigo-700 text-white px-3 py-2 rounded-md text-sm font-medium">Master Customers</a>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="-mt-24 pb-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <!-- Search & Filter Section -->
            <div class="relative rounded-lg bg-white shadow-xl ring-1 ring-gray-900/5 sm:rounded-xl p-8 mb-6">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:tracking-tight">Master Customers</h2>
                    <p class="mt-2 text-sm text-gray-500">Search by name, customer ID, email, or location. Click a customer to see all their vehicles.</p>
                </div>
                <form method="GET" action="{{ route('master-customers.index') }}" class="flex flex-wrap gap-3 items-center">
                    <div class="relative flex-grow max-w-2xl">
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                            class="block w-full rounded-md border-0 py-2.5 px-4 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
                            placeholder="Search by name, ID, email, phone, address...">
                        @if(request('search'))
                        <a href="{{ route('master-customers.index') }}" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
                            </svg>
                        </a>
                        @endif
                    </div>
                    <!-- Source Filter -->
                    <select name="source" class="rounded-md border-0 py-2.5 px-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                        <option value="">All Sources</option>
                        <option value="customer_import" {{ request('source') === 'customer_import' ? 'selected' : '' }}>Real Customers</option>
                        <option value="vehicle_import" {{ request('source') === 'vehicle_import' ? 'selected' : '' }}>Legacy / Placeholder</option>
                    </select>
                    <button type="submit" class="inline-flex justify-center items-center rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        Search
                    </button>
                </form>
            </div>

            <!-- Stats bar -->
            <div class="flex items-center justify-between text-sm text-gray-500 px-1 mb-3">
                <span>{{ $customers->total() }} customers found</span>
                <span>Page {{ $customers->currentPage() }} of {{ $customers->lastPage() }}</span>
            </div>

            <!-- Customers Table + Detail Slide-out -->
            <div class="flex gap-6 items-start" x-data="{ selected: null }">

                <!-- Table -->
                <div class="flex-1 bg-white shadow ring-1 ring-gray-200 sm:rounded-lg overflow-hidden min-w-0">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">ID</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Name</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">City</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Phone</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Email</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Vehicles</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Source</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse($customers as $customer)
                                <tr class="hover:bg-indigo-50/40 cursor-pointer transition-colors"
                                    :class="selected && selected.id == {{ $customer->magic_cust }} ? 'bg-indigo-50 ring-2 ring-indigo-200 ring-inset' : ''"
                                    @click="selected = {{ json_encode([
                                        'id'           => $customer->magic_cust,
                                        'name'         => $customer->name ?? '(No Name)',
                                        'title'        => $customer->title,
                                        'company'      => $customer->company_name,
                                        'email'        => $customer->email,
                                        'address_1'    => $customer->address_1,
                                        'address_2'    => $customer->address_2,
                                        'address_3'    => $customer->address_3,
                                        'address_4'    => $customer->address_4,
                                        'address_5'    => $customer->address_5,
                                        'full_address' => $customer->full_address,
                                        'telp_1'       => $customer->telp_1,
                                        'telp_2'       => $customer->telp_2,
                                        'telp_3'       => $customer->telp_3,
                                        'telp_4'       => $customer->telp_4,
                                        'date_created' => $customer->date_created?->format('d M Y'),
                                        'source'       => $customer->source,
                                        'vehicles_count' => $customer->vehicles_count,
                                        'vehicles'     => $customer->vehicles->map(fn($v) => [
                                            'magic' => $v->magic,
                                            'registration_no' => $v->registration_no,
                                            'description'     => $v->description,
                                            'chassis_no'      => $v->chassis_no,
                                            'status'          => $v->status,
                                            'last_service_date' => $v->last_service_date?->format('d M Y'),
                                        ])->toArray(),
                                    ]) }}">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-500 font-mono sm:pl-6">{{ $customer->magic_cust }}</td>
                                    <td class="px-3 py-4 text-sm">
                                        <p class="font-medium text-gray-900">
                                            @if($customer->title) <span class="text-gray-400">{{ $customer->title }}</span> @endif
                                            {{ $customer->name ?? '(No Name)' }}
                                        </p>
                                        @if($customer->company_name)
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $customer->company_name }}</p>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $customer->address_5 ?: '-' }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 font-mono">
                                        {{ $customer->telp_1 ?: ($customer->telp_2 ?: '-') }}
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-500 max-w-[160px] truncate">
                                        {{ $customer->email ?: '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-center">
                                        @if($customer->vehicles_count > 0)
                                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-600/20">
                                            {{ $customer->vehicles_count }}
                                        </span>
                                        @else
                                        <span class="text-gray-300">0</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        @if($customer->source === 'customer_import')
                                            <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">real</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20">legacy</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-3 py-12 text-center text-sm text-gray-500">
                                        No customers found matching your search.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($customers->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
                        {{ $customers->links() }}
                    </div>
                    @endif
                </div>

                <!-- Detail Sidebar (shows when a customer is selected) -->
                <div class="w-80 flex-shrink-0 sticky top-6"
                     x-show="selected"
                     x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-x-4"
                     x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="bg-white shadow-xl ring-1 ring-gray-200 rounded-xl overflow-hidden">

                        <!-- Header -->
                        <div class="bg-indigo-600 px-5 py-4">
                            <div class="flex items-start justify-between">
                                <div class="min-w-0">
                                    <p class="text-indigo-200 text-xs font-medium uppercase tracking-wide" x-text="selected?.source === 'vehicle_import' ? 'Legacy Customer' : 'Customer Profile'"></p>
                                    <h3 class="mt-1 text-base font-bold text-white truncate" x-text="selected?.title ? selected.title + ' ' + selected.name : selected?.name"></h3>
                                    <p class="text-indigo-300 text-xs mt-0.5" x-text="'ID: ' + selected?.id"></p>
                                </div>
                                <button @click="selected = null" class="flex-shrink-0 rounded-md p-1 text-indigo-200 hover:text-white hover:bg-indigo-500 transition ml-2">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Contact Info -->
                        <div class="px-5 py-4 space-y-3 border-b border-gray-100">
                            <template x-if="selected?.company">
                                <div>
                                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Company</p>
                                    <p class="text-sm text-gray-900 mt-0.5" x-text="selected.company"></p>
                                </div>
                            </template>
                            <template x-if="selected?.full_address">
                                <div>
                                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Address</p>
                                    <p class="text-sm text-gray-700 mt-0.5 leading-relaxed" x-text="selected.full_address"></p>
                                </div>
                            </template>
                            <div>
                                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Phone</p>
                                <div class="mt-1 flex flex-col gap-1">
                                    <template x-for="ph in [selected?.telp_1, selected?.telp_2, selected?.telp_3, selected?.telp_4].filter(Boolean)" :key="ph">
                                        <a :href="'tel:' + ph" class="text-sm text-indigo-600 hover:text-indigo-800 font-mono" x-text="ph"></a>
                                    </template>
                                    <template x-if="!selected?.telp_1 && !selected?.telp_2 && !selected?.telp_3 && !selected?.telp_4">
                                        <span class="text-sm text-gray-400">No phone on record</span>
                                    </template>
                                </div>
                            </div>
                            <template x-if="selected?.email">
                                <div>
                                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Email</p>
                                    <a :href="'mailto:' + selected.email" class="text-sm text-indigo-600 hover:text-indigo-800 truncate block mt-0.5" x-text="selected.email"></a>
                                </div>
                            </template>
                        </div>

                        <!-- Vehicles -->
                        <div class="px-5 py-4">
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">
                                Vehicles (<span x-text="selected?.vehicles_count ?? 0"></span>)
                            </p>
                            <div class="space-y-2 max-h-64 overflow-y-auto">
                                <template x-for="v in (selected?.vehicles ?? [])" :key="v.magic">
                                    <a :href="'/master-vehicles/' + v.magic"
                                       class="block rounded-lg bg-gray-50 hover:bg-indigo-50 px-3 py-2 transition group">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-semibold text-gray-900 group-hover:text-indigo-700" x-text="v.registration_no || '(No Plate)'"></span>
                                            <span class="text-xs px-1.5 py-0.5 rounded"
                                                  :class="v.status === 'C' ? 'bg-gray-100 text-gray-500' : 'bg-green-100 text-green-700'"
                                                  x-text="v.status === 'C' ? 'Closed' : (v.status || '?')"></span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-0.5" x-text="v.description"></p>
                                        <p class="text-xs text-gray-400 font-mono mt-0.5" x-text="v.chassis_no"></p>
                                        <template x-if="v.last_service_date">
                                            <p class="text-xs text-gray-400 mt-0.5">Last service: <span x-text="v.last_service_date"></span></p>
                                        </template>
                                    </a>
                                </template>
                                <template x-if="!selected?.vehicles?.length">
                                    <p class="text-sm text-gray-400 text-center py-3">No vehicles on record</p>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>
</body>
</html>
