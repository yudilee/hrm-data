<div
    x-data="{ open: @json($open ?? false), dismissed: false }"
    x-show="open && !dismissed"
    x-cloak
    x-transition
    class="relative rounded-2xl bg-gradient-to-br from-indigo-500 via-violet-500 to-purple-600 p-6 mb-6 text-white overflow-hidden shadow-xl"
>
    {{-- Background decoration --}}
    <div class="absolute inset-0 opacity-10">
        <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-white blur-2xl"></div>
        <div class="absolute -bottom-10 -left-10 w-32 h-32 rounded-full bg-white blur-2xl"></div>
    </div>

    <div class="relative">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="text-xl font-bold">{{ $title ?? 'Welcome to Master Data Hub' }}</h2>
                <div class="mt-2 text-sm text-white/80 leading-relaxed max-w-2xl">
                    {{ $slot }}
                </div>
            </div>
            <button @click="dismissed = true" class="shrink-0 p-1.5 rounded-lg hover:bg-white/10 transition-colors ml-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        @if(isset($actions))
            <div class="mt-4 flex flex-wrap gap-2">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
