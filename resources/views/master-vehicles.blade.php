<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Master Vehicles - RTS Repository</title>
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
                        <a href="{{ route('master-vehicles.index') }}" class="bg-indigo-700 text-white px-3 py-2 rounded-md text-sm font-medium">Master Vehicles</a>
                        <a href="{{ route('master-customers.index') }}" class="text-indigo-100 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Master Customers</a>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="-mt-24 pb-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <!-- Search Section -->
            <div class="relative rounded-lg bg-white shadow-xl ring-1 ring-gray-900/5 sm:rounded-xl p-8 mb-6">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:tracking-tight">Master Vehicles</h2>
                    <p class="mt-2 text-sm text-gray-500">Search by registration number, chassis number, engine number, or description. Click a chassis number to view RTS labour codes.</p>
                </div>
                <form method="GET" action="{{ route('master-vehicles.index') }}" class="flex gap-4 items-center">
                    <div class="relative flex-grow max-w-2xl">
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                            class="block w-full rounded-md border-0 py-2.5 px-4 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
                            placeholder="Search by Registration No, Chassis No, Engine No, Description...">
                        @if(request('search'))
                        <a href="{{ route('master-vehicles.index') }}" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
                            </svg>
                        </a>
                        @endif
                    </div>
                    <button type="submit" class="inline-flex justify-center items-center rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        Search
                    </button>
                </form>
            </div>

            <!-- Stats bar -->
            <div class="flex items-center justify-between text-sm text-gray-500 px-1 mb-3">
                <span>{{ $vehicles->total() }} vehicles found</span>
                <span>Page {{ $vehicles->currentPage() }} of {{ $vehicles->lastPage() }}</span>
            </div>

            <!-- Data Table -->
            <div class="bg-white shadow ring-1 ring-gray-200 sm:rounded-lg overflow-hidden" x-data="{ customerModal: null }">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Registration No</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Franc</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Variant</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Description</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    Chassis No
                                    <span class="ml-1 text-xs font-normal text-indigo-500">(click for details)</span>
                                </th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">MHL Number</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Engine No</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Customer</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($vehicles as $vehicle)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-semibold text-gray-900 sm:pl-6">
                                    {{ $vehicle->registration_no ?: '-' }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    @if($vehicle->franc)
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">{{ $vehicle->franc }}</span>
                                    @else -
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $vehicle->variant ?: '-' }}</td>
                                <td class="px-3 py-4 text-sm text-gray-700 font-medium max-w-[180px]">{{ $vehicle->description ?: '-' }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-mono">
                                    @if($vehicle->chassis_no)
                                        <a href="{{ route('master-vehicles.show', $vehicle->magic) }}"
                                           class="text-indigo-600 hover:text-indigo-800 hover:underline font-medium"
                                           title="View Vehicle Details">
                                            {{ $vehicle->chassis_no }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 font-mono">{{ $vehicle->mhl_number ?: '-' }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 font-mono">{{ $vehicle->engine_no ?: '-' }}</td>
                                <td class="px-3 py-4 text-sm">
                                    @if($vehicle->customer)
                                        <button
                                            @click="customerModal = {{ json_encode([
                                                'id'           => $vehicle->customer->magic_cust,
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
                                            class="text-left group">
                                            <span class="font-medium text-gray-900 group-hover:text-indigo-600 transition-colors">
                                                {{ $vehicle->customer->name ?? '(No Name)' }}
                                            </span>
                                            @if($vehicle->customer->source === 'vehicle_import')
                                                <span class="ml-1 inline-flex items-center rounded-full bg-amber-50 px-1.5 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20">legacy</span>
                                            @endif
                                            @if($vehicle->customer->address_5)
                                            <p class="text-xs text-gray-400 mt-0.5">{{ $vehicle->customer->address_5 }}</p>
                                            @endif
                                        </button>
                                    @else
                                        <span class="text-gray-400 text-sm">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-3 py-12 text-center text-sm text-gray-500">
                                    <svg class="mx-auto h-8 w-8 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/>
                                    </svg>
                                    No vehicles found matching your search.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($vehicles->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $vehicles->links() }}
                </div>
                @endif

                <!-- Customer Detail Modal -->
                <div x-show="customerModal" x-cloak
                     class="fixed inset-0 z-50 flex items-center justify-center p-4"
                     @keydown.escape.window="customerModal = null">
                    <!-- Backdrop -->
                    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" @click="customerModal = null"></div>

                    <!-- Panel -->
                    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg ring-1 ring-gray-900/5 overflow-hidden"
                         x-show="customerModal"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95">

                        <!-- Header -->
                        <div class="bg-indigo-600 px-6 py-5">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <svg class="h-5 w-5 text-indigo-200" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                                        </svg>
                                        <p class="text-indigo-200 text-xs font-medium uppercase tracking-wide" x-text="customerModal?.source === 'vehicle_import' ? 'Legacy Customer' : 'Customer'"></p>
                                    </div>
                                    <h3 class="mt-1 text-xl font-bold text-white" x-text="customerModal?.title ? customerModal.title + ' ' + customerModal.name : customerModal?.name"></h3>
                                    <p class="text-indigo-200 text-sm mt-0.5" x-text="'ID: ' + customerModal?.id"></p>
                                </div>
                                <button @click="customerModal = null" class="rounded-md p-1 text-indigo-200 hover:text-white hover:bg-indigo-500 transition">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="px-6 py-5 space-y-4">

                            <!-- Company -->
                            <template x-if="customerModal?.company">
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 flex-shrink-0 h-5 w-5 text-gray-400">
                                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Company</p>
                                        <p class="text-sm text-gray-900 font-medium" x-text="customerModal.company"></p>
                                    </div>
                                </div>
                            </template>

                            <!-- Address -->
                            <template x-if="customerModal?.address">
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 flex-shrink-0 h-5 w-5 text-gray-400">
                                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Address</p>
                                        <p class="text-sm text-gray-900" x-text="customerModal.address"></p>
                                    </div>
                                </div>
                            </template>

                            <!-- Phone numbers -->
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 flex-shrink-0 h-5 w-5 text-gray-400">
                                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Phone Numbers</p>
                                    <div class="mt-1 flex flex-wrap gap-2">
                                        <template x-if="customerModal?.telp_1">
                                            <a :href="'tel:' + customerModal.telp_1" class="text-sm text-indigo-600 hover:text-indigo-800 font-mono" x-text="customerModal.telp_1"></a>
                                        </template>
                                        <template x-if="customerModal?.telp_2">
                                            <a :href="'tel:' + customerModal.telp_2" class="text-sm text-indigo-600 hover:text-indigo-800 font-mono" x-text="customerModal.telp_2"></a>
                                        </template>
                                        <template x-if="customerModal?.telp_3">
                                            <a :href="'tel:' + customerModal.telp_3" class="text-sm text-indigo-600 hover:text-indigo-800 font-mono" x-text="customerModal.telp_3"></a>
                                        </template>
                                        <template x-if="customerModal?.telp_4">
                                            <a :href="'tel:' + customerModal.telp_4" class="text-sm text-indigo-600 hover:text-indigo-800 font-mono" x-text="customerModal.telp_4"></a>
                                        </template>
                                        <template x-if="!customerModal?.telp_1 && !customerModal?.telp_2 && !customerModal?.telp_3 && !customerModal?.telp_4">
                                            <span class="text-sm text-gray-400">No phone on record</span>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Email -->
                            <template x-if="customerModal?.email">
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 flex-shrink-0 h-5 w-5 text-gray-400">
                                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Email</p>
                                        <a :href="'mailto:' + customerModal.email" class="text-sm text-indigo-600 hover:text-indigo-800" x-text="customerModal.email"></a>
                                    </div>
                                </div>
                            </template>

                        </div>

                        <!-- Footer -->
                        <div class="bg-gray-50 px-6 py-4 flex justify-between items-center border-t border-gray-100">
                            <a :href="'/master-customers?id=' + customerModal?.id"
                               class="text-sm text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                                </svg>
                                View Full Customer Profile
                            </a>
                            <button @click="customerModal = null" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>
</body>
</html>
