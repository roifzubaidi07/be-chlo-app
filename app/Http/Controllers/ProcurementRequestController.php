<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveProcurementRequestRequest;
use App\Http\Requests\IndexProcurementRequestRequest;
use App\Http\Requests\ProcureProcurementRequestRequest;
use App\Http\Requests\RejectProcurementRequestRequest;
use App\Http\Requests\StoreProcurementRequestRequest;
use App\Http\Resources\ProcurementRequestResource;
use App\Models\Approval;
use App\Models\ProcurementOrder;
use App\Models\ProcurementRequest;
use App\Models\StatusHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ProcurementRequestController extends Controller
{
    public function index(IndexProcurementRequestRequest $request): JsonResponse
    {
        $this->authorize('viewAny', ProcurementRequest::class);

        $query = ProcurementRequest::query()
            ->with(['requester', 'requestItems.item'])
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return ProcurementRequestResource::collection($query->paginate(20))->response();
    }

    public function store(StoreProcurementRequestRequest $request): JsonResponse
    {
        $this->authorize('create', ProcurementRequest::class);

        $submit = $request->boolean('submit');
        $initialStatus = $submit
            ? ProcurementRequest::STATUS_SUBMITTED
            : ProcurementRequest::STATUS_DRAFT;

        $procurementRequest = DB::transaction(function () use ($request, $initialStatus) {
            $code = $this->generateRequestCode();

            $pr = ProcurementRequest::query()->create([
                'code' => $code,
                'user_request_id' => $request->user()->id,
                'status' => $initialStatus,
                'lock_version' => 0,
                'created_at' => now(),
            ]);

            $this->recordHistory($pr, null, $initialStatus);

            foreach ($request->input('items', []) as $index => $row) {
                $lineCode = $row['code'] ?? 'L'.($index + 1);
                $pr->requestItems()->create([
                    'code' => $lineCode,
                    'item_id' => $row['item_id'],
                    'qty' => $row['qty'],
                    'discount' => $row['discount'] ?? 0,
                    'tax' => $row['tax'] ?? 0,
                ]);
            }

            return $pr->load(['requestItems.item', 'requester']);
        });

        return (new ProcurementRequestResource($procurementRequest))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(ProcurementRequest $procurementRequest): JsonResponse
    {
        $this->authorize('view', $procurementRequest);

        $procurementRequest->load([
            'requester',
            'requestItems.item',
            'approvals.approver',
            'procurementOrders.vendor',
        ]);

        return (new ProcurementRequestResource($procurementRequest))->response();
    }

    public function approve(ApproveProcurementRequestRequest $request, ProcurementRequest $procurementRequest): JsonResponse
    {
        DB::transaction(function () use ($request, $procurementRequest) {
            $locked = ProcurementRequest::query()
                ->whereKey($procurementRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== ProcurementRequest::STATUS_SUBMITTED) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Request is not awaiting approval.');
            }

            if (Approval::query()
                ->where('request_id', $locked->id)
                ->where('user_approval_id', $request->user()->id)
                ->exists()) {
                abort(Response::HTTP_CONFLICT, 'You have already acted on this request.');
            }

            $approvalCode = 'A'.($locked->approvals()->count() + 1);

            Approval::query()->create([
                'code' => $approvalCode,
                'request_id' => $locked->id,
                'user_approval_id' => $request->user()->id,
                'status' => 'approved',
            ]);

            $from = $locked->status;
            $locked->status = ProcurementRequest::STATUS_APPROVED;
            $locked->lock_version = $locked->lock_version + 1;
            $locked->save();

            $this->recordHistory($locked, $from, ProcurementRequest::STATUS_APPROVED);
        });

        $procurementRequest->refresh()->load(['requester', 'requestItems.item', 'approvals.approver']);

        return (new ProcurementRequestResource($procurementRequest))->response();
    }

    public function reject(RejectProcurementRequestRequest $request, ProcurementRequest $procurementRequest): JsonResponse
    {
        DB::transaction(function () use ($procurementRequest) {
            $locked = ProcurementRequest::query()
                ->whereKey($procurementRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== ProcurementRequest::STATUS_SUBMITTED) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Request cannot be rejected in its current state.');
            }

            $from = $locked->status;
            $locked->status = ProcurementRequest::STATUS_REJECTED;
            $locked->lock_version = $locked->lock_version + 1;
            $locked->save();

            $this->recordHistory($locked, $from, ProcurementRequest::STATUS_REJECTED);
        });

        $procurementRequest->refresh()->load(['requester', 'requestItems.item']);

        return (new ProcurementRequestResource($procurementRequest))->response();
    }

    public function procure(ProcureProcurementRequestRequest $request, ProcurementRequest $procurementRequest): JsonResponse
    {
        DB::transaction(function () use ($request, $procurementRequest) {
            $locked = ProcurementRequest::query()
                ->whereKey($procurementRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== ProcurementRequest::STATUS_APPROVED) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Request must be approved before procurement.');
            }

            $poNumber = $request->input('po_number') ?: $this->generatePoNumber();

            ProcurementOrder::query()->create([
                'request_id' => $locked->id,
                'vendor_id' => (int) $request->input('vendor_id'),
                'po_number' => $poNumber,
                'status' => 'ORDERED',
            ]);

            $from = $locked->status;
            $locked->status = ProcurementRequest::STATUS_IN_PROCUREMENT;
            $locked->lock_version = $locked->lock_version + 1;
            $locked->save();

            $this->recordHistory($locked, $from, ProcurementRequest::STATUS_IN_PROCUREMENT);
        });

        $procurementRequest->refresh()->load(['requester', 'requestItems.item', 'procurementOrders.vendor']);

        return (new ProcurementRequestResource($procurementRequest))->response();
    }

    private function generateRequestCode(): string
    {
        $year = (int) date('Y');
        $prefix = sprintf('REQ-%d-', $year);

        $last = ProcurementRequest::query()
            ->where('code', 'like', $prefix.'%')
            ->orderByDesc('code')
            ->value('code');

        $next = 1;
        if ($last !== null && preg_match('/^'.preg_quote($prefix, '/').'(\d+)$/', $last, $m)) {
            $next = (int) $m[1] + 1;
        }

        return $prefix.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    private function generatePoNumber(): string
    {
        $year = (int) date('Y');
        $prefix = sprintf('PO-%d-', $year);

        $last = ProcurementOrder::query()
            ->where('po_number', 'like', $prefix.'%')
            ->orderByDesc('po_number')
            ->value('po_number');

        $next = 1;
        if ($last !== null && preg_match('/^'.preg_quote($prefix, '/').'(\d+)$/', $last, $m)) {
            $next = (int) $m[1] + 1;
        }

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function recordHistory(ProcurementRequest $procurementRequest, ?string $from, string $to): void
    {
        StatusHistory::query()->create([
            'request_id' => $procurementRequest->id,
            'changed_by_user_id' => auth()->id(),
            'from_status' => $from,
            'to_status' => $to,
            'created_at' => now(),
        ]);
    }
}
