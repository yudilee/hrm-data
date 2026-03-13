<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vehicle Details - RTS Repository</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
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
                        <a href="{{ route('master-customers.index') }}" class="text-indigo-100 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Master Customers</a>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="-mt-24 pb-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            
            <!-- Breadcrumbs / Back -->
            <div class="mb-4">
                <a href="{{ route('master-vehicles.index') }}" class="inline-flex items-center text-sm font-medium text-white hover:text-indigo-100 transition">
                    <svg class="mr-1 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                    </svg>
                    Back to Vehicles
                </a>
            </div>

            <!-- Vehicle Detail Card -->
            <div class="bg-white shadow sm:rounded-lg overflow-hidden ring-1 ring-gray-900/5">
                
                <!-- Hero Header -->
                <div class="bg-indigo-50 border-b border-indigo-100 px-6 py-8 sm:px-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ $vehicle->registration_no ?: '(No Plate)' }}</h1>
                            <span class="inline-flex items-center rounded-md bg-indigo-100 px-2.5 py-1 text-xs font-semibold text-indigo-800 border border-indigo-200">
                                {{ $vehicle->franc ?: 'UNK' }}
                            </span>
                        </div>
                        <p class="mt-2 text-lg text-indigo-900 font-medium">{{ $vehicle->description ?: 'No Description Available' }}</p>
                        <p class="mt-1 text-sm text-indigo-700">Variant: {{ $vehicle->variant ?: 'N/A' }}</p>
                    </div>
                    
                    @if($vehicle->chassis_no)
                    <div class="flex-shrink-0">
                        <a href="{{ route('home') }}?chassis={{ $vehicle->chassis_no }}" 
                           class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline transition">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Lookup RTS Labour Codes
                        </a>
                    </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-200">
                    
                    <!-- Left Col: Technical Specs -->
                    <div class="px-6 py-6 sm:px-8">
                        <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.492-3.053c.217-.266.35-.595.394-.945l.44-3.52 3.14-3.14a2.652 2.652 0 00-3.75-3.75l-3.14 3.14-3.52.44c-.35.044-.68.177-.945.394l-3.053 2.492.001.001M11.42 15.17L7.5 19.5 4.5 21 3 19.5l1.5-3 4.33-3.92M8.25 15.75l-1.5 1.5M10.5 13.5l-2.25 2.25" />
                            </svg>
                            Technical Information
                        </h2>
                        
                        <dl class="space-y-4">
                            <div class="grid grid-cols-3 gap-4">
                                <dt class="text-sm font-medium text-gray-500">Chassis No</dt>
                                <dd class="text-sm font-mono text-gray-900 col-span-2">{{ $vehicle->chassis_no ?: '-' }}</dd>
                            </div>
                            <div class="grid grid-cols-3 gap-4">
                                <dt class="text-sm font-medium text-gray-500">Engine No</dt>
                                <dd class="text-sm font-mono text-gray-900 col-span-2">{{ $vehicle->engine_no ?: '-' }}</dd>
                            </div>
                            <div class="grid grid-cols-3 gap-4">
                                <dt class="text-sm font-medium text-gray-500">MHL Number</dt>
                                <dd class="text-sm font-mono text-gray-900 col-span-2">{{ $vehicle->mhl_number ?: '-' }}</dd>
                            </div>
                            <hr class="border-gray-100">
                            <div class="grid grid-cols-3 gap-4">
                                <dt class="text-sm font-medium text-gray-500">Reg. Date</dt>
                                <dd class="text-sm text-gray-900 col-span-2">{{ $vehicle->reg_date ? $vehicle->reg_date->format('d M Y') : '-' }}</dd>
                            </div>
                            <div class="grid grid-cols-3 gap-4">
                                <dt class="text-sm font-medium text-gray-500">Last Service</dt>
                                <dd class="text-sm text-gray-900 col-span-2">{{ $vehicle->last_service_date ? $vehicle->last_service_date->format('d M Y') : '-' }}</dd>
                            </div>
                            <div class="grid grid-cols-3 gap-4">
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="text-sm text-gray-900 col-span-2">
                                    @if($vehicle->status === 'C') <span class="text-gray-500 bg-gray-100 px-2 py-0.5 rounded text-xs font-semibold">Closed</span>
                                    @else <span class="text-green-700 bg-green-100 px-2 py-0.5 rounded text-xs font-semibold">{{ $vehicle->status ?: 'Active' }}</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Right Col: Owner Info -->
                    <div class="bg-gray-50/50 px-6 py-6 sm:px-8">
                        <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-4 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                                Registered Owner
                            </div>
                            @if($vehicle->customer && $vehicle->customer->source === 'vehicle_import')
                                <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20">Legacy Record</span>
                            @endif
                        </h2>

                        @if($vehicle->customer)
                            <div class="space-y-5">
                                <div>
                                    <p class="text-lg font-bold text-gray-900">
                                        {{ $vehicle->customer->title ?: '' }} {{ $vehicle->customer->name ?: '(No Name)' }}
                                    </p>
                                    @if($vehicle->customer->company_name)
                                        <p class="text-sm text-gray-500 flex items-center gap-1 mt-0.5">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                            </svg>
                                            {{ $vehicle->customer->company_name }}
                                        </p>
                                    @endif
                                </div>

                                @if($vehicle->customer->full_address)
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Address</p>
                                        <p class="mt-1 text-sm text-gray-800 leading-relaxed">{{ $vehicle->customer->full_address }}</p>
                                    </div>
                                @endif

                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Contact Details</p>
                                    <ul class="mt-2 space-y-2">
                                        @foreach(['telp_1', 'telp_2', 'telp_3', 'telp_4'] as $tel)
                                            @if($vehicle->customer->$tel)
                                                <li class="flex items-center gap-2 text-sm text-indigo-600 font-mono">
                                                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                                    </svg>
                                                    <a href="tel:{{ $vehicle->customer->$tel }}" class="hover:underline">{{ $vehicle->customer->$tel }}</a>
                                                </li>
                                            @endif
                                        @endforeach
                                        @if($vehicle->customer->email)
                                            <li class="flex items-center gap-2 text-sm text-indigo-600 pt-1">
                                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                                </svg>
                                                <a href="mailto:{{ $vehicle->customer->email }}" class="hover:underline">{{ $vehicle->customer->email }}</a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>

                                <div class="pt-4 mt-4 border-t border-gray-200">
                                    <a href="{{ route('master-customers.index') }}?id={{ $vehicle->customer->magic_cust }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500 flex items-center gap-1">
                                        View Full Customer Profile
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-12 text-gray-500 text-sm">
                                <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                No related customer data found.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>
</body>
</html>
