@extends('layouts.app')

@section('title', 'Dashboard')

@php
    $statuses = ['' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'expired' => 'Expired'];
@endphp

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Payment Requests</h1>
            <p class="mt-1 text-sm text-slate-500">
                @if (auth()->user()->isFinance())
                    All payment requests submitted across the company.
                @else
                    Your submitted payment requests.
                @endif
            </p>
        </div>
        <a href="{{ route('payment-requests.create') }}"
           class="inline-flex items-center justify-center rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700">
            + New Request
        </a>
    </div>

    <div class="mt-6 flex flex-wrap gap-2">
        @foreach ($statuses as $value => $label)
            <a href="{{ route('dashboard', $value ? ['status' => $value] : []) }}"
               class="rounded-full px-3 py-1.5 text-sm font-medium {{ $statusFilter === $value || (! $statusFilter && $value === '') ? 'bg-brand-600 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="mt-6 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Date</th>
                    @if (auth()->user()->isFinance())
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Requester</th>
                    @endif
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Description</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Amount</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">EUR Equivalent</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse ($paymentRequests as $paymentRequest)
                    <tr class="hover:bg-slate-50">
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500">{{ $paymentRequest->created_at->format('M j, Y') }}</td>
                        @if (auth()->user()->isFinance())
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-700">{{ $paymentRequest->user->name }}</td>
                        @endif
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $paymentRequest->description ?: '—' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-slate-900">{{ $paymentRequest->amount }} {{ $paymentRequest->currency }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-slate-500">&euro;{{ $paymentRequest->amount_eur }}</td>
                        <td class="whitespace-nowrap px-4 py-3"><x-status-badge :status="$paymentRequest->status" /></td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                            <a href="{{ route('payment-requests.show', $paymentRequest) }}" class="font-medium text-brand-600 hover:text-brand-700">View</a>

                            @if (auth()->user()->isFinance() && $paymentRequest->isPending())
                                <form method="POST" action="{{ route('payment-requests.approve', $paymentRequest) }}" class="ml-3 inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="font-medium text-emerald-600 hover:text-emerald-700">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('payment-requests.reject', $paymentRequest) }}" class="ml-3 inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="font-medium text-rose-600 hover:text-rose-700">Reject</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->isFinance() ? 7 : 6 }}" class="px-4 py-10 text-center text-sm text-slate-500">
                            No payment requests found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $paymentRequests->links() }}
    </div>
@endsection
