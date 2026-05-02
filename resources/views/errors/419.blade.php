@extends('layouts.app')

@section('title', 'Session Expired')

@section('content')
<div class="flex flex-col items-center justify-center py-20 px-4 text-center">
    <div class="w-24 h-24 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center mb-6">
        <svg class="w-12 h-12 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    </div>
    <h1 class="text-6xl font-black text-slate-300 dark:text-slate-600">419</h1>
    <h2 class="mt-4 text-2xl font-bold text-slate-900 dark:text-white">Session Expired</h2>
    <p class="mt-2 text-slate-500 dark:text-slate-400 max-w-md">Your session has expired due to inactivity. Please refresh the page and log in again.</p>
    <div class="mt-8 flex gap-3">
        <a href="javascript:location.reload()" class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-xl hover:bg-indigo-700 transition-colors shadow-sm">Refresh Page</a>
        <a href="{{ route('login') }}" class="px-6 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">Login</a>
    </div>
</div>
@endsection
