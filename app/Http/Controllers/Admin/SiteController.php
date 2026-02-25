<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Store\SiteRequest as StoreRequest;
use App\Http\Requests\Update\SiteRequest as UpdateRequest;
use App\Models\Site;
use App\Services\SiteService as Service;
use App\Services\TransactionService;
use App\Services\WithdrawalService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SiteController extends BaseController
{
    private Service $service;
    private TransactionService $transactionService;
    private WithdrawalService $withdrawalService;

    public function __construct(
        Service            $service,
        TransactionService $transactionService,
        WithdrawalService  $withdrawalService
    ) {
        $this->middleware('permission:sites-index|sites-create|sites-edit', ['only' => ['index']]);
        $this->middleware('permission:sites-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:sites-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:sites-delete', ['only' => ['destroy']]);

        $this->service = $service;
        $this->transactionService = $transactionService;
        $this->withdrawalService = $withdrawalService;
        $this->module = 'sites';
    }

    public function index(): Renderable
    {
        $this->data = [
            'module' => __('Sites'),
            'title' => __('List'),
            'items' => $this->service->paginate(),
        ];

        return $this->render('list');
    }

    public function create(): Renderable
    {
        $this->data = [
            'title' => __('Create'),
            'module' => __('Sites'),
            'method' => 'POST',
            'action' => route('admin.' . $this->module . '.store'),
        ];

        return $this->render('form');
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();

        // Handle image upload
        if ($request->hasFile('logo')) {
            $validatedData['logo'] = $this->handleImageUpload($request->file('logo'));
        }

        // Create site without token first
        $item = $this->service->create($validatedData);

        // Generate a unique API token for this site
        if (!$item->token) {
            do {
                $token = Str::random(64);
            } while (Site::where('token', $token)->exists());

            $item->token = $token;
            $item->save();
        }

        return $this->redirectSuccess('admin.sites.index');
    }

    public function show(string $id): JsonResponse
    {
        $this->data = [
            'item' => $this->service->getById($id),
        ];

        return $this->json();
    }

    public function edit(string $id): Renderable
    {
        $this->data = [
            'title' => __('Edit'),
            'module' => __('Sites'),
            'item' => $this->service->getById($id),
            'method' => 'PUT',
            'action' => route('admin.' . $this->module . '.update', $id),
        ];

        return $this->render('form');
    }

    public function update(UpdateRequest $request, string $id): RedirectResponse
    {
        $validatedData = $request->validated();

        // Handle image upload
        if ($request->hasFile('logo')) {
            $site = $this->service->getById($id);

            // Delete old image if exists
            if ($site->logo && Storage::disk('public')->exists($site->logo)) {
                Storage::disk('public')->delete($site->logo);
            }

            $validatedData['logo'] = $this->handleImageUpload($request->file('logo'));
        }

        $this->service->update($id, $validatedData);

        return $this->redirectSuccess('admin.sites.index');
    }

    public function destroy(string $id): JsonResponse
    {
        // Check if delete confirmation was received
        if (!request()->has('confirmed')) {
            $this->data = [
                'message' => __('Delete confirmation required'),
                'confirmed' => false,
            ];

            return $this->json(422);
        }

        $message = __('Unknown error');
        $code = 500;

        if ($this->service->delete($id)) {
            $message = __('Delete successfully');
            $code = 200;
        }

        $this->data = [
            'message' => $message,
        ];

        return $this->json($code);
    }

    public function restore(string $id): JsonResponse
    {
        $message = __('Unknown error');
        $code = 500;

        if ($this->service->restore($id)) {
            $message = __('Restore successfully');
            $code = 200;
        }

        $this->data = [
            'message' => $message,
        ];

        return $this->json($code);
    }

    public function delete(string $id): JsonResponse
    {
        $code = 500;
        $message = __('Unknown error');

        if ($this->service->forceDelete($id)) {
            $code = 200;
            $message = __('Force delete successfully');
        }

        $this->data = [
            'message' => $message,
        ];

        return $this->json($code);
    }

    /**
     * Handle image upload
     */
    private function handleImageUpload($file): string
    {
        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $uploadPath = 'uploads/sites';

        // Ensure the directory exists
        Storage::disk('public')->makeDirectory($uploadPath, 0755, true, true);

        // Store file in storage/app/public/uploads/sites
        Storage::disk('public')->putFileAs($uploadPath, $file, $fileName);

        // Return the path for storage link (without 'public/' prefix)
        return $uploadPath . '/' . $fileName;
    }

    public function statistics()
    {
        $site_id = 0;

        if (auth()->user()->hasRole('Merchant')) {
            $site_id = 1;
        } elseif (request()->has('site_id')) {
            $site_id = request()->input('site_id');
        }

        $createdFrom = request('created_from', date('Y-m-d'));
        $createdTo = request('created_to', date('Y-m-d'));

        $transactionAmount = $this->transactionService->sumAmount();
        $transactionCount = $this->transactionService->paidTransactionsCount();
        $transactionFeeAmount = $this->transactionService->sumFeeAmount();
        $transactionTotalAmount = $transactionAmount - $transactionFeeAmount;

        $withdrawalAmount = $this->withdrawalService->sumAmount();
        $withdrawalCount = $this->withdrawalService->paidWithdrawalsCount();
        $withdrawalFeeAmount = $this->withdrawalService->sumFeeAmount();
        $withdrawalTotalAmount = $withdrawalAmount + $withdrawalFeeAmount;

        $totalAmount = $transactionTotalAmount - $withdrawalTotalAmount;

        $site = $this->service->getById($site_id);
        $settlement_fee = $site ? $site->settlement_fee : 0;
        $settlement_fee_amount = number_format(($totalAmount * $settlement_fee) / 100, 2);

        $this->data = [
            'module' => __('Sites'),
            'title' => __('Statistics'),
            'transaction_amount' => $transactionAmount,
            'transaction_fee_amount' => $transactionFeeAmount,
            'transaction_total_amount' => $transactionTotalAmount,
            'transaction_count' => $transactionCount,
            'withdrawal_amount' => $withdrawalAmount,
            'withdrawal_fee_amount' => $withdrawalFeeAmount,
            'withdrawal_total_amount' => $withdrawalTotalAmount,
            'withdrawal_count' => $withdrawalCount,
            'total' => $totalAmount,
            'site' => $site,
            'settlement_fee' => $settlement_fee,
            'settlement_fee_amount' => $settlement_fee_amount,
            'total_with_settlement_fee' => $totalAmount + $settlement_fee_amount,
            'createdFrom' => $createdFrom,
            'createdTo' => $createdTo
        ];

        return $this->render('statistics');
    }
}
