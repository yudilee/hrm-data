@extends('layouts.app')

@section('title', 'Access Denied')

@section('content')
<div class="flex flex-col items-center justify-center py-20 px-4 text-center">
    <div class="w-24 h-24 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center mb-6">
        <svg class="w-12 h-12 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
    </div>
    <h1 class="text-6xl font-black text-slate-300 dark:text-slate-600">403</h1>
    <h2 class="mt-4 text-2xl font-bold text-slate-900 dark:text-white">Access Denied</h2>
    <p class="mt-2 text-slate-500 dark:text-slate-400 max-w-lg">
        {{ $exception->getMessage() ?: "You don't have permission to access this page. Please contact your administrator if you believe this is a mistake." }}
    </p>
    <div class="mt-8">
        <a href="{{ route('dashboard') }}" class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-xl hover:bg-indigo-700 transition-colors shadow-sm">Go to Dashboard</a>
    </div>
</div>
@endsection
