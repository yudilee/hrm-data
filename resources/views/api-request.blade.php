<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request API Access — {{ config('app.name') }}</title>
    <meta name="description" content="Request read-only API access to the Master Data Hub to integrate with your systems.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 min-h-screen flex items-center justify-center p-6">

<div class="w-full max-w-xl" x-data="requestForm()">

    {{-- Logo / Header --}}
    <div class="text-center mb-8">
        <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-indigo-500/30">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-white">Request API Access</h1>
        <p class="text-slate-400 mt-1 text-sm">{{ config('app.name') }} — Master Data Hub</p>
    </div>

    {{-- Success State --}}
    <div x-show="submitted" x-cloak class="bg-emerald-900/30 border border-emerald-500 rounded-2xl p-8 text-center">
        <div class="w-12 h-12 bg-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-emerald-300 mb-2">Request Submitted!</h2>
        <p class="text-emerald-400 text-sm">We'll review your request and email you at <strong x-text="email"></strong> once it's processed. This usually takes 1–2 business days.</p>
    </div>

    {{-- Form --}}
    <div x-show="!submitted" class="bg-white/5 backdrop-blur border border-white/10 rounded-2xl p-8 space-y-5">
        <div x-show="error" x-cloak class="bg-red-900/30 border border-red-500 rounded-lg p-3 text-sm text-red-300" x-text="error"></div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">Full Name *</label>
                <input x-model="form.name" type="text" required
                       class="w-full bg-slate-800/60 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="John Doe">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">Work Email *</label>
                <input x-model="form.email" type="email" required
                       class="w-full bg-slate-800/60 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="you@company.com">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1.5">Company / Organisation</label>
            <input x-model="form.company" type="text"
                   class="w-full bg-slate-800/60 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   placeholder="Acme Corp">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1.5">How will you use the API? *</label>
            <textarea x-model="form.use_case" required rows="4"
                      class="w-full bg-slate-800/60 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                      placeholder="Describe your integration purpose, expected call volume, and data you need to access..."></textarea>
            <p class="text-xs text-slate-500 mt-1">Minimum 20 characters.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Data Access Required *</label>
            <div class="grid grid-cols-1 gap-2">
                @foreach($abilities as $key => $desc)
                @if($key !== '*')
                <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-700 hover:border-indigo-500 cursor-pointer transition-colors"
                       :class="form.requested_abilities.includes('{{ $key }}') ? 'border-indigo-500 bg-indigo-900/20' : ''">
                    <input type="checkbox" value="{{ $key }}"
                           @change="toggleAbility('{{ $key }}')"
                           class="mt-0.5 rounded border-slate-600 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <p class="text-sm font-mono text-indigo-400">{{ $key }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $desc }}</p>
                    </div>
                </label>
                @endif
                @endforeach
            </div>
        </div>

        <button @click="submit" :disabled="loading"
                class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-wait text-white font-semibold text-sm transition-colors">
            <span x-text="loading ? 'Submitting...' : 'Submit Request'"></span>
        </button>

        <p class="text-center text-xs text-slate-500">
            Already have a token? Use <code class="bg-slate-800 px-1 py-0.5 rounded">Authorization: Bearer {token}</code>.
            Need help? Contact <a href="mailto:{{ config('mail.from.address') }}" class="text-indigo-400 hover:underline">{{ config('mail.from.address') }}</a>.
        </p>
    </div>
</div>

<script src="https://unpkg.com/alpinejs@3/dist/cdn.min.js" defer></script>
<script>
function requestForm() {
    return {
        submitted: false,
        loading: false,
        error: null,
        email: '',
        form: {
            name: '',
            email: '',
            company: '',
            use_case: '',
            requested_abilities: [],
        },

        toggleAbility(key) {
            const idx = this.form.requested_abilities.indexOf(key);
            if (idx >= 0) {
                this.form.requested_abilities.splice(idx, 1);
            } else {
                this.form.requested_abilities.push(key);
            }
        },

        async submit() {
            this.error = null;

            if (!this.form.name || !this.form.email || !this.form.use_case || this.form.requested_abilities.length === 0) {
                this.error = 'Please fill in all required fields and select at least one data access permission.';
                return;
            }

            this.loading = true;
            this.email   = this.form.email;

            try {
                const res = await fetch('{{ url("/api/token-requests") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(this.form),
                });
                const data = await res.json();

                if (data.success) {
                    this.submitted = true;
                } else {
                    this.error = data.error || data.message || 'Something went wrong. Please try again.';
                }
            } catch (e) {
                this.error = 'Network error. Please check your connection and try again.';
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>
</body>
</html>
