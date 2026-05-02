<!DOCTYPE html>
<html lang="en" id="html-root">
<head>
    <script>
        // Check for saved theme preference or default to dark
        const theme = localStorage.getItem('theme') || 'dark';
        if (theme === 'light') {
            document.documentElement.classList.remove('dark');
            document.documentElement.style.colorScheme = 'light';
        } else {
            document.documentElement.classList.add('dark');
            document.documentElement.style.colorScheme = 'dark';
        }
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Master Data Hub')) - {{ config('app.name', 'Master Data Hub') }}</title>
    <link rel="icon" href="{{ asset('images/logo-dark.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 min-h-screen flex" 
    x-data="{ 
        sidebarOpen: window.innerWidth >= 1280,
        mobileSidebarOpen: false,
        darkMode: document.documentElement.classList.contains('dark')
    }"
    x-init="
        $watch('darkMode', val => {
            if (val) {
                document.documentElement.classList.add('dark');
                document.documentElement.style.colorScheme = 'dark';
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.style.colorScheme = 'light';
                localStorage.setItem('theme', 'light');
            }
        });
        document.documentElement.classList.toggle('dark', darkMode);
        document.documentElement.style.colorScheme = darkMode ? 'dark' : 'light';
        // Close mobile sidebar on resize
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1280) mobileSidebarOpen = false;
        });
    "
    @keydown.ctrl.k.prevent="$refs.globalSearch?.focus()"
