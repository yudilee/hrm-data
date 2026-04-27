<!DOCTYPE html>
<html lang="en">
<head>
    <script>
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
    <title>Login - {{ config('app.name', 'Master Data Hub') }}</title>
    <link rel="icon" href="{{ asset('images/logo-dark.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-slate-50 dark:bg-slate-900 min-h-screen flex items-center justify-center p-4 transition-colors duration-300" x-data="{ darkMode: document.documentElement.classList.contains('dark') }">
    <div class="w-full max-w-md">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <template x-if="!darkMode">
                <img src="{{ asset('images/logo-light.png') }}" alt="Master Data Hub Logo" class="mx-auto h-20 mb-4 object-contain">
            </template>
            <template x-if="darkMode">
                <img src="{{ asset('images/logo-dark.png') }}" alt="Master Data Hub Logo" class="mx-auto h-20 mb-4 object-contain">
            </template>
            <p class="text-slate-600 dark:text-slate-400 mt-2">Sign in to your account</p>
        </div>

        {{-- Login Card --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 shadow-xl border border-slate-200 dark:border-slate-700">
            @if($errors->any())
                <div class="mb-6 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 text-red-600 dark:text-red-300 p-4 rounded-lg text-sm">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @if(session('warning'))
                <div class="mb-6 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700 text-amber-600 dark:text-amber-300 p-4 rounded-lg text-sm">
                    {{ session('warning') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="space-y-5">
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Email</label>
                        <input type="text" name="email" id="email" value="{{ old('email') }}" required autofocus
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Password</label>
                        <input type="password" name="password" id="password" required
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition">
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="remember" id="remember" class="w-4 h-4 text-emerald-500 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-600 rounded focus:ring-emerald-500">
                        <label for="remember" class="ml-2 text-sm text-slate-600 dark:text-slate-400">Remember me</label>
                    </div>
                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-emerald-500 to-cyan-500 text-white font-semibold rounded-lg hover:from-emerald-600 hover:to-cyan-600 transition-all shadow-lg shadow-emerald-500/25">
                        Sign In
                    </button>
                </div>
            </form>
        </div>


    </div>
</body>
</html>
