@extends('layouts.app')

@section('title', 'Vehicle History - Dealership MasterData Hub')

@section('breadcrumb')
    <li class="inline-flex items-center">
        <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
        </svg>
        <span class="text-sm font-medium text-gray-500">Vehicle History</span>
    </li>
@endsection

@section('content')
<div x-data="vehicleHistory()" class="flex flex-col h-full space-y-4">

    <!-- Header & Search Controls -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-4">
        <div class="flex items-end justify-between">
            <div class="flex items-center space-x-6">
                <!-- Police No Input -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Police No.</label>
                    <input type="text" x-model="searchQuery.cnpol" @keydown.enter="search()"
                           class="w-48 bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5" 
                           placeholder="e.g. L1934SB">
                </div>
                <!-- Chassis No Input -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Chassis No.</label>
                    <input type="text" x-model="searchQuery.chasn" @keydown.enter="search()"
                           class="w-64 bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5" 
                           placeholder="e.g. WDD1760442...">
                </div>
            </div>
            
            <div class="flex space-x-3">
                <button @click="search()" :disabled="isLoading"
                        class="text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 focus:outline-none flex items-center">
                    <span x-show="!isLoading">Check History</span>
                    <span x-show="isLoading" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Processing...
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- Error Alert -->
    <div x-show="errorMessage" x-cloak class="p-4 bg-red-50 text-red-700 border border-red-200 rounded-lg text-sm font-medium" x-text="errorMessage"></div>

    <!-- Summary (Top Grid) -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-4" x-show="vehicleData">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Vehicle Info -->
            <div class="space-y-3 col-span-2">
                <div class="flex items-center">
                    <span class="w-32 text-sm text-gray-500 dark:text-slate-400">Police No.</span>
                    <div class="px-3 py-1 bg-gray-100 dark:bg-slate-950 rounded border border-gray-200 dark:border-slate-700 text-sm font-semibold flex-1" x-text="vehicleData?.CNPOL || '-'"></div>
                </div>
                <div class="flex items-center">
                    <span class="w-32 text-sm text-gray-500 dark:text-slate-400">STNK Date</span>
                    <div class="px-3 py-1 bg-gray-100 dark:bg-slate-900 rounded border border-gray-200 dark:border-slate-700 text-sm dark:text-slate-300 flex-1" x-text="vehicleData?.DSTNK || '-'"></div>
                </div>
                <div class="flex items-center">
                    <span class="w-32 text-sm text-gray-500 dark:text-slate-400">Type Kendaraan</span>
                    <div class="px-3 py-1 bg-gray-100 dark:bg-slate-900 rounded border border-gray-200 dark:border-slate-700 text-sm font-semibold dark:text-slate-300 flex-1" x-text="vehicleData?.ETYPE || '-'"></div>
                </div>
            </div>

            <div class="space-y-3 col-span-2">
                <div class="flex items-center">
                    <span class="w-24 text-sm text-gray-500 dark:text-slate-400">Chassis No.</span>
                    <div class="px-3 py-1 bg-gray-100 dark:bg-slate-900 rounded border border-gray-200 dark:border-slate-700 text-sm font-semibold dark:text-slate-300 flex-1" x-text="vehicleData?.CHASN || '-'"></div>
                </div>
                <div class="flex items-center">
                    <span class="w-24 text-sm text-gray-500 dark:text-slate-400">Engine No.</span>
                    <div class="px-3 py-1 bg-gray-100 dark:bg-slate-900 rounded border border-gray-200 dark:border-slate-700 text-sm font-mono dark:text-slate-300 flex-1" x-text="vehicleData?.CENGN || '-'"></div>
                </div>
            </div>
        </div>

        <div class="mt-4 pt-4 border-t border-gray-100 grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Cust Info -->
            <div class="space-y-3">
                 <div class="flex items-center">
                    <span class="w-32 text-sm text-gray-500 dark:text-slate-400">Name</span>
                    <div class="px-3 py-1 bg-gray-100 dark:bg-slate-900 rounded border border-gray-200 dark:border-slate-700 text-sm flex-1 font-semibold dark:text-slate-300" x-text="vehicleData?.ENAME || '-'"></div>
                </div>
                <div class="flex items-center">
                    <span class="w-32 text-sm text-gray-500 dark:text-slate-400">Address</span>
                    <div class="px-3 py-1 bg-gray-100 dark:bg-slate-900 rounded border border-gray-200 dark:border-slate-700 text-sm dark:text-slate-300 flex-1 truncate" :title="vehicleData?.EADDR || ''" x-text="vehicleData?.EADDR || '-'"></div>
                </div>
                <div class="flex items-center">
                    <span class="w-32 text-sm text-gray-500 dark:text-slate-400">City</span>
                    <div class="px-3 py-1 bg-gray-100 dark:bg-slate-900 rounded border border-gray-200 dark:border-slate-700 text-sm dark:text-slate-300 flex-1" x-text="vehicleData?.ECITY || '-'"></div>
                </div>
            </div>
            
            <div class="space-y-3">
                 <div class="flex items-center">
                    <span class="w-24 text-sm text-gray-500 dark:text-slate-400">Phone</span>
                    <div class="px-3 py-1 bg-gray-100 dark:bg-slate-900 rounded border border-gray-200 dark:border-slate-700 text-sm dark:text-slate-300 flex-1" x-text="vehicleData?.EPHON || '-'"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main All Invoice Grid -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 flex-1 min-h-[300px] flex flex-col overflow-hidden" x-show="invoices.length > 0">
        <div class="p-3 border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900/50 flex justify-between items-center">
            <h3 class="font-bold text-gray-800 dark:text-slate-200 text-sm">*** All Invoice ***</h3>
            <div class="relative max-w-xs w-full">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" x-model="invoiceSearchTerm" class="block w-full pl-9 rounded-lg border-0 py-1.5 text-sm text-gray-900 dark:text-white bg-white dark:bg-slate-800 ring-1 ring-inset ring-gray-200 dark:ring-slate-700 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600" placeholder="Search repairs or parts...">
            </div>
        </div>
        
        <div class="overflow-x-auto flex-1">
            <table class="w-full text-xs text-left text-gray-600 dark:text-slate-400 font-mono align-middle">
                <thead class="text-xs text-gray-700 dark:text-slate-300 uppercase bg-gray-100 dark:bg-slate-900 sticky top-0">
                    <tr>
                        <th scope="col" class="px-3 py-2 border-r border-b border-gray-200 dark:border-slate-700">WIP No.</th>
                        <th scope="col" class="px-3 py-2 border-r border-b border-gray-200 dark:border-slate-700">Branch</th>
                        <th scope="col" class="px-3 py-2 border-r border-b border-gray-200 dark:border-slate-700">Police No.</th>
                        <th scope="col" class="px-3 py-2 border-r border-b border-gray-200 dark:border-slate-700">Chassis No.</th>
                        <th scope="col" class="px-3 py-2 border-r border-b border-gray-200 dark:border-slate-700">Receive Date</th>
                        <th scope="col" class="px-3 py-2 border-r border-b border-gray-200 dark:border-slate-700">Invoice</th>
                        <th scope="col" class="px-3 py-2 border-r border-b border-gray-200 dark:border-slate-700">Invoice Date</th>
                        <th scope="col" class="px-3 py-2 border-r border-b border-gray-200 dark:border-slate-700 text-right">Labour</th>
                        <th scope="col" class="px-3 py-2 border-r border-b border-gray-200 dark:border-slate-700 text-right">Sparepart</th>
                        <th scope="col" class="px-3 py-2 border-r border-b border-gray-200 dark:border-slate-700 text-right">Sublet</th>
                        <th scope="col" class="px-3 py-2 border-r border-b border-gray-200 dark:border-slate-700 text-right">Others</th>
                        <th scope="col" class="px-3 py-2 border-r border-b border-gray-200 dark:border-slate-700 text-right">Subtotal</th>
                        <th scope="col" class="px-3 py-2 border-r border-b border-gray-200 dark:border-slate-700 text-right">Tax</th>
                        <th scope="col" class="px-3 py-2 border-r border-b border-gray-200 dark:border-slate-700 text-right">Amount</th>
                        <th scope="col" class="px-3 py-2 border-b border-gray-200 dark:border-slate-700 text-right">KM Pos.</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                    <template x-for="inv in filteredInvoices" :key="inv.id">
                        <tr @click="loadDetails(inv)" 
                            class="hover:bg-indigo-50 dark:hover:bg-indigo-900/30 cursor-pointer transition-colors"
                            :class="{'bg-indigo-100 dark:bg-indigo-900/50': selectedInvoice?.id === inv.id, 'bg-white dark:bg-slate-800': selectedInvoice?.id !== inv.id}">
                            <td class="px-3 py-2 border-r border-gray-200 dark:border-slate-700 whitespace-nowrap font-bold text-gray-900 dark:text-white" x-text="inv.CJOBN"></td>
                            <td class="px-3 py-2 border-r border-gray-200 dark:border-slate-700 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 ring-1 ring-inset ring-slate-200 dark:ring-slate-600" x-text="inv.source"></span>
                            </td>
                            <td class="px-3 py-2 border-r border-gray-200 dark:border-slate-700 whitespace-nowrap font-bold" 
                                :class="inv.CNPOL !== vehicleData?.CNPOL ? 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20' : 'text-gray-900 dark:text-white'" 
                                x-text="inv.CNPOL"></td>
                            <td class="px-3 py-2 border-r border-gray-200 dark:border-slate-700 whitespace-nowrap font-mono" 
                                :class="inv.CHASN !== vehicleData?.CHASN ? 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 font-bold' : 'text-gray-500 dark:text-slate-400'" 
                                x-text="inv.CHASN"></td>
                            <td class="px-3 py-2 border-r border-gray-200 dark:border-slate-700 whitespace-nowrap" x-text="inv.DRECV"></td>
                            <td class="px-3 py-2 border-r border-gray-200 dark:border-slate-700 whitespace-nowrap text-indigo-600 dark:text-indigo-400 font-bold" x-text="inv.CINVN"></td>
                            <td class="px-3 py-2 border-r border-gray-200 dark:border-slate-700 whitespace-nowrap" x-text="inv.DINVN"></td>
                            <td class="px-3 py-2 border-r border-gray-200 dark:border-slate-700 whitespace-nowrap text-right" x-text="formatCurrency(inv.ALBRS)"></td>
                            <td class="px-3 py-2 border-r border-gray-200 dark:border-slate-700 whitespace-nowrap text-right" x-text="formatCurrency(inv.ASPTS)"></td>
                            <td class="px-3 py-2 border-r border-gray-200 dark:border-slate-700 whitespace-nowrap text-right" x-text="formatCurrency(inv.ASSPS)"></td>
                            <td class="px-3 py-2 border-r border-gray-200 dark:border-slate-700 whitespace-nowrap text-right" x-text="formatCurrency(inv.AOTHS1 + inv.AOTHS2)"></td>
                            <td class="px-3 py-2 border-r border-gray-200 dark:border-slate-700 whitespace-nowrap text-right font-medium text-gray-800 dark:text-slate-200" x-text="formatCurrency(inv.SUBTOTAL)"></td>
                            <td class="px-3 py-2 border-r border-gray-200 dark:border-slate-700 whitespace-nowrap text-right" x-text="formatCurrency(inv.ATAXS)"></td>
                            <td class="px-3 py-2 border-r border-gray-200 dark:border-slate-700 whitespace-nowrap text-right font-bold text-gray-900 dark:text-white" x-text="formatCurrency(inv.AMOUNT)"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-right" x-text="inv.EKMPOS"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Details Sections (Bottom Row) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 h-64" x-show="selectedInvoice">
        
        <!-- Labour Details -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 flex flex-col overflow-hidden">
             <div class="p-2 border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900/50">
                 <h3 class="font-bold text-gray-800 dark:text-slate-200 text-sm">
                    Labour Detail Invoice : <span class="text-indigo-600 dark:text-indigo-400" x-text="selectedInvoice?.CINVN"></span>
                </h3>
             </div>
             <div class="overflow-y-auto flex-1 relative">
                 <div x-show="isDetailsLoading" class="absolute inset-0 bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm flex items-center justify-center z-10">
                    <svg class="animate-spin h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                 </div>
                 <table class="w-full text-xs text-left text-gray-600 dark:text-slate-400">
                    <thead class="text-xs text-gray-700 dark:text-slate-300 bg-white dark:bg-slate-800 sticky top-0 shadow-sm">
                        <tr>
                            <th scope="col" class="px-2 py-1.5 border-r border-b border-gray-200 dark:border-slate-700">No.</th>
                            <th scope="col" class="px-2 py-1.5 border-r border-b border-gray-200 dark:border-slate-700 min-w-48">Description</th>
                            <th scope="col" class="px-2 py-1.5 border-r border-b border-gray-200 dark:border-slate-700 text-right">T.Allowed</th>
                            <th scope="col" class="px-2 py-1.5 border-r border-b border-gray-200 dark:border-slate-700 text-right">Net Value</th>
                            <th scope="col" class="px-2 py-1.5 border-b border-gray-200 dark:border-slate-700 text-right">T.Taken</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-700 font-mono">
                         <template x-for="(lab, idx) in details.labours" :key="idx">
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50">
                                <td class="px-2 py-1 border-r border-gray-100 dark:border-slate-700" x-text="idx + 1"></td>
                                <td class="px-2 py-1 border-r border-gray-100 dark:border-slate-700 truncate max-w-xs" :title="lab.EMJOB" x-text="lab.EMJOB"></td>
                                <td class="px-2 py-1 border-r border-gray-100 dark:border-slate-700 text-right" x-text="parseFloat(lab.QHOUR).toFixed(2)"></td>
                                <td class="px-2 py-1 border-r border-gray-100 dark:border-slate-700 text-right" x-text="formatCurrency(lab.NET)"></td>
                                <td class="px-2 py-1 border-gray-100 dark:border-slate-700 text-right" x-text="parseFloat(lab.TAKEN).toFixed(2)"></td>
                            </tr>
                        </template>
                        <tr x-show="details.labours.length === 0 && !isDetailsLoading">
                            <td colspan="5" class="px-4 py-3 text-center text-gray-400 dark:text-slate-500 italic">No labours found for this invoice.</td>
                        </tr>
                    </tbody>
                 </table>
             </div>
        </div>

        <!-- Spareparts Details -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 flex flex-col overflow-hidden">
             <div class="p-2 border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900/50">
                 <h3 class="font-bold text-gray-800 dark:text-slate-200 text-sm">
                    Sparepart Detail Invoice : <span class="text-indigo-600 dark:text-indigo-400" x-text="selectedInvoice?.CINVN"></span>
                </h3>
             </div>
             <div class="overflow-y-auto flex-1 relative">
                 <div x-show="isDetailsLoading" class="absolute inset-0 bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm flex items-center justify-center z-10">
                    <svg class="animate-spin h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                 </div>
                 <table class="w-full text-xs text-left text-gray-600 dark:text-slate-400">
                    <thead class="text-xs text-gray-700 dark:text-slate-300 bg-white dark:bg-slate-800 sticky top-0 shadow-sm">
                        <tr>
                            <th scope="col" class="px-2 py-1.5 border-r border-b border-gray-200 dark:border-slate-700">Part Number</th>
                            <th scope="col" class="px-2 py-1.5 border-r border-b border-gray-200 dark:border-slate-700 min-w-48">Description</th>
                            <th scope="col" class="px-2 py-1.5 border-r border-b border-gray-200 dark:border-slate-700 text-right">Qty</th>
                            <th scope="col" class="px-2 py-1.5 border-r border-b border-gray-200 dark:border-slate-700 text-right">Price/pcs</th>
                            <th scope="col" class="px-2 py-1.5 border-b border-gray-200 dark:border-slate-700 text-right">Net Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-700 font-mono">
                        <template x-for="(part, idx) in details.parts" :key="idx">
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50">
                                <td class="px-2 py-1 border-r border-gray-100 dark:border-slate-700 whitespace-nowrap font-semibold text-gray-700 dark:text-slate-200" x-text="part.CPART"></td>
                                <td class="px-2 py-1 border-r border-gray-100 dark:border-slate-700 truncate max-w-xs" :title="part.EDESC" x-text="part.EDESC"></td>
                                <td class="px-2 py-1 border-r border-gray-100 dark:border-slate-700 text-right" x-text="parseFloat(part.QRECV).toFixed(2)"></td>
                                <td class="px-2 py-1 border-r border-gray-100 dark:border-slate-700 text-right" x-text="formatCurrency(part.ASPPRC)"></td>
                                <td class="px-2 py-1 border-gray-100 dark:border-slate-700 text-right" x-text="formatCurrency(part.AFIFO)"></td>
                            </tr>
                        </template>
                        <tr x-show="details.parts.length === 0 && !isDetailsLoading">
                            <td colspan="5" class="px-4 py-3 text-center text-gray-400 dark:text-slate-500 italic">No spareparts found for this invoice.</td>
                        </tr>
                    </tbody>
                 </table>
             </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('vehicleHistory', () => ({
            searchQuery: {
                cnpol: '',
                chasn: ''
            },
            isLoading: false,
            errorMessage: '',
            
            vehicleData: null,
            invoices: [],
            invoiceSearchTerm: '',
            
            selectedInvoice: null,
            isDetailsLoading: false,
            details: {
                labours: [],
                parts: []
            },

            get filteredInvoices() {
                if (!this.invoiceSearchTerm) return this.invoices;
                const term = this.invoiceSearchTerm.toLowerCase();
                return this.invoices.filter(inv => {
                    return inv.search_text && inv.search_text.includes(term);
                });
            },

            formatCurrency(value) {
                if(value === null || value === undefined) return '0';
                // Indonesian separator formatting (1.000.000)
                return Math.round(value).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            },

            async search() {
                if(!this.searchQuery.cnpol && !this.searchQuery.chasn) {
                    this.errorMessage = 'Please enter Police No. or Chassis No.';
                    return;
                }
                
                this.isLoading = true;
                this.errorMessage = '';
                this.vehicleData = null;
                this.invoices = [];
                this.selectedInvoice = null;
                
                try {
                    const params = new URLSearchParams();
                    if(this.searchQuery.cnpol) params.append('cnpol', this.searchQuery.cnpol);
                    if(this.searchQuery.chasn) params.append('chasn', this.searchQuery.chasn);
                    
                    const response = await fetch(`/api/service-history/search?${params.toString()}`);
                    const data = await response.json();
                    
                    if(!response.ok) {
                        throw new Error(data.error || 'Failed to fetch history');
                    }
                    
                    this.vehicleData = data.vehicle;
                    this.invoices = data.invoices;
                    
                    if(this.invoices.length > 0) {
                        // Automatically load details for the first invoice found
                        this.loadDetails(this.invoices[0]);
                    }
                    
                } catch (error) {
                    this.errorMessage = error.message;
                } finally {
                    this.isLoading = false;
                }
            },
            
            async loadDetails(invoice) {
                this.selectedInvoice = invoice;
                this.isDetailsLoading = true;
                this.details = { labours: [], parts: [] };
                
                try {
                    const response = await fetch(`/api/service-history/details?id=${invoice.id}`);
                    const data = await response.json();
                    
                    if(!response.ok) {
                        throw new Error(data.error || 'Failed to fetch details');
                    }
                    
                    this.details = data;
                } catch (error) {
                    console.error('Failed to load details:', error);
                    // Don't show major error to user, just leave empty tables
                } finally {
                    this.isDetailsLoading = false;
                }
            }
        }))
    });
</script>
@endpush
