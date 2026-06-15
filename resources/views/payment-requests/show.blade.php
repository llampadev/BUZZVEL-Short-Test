@extends('layouts.app')

@section('title', 'Payment Request #'.$paymentRequest->id)

@section('content')
    <div class="mx-auto max-w-2xl">
        <a href="{{ route('dashboard') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">&larr; Back to dashboard</a>

        <div class="mt-4 flex items-center justify-between">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Payment Request #{{ $paymentRequest->id }}</h1>
            <x-status-badge :status="$paymentRequest->status" class="text-sm" />
        </div>

        <div class="mt-6 rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="grid grid-cols-1 gap-6 p-6 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Requester</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $paymentRequest->user->name }}</p>
                    <p class="text-sm text-slate-500">{{ $paymentRequest->user->email }} &middot; {{ $paymentRequest->user->country }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Amount Requested</p>
                    <p class="mt-1 text-lg font-semibold text-slate-900">{{ $paymentRequest->amount }} {{ $paymentRequest->currency }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Amount in EUR</p>
                    <p class="mt-1 text-lg font-semibold text-slate-900">&euro;{{ $paymentRequest->amount_eur }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Exchange Rate (EUR &rarr; {{ $paymentRequest->currency }})</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $paymentRequest->exchange_rate }}</p>
                    <p class="text-xs text-slate-500">
                        Source: <span class="break-all">{{ $paymentRequest->exchange_rate_source }}</span><br>
                        Fetched: {{ $paymentRequest->exchange_rate_fetched_at->format('M j, Y H:i') }} UTC
                    </p>
                </div>

                <div class="sm:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Description</p>
                    <p class="mt-1 text-sm text-slate-700">{{ $paymentRequest->description ?: '—' }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Submitted</p>
                    <p class="mt-1 text-sm text-slate-700">{{ $paymentRequest->created_at->format('M j, Y H:i') }} UTC</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                        @if ($paymentRequest->isPending())
                            Expires
                        @else
                            Expired
                        @endif
                    </p>
                    <p class="mt-1 text-sm text-slate-700">{{ $paymentRequest->expires_at->format('M j, Y H:i') }} UTC</p>
                </div>

                @if ($paymentRequest->approver)
                    <div class="sm:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Reviewed By</p>
                        <p class="mt-1 text-sm text-slate-700">
                            {{ $paymentRequest->approver->name }} on {{ $paymentRequest->approved_at->format('M j, Y H:i') }} UTC
                        </p>
                    </div>
                @endif
            </div>

            @if (auth()->user()->isFinance() && $paymentRequest->isPending())
                <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4">
                    <form method="POST" action="{{ route('payment-requests.reject', $paymentRequest) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-rose-600 shadow-sm ring-1 ring-inset ring-rose-200 hover:bg-rose-50">
                            Reject
                        </button>
                    </form>
                    <form method="POST" action="{{ route('payment-requests.approve', $paymentRequest) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                            Approve
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection
