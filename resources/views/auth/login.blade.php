@extends('layouts.guest')

@section('title', 'Sign in to your account')

@section('content')
    <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">Email address</label>
            <input id="email" name="email" type="email" autocomplete="email" required autofocus
                   value="{{ old('email') }}"
                   class="mt-1 block w-full rounded-md border-0 px-3 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required
                   class="mt-1 block w-full rounded-md border-0 px-3 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm">
        </div>

        <div class="flex items-center">
            <input id="remember" name="remember" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-600">
            <label for="remember" class="ml-2 block text-sm text-slate-600">Remember me</label>
        </div>

        <button type="submit"
                class="flex w-full justify-center rounded-md bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600">
            Sign in
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Not a member?
        <a href="{{ route('register') }}" class="font-semibold text-brand-600 hover:text-brand-700">Create an account</a>
    </p>

    <div class="mt-6 rounded-lg border border-slate-200 bg-slate-50 p-4 text-xs text-slate-500">
        <p class="mb-1 font-semibold text-slate-600">Seeded demo accounts (password: <code class="font-mono">password</code>)</p>
        <p>Employee: maria.costa@buzzvel.com</p>
        <p>Finance: anna.mueller@buzzvel.com</p>
    </div>
@endsection