>

    {{-- Sidebar --}}
    {{-- Sidebar --}}
    @if(!request('compact'))
    {{-- Mobile overlay backdrop --}}
    <div x-show="mobileSidebarOpen" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[55] xl:hidden"
        @click="mobileSidebarOpen = false"
    ></div>

    <aside
        :class="{
            'w-64': sidebarOpen,
            'w-16': !sidebarOpen,
            'translate-x-0': mobileSidebarOpen,
            '-translate-x-full xl:translate-x-0': !mobileSidebarOpen
        }"
        class="bg-white dark:bg-slate-950 border-r border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-200 flex flex-col transition-all duration-300 fixed h-full z-[60]"
        {{-- Logo --}}
        <div class="flex items-center justify-between p-4 border-b border-slate-200 dark:border-slate-800 h-16">
            <template x-if="!darkMode">
                <img x-show="sidebarOpen" x-cloak src="{{ asset('images/logo-light.png') }}" alt="App Logo" class="h-8 object-contain">
            </template>
            <template x-if="darkMode">
                <img x-show="sidebarOpen" x-cloak src="{{ asset('images/logo-dark.png') }}" alt="App Logo" class="h-8 object-contain">
            </template>
            <button @click="sidebarOpen = !sidebarOpen" class="p-1 rounded hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" aria-label="Toggle sidebar" title="Toggle sidebar">
                <svg x-show="sidebarOpen" class="w-5 h-5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
                <svg x-show="!sidebarOpen" class="w-5 h-5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
            </button>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
            {{-- Dashboard --}}
            @if(\App\Models\Setting::get('show_dashboard', '1') === '1')
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-emerald-50 dark:bg-emerald-600/20 text-emerald-600 dark:text-emerald-400 font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-600 dark:text-slate-400' }}" title="Dashboard" aria-label="Dashboard">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span x-show="sidebarOpen" x-cloak>Dashboard</span>
            </a>
            @endif

            @if(auth()->check())
            {{-- Service History --}}
            <a href="{{ route('service-history.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('service-history*') ? 'bg-emerald-50 dark:bg-emerald-600/20 text-emerald-600 dark:text-emerald-400 font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-600 dark:text-slate-400' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span x-show="sidebarOpen" x-cloak>Service History</span>
            </a>

            {{-- Labour Code Search --}}
            <a href="{{ route('labour-search') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('labour-search*') ? 'bg-emerald-50 dark:bg-emerald-600/20 text-emerald-600 dark:text-emerald-400 font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-600 dark:text-slate-400' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <span x-show="sidebarOpen" x-cloak>Labour Code Search</span>
            </a>

            {{-- Master Vehicles --}}
            <a href="{{ route('master-vehicles.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('master-vehicles*') ? 'bg-emerald-50 dark:bg-emerald-600/20 text-emerald-600 dark:text-emerald-400 font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-600 dark:text-slate-400' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span x-show="sidebarOpen" x-cloak>Master Vehicles</span>
            </a>

            {{-- Master Customers --}}
            <a href="{{ route('master-customers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('master-customers*') ? 'bg-emerald-50 dark:bg-emerald-600/20 text-emerald-600 dark:text-emerald-400 font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-600 dark:text-slate-400' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span x-show="sidebarOpen" x-cloak>Master Customers</span>
            </a>

            {{-- Master Suppliers --}}
            <a href="{{ route('master-suppliers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('master-suppliers*') ? 'bg-emerald-50 dark:bg-emerald-600/20 text-emerald-600 dark:text-emerald-400 font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-600 dark:text-slate-400' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span x-show="sidebarOpen" x-cloak>Master Suppliers</span>
            </a>
            @endif

            @if(auth()->check() && auth()->user()->role === 'admin')
            {{-- Import Data --}}
            <a href="{{ route('import.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('import*') ? 'bg-emerald-50 dark:bg-emerald-600/20 text-emerald-600 dark:text-emerald-400 font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-600 dark:text-slate-400' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span x-show="sidebarOpen" x-cloak>Import Data</span>
            </a>

            {{-- Odoo Export --}}
            <a href="{{ route('odoo-export.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('odoo-export*') ? 'bg-emerald-50 dark:bg-emerald-600/20 text-emerald-600 dark:text-emerald-400 font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-600 dark:text-slate-400' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span x-show="sidebarOpen" x-cloak>Odoo Export</span>
            </a>

            <div class="pt-4 pb-2">
                <p x-show="sidebarOpen" x-cloak class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Admin</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('admin.users*') ? 'bg-emerald-50 dark:bg-emerald-600/20 text-emerald-600 dark:text-emerald-400 font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-600 dark:text-slate-400' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span x-show="sidebarOpen" x-cloak>Users</span>
            </a>
            <a href="{{ route('admin.backups.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('admin.backups*') ? 'bg-emerald-50 dark:bg-emerald-600/20 text-emerald-600 dark:text-emerald-400 font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-600 dark:text-slate-400' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                <span x-show="sidebarOpen" x-cloak>Backups</span>
            </a>
            <a href="{{ route('admin.sessions.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('admin.sessions*') ? 'bg-emerald-50 dark:bg-emerald-600/20 text-emerald-600 dark:text-emerald-400 font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-600 dark:text-slate-400' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span x-show="sidebarOpen" x-cloak>Sessions</span>
            </a>
            <a href="{{ route('admin.api-tokens.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('admin.api-tokens*') ? 'bg-emerald-50 dark:bg-emerald-600/20 text-emerald-600 dark:text-emerald-400 font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-600 dark:text-slate-400' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                <span x-show="sidebarOpen" x-cloak>API Tokens</span>
            </a>
            <a href="{{ route('admin.token-requests.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('admin.token-requests*') ? 'bg-emerald-50 dark:bg-emerald-600/20 text-emerald-600 dark:text-emerald-400 font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-600 dark:text-slate-400' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                <span x-show="sidebarOpen" x-cloak>Token Requests</span>
            </a>

            {{-- Security Section --}}
            <div class="pt-3 pb-1">
                <p x-show="sidebarOpen" x-cloak class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Security & Logs</p>
            </div>
            <a href="{{ route('admin.api-logs.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('admin.api-logs*') ? 'bg-emerald-50 dark:bg-emerald-600/20 text-emerald-600 dark:text-emerald-400 font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-600 dark:text-slate-400' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span x-show="sidebarOpen" x-cloak>API Logs</span>
            </a>
            <a href="{{ route('admin.audit-logs.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('admin.audit-logs*') ? 'bg-emerald-50 dark:bg-emerald-600/20 text-emerald-600 dark:text-emerald-400 font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-600 dark:text-slate-400' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span x-show="sidebarOpen" x-cloak>Audit Trail</span>
            </a>
            <a href="{{ route('admin.login-attempts.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('admin.login-attempts*') ? 'bg-emerald-50 dark:bg-emerald-600/20 text-emerald-600 dark:text-emerald-400 font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-600 dark:text-slate-400' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span x-show="sidebarOpen" x-cloak>Login Attempts</span>
            </a>
            <a href="{{ route('admin.log-viewer.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('admin.log-viewer*') ? 'bg-emerald-50 dark:bg-emerald-600/20 text-emerald-600 dark:text-emerald-400 font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-600 dark:text-slate-400' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                <span x-show="sidebarOpen" x-cloak>Log Viewer</span>
            </a>
            @endif

            @if(auth()->check() && auth()->user()->role === 'admin')
            <div class="pt-4 pb-2">
                <p x-show="sidebarOpen" x-cloak class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">System</p>
            </div>
            <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('settings*') ? 'bg-emerald-50 dark:bg-emerald-600/20 text-emerald-600 dark:text-emerald-400 font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-600 dark:text-slate-400' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span x-show="sidebarOpen" x-cloak>Settings</span>
            </a>
            @endif

            {{-- API Docs and My Tokens — visible to all authenticated users --}}
            @if(auth()->check())
            <div class="pt-4 pb-2">
                <p x-show="sidebarOpen" x-cloak class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Developer</p>
            </div>
            <a href="{{ route('user.api-tokens.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('user.api-tokens*') ? 'bg-emerald-50 dark:bg-emerald-600/20 text-emerald-600 dark:text-emerald-400 font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-600 dark:text-slate-400' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                <span x-show="sidebarOpen" x-cloak>My API Tokens</span>
            </a>
            <a href="/docs/api" target="_blank" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-600 dark:text-slate-400">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                <span x-show="sidebarOpen" x-cloak>API Docs</span>
            </a>
            @endif

            {{-- Recently Viewed (Alpine.js powered) --}}
            @if(auth()->check())
            <div class="pt-4 pb-2" x-data="recentlyViewed()" x-show="items.length > 0" x-cloak>
                <p x-show="sidebarOpen" class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Recently Viewed</p>
                <div class="mt-2 space-y-1">
                    <template x-for="item in items" :key="item.url">
                        <a :href="item.url" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-600 dark:text-slate-400" :title="item.name">
                            <svg x-show="item.type === 'customer'" class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <svg x-show="item.type === 'vehicle'" class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <span x-show="sidebarOpen" class="text-xs truncate font-medium" x-text="item.name"></span>
                        </a>
                    </template>
                </div>
            </div>
            @endif
        </nav>

        {{-- Theme Toggle --}}
        <div class="px-6 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            <button @click="darkMode = !darkMode" class="w-full flex items-center justify-center gap-2 px-3 py-2 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-semibold transition-all shadow-sm">
                <template x-if="darkMode">
                    <div class="flex items-center gap-2 text-slate-600">
                        <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464l-.707-.707a1 1 0 00-1.414 1.414l.707.707a1 1 0 001.414-1.414zm2.12 8.485l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"/></svg>
                        <span x-show="sidebarOpen" x-cloak>Switch to Light</span>
                    </div>
                </template>
                <template x-if="!darkMode">
                    <div class="flex items-center gap-2 text-slate-300">
                        <svg class="w-4 h-4 text-indigo-400" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/></svg>
                        <span x-show="sidebarOpen" x-cloak>Switch to Dark</span>
                    </div>
                </template>
            </button>
        </div>

        {{-- User --}}
        @if(auth()->check())
        <div class="p-3 border-t border-slate-700">
            <div class="flex items-center gap-3 px-3 py-2">
                <div class="w-8 h-8 bg-emerald-600 rounded-full flex items-center justify-center text-sm font-bold shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div x-show="sidebarOpen" x-cloak class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ auth()->user()->getRoleDisplayName() }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span x-show="sidebarOpen" x-cloak>Logout</span>
                </button>
            </form>
        </div>
        @endif
    </aside>
    @endif

    {{-- Main Content --}}
    <main @if(!request('compact')) :class="sidebarOpen ? 'xl:ml-64' : 'xl:ml-16'" @endif class="flex-1 transition-all duration-300 min-h-screen ml-0">
        {{-- Header --}}
        @if(!request('compact'))
        <header class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 px-6 py-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                {{-- Hamburger for mobile --}}
                <button @click="mobileSidebarOpen = !mobileSidebarOpen" class="xl:hidden p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" aria-label="Open menu">
                    <svg class="w-6 h-6 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="text-2xl font-bold">@yield('title', 'Dashboard')</h1>
                @hasSection('breadcrumb')
                    @yield('breadcrumb')
                @endif
                @hasSection('subtitle')
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">@yield('subtitle')</p>
                @endif
            </div>

            <!-- Global Search Component -->
            <div x-data="{ 
                query: '', 
                results: null, 
                loading: false,
                open: false,
                
                async search() {
                    if (this.query.length < 2) {
                        this.results = null;
                        this.open = false;
                        return;
                    }
                    this.loading = true;
                    this.open = true;
                    try {
                        let res = await fetch('/api/global-search?q=' + encodeURIComponent(this.query));
                        this.results = await res.json();
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.loading = false;
                    }
                }
            }" 
            @click.away="open = false"
            class="relative w-full md:w-96 z-50">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" x-ref="globalSearch" x-model.debounce.300ms="query" @input="search" @focus="query.length >= 2 ? open = true : null"
                        class="block w-full pl-10 pr-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl leading-5 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-all"
                        placeholder="Global search (customers, vehicles, invoices)..."
                        aria-label="Global search">
                    <div x-show="loading" class="absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="animate-spin h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>
                </div>

                <!-- Dropdown Results -->
                <div x-show="open && results" x-cloak
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute mt-2 w-full md:w-[32rem] right-0 bg-white dark:bg-slate-800 rounded-xl shadow-2xl ring-1 ring-black ring-opacity-5 dark:ring-slate-700 overflow-hidden divide-y divide-slate-100 dark:divide-slate-700">
                    
                    <div class="max-h-96 overflow-y-auto p-2 space-y-4">
                        
                        <!-- Customers -->
                        <div x-show="results?.customers?.length > 0">
                            <h3 class="px-3 py-1.5 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Customers</h3>
                            <ul class="mt-1 space-y-1">
                                <template x-for="c in results?.customers" :key="'c'+c.id">
                                    <li>
                                        <a :href="'/master-customers/' + c.id" class="block px-3 py-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors group">
                                            <p class="text-sm font-semibold text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400" x-text="c.name"></p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400" x-text="[c.email, c.telp_1].filter(Boolean).join(' • ')"></p>
                                        </a>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        <!-- Vehicles -->
                        <div x-show="results?.vehicles?.length > 0">
                            <h3 class="px-3 py-1.5 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Vehicles</h3>
                            <ul class="mt-1 space-y-1">
                                <template x-for="v in results?.vehicles" :key="'v'+v.id">
                                    <li>
                                        <a :href="'/master-vehicles/' + v.id" class="block px-3 py-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors group">
                                            <p class="text-sm font-semibold text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400">
                                                <span x-text="v.registration_no"></span> 
                                                <span class="ml-2 text-xs font-normal px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-400" x-text="v.description"></span>
                                            </p>
                                            <p class="text-xs font-mono text-slate-500 dark:text-slate-400 mt-1" x-text="v.chassis_no"></p>
                                        </a>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        <!-- Histories -->
                        <div x-show="results?.histories?.length > 0">
                            <h3 class="px-3 py-1.5 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Service Invoices</h3>
                            <ul class="mt-1 space-y-1">
                                <template x-for="h in results?.histories" :key="'h'+h.id">
                                    <li>
                                        <!-- Since we don't have a dedicated invoice page, we link to the vehicle page with a hash or param to highlight it, or just the vehicle page -->
                                        <a :href="'/master-vehicles/' + h.vehicle_id + '#invoice-' + h.id" class="block px-3 py-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors group">
                                            <p class="text-sm font-semibold text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400" x-text="h.CINVN"></p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                                Vehicle: <span class="font-mono" x-text="h.vehicle?.registration_no"></span> • 
                                                <span x-text="h.DRECV ? h.DRECV.substring(0,10) : ''"></span>
                                            </p>
                                        </a>
                                    </li>
                                </template>
                            </ul>
                        </div>
                        
                        <div x-show="!loading && (!results?.customers?.length && !results?.vehicles?.length && !results?.histories?.length)" class="px-3 py-6 text-center text-sm text-slate-500">
                            No results found for "<span class="font-bold" x-text="query"></span>".
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notification Bell (admin only) --}}
            @if(auth()->check() && auth()->user()->role === 'admin')
            <div x-data="notifBell()" class="relative shrink-0" @click.away="open = false">
                <button @click="toggle" class="relative p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" aria-label="Notifications" title="Notifications">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span x-show="unread > 0" x-cloak x-text="unread > 9 ? '9+' : unread"
                          class="absolute -top-1 -right-1 flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold"></span>
                </button>

                <div x-show="open" x-cloak
                     class="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-900 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden z-50">
                    <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <span class="font-semibold text-sm">Notifications</span>
                        <button @click="markAllRead" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Mark all read</button>
                    </div>
                    <div class="max-h-72 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
                        <template x-if="notifications.length === 0">
                            <p class="px-4 py-6 text-sm text-center text-slate-400">No notifications</p>
                        </template>
                        <template x-for="n in notifications" :key="n.id">
                            <div :class="n.read_at ? 'opacity-60' : ''" class="px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                <p class="text-sm font-medium" x-text="n.title"></p>
                                <p class="text-xs text-slate-500 mt-0.5" x-text="n.body"></p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            @endif
        </header>
        @endif

        {{-- Flash Messages (also dispatched as toasts) --}}
        @if(session('success'))
            <div id="flash-success" data-message="{{ session('success') }}" class="hidden"></div>
        @endif
        @if(session('error'))
            <div id="flash-error" data-message="{{ session('error') }}" class="hidden"></div>
        @endif

    {{-- Page Content --}}
    <div class="p-6">
        @yield('content')
    </div>

    {{-- Toast Notification System --}}
    <x-ui.toasts />
