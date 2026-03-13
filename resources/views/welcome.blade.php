<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Keyloop DMS Labour Codes</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>
    <!-- Alpine Core -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full">
    <div class="min-h-full" x-data="labourSearch()">
        
        <!-- Header -->
        <header class="bg-indigo-600 pb-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between border-b border-indigo-500 border-opacity-25 shadow-sm">
                    <div class="flex items-center gap-6">
                        <div class="flex items-center">
                            <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.492-3.053c.217-.266.35-.595.394-.945l.44-3.52 3.14-3.14a2.652 2.652 0 00-3.75-3.75l-3.14 3.14-3.52.44c-.35.044-.68.177-.945.394l-3.053 2.492.001.001M11.42 15.17L7.5 19.5 4.5 21 3 19.5l1.5-3 4.33-3.92M8.25 15.75l-1.5 1.5M10.5 13.5l-2.25 2.25"></path>
                            </svg>
                            <span class="ml-3 text-xl font-bold text-white tracking-tight">RTS Labour Repository</span>
                        </div>
                        <nav class="flex space-x-4">
                            <a href="{{ route('home') }}" class="bg-indigo-700 text-white px-3 py-2 rounded-md text-sm font-medium">Labour Search</a>
                            <a href="{{ route('master-vehicles.index') }}" class="text-indigo-100 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Master Vehicles</a>
                        </nav>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="-mt-24 pb-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="relative rounded-lg bg-white shadow-xl ring-1 ring-gray-900/5 sm:rounded-xl p-8 mb-8 overflow-hidden">
                    <div class="pointer-events-none absolute inset-0 rounded-xl ring-1 ring-inset ring-gray-900/5"></div>
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">Search Vehicle Data</h2>
                        <p class="mt-2 text-sm text-gray-500">Enter a full chassis number (VIN). The system will extract the first 6 digits to identify the vehicle model and load the exact RTS labour codes and associated service times.</p>
                    </div>

                    <form @submit.prevent="performSearch" class="flex flex-col sm:flex-row gap-4 relative z-10">
                        <div class="flex-grow">
                            <label for="chassis" class="sr-only">Chassis Number</label>
                            <input 
                                x-model="chassisInput" 
                                type="text" 
                                id="chassis" 
                                class="block w-full rounded-md border-0 py-3 px-4 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-md sm:leading-6 uppercase" 
                                placeholder="eg. W1N243..." 
                                required 
                                autofocus>
                        </div>
                        <button 
                            type="submit" 
                            class="inline-flex justify-center items-center rounded-md bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 whitespace-nowrap disabled:opacity-75 disabled:cursor-not-allowed transition"
                            :disabled="isLoading">
                            <svg x-show="isLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="isLoading ? 'Searching...' : 'Lookup Labour Codes'"></span>
                        </button>
                    </form>
                    
                    <div x-show="error" x-transition x-cloak class="mt-4 rounded-md bg-red-50 p-4 relative z-10">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800" x-text="error"></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Results Section -->
                <div x-show="hasSearched && !isLoading && !error" x-transition.opacity.duration.500ms x-cloak class="mt-8 relative z-0">
                    
                    <div class="sm:flex sm:items-center mb-6">
                        <div class="sm:flex-auto">
                            <h2 class="text-base font-semibold leading-6 text-gray-900">
                                Results for Model: <span class="text-indigo-600 font-bold ml-1 text-lg" x-text="results.model_prefix"></span>
                            </h2>
                            <p class="mt-2 text-sm text-gray-700">Displaying <span class="font-bold" x-text="results.total_results"></span> total labour operations found across the entire catalogue.</p>
                        </div>
                        <div class="mt-4 md:mt-0 flex gap-4 md:ml-4">
                            <!-- Quick Filter -->
                            <div class="relative rounded-md shadow-sm xl:w-80">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <input x-model="searchFilter" type="text" class="block w-full rounded-md border-0 py-2 pl-10 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="Filter by keyword...">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-start">
                        <!-- Left col: Group List -->
                        <div class="md:col-span-1 bg-white shadow-sm ring-1 ring-gray-200 rounded-lg overflow-hidden flex flex-col max-h-[800px]">
                            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 sticky top-0 font-semibold text-gray-900 text-sm">
                                Labour Groups
                            </div>
                            <div class="overflow-y-auto flex-1 p-2 space-y-1">
                                <template x-for="groupName in Object.keys(groupedData).sort((a,b) => { let an = parseInt(a); let bn = parseInt(b); if(!isNaN(an) && !isNaN(bn)) return an - bn; return String(a).localeCompare(String(b)); })" :key="groupName">
                                    <button 
                                        type="button"
                                        @click="activeGroup = groupName"
                                        :class="activeGroup === groupName ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-600/20' : 'text-gray-700 hover:text-indigo-700 hover:bg-gray-50'"
                                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md w-full justify-between transition-colors">
                                        <span class="truncate font-semibold" x-text="groupName"></span>
                                        <span 
                                            :class="activeGroup === groupName ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600'"
                                            class="ml-3 flex-none py-0.5 px-2.5 text-xs rounded-full font-medium" 
                                            x-text="groupedData[groupName].length">
                                        </span>
                                    </button>
                                </template>
                                <div x-show="Object.keys(groupedData).length === 0" class="p-4 text-center text-sm text-gray-500">
                                    No groups found.
                                </div>
                            </div>
                        </div>

                        <!-- Right col: Detail Table -->
                        <div class="md:col-span-3 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-lg overflow-hidden flex flex-col max-h-[800px]">
                            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 sticky top-0 flex justify-between items-center sm:px-6 z-20">
                                <h3 class="text-sm font-semibold text-gray-900">
                                    Operations in: <span class="text-indigo-600 font-bold ml-1" x-text="activeGroup || 'None Selected'"></span>
                                </h3>
                                <span class="text-xs text-gray-500 font-medium" x-show="activeGroup" x-text="(groupedData[activeGroup] ? groupedData[activeGroup].length : 0) + ' items'"></span>
                            </div>
                            <div class="overflow-x-auto overflow-y-auto flex-1 relative">
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead class="bg-gray-50 sticky top-0 z-10 outline outline-1 outline-gray-200">
                                        <tr>
                                            <th scope="col" class="px-3 py-3.5 pl-4 sm:pl-6 text-left text-sm font-semibold text-gray-900 whitespace-nowrap min-w-[80px]">Labour Key</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Description</th>
                                            <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900 whitespace-nowrap pr-4">Est. Time</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        <template x-if="activeGroup && groupedData[activeGroup]">
                                            <template x-for="item in groupedData[activeGroup]" :key="item.id">
                                                <tr class="hover:bg-gray-50 transition">
                                                    <td class="whitespace-nowrap px-3 py-4 pl-4 sm:pl-6 text-sm text-gray-500 font-mono" x-text="item.labour_key || '-'"></td>
                                                    <td class="px-3 py-4 text-sm text-gray-500 w-full" x-text="item.description || '-'"></td>
                                                    <td class="whitespace-nowrap px-3 py-4 pr-4 sm:pr-6 text-right text-sm font-semibold text-gray-900">
                                                        <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20" x-text="Number(item.time_hours).toFixed(2)"></span>
                                                    </td>
                                                </tr>
                                            </template>
                                        </template>
                                        <tr x-show="!activeGroup || !groupedData[activeGroup]">
                                            <td colspan="3" class="py-8 text-center text-sm text-gray-500">
                                                Select a labour group on the left to view operations.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('labourSearch', () => ({
                chassisInput: new URLSearchParams(window.location.search).get('chassis') || '',
                isLoading: false,
                hasSearched: false,
                error: null,
                searchFilter: '',
                activeGroup: null,
                results: {
                    model_prefix: '',
                    total_results: 0,
                    data: []
                },
                
                init() {
                    if (this.chassisInput) {
                        this.performSearch();
                    }
                },
                
                get filteredData() {
                    let term = this.searchFilter.toLowerCase();
                    if(!term) return this.results.data;
                    return this.results.data.filter(item => {
                        return (item.description && item.description.toLowerCase().includes(term)) ||
                               (item.group_name && item.group_name.toLowerCase().includes(term)) ||
                               (item.labour_key && item.labour_key.toLowerCase().includes(term));
                    });
                },

                get groupedData() {
                    let grouped = {};
                    this.filteredData.forEach(item => {
                        let name = item.group_name || 'Uncategorized';
                        if (!grouped[name]) {
                            grouped[name] = [];
                        }
                        grouped[name].push(item);
                    });
                    return grouped;
                },

                async performSearch() {
                    if(this.chassisInput.length < 6) {
                        this.error = "Please enter a valid chassis number (at least 6 characters)";
                        return;
                    }

                    this.isLoading = true;
                    this.error = null;
                    
                    try {
                        let response = await fetch(`/api/labour-codes?chassis_number=${encodeURIComponent(this.chassisInput)}`);
                        let data = await response.json();
                        
                        if(!response.ok) {
                            throw new Error(data.error || "Failed to fetch data.");
                        }

                        this.results = data;
                        this.hasSearched = true;
                        
                        let sortedGroups = Object.keys(this.groupedData).sort((a,b) => { let an = parseInt(a); let bn = parseInt(b); if(!isNaN(an) && !isNaN(bn)) return an - bn; return String(a).localeCompare(String(b)); });
                        if(sortedGroups.length > 0) {
                            // If an activeGroup was already selected and it's still in the list, keep it. 
                            // Otherwise, auto-select the first group
                            if (!this.activeGroup || !sortedGroups.includes(this.activeGroup)) {
                                this.activeGroup = sortedGroups[0];
                            }
                        } else {
                            this.activeGroup = null;
                        }
                        
                        // Check if we didn't find any results for a valid prefix
                        if(this.results.total_results === 0) {
                            this.error = `No labour codes found for prefix ${this.results.model_prefix}. Are you sure it's valid?`;
                        }
                    } catch (e) {
                        this.error = e.message;
                    } finally {
                        this.isLoading = false;
                    }
                }
            }))
        })
    </script>
</body>
</html>
