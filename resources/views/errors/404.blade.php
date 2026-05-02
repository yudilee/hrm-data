@extends('layouts.app')

@section('title', 'Page Not Found')

@section('content')
<div class="flex flex-col items-center justify-center py-20 px-4 text-center">
    <div class="w-24 h-24 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-6">
        <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    </div>
    <h1 class="text-6xl font-black text-slate-300 dark:text-slate-600">404</h1>
    <h2 class="mt-4 text-2xl font-bold text-slate-900 dark:text-white">Page Not Found</h2>
    <p class="mt-2 text-slate-500 dark:text-slate-400 max-w-md">The page you're looking for doesn't exist or has been moved. Check the URL or try searching.</p>
    <div class="mt-8 flex gap-3">
        <a href="{{ route('dashboard') }}" class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-xl hover:bg-indigo-700 transition-colors shadow-sm">Go to Dashboard</a>
        <a href="javascript:history.back()" class="px-6 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">Go Back</a>
    </div>
</div>
@endsection
