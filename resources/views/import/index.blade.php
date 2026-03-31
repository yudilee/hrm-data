@extends('layouts.app')

@section('title', 'Import Data - RTS Master')

@section('breadcrumb')
    <li class="inline-flex items-center">
        <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
        </svg>
        <span class="text-sm font-medium text-gray-500">Import</span>
    </li>
@endsection

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Data Import Center</h1>
    <p class="mt-2 text-lg text-gray-600 dark:text-slate-400">Upload your Excel files to update Master Customers and Vehicles data.</p>
</div>

@if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-400 text-green-700 rounded-lg shadow-sm flex items-center">
        <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 00 1.414 0l4-4z" clip-rule="evenodd"></path>
        </svg>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-400 text-red-700 rounded-lg shadow-sm flex items-center">
        <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
        </svg>
        {{ session('error') }}
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    <!-- Customer Import Card -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-gray-100 dark:border-slate-700 overflow-hidden transform transition duration-500 hover:scale-[1.02]">
        <div class="p-8">
            <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center mb-6">
                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Master Customers</h2>
            <p class="text-gray-500 dark:text-slate-400 mb-6">Update customer records using your standard XLSX/XLS template.</p>
            
            <form action="{{ route('import.customers') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="flex items-center justify-center w-full mb-4">
                    <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 dark:border-slate-600 rounded-xl cursor-pointer bg-gray-50 dark:bg-slate-900 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 hover:border-indigo-300 dark:hover:border-indigo-500 transition-all">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <svg class="w-8 h-8 mb-3 text-gray-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <p class="mb-2 text-sm text-gray-500 dark:text-slate-400 font-medium">Click to upload or drag and drop</p>
                            <p class="text-xs text-gray-400 dark:text-slate-600">XLS, XLSX, or CSV only</p>
                        </div>
                        <input type="file" name="file" class="hidden" required onchange="this.form.submit()">
                    </label>
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white font-semibold py-3 rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all">
                    Start Customer Import
                </button>
            </form>
        </div>
        <div class="bg-gray-50 px-8 py-4 border-t border-gray-100 italic text-xs text-gray-400">
            Expected headers: Magic cust, Nama Customer, ADDRESS 1, etc.
        </div>
    </div>

    <!-- Vehicle Import Card -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-gray-100 dark:border-slate-700 overflow-hidden transform transition duration-500 hover:scale-[1.02]">
        <div class="p-8">
            <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center mb-6">
                <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Master Vehicles</h2>
            <p class="text-gray-500 dark:text-slate-400 mb-6">Update vehicle records and link them to existing customers.</p>
            
            <form action="{{ route('import.vehicles') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="flex items-center justify-center w-full mb-4">
                    <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer bg-gray-50 hover:bg-emerald-50 hover:border-emerald-300 transition-all">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <svg class="w-8 h-8 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <p class="mb-2 text-sm text-gray-500 font-medium">Click to upload or drag and drop</p>
                            <p class="text-xs text-gray-400">XLS, XLSX, or CSV only</p>
                        </div>
                        <input type="file" name="file" class="hidden" required onchange="this.form.submit()">
                    </label>
                </div>
                <button type="submit" class="w-full bg-emerald-600 text-white font-semibold py-3 rounded-xl hover:bg-emerald-700 shadow-lg shadow-emerald-200 transition-all">
                    Start Vehicle Import
                </button>
            </form>
        </div>
            <div class="bg-gray-50 px-8 py-4 border-t border-gray-100 italic text-xs text-gray-400">
                Expected headers: Magic, Registration No, Franc, Model, etc.
            </div>
        </div>

        <!-- Service History Sync Card -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-gray-100 dark:border-slate-700 overflow-hidden transform transition duration-500 hover:scale-[1.02] md:col-span-2">
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="px-3 py-1 bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300 text-xs font-bold rounded-full uppercase">Server-Side Sync</span>
                </div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Service History (FoxPro)</h2>
                <p class="text-gray-500 dark:text-slate-400 mb-6 font-medium">Synchronize legacy repair data directly from the server's FoxPro DBF storage. This process refreshes over 1 million records.</p>
                
                <form action="{{ route('import.history') }}" method="POST" id="syncForm">
                    @csrf
                    <div class="bg-gray-50 dark:bg-slate-900/50 rounded-xl p-4 border border-gray-200 dark:border-slate-700 mb-6">
                        <div class="flex items-center text-sm text-gray-600 dark:text-slate-400">
                            <svg class="w-5 h-5 mr-2 text-indigo-500 dark:text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                            </svg>
                            Source: <code class="ml-1 bg-white dark:bg-slate-900 px-2 py-0.5 rounded border border-gray-200 dark:border-slate-700 font-mono">/vehicle history/*.DBF</code>
                        </div>
                    </div>

                    <div x-data="{ syncing: false }">
                        <button type="submit" 
                                @click="syncing = true; $refs.submitBtn.innerHTML = 'Synchronizing... Please Wait';"
                                x-ref="submitBtn"
                                :disabled="syncing"
                                class="w-full bg-amber-600 text-white font-bold py-4 rounded-xl hover:bg-amber-700 shadow-lg shadow-amber-200 transition-all flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-5 h-5 mr-3" :class="syncing ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Sync History Now
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<div class="mt-12 bg-amber-50 dark:bg-amber-900/20 rounded-2xl p-8 border border-amber-100 dark:border-amber-900/30 shadow-sm">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="h-6 w-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div class="ml-3">
            <h3 class="text-sm font-bold text-amber-800 dark:text-amber-300 uppercase tracking-wider">Import Guidelines</h3>
            <div class="mt-2 text-sm text-amber-700 dark:text-amber-400 space-y-2">
                <ul class="list-disc pl-5">
                    <li>The system will automatically <strong>Merge</strong> data based on the unique "Magic" IDs.</li>
                    <li>Files should contain a header row as first row.</li>
                    <li>If a vehicle references a Customer Magic that doesn't exist, a <strong>placeholder customer</strong> will be created automatically.</li>
                    <li>Dates should be in DD/MM/YYYY format or Excel date format.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
