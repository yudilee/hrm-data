<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Successful</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-900 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-white dark:bg-slate-800 rounded-3xl shadow-xl p-8 text-center border border-slate-100 dark:border-slate-700">
        <div class="w-20 h-20 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Export Successful!</h1>
        <p class="text-slate-600 dark:text-slate-400 mb-8 text-sm">
            Labour selection for Job Order <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $jobOrderId }}</span> has been sent back to Odoo.
        </p>

        <div class="space-y-3">
            <button onclick="window.close()" class="w-full py-3 bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-semibold rounded-xl hover:opacity-90 transition-all">
                Close Window
            </button>
            <a href="/" class="block w-full py-3 bg-white dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-semibold rounded-xl border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-600 transition-all text-sm text-center">
                Return to Dashboard
            </a>
        </div>
        
        <p class="mt-8 text-[10px] uppercase tracking-widest text-slate-400 font-medium">
            RTS Labour Integration System
        </p>
    </div>
</body>
</html>
