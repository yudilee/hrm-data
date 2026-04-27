@extends('layouts.app')

@section('title', 'Log Viewer')
@section('subtitle', 'Browse application log files (read-only)')

@section('content')
<div class="space-y-6" x-data="logViewer()">

    {{-- Channel Tabs --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5">
        <div class="flex flex-wrap gap-2 mb-4">
            @foreach($files as $channel => $channelFiles)
            <button @click="loadChannel('{{ $channel }}')"
                    :class="activeChannel === '{{ $channel }}' ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                    class="px-4 py-1.5 rounded-lg text-sm font-medium transition-colors capitalize">
                {{ $channel }}
                <span class="ml-1 text-xs opacity-60">({{ $channelFiles->count() }})</span>
            </button>
            @endforeach
        </div>

        {{-- File List for active channel --}}
        <div class="text-sm text-slate-600 dark:text-slate-400">
            @foreach($files as $channel => $channelFiles)
            <div x-show="activeChannel === '{{ $channel }}'" class="flex flex-wrap gap-2">
                @foreach($channelFiles as $file)
                <button @click="loadFile('{{ $channel }}', '{{ $file['filename'] }}')"
                        :class="activeFile === '{{ $file['filename'] }}' ? 'ring-2 ring-indigo-500' : ''"
                        class="px-3 py-1 rounded-lg border border-slate-200 dark:border-slate-700 text-xs font-mono hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    {{ $file['filename'] }}
                    <span class="text-slate-400 ml-1">{{ number_format($file['size'] / 1024, 1) }} KB</span>
                </button>
                @endforeach
            </div>
            @endforeach
        </div>
    </div>

    {{-- Log Output --}}
    <div class="bg-slate-950 rounded-xl border border-slate-800 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-800 flex items-center justify-between">
            <span class="text-sm text-slate-400 font-mono" x-text="activeFile || 'Select a log file above'"></span>
            <div class="flex items-center gap-3">
                <label class="flex items-center gap-2 text-xs text-slate-500 cursor-pointer">
                    <input type="checkbox" x-model="autoRefresh" @change="toggleAutoRefresh" class="rounded">
                    Auto-refresh (30s)
                </label>
                <button @click="reload()" class="text-xs text-indigo-400 hover:text-indigo-300">↻ Reload</button>
            </div>
        </div>
        <div class="p-4 h-96 overflow-y-auto font-mono text-xs leading-relaxed" id="log-output">
            <template x-if="loading">
                <p class="text-slate-500 animate-pulse">Loading log file...</p>
            </template>
            <template x-if="!loading && lines.length === 0">
                <p class="text-slate-600">No log entries. Select a channel and file above.</p>
            </template>
            <template x-for="(line, i) in lines" :key="i">
                <div :class="{
                    'text-red-400':    line.level === 'error',
                    'text-amber-400':  line.level === 'warning',
                    'text-slate-400':  line.level === 'debug',
                    'text-slate-200':  line.level === 'info',
                }" class="py-0.5 hover:bg-white/5 px-1 rounded" x-text="line.text"></div>
            </template>
        </div>
    </div>

    {{-- Legend --}}
    <div class="flex gap-4 text-xs">
        @foreach(['error' => 'text-red-400', 'warning' => 'text-amber-400', 'info' => 'text-slate-300', 'debug' => 'text-slate-500'] as $level => $color)
        <span class="flex items-center gap-1 {{ $color }}">
            <span class="w-2 h-2 rounded-full bg-current"></span>{{ ucfirst($level) }}
        </span>
        @endforeach
    </div>

</div>

@push('scripts')
<script>
function logViewer() {
    return {
        activeChannel: 'laravel',
        activeFile: null,
        lines: [],
        loading: false,
        autoRefresh: false,
        timer: null,

        init() {
            this.loadChannel('laravel');
        },

        loadChannel(channel) {
            this.activeChannel = channel;
            this.activeFile    = null;
            this.lines         = [];
            this.fetch(channel, null);
        },

        loadFile(channel, file) {
            this.activeChannel = channel;
            this.activeFile    = file;
            this.fetch(channel, file);
        },

        reload() {
            this.fetch(this.activeChannel, this.activeFile);
        },

        fetch(channel, file) {
            this.loading = true;
            const url = `{{ route('admin.log-viewer.show', ':channel') }}`.replace(':channel', channel)
                + (file ? `?file=${encodeURIComponent(file)}` : '');
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => {
                    this.lines      = data.lines || [];
                    this.activeFile = data.file || this.activeFile;
                    this.loading    = false;
                    this.$nextTick(() => {
                        const el = document.getElementById('log-output');
                        if (el) el.scrollTop = el.scrollHeight;
                    });
                })
                .catch(() => { this.loading = false; });
        },

        toggleAutoRefresh() {
            if (this.autoRefresh) {
                this.timer = setInterval(() => this.reload(), 30000);
            } else {
                clearInterval(this.timer);
            }
        },
    };
}
</script>
@endpush
@endsection
