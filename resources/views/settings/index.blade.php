@extends('layouts.app')

@section('title', 'Application Settings')
@section('subtitle', 'Configure application behavior')

@section('breadcrumb')
    <x-ui.breadcrumb :items="[['label' => 'Settings']]" />
@endsection

@section('content')
<div class="max-w-2xl space-y-6">

    @if(session('success'))
        <div id="flash-success" data-message="{{ session('success') }}" class="hidden"></div>
    @endif
    @if(session('error'))
        <div id="flash-error" data-message="{{ session('error') }}" class="hidden"></div>
    @endif

    {{-- ═══ LABOUR CODES REBUILD ═══ --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm"
         x-data="labourRebuild()" x-init="init()">

        <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-violet-100 dark:bg-violet-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100">Rebuild Labour Codes</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Clears &amp; re-imports all RTS codes from <code class="bg-slate-100 dark:bg-slate-900 px-1.5 rounded font-mono text-[11px]">Data Operation/</code></p>
                </div>
            </div>
            {{-- Status badge --}}
            <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider"
                  :class="{
                    'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400': status === 'idle',
                    'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300 animate-pulse': status === 'running',
                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300': status === 'success',
                    'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300': status === 'error',
                  }"
                  x-text="statusLabel"></span>
        </div>

        <div class="px-6 py-5 space-y-4">

            {{-- Info block --}}
            <div class="flex gap-3 p-3 bg-violet-50 dark:bg-violet-900/20 border border-violet-100 dark:border-violet-900/30 rounded-lg text-xs text-violet-700 dark:text-violet-300">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div>
                    This will <strong>truncate</strong> the <code class="bg-violet-100 dark:bg-violet-900 px-1 rounded font-mono">labour_codes</code> table then re-read every
                    <code class="bg-violet-100 dark:bg-violet-900 px-1 rounded font-mono">Data Operation DMS *.xls</code> file and re-insert all rows.
                    The process may take <strong>1–3 minutes</strong> depending on file count.
                </div>
            </div>

            {{-- Result / last run summary --}}
            <div x-show="result || finishedAt" x-cloak class="flex items-center justify-between text-xs text-slate-500">
                <span x-show="result" x-text="'Result: ' + result"></span>
                <span x-show="finishedAt" x-text="'Finished: ' + finishedAt"></span>
            </div>

            {{-- Run button --}}
            <button @click="run()" :disabled="running"
                class="w-full py-3 rounded-xl font-bold text-sm transition-all flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed"
                :class="running
                    ? 'bg-violet-100 text-violet-500 dark:bg-violet-900/30 dark:text-violet-400'
                    : 'bg-violet-600 text-white hover:bg-violet-700 shadow-lg shadow-violet-200 dark:shadow-none'">
                <svg :class="running ? 'animate-spin' : ''" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span x-text="running ? 'Processing... Please wait' : 'Run Rebuild Now'"></span>
            </button>

            {{-- Elapsed timer --}}
            <div x-show="running" x-cloak class="text-center text-xs text-violet-500 dark:text-violet-400" x-text="'Elapsed: ' + elapsed + 's'"></div>

            {{-- Log output --}}
            <div x-show="log" x-cloak
                 class="bg-slate-900 rounded-lg overflow-hidden border border-slate-700">
                <div class="flex items-center justify-between px-4 py-2 bg-slate-800 border-b border-slate-700">
                    <span class="text-xs font-mono text-slate-400 uppercase tracking-widest">Script Output</span>
                    <button @click="log = ''" class="text-slate-500 hover:text-slate-300 text-xs">✕ Clear</button>
                </div>
                <pre class="px-4 py-3 text-xs font-mono text-emerald-400 overflow-auto max-h-64 whitespace-pre-wrap"
                     x-text="log"></pre>
            </div>

        </div>
    </div>

    {{-- ═══ DATABASE MANAGEMENT ═══ --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">

        <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 flex items-center gap-3">
            <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <div>
                <h3 class="text-base font-semibold text-red-700 dark:text-red-400">Database Management</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Destructive operations — cannot be undone</p>
            </div>
        </div>

        <div class="px-6 py-5 space-y-3">
            <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-100 dark:border-red-900/30">
                <p class="text-sm text-red-800 dark:text-red-300 font-medium">Clear All Local Data</p>
                <p class="text-xs text-red-600 dark:text-red-400/80 mt-1">This will permanently delete all local journal entries and lines. This action cannot be undone.</p>

                <div class="mt-4">
                    <x-ui.confirm-modal
                        title="Empty Database"
                        message="Are you sure you want to permanently delete all journal entries and lines? This action cannot be undone."
                        confirmText="Yes, Empty Database"
                        formAction="{{ route('settings.empty-database') }}"
                        formMethod="POST"
                    >
                        <x-slot name="trigger">
                            <span class="inline-block px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-all cursor-pointer">Empty Database Now</span>
                        </x-slot>
                        <x-slot name="formFields">
                            <input type="hidden" name="confirm" value="yes">
                        </x-slot>
                    </x-ui.confirm-modal>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
function labourRebuild() {
    return {
        status: 'idle',
        statusLabel: 'Idle',
        running: false,
        result: '',
        log: '',
        finishedAt: '',
        elapsed: 0,
        _timer: null,
        _pollTimer: null,

        init() {
            // On load, check if a previous run result exists
            fetch('{{ route("settings.rebuild-status") }}')
                .then(r => r.json())
                .then(d => this.applyState(d))
                .catch(() => {});
        },

        async run() {
            if (this.running) return;

            this.running = true;
            this.status  = 'running';
            this.statusLabel = 'Running';
            this.log = '';
            this.result = '';
            this.finishedAt = '';
            this.elapsed = 0;

            // Elapsed counter
            this._timer = setInterval(() => this.elapsed++, 1000);

            try {
                const resp = await fetch('{{ route("settings.rebuild-labour-codes") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    }
                });
                const data = await resp.json();
                this.applyState(data);
            } catch (e) {
                this.status = 'error';
                this.statusLabel = 'Error';
                this.log = 'Request failed: ' + e.message;
            } finally {
                this.running = false;
                clearInterval(this._timer);
            }
        },

        applyState(d) {
            this.status     = d.status ?? 'idle';
            this.result     = d.result ?? '';
            this.log        = d.log ?? '';
            this.finishedAt = d.finished_at ?? '';
            this.running    = d.running ?? false;

            const labels = {
                idle: 'Idle', running: 'Running',
                success: 'Success ✓', error: 'Error ✗', already_running: 'Already Running'
            };
            this.statusLabel = labels[this.status] ?? this.status;
        }
    }
}
</script>
@endsection
