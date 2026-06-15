<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\ExchangeRateException;
use App\Exceptions\PaymentRequestReviewException;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest\IndexPaymentRequestRequest;
use App\Http\Requests\PaymentRequest\StorePaymentRequestRequest;
use App\Models\PaymentRequest;
use App\Services\PaymentRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PaymentRequestController extends Controller
{
    public function __construct(
        protected PaymentRequestService $paymentRequestService,
    ) {}

    public function index(IndexPaymentRequestRequest $request): View
    {
        $user = $request->user();

        $query = PaymentRequest::query()->with(['user', 'approver'])->latest();

        if (! $user->isFinance()) {
            $query->where('user_id', $user->id);
        }

        $status = $request->validated('status');

        if ($status) {
            $query->where('status', $status);
        }

        return view('payment-requests.index', [
            'paymentRequests' => $query->paginate(10)->withQueryString(),
            'statusFilter' => $status,
        ]);
    }

    public function create(): View
    {
        return view('payment-requests.create', [
            'currencies' => config('currencies.supported'),
        ]);
    }

    public function store(StorePaymentRequestRequest $request): RedirectResponse
    {
        try {
            $paymentRequest = $this->paymentRequestService->create($request->user(), $request->validated());
        } catch (ExchangeRateException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('payment-requests.show', $paymentRequest)
            ->with('success', 'Payment request created successfully.');
    }

    public function show(PaymentRequest $paymentRequest): View
    {
        Gate::authorize('view', $paymentRequest);

        $paymentRequest->load(['user', 'approver']);

        return view('payment-requests.show', compact('paymentRequest'));
    }

    public function approve(Request $request, PaymentRequest $paymentRequest): RedirectResponse
    {
        return $this->review($request, $paymentRequest, PaymentRequest::STATUS_APPROVED);
    }

    public function reject(Request $request, PaymentRequest $paymentRequest): RedirectResponse
    {
        return $this->review($request, $paymentRequest, PaymentRequest::STATUS_REJECTED);
    }

    protected function review(Request $request, PaymentRequest $paymentRequest, string $status): RedirectResponse
    {
        try {
            $this->paymentRequestService->review($paymentRequest, $request->user(), $status);
        } catch (PaymentRequestReviewException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', "Payment request {$status} successfully.");
    }
}
