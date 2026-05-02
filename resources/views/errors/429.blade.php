@extends('layouts.app')

@section('title', 'Too Many Requests')

@section('content')
<div class="flex flex-col items-center justify-center py-20 px-4 text-center">
    <div class="w-24 h-24 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center mb-6">
        <svg class="w-12 h-12 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
    </div>
    <h1 class="text-6xl font-black text-slate-300 dark:text-slate-600">429</h1>
    <h2 class="mt-4 text-2xl font-bold text-slate-900 dark:text-white">Too Many Requests</h2>
    <p class="mt-2 text-slate-500 dark:text-slate-400 max-w-md">You've sent too many requests in a short period. Please wait a moment and try again.</p>
    <div class="mt-8">
        <a href="javascript:location.reload()" class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-xl hover:bg-indigo-700 transition-colors shadow-sm">Try Again</a>
    </div>
</div>
@endsection
