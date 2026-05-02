@extends('layouts.app')

@section('title', 'Labour Codes Sent')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-8 text-center">
        {{-- Success Icon --}}
        <div class="w-20 h-20 mx-auto bg-gradient-to-br from-emerald-400 to-green-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-emerald-500/25">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Labour Codes Sent Successfully!</h2>
        <p class="text-slate-500 dark:text-slate-400 mb-6">
            <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $count }}</span> labour code{{ $count > 1 ? 's have' : ' has' }} been sent to Odoo for job order
            <span class="font-bold text-slate-900 dark:text-white">{{ $jobNumber }}</span>.
        </p>

        {{-- Summary Table --}}
        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-xl p-4 mb-6 text-left">
            <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Codes Sent</h3>
            <div class="space-y-2">
                @foreach($codes as $code)
                <div class="flex items-center justify-between py-1.5">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded">{{ $code->code }}</span>
                        <span class="text-sm text-slate-700 dark:text-slate-300">{{ $code->description }}</span>
                    </div>
                    @if($code->time_hours)
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ number_format($code->time_hours, 2) }}h</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-center gap-3">
            <button onclick="window.close()" class="px-6 py-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 font-semibold rounded-xl text-sm transition-colors">
                Close Window
            </button>
        </div>

        <p class="text-xs text-slate-400 dark:text-slate-500 mt-6">
            You can now return to Odoo. The labour codes have been automatically added to the job order.
        </p>
    </div>
</div>
@endsection
