@extends('layouts.app')

@section('title', 'API Token Management')
@section('subtitle', 'Create and revoke API access tokens for external integrations')

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
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Tokens grant read-only access to the Master Data Hub API v2.</p>
        </div>
        <form method="POST" action="{{ route('admin.api-tokens.store') }}" class="p-6 space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- User --}}
                <div>
                    <label class="block text-sm font-medium mb-1.5" for="user_id">Token Owner</label>
                    <select id="user_id" name="user_id" required
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }}) — {{ ucfirst($user->role) }}</option>
                        @endforeach
                    </select>
                    @error('user_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Token Name --}}
                <div>
                    <label class="block text-sm font-medium mb-1.5" for="token_name">Token Name</label>
                    <input id="token_name" type="text" name="name" required placeholder="e.g. odoo-sync, mobile-app, dashboard"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                        value="{{ old('name') }}">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
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
                            {{ $key === '*' ? '' : 'checked' }}
                            {{ in_array($key, old('abilities', []), true) ? 'checked' : '' }}>
                        <div>
                            <p class="text-sm font-semibold font-mono text-indigo-600 dark:text-indigo-400">{{ $key }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $description }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-2">Tip: Select specific abilities for least-privilege access. Use <code>*</code> only for trusted integrations.</p>
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
            <h2 class="text-lg font-semibold">Active Tokens</h2>
            <span class="text-sm text-slate-500">{{ $tokens->count() }} token{{ $tokens->count() !== 1 ? 's' : '' }}</span>
        </div>

        @if($tokens->isEmpty())
        <div class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
            <svg class="w-10 h-10 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            <p class="font-medium">No API tokens yet</p>
            <p class="text-sm mt-1">Create a token above to allow external systems to access the API.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-left">
                        <th class="px-6 py-3 font-semibold text-slate-500 dark:text-slate-400">Token Name</th>
                        <th class="px-6 py-3 font-semibold text-slate-500 dark:text-slate-400">Owner</th>
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
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                            {{ $token->tokenable?->name ?? 'Unknown' }}
                            <span class="text-xs text-slate-400">{{ $token->tokenable?->email }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @foreach($token->abilities as $ability)
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-mono
                                    {{ $ability === '*' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400' : 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400' }}">
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
                            <form method="POST" action="{{ route('admin.api-tokens.destroy', $token->id) }}"
                                onsubmit="return confirm('Revoke token \"{{ $token->name }}\"? This cannot be undone.')">
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

    {{-- API Quick Reference --}}
    <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6">
        <h3 class="font-semibold mb-3 text-slate-700 dark:text-slate-300">Quick Reference — API v2 Endpoints</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm font-mono">
            @php
            $endpoints = [
                'GET /api/health'                            => 'Health check (no auth)',
                'GET /api/v2/auth/me'                        => 'Current token info',
                'GET /api/v2/customers'                      => 'List customers',
                'GET /api/v2/customers/{id}'                 => 'Show customer',
                'GET /api/v2/vehicles'                       => 'List vehicles',
                'GET /api/v2/vehicles/{id}'                  => 'Show vehicle',
                'GET /api/v2/vehicles/{id}/service-history'  => 'Vehicle history',
                'GET /api/v2/service-histories'              => 'List service records',
                'GET /api/v2/service-histories/{id}'         => 'Show service record',
                'GET /api/v2/suppliers'                      => 'List suppliers',
                'GET /api/v2/labour-codes'                   => 'Search labour codes',
                'GET /api/v2/search?q='                      => 'Global search',
            ];
            @endphp
            @foreach($endpoints as $endpoint => $desc)
            <div class="flex items-baseline gap-2">
                <code class="text-indigo-600 dark:text-indigo-400 text-xs">{{ $endpoint }}</code>
                <span class="text-slate-500 dark:text-slate-400 text-xs shrink-0">— {{ $desc }}</span>
            </div>
            @endforeach
        </div>
        <p class="mt-4 text-xs text-slate-500">
            Use <code class="bg-slate-200 dark:bg-slate-800 px-1 rounded">Authorization: Bearer {token}</code> header.
            Full docs at <a href="/docs/api" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">/docs/api</a>.
        </p>
    </div>

</div>
@endsection
