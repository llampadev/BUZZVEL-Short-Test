@extends('layouts.guest')

@section('title', 'Create your account')

@section('content')
    <form method="POST" action="{{ route('register.store') }}" class="space-y-5">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700">Full name</label>
            <input id="name" name="name" type="text" required autofocus
                   value="{{ old('name') }}"
                   class="mt-1 block w-full rounded-md border-0 px-3 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm">
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">Email address</label>
            <input id="email" name="email" type="email" autocomplete="email" required
                   value="{{ old('email') }}"
                   class="mt-1 block w-full rounded-md border-0 px-3 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required
                   class="mt-1 block w-full rounded-md border-0 px-3 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm">
            <p class="mt-1 text-xs text-slate-400">At least 8 characters.</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="country" class="block text-sm font-medium text-slate-700">Country</label>
                <input id="country" name="country" type="text" required
                       value="{{ old('country') }}"
                       class="mt-1 block w-full rounded-md border-0 px-3 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm">
            </div>

            <div>
                <label for="currency" class="block text-sm font-medium text-slate-700">Currency</label>
                <select id="currency" name="currency" required
                        class="mt-1 block w-full rounded-md border-0 px-3 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm">
                    <option value="">Select&hellip;</option>
                    @foreach (config('currencies.supported') as $currency)
                        <option value="{{ $currency }}" @selected(old('currency') === $currency)>{{ $currency }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label for="role" class="block text-sm font-medium text-slate-700">Role</label>
            <select id="role" name="role"
                    class="mt-1 block w-full rounded-md border-0 px-3 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm">
                <option value="employee" @selected(old('role', 'employee') === 'employee')>Employee</option>
                <option value="finance" @selected(old('role') === 'finance')>Finance team</option>
            </select>
            <p class="mt-1 text-xs text-slate-400">Finance team members can approve or reject payment requests.</p>
        </div>

        <button type="submit"
                class="flex w-full justify-center rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600">
            Create account
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Already have an account?
        <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:text-brand-700">Sign in</a>
    </p>
@endsection
