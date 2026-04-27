@extends('layouts.app')

@section('title', 'Token Requests')
@section('subtitle', 'Review and approve API access requests from external partners')

@section('content')
<div class="space-y-6">

    {{-- Pending Requests --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-amber-50 dark:bg-amber-900/20">
            <h3 class="font-semibold text-amber-800 dark:text-amber-300">⏳ Pending Requests</h3>
            <span class="text-sm text-amber-700 dark:text-amber-400">{{ $pending->count() }} awaiting review</span>
        </div>

        @if($pending->isEmpty())
        <div class="p-10 text-center text-slate-400">No pending requests. You're all caught up!</div>
        @else
        <div class="divide-y divide-slate-100 dark:divide-slate-800">
            @foreach($pending as $req)
            <div x-data="{ open: false }" class="p-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $req->name }}
                            @if($req->company)<span class="text-slate-400 font-normal text-sm ml-1">— {{ $req->company }}</span>@endif
                        </p>
                        <p class="text-sm text-indigo-600 dark:text-indigo-400">{{ $req->email }}</p>
                        <p class="text-xs text-slate-500 mt-1">Submitted {{ $req->created_at->diffForHumans() }}</p>
                        <div class="mt-2 flex flex-wrap gap-1">
                            @foreach($req->requested_abilities as $ability)
                            <span class="px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 text-xs font-mono">{{ $ability }}</span>
                            @endforeach
                        </div>
                    </div>
                    <button @click="open = !open" class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 font-medium shrink-0">
                        Review →
                    </button>
                </div>

                {{-- Review Panel --}}
                <div x-show="open" x-cloak class="mt-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 space-y-4">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase mb-1">Use Case Description</p>
                        <p class="text-sm text-slate-700 dark:text-slate-300">{{ $req->use_case }}</p>
                    </div>

                    {{-- Approve Form --}}
                    <form method="POST" action="{{ route('admin.token-requests.approve', $req) }}" class="space-y-3 border-t border-slate-200 dark:border-slate-700 pt-3">
                        @csrf
                        <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-400">Approve — Configure Token</p>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium mb-1">Grant Abilities</label>
                                <div class="space-y-1">
                                    @foreach($req->requested_abilities as $ability)
                                    <label class="flex items-center gap-2 text-xs">
                                        <input type="checkbox" name="abilities[]" value="{{ $ability }}" checked class="rounded">
                                        <span class="font-mono">{{ $ability }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div>
                                    <label class="block text-xs font-medium mb-1">Expiry Date (optional)</label>
                                    <input type="date" name="expires_at"
                                           class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-2 py-1 text-xs">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium mb-1">Admin Notes (sent to requester)</label>
                                    <textarea name="notes" rows="2"
                                              class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-2 py-1 text-xs"></textarea>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium">✓ Approve & Send Token</button>
                    </form>

                    {{-- Reject Form --}}
                    <form method="POST" action="{{ route('admin.token-requests.reject', $req) }}" class="border-t border-slate-200 dark:border-slate-700 pt-3 space-y-2">
                        @csrf
                        <div class="flex gap-2 items-end">
                            <div class="flex-1">
                                <label class="block text-xs font-medium mb-1 text-red-600">Reject — Reason (optional)</label>
                                <input type="text" name="notes" placeholder="e.g. Insufficient justification"
                                       class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-2 py-1 text-xs">
                            </div>
                            <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-medium shrink-0">✗ Reject</button>
                        </div>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Reviewed --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="font-semibold">Reviewed Requests</h3>
        </div>
        @if($reviewed->isEmpty())
        <div class="p-10 text-center text-slate-400">No reviewed requests yet.</div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-left">
                        @foreach(['Name', 'Email', 'Company', 'Status', 'Reviewed By', 'Date'] as $col)
                        <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                    @foreach($reviewed as $req)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-3 font-medium">{{ $req->name }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $req->email }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ $req->company ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold
                                {{ $req->status === 'approved' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                                {{ ucfirst($req->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-slate-500">{{ $req->reviewer?->name ?? 'System' }}</td>
                        <td class="px-5 py-3 text-slate-500 text-xs">{{ $req->reviewed_at?->format('d M Y') ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800">{{ $reviewed->links() }}</div>
        @endif
    </div>
</div>
@endsection
