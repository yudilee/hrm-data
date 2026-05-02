<div
    x-data="{
        open: false,
        confirmed: false,
        openModal() { this.open = true; this.confirmed = false; },
        confirm() {
            this.confirmed = true;
            this.open = false;
            this.$refs.triggerForm?.submit();
        },
        cancel() { this.open = false; this.confirmed = false; }
    }"
    x-on:keydown.escape.window="cancel()"
>
    {{-- Trigger --}}
    <span @click="openModal()" class="cursor-pointer">
        {{ $trigger }}
    </span>

    {{-- Modal backdrop --}}
    <div x-show="open" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[70] bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4"
    >
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
            class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-700"
            @click.outside="cancel()"
        >
            <div class="flex items-start gap-4">
                <div class="shrink-0 w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/40 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $title ?? 'Confirm Action' }}</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $message ?? 'Are you sure you want to proceed? This action cannot be undone.' }}</p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button @click="cancel()" class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-xl transition-colors">
                    Cancel
                </button>
                <button @click="confirm()" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-sm transition-colors active:scale-95">
                    {{ $confirmText ?? 'Confirm' }}
                </button>
            </div>
        </div>
    </div>

    {{-- Hidden form that gets submitted on confirm --}}
    @if(isset($formAction))
    <form x-ref="triggerForm" method="POST" action="{{ $formAction }}" class="hidden">
        @csrf
        @if(isset($formMethod) && $formMethod !== 'POST')
            @method($formMethod)
        @endif
        {{ $formFields ?? '' }}
    </form>
    @endif
</div>
