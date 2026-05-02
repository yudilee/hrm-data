@extends('layouts.app')

@section('title', 'Create User')

@section('content')
<div class="max-w-lg">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
        @if($errors->any())
            <div class="mb-6 bg-red-100 dark:bg-red-900/30 border border-red-300 dark:border-red-700 text-red-700 dark:text-red-300 p-4 rounded-lg text-sm">
                <p class="font-medium mb-1">Please fix the following errors:</p>
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border rounded-lg text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 @error('name') border-red-400 dark:border-red-500 @else border-slate-300 dark:border-slate-600 @enderror">
                    <x-ui.field-error for="name" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border rounded-lg text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 @error('email') border-red-400 dark:border-red-500 @else border-slate-300 dark:border-slate-600 @enderror">
                    <x-ui.field-error for="email" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Role</label>
                    <select name="role" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border rounded-lg text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 @error('role') border-red-400 dark:border-red-500 @else border-slate-300 dark:border-slate-600 @enderror">
                        <option value="user">User</option>
                        <option value="invoice">Invoice</option>
                        <option value="admin">Admin</option>
                    </select>
                    <x-ui.field-error for="role" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Password</label>
                    <input type="password" name="password" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border rounded-lg text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 @error('password') border-red-400 dark:border-red-500 @else border-slate-300 dark:border-slate-600 @enderror">
                    <x-ui.field-error for="password" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border rounded-lg text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 @error('password_confirmation') border-red-400 dark:border-red-500 @else border-slate-300 dark:border-slate-600 @enderror">
                    <x-ui.field-error for="password_confirmation" />
                </div>
                <div class="flex gap-3 pt-2">
                    <a href="{{ route('admin.users.index') }}" class="flex-1 py-2.5 text-center bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">Cancel</a>
                    <button type="submit" class="flex-1 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">Create User</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
