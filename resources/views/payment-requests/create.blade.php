@extends('layouts.app')

@section('title', 'New Payment Request')

@section('content')
    <div class="mx-auto max-w-xl">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">New Payment Request</h1>
        <p class="mt-1 text-sm text-slate-500">
            Submit a payment request in your local currency. The current EUR exchange rate is fetched
            automatically and stored permanently with your request.
        </p>

        <form method="POST" action="{{ route('payment-requests.store') }}" class="mt-6 space-y-5 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            @csrf

            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-2">
                    <label for="amount" class="block text-sm font-medium text-slate-700">Amount</label>
                    <input id="amount" name="amount" type="number" step="0.01" min="0.01" max="999999999.99" required
                           value="{{ old('amount') }}"
                           class="mt-1 block w-full rounded-md border-0 px-3 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm">
                </div>

                <div>
                    <label for="currency" class="block text-sm font-medium text-slate-700">Currency</label>
                    <select id="currency" name="currency" required
                            class="mt-1 block w-full rounded-md border-0 px-3 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm">
                        @foreach ($currencies as $currency)
                            <option value="{{ $currency }}" @selected(old('currency', auth()->user()->currency) === $currency)>{{ $currency }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-slate-700">Description <span class="text-slate-400">(optional)</span></label>
                <textarea id="description" name="description" rows="3" maxlength="255"
                          class="mt-1 block w-full rounded-md border-0 px-3 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm"
                          placeholder="e.g. Conference travel expenses">{{ old('description') }}</textarea>
            </div>

            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-xs text-slate-500">
                Once submitted, this request will be marked <span class="font-semibold text-slate-700">pending</span> and
                automatically <span class="font-semibold text-slate-700">expire after 48 hours</span> if not reviewed by
                the finance team.
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('dashboard') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">Cancel</a>
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700">
                    Submit request
                </button>
            </div>
        </form>
    </div>
@endsection
