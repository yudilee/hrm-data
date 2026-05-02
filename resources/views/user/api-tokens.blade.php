@extends('layouts.app')

@section('title', 'My API Tokens')
@section('subtitle', 'Create and manage your personal API access tokens')

@section('content')
<div class="space-y-6">

    {{-- New Token Alert --}}
    @if(session('new_token'))
    <div x-data="{ copied: false }" class="bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-400 dark:border-emerald-600 rounded-xl p-5">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div class="flex-1">
                <p class="font-semibold text-emerald-800 dark:text-emerald-300">Token "{{ session('new_token_name') }}" created</p>
                <p class="text-sm text-emerald-700 dark:text-emerald-400 mt-1">Copy this token now — it will <strong>not</strong> be shown again.</p>
                <div class="mt-3 flex items-center gap-2">
                    <code id="new-token-value" class="flex-1 bg-white dark:bg-slate-900 border border-emerald-300 dark:border-emerald-700 rounded-lg px-4 py-2 text-sm font-mono text-slate-800 dark:text-slate-200 break-all select-all">{{ session('new_token') }}</code>
                    <button @click="navigator.clipboard.writeText('{{ session('new_token') }}'); copied = true; setTimeout(() => copied = false, 2000)"
                        class="shrink-0 flex items-center gap-1.5 px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition-colors">
                        <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <svg x-show="copied" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Create Token Form --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800">
            <h2 class="text-lg font-semibold">Create New API Token</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Tokens grant you programmatic access to the API based on your current permissions.</p>
        </div>
        <form method="POST" action="{{ route('user.api-tokens.store') }}" class="p-6 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-1.5" for="token_name">Token Name</label>
                <input id="token_name" type="text" name="name" required placeholder="e.g. personal-script, my-mobile-app"
                    class="w-full md:w-1/2 rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                    value="{{ old('name') }}">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Abilities --}}
            <div>
                <label class="block text-sm font-medium mb-2">Token Abilities (Permissions)</label>
                @error('abilities') <p class="text-red-500 text-xs mb-2">{{ $message }}</p> @enderror
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($abilities as $key => $description)
                    <label class="flex items-start gap-3 p-3 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer transition-colors">
                        <input type="checkbox" name="abilities[]" value="{{ $key }}"
                            class="mt-0.5 rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500"
                            checked>
                        <div>
                            <p class="text-sm font-semibold font-mono text-indigo-600 dark:text-indigo-400">{{ $key }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $description }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-2">Tip: Uncheck abilities you do not need for this specific token to follow the principle of least privilege.</p>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm transition-colors">
                    Generate Token
                </button>
            </div>
        </form>
    </div>

    {{-- Active Tokens List --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h2 class="text-lg font-semibold">My Active Tokens</h2>
            <span class="text-sm text-slate-500">{{ $tokens->count() }} token{{ $tokens->count() !== 1 ? 's' : '' }}</span>
        </div>

        @if($tokens->isEmpty())
        <div class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
            <svg class="w-10 h-10 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            <p class="font-medium">No personal API tokens yet</p>
            <p class="text-sm mt-1">Create a token above to access the API programmatically.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-left">
                        <th class="px-6 py-3 font-semibold text-slate-500 dark:text-slate-400">Token Name</th>
                        <th class="px-6 py-3 font-semibold text-slate-500 dark:text-slate-400">Abilities</th>
                        <th class="px-6 py-3 font-semibold text-slate-500 dark:text-slate-400">Last Used</th>
                        <th class="px-6 py-3 font-semibold text-slate-500 dark:text-slate-400">Created</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($tokens as $token)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4 font-medium">{{ $token->name }}</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @foreach($token->abilities as $ability)
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-mono bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400">
                                    {{ $ability }}
                                </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-500">
                            {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Never' }}
                        </td>
                        <td class="px-6 py-4 text-slate-500">{{ $token->created_at->format('d M Y') }}</td>
                        <td class="px-6 py-4">
                            <form method="POST" action="{{ route('user.api-tokens.destroy', $token->id) }}"
                                onsubmit="return confirm('Revoke your token \"{{ $token->name }}\"? This cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 dark:hover:text-red-400 font-medium text-xs transition-colors">
                                    Revoke
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- How to Use Your Token --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
            <h2 class="text-lg font-semibold flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                How to Use Your Token
            </h2>
        </div>
        <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Authentication Section --}}
            <div class="space-y-4">
                <div>
                    <h4 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-2">1. Authentication Header</h4>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mb-3">Include your token in the <code class="bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded">Authorization</code> header as a Bearer token.</p>
                    <div class="bg-slate-900 rounded-lg p-4 font-mono text-xs text-indigo-300 border border-slate-700">
                        Authorization: Bearer <span class="text-emerald-400">your_token_here</span>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-2">2. Available Endpoints (v2)</h4>
                    <div class="space-y-2">
                        @php
                        $apiEndpoints = [
                            'GET /api/v2/customers' => 'read:customers',
                            'GET /api/v2/vehicles' => 'read:vehicles',
                            'GET /api/v2/service-histories' => 'read:service-histories',
                            'GET /api/v2/suppliers' => 'read:suppliers',
                            'GET /api/v2/labour-codes' => 'read:labour-codes',
                            'GET /api/v2/labour-codes?chassis=...' => 'filter by chassis',
                            'GET /api/v2/labour-codes?prefix=...' => 'filter by prefix',
                            'GET /api/v2/search' => 'search',
                        ];
                        @endphp
                        @foreach($apiEndpoints as $path => $ability)
                        <div class="flex items-center justify-between gap-4 p-2 rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700/50">
                            <code class="text-xs text-indigo-600 dark:text-indigo-400 font-bold">{{ $path }}</code>
                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400">{{ $ability }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Examples Section --}}
            <div class="space-y-4">
                <div>
                    <h4 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-2">3. cURL Example</h4>
                    <div class="bg-slate-900 rounded-lg p-4 font-mono text-[11px] text-slate-300 border border-slate-700 leading-relaxed overflow-x-auto">
                        <span class="text-purple-400">curl</span> -X GET <span class="text-emerald-400">"https://hrm-data.hartonomotor-group.com/api/v2/customers"</span> \<br>
                        &nbsp;&nbsp;&nbsp;&nbsp; -H <span class="text-emerald-400">"Authorization: Bearer your_token_here"</span> \<br>
                        &nbsp;&nbsp;&nbsp;&nbsp; -H <span class="text-emerald-400">"Accept: application/json"</span>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-2">4. Best Practices & Limits</h4>
                    <ul class="text-xs space-y-2.5 text-slate-600 dark:text-slate-400">
                        <li class="flex gap-2">
                            <span class="text-emerald-500 font-bold">✓</span>
                            <span><strong>Rate Limit:</strong> 60 requests per minute (Standard User).</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="text-emerald-500 font-bold">✓</span>
                            <span><strong>Expiration:</strong> Use specific expiry dates for temporary scripts.</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="text-amber-500 font-bold">!</span>
                            <span><strong>Security:</strong> Never commit tokens to public repositories (git).</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="text-indigo-500 font-bold">ℹ</span>
                            <span><strong>Audit:</strong> All requests are logged with your User ID and IP address.</span>
                        </li>
                    </ul>
                </div>
                
                <div class="pt-2">
                    <a href="/docs/api" target="_blank" class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                        View Full API Interactive Documentation
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-0L10 14"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
