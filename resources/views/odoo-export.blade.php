@extends('layouts.app')

@section('title', 'Odoo Export Center - Dealership MasterData Hub')

@section('breadcrumb')
<li class="inline-flex items-center">
    <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
    </svg>
    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Odoo Export</span>
</li>
@endsection

@section('content')
<div class="space-y-8" x-data="{ 
    status: '{{ $status }}', 
    fileUrl: '{{ $fileUrl }}', 
    finishedAt: '{{ $finishedAt }}',
    error: '',
    poll() {
        if (this.status !== 'processing') return;
        
        fetch('{{ route('odoo-export.status') }}')
            .then(res => res.json())
            .then(data => {
                this.status = data.status;
                this.fileUrl = data.file_url;
                this.finishedAt = data.finished_at;
                this.error = data.error;
                
                if (this.status === 'processing') {
                    setTimeout(() => this.poll(), 3000);
                }
            });
    }
}" x-init="poll()">
    <div class="rounded-2xl bg-white dark:bg-slate-800 p-8 shadow-sm ring-1 ring-gray-200 dark:ring-slate-700">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Odoo Export Center</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">Generate formatted Excel templates for Odoo Master Data import.</p>
            </div>
            <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Stats Card -->
            <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/50 p-6 border border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-4 mb-6">
                    <div class="p-3 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold">Export Readiness</h3>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                        <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Master Customers</span>
                        <span class="text-lg font-bold text-slate-900 dark:text-white">{{ number_format($customerCount) }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                        <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Master Suppliers</span>
                        <span class="text-lg font-bold text-slate-900 dark:text-white">{{ number_format($supplierCount) }}</span>
                    </div>
                    <div class="flex items-center justify-between p-4 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-100 dark:border-indigo-800">
                        <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">Total Export Rows</span>
                        <span class="text-2xl font-extrabold text-indigo-700 dark:text-indigo-300">{{ number_format($customerCount + $supplierCount) }}</span>
                    </div>
                </div>
            </div>

            <!-- Export Form Card -->
            <div class="rounded-2xl bg-white dark:bg-slate-800 p-8 flex flex-col justify-center border-2 border-dashed border-slate-200 dark:border-slate-700">
                <h3 class="text-xl font-bold mb-4">Contact Template Export</h3>
                
                <div class="mb-8">
                    <template x-if="status === 'idle' || status === 'completed' || status === 'failed'">
                        <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                            This will group all customers and vendors into one unified Odoo template. 
                            Individuals and Companies will be automatically detected based on name tags: "PT", "CV", "UD", "TOKO", "PO".
                        </p>
                    </template>
                    
                    <template x-if="status === 'processing'">
                        <div class="p-6 bg-indigo-50 dark:bg-indigo-900/30 rounded-2xl border border-indigo-100 dark:border-indigo-800">
                            <div class="flex items-center gap-4">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                                <div>
                                    <p class="text-sm font-bold text-indigo-900 dark:text-indigo-100">Export in progress...</p>
                                    <p class="text-xs text-indigo-600 dark:text-indigo-400 mt-1">Generating 17k+ rows. Please stay on this page.</p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="status === 'failed'">
                        <div class="p-6 bg-red-50 dark:bg-red-900/30 rounded-2xl border border-red-100 dark:border-red-800 mt-4">
                            <p class="text-sm font-bold text-red-900 dark:text-red-100">Export failed!</p>
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1" x-text="error"></p>
                        </div>
                    </template>
                </div>

                <!-- Action Button -->
                <template x-if="status !== 'processing' && status !== 'completed'">
                    <div>
                        <button type="button" 
                                @click="
                                    status = 'processing'; 
                                    fetch('{{ route('odoo-export.contacts') }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        }
                                    }).then(() => poll());
                                "
                                class="w-full py-4 rounded-2xl bg-indigo-600 dark:bg-indigo-500 text-white font-bold text-lg hover:bg-indigo-700 dark:hover:bg-indigo-400 shadow-xl shadow-indigo-200 dark:shadow-none transition-all flex items-center justify-center">
                            <span>Export to Odoo Template &rarr;</span>
                        </button>
                    </div>
                </template>

                <!-- Success / Download Notification -->
                <template x-if="status === 'completed'">
                    <div class="space-y-4">
                        <div class="p-6 bg-emerald-50 dark:bg-emerald-900/30 rounded-2xl border border-emerald-100 dark:border-emerald-800 flex items-center gap-4">
                            <div class="p-2 bg-emerald-600 rounded-lg text-white">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-emerald-900 dark:text-emerald-100">Export Ready!</p>
                                <p class="text-xs text-emerald-600 dark:text-emerald-400" x-text="'Finished at ' + finishedAt"></p>
                            </div>
                        </div>
                        
                        <div class="flex gap-4">
                            <a :href="fileUrl" download class="flex-1 py-4 rounded-2xl bg-emerald-600 text-white text-center font-bold text-lg hover:bg-emerald-700 shadow-xl shadow-emerald-200 transition-all flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Download Link
                            </a>
                            <form action="{{ route('odoo-export.contacts') }}" method="POST">
                                @csrf
                                <button type="submit" @click="status = 'processing'; poll()" class="p-4 rounded-2xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 transition-all border border-slate-200 dark:border-slate-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </template>

                <div class="mt-6 flex items-center gap-3 text-xs text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Target Path: <span class="font-mono text-slate-500">/Master Data Template/1. Master Contact Customer & Vendor.xlsx</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Logic Visualization -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="rounded-2xl bg-white dark:bg-slate-800 p-8 shadow-sm ring-1 ring-slate-200 dark:ring-slate-700 lg:col-span-1">
            <h4 class="text-sm font-bold uppercase tracking-widest text-slate-400 mb-6">Column Mapping</h4>
            <ul class="space-y-4">
                <li class="flex items-center justify-between text-sm">
                    <span class="text-slate-500">ref</span>
                    <span class="font-mono text-indigo-600 dark:text-indigo-400">magic_cust / code</span>
                </li>
                <li class="flex items-center justify-between text-sm border-t border-slate-50 dark:border-slate-900 pt-4">
                    <span class="text-slate-500">vat (NPWP)</span>
                    <span class="font-mono text-indigo-600 dark:text-indigo-400">(Available logic)</span>
                </li>
                <li class="flex items-center justify-between text-sm border-t border-slate-50 dark:border-slate-900 pt-4">
                    <span class="text-slate-500">is_individual</span>
                    <span class="font-mono text-indigo-600 dark:text-indigo-400">Smart Detection</span>
                </li>
            </ul>
        </div>
        
        <div class="rounded-2xl bg-emerald-900/5 dark:bg-emerald-900/10 p-8 border border-emerald-100 dark:border-emerald-900/30 lg:col-span-2">
            <div class="flex items-start gap-4">
                <div class="p-3 rounded-lg bg-emerald-600 text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 00-2 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <div>
                    <h4 class="font-bold text-emerald-900 dark:text-emerald-400">Background Processing Ready</h4>
                    <p class="text-sm text-emerald-700 dark:text-emerald-500 mt-2 leading-relaxed">
                        The 17,544 row export is handled by our background workers. This ensures accuracy and allows you to download the file directly once it is compiled. 
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
