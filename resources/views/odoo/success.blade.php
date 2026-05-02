@extends('layouts.app')

@section('title', 'Export Successful')

@section('content')
<div class="flex flex-col items-center justify-center py-20 px-4 text-center">
    <div class="w-24 h-24 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center mb-6">
        <svg class="w-12 h-12 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
    </div>
    
    <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Selection Exported!</h1>
    <p class="mt-4 text-slate-500 dark:text-slate-400 max-w-md">
        The selected labour codes for Job Order <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $jobOrderId }}</span> have been successfully transmitted back to Odoo.
    </p>

    <div class="mt-10 flex flex-col sm:flex-row items-center gap-4">
        <button onclick="window.close()" class="px-8 py-3 bg-slate-900 dark:bg-white dark:text-slate-900 text-white text-sm font-semibold rounded-xl hover:opacity-90 transition-all shadow-lg">
            Close Window
        </button>
        <a href="{{ route('dashboard') }}" class="px-8 py-3 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-sm font-semibold rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all">
            Go to Dashboard
        </a>
    </div>
    
    <p class="mt-8 text-xs text-slate-400">
        You can now return to your Odoo tab to continue the workflow.
    </p>
</div>
@endsection
