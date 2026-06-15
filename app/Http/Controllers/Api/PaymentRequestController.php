<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ExchangeRateException;
use App\Exceptions\PaymentRequestReviewException;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest\IndexPaymentRequestRequest;
use App\Http\Requests\PaymentRequest\StorePaymentRequestRequest;
use App\Http\Resources\PaymentRequestResource;
use App\Models\PaymentRequest;
use App\Services\PaymentRequestService;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

#[Group('Payment Requests', weight: 2)]
class PaymentRequestController extends Controller
{
    public function __construct(
        protected PaymentRequestService $paymentRequestService,
    ) {}

    /**
     * List payment requests
     *
     * Employees only see their own payment requests; finance users see
     * requests submitted by everyone. Results are paginated (15 per page)
     * and can be filtered by `status`.
     */
    public function index(IndexPaymentRequestRequest $request): JsonResponse
    {
        $user = $request->user();

        $query = PaymentRequest::query()->with(['user', 'approver'])->latest();

        if (! $user->isFinance()) {
            $query->where('user_id', $user->id);
        }

        if ($status = $request->validated('status')) {
            $query->where('status', $status);
        }

        $paymentRequests = $query->paginate(15);

        return response()->json([
            'data' => PaymentRequestResource::collection($paymentRequests->items()),
            'meta' => [
                'current_page' => $paymentRequests->currentPage(),
                'last_page' => $paymentRequests->lastPage(),
                'per_page' => $paymentRequests->perPage(),
                'total' => $paymentRequests->total(),
            ],
        ]);
    }

    /**
     * Create a payment request
     *
     * Submits a new payment request in the given currency. The EUR exchange
     * rate is fetched automatically at creation time and stored immutably
     * (rate, source and timestamp), along with the converted `amount_eur`.
     * The request is created with `pending` status and expires automatically
     * after 48 hours if not reviewed.
     */
    public function store(StorePaymentRequestRequest $request): JsonResponse
    {
        try {
            $paymentRequest = $this->paymentRequestService->create($request->user(), $request->validated());
        } catch (ExchangeRateException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 503);
        }

        $paymentRequest->load(['user', 'approver']);

        return response()->json([
            'message' => 'Payment request created successfully.',
            'data' => new PaymentRequestResource($paymentRequest),
        ], 201);
    }

    /**
     * Get a payment request
     *
     * Returns the details of a single payment request. Only the owner of
     * the request or a finance user may view it.
     */
    public function show(Request $request, PaymentRequest $paymentRequest): JsonResponse
    {
        Gate::authorize('view', $paymentRequest);

        $paymentRequest->load(['user', 'approver']);

        return response()->json([
            'data' => new PaymentRequestResource($paymentRequest),
        ]);
    }

    /**
     * Approve a payment request
     *
     * Marks a `pending` payment request as `approved`. Only users with the
     * `finance` role may perform this action. Returns `403` if the user is
     * not finance, or `409` if the request is no longer pending.
     */
    public function approve(Request $request, PaymentRequest $paymentRequest): JsonResponse
    {
        return $this->review($request, $paymentRequest, PaymentRequest::STATUS_APPROVED);
    }

    /**
     * Reject a payment request
     *
     * Marks a `pending` payment request as `rejected`. Only users with the
     * `finance` role may perform this action. Returns `403` if the user is
     * not finance, or `409` if the request is no longer pending.
     */
    public function reject(Request $request, PaymentRequest $paymentRequest): JsonResponse
    {
        return $this->review($request, $paymentRequest, PaymentRequest::STATUS_REJECTED);
    }

    protected function review(Request $request, PaymentRequest $paymentRequest, string $status): JsonResponse
    {
        try {
            $this->paymentRequestService->review($paymentRequest, $request->user(), $status);
        } catch (PaymentRequestReviewException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], $exception->statusCode);
        }

        $paymentRequest->load(['user', 'approver']);

        return response()->json([
            'message' => "Payment request {$status} successfully.",
            'data' => new PaymentRequestResource($paymentRequest),
        ]);
    }
}
