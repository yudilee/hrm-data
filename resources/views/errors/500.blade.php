@extends('layouts.app')

@section('title', 'Server Error')

@section('content')
<div class="flex flex-col items-center justify-center py-20 px-4 text-center">
    <div class="w-24 h-24 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mb-6">
        <svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
        </svg>
    </div>
    <h1 class="text-6xl font-black text-slate-300 dark:text-slate-600">500</h1>
    <h2 class="mt-4 text-2xl font-bold text-slate-900 dark:text-white">Something Went Wrong</h2>
    <p class="mt-2 text-slate-500 dark:text-slate-400 max-w-md">An unexpected error occurred. Our team has been notified. Please try again in a moment.</p>
    <div class="mt-8 flex gap-3">
        <a href="javascript:location.reload()" class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-xl hover:bg-indigo-700 transition-colors shadow-sm">Try Again</a>
        <a href="{{ route('dashboard') }}" class="px-6 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">Dashboard</a>
    </div>
</div>
@endsection