</main>

@stack('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.Alpine) {
            window.Alpine.start();
        }

        // Convert any flash messages to toast notifications
        const flashSuccess = document.getElementById('flash-success');
        const flashError = document.getElementById('flash-error');
        if (flashSuccess) {
            window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: flashSuccess.dataset.message } }));
        }
        if (flashError) {
            window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: flashError.dataset.message } }));
        }
    });

    // Global toast helper for use anywhere
    window.showToast = function(type, message) {
        window.dispatchEvent(new CustomEvent('toast', { detail: { type: type, message: message } }));
    };

    function recentlyViewed() {
        return {
            items: [],
            init() {
                try {
                    this.items = JSON.parse(localStorage.getItem('recently_viewed')) || [];
                } catch (e) {
                    this.items = [];
                }
            }
        }
    }

    function notifBell() {
        return {
            open: false,
            unread: 0,
            notifications: [],

            async toggle() {
                this.open = !this.open;
                if (this.open) await this.load();
            },

            async load() {
                try {
                    const r = await fetch('{{ route('notifications.index') }}', {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    this.notifications = await r.json();
                    this.unread = this.notifications.filter(n => !n.read_at).length;
                } catch (e) {}
            },

            async markAllRead() {
                await fetch('{{ route('notifications.read-all') }}', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    }
                });
                this.notifications = this.notifications.map(n => ({ ...n, read_at: new Date().toISOString() }));
                this.unread = 0;
            },

            init() {
                // Poll every 60 seconds for new notifications
                this.load();
                setInterval(() => this.load(), 60000);
            },
        };
    }
</script>
</body>
</html>
