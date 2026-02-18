<?php

namespace App\Services;

use App\Core\Services\BaseService;
use App\Enums\VendorDepositTransactionType;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Repositories\VendorDepositTransactionRepository;
use App\Repositories\VendorRepository as Repository;
use Illuminate\Support\Facades\Hash;

class VendorService extends BaseService
{
    public function __construct(
        protected Repository                         $repository,
        protected VendorDepositTransactionRepository $depositTransactionRepository
    ) {
    }

    public function create(array $data): object
    {
        $data['password'] = Hash::make($data['password']);

        return $this->repository->create($data);
    }

    public function update(int $id, array $data): ?object
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        return $this->repository->update($id, $data);
    }

    /**
     * Get active vendors
     */
    public function getActives()
    {
        return $this->repository->getModel()->where('status', 1)->get();
    }

    /**
     * Get child vendors for a parent vendor
     */
    public function getChildren(int $parentId)
    {
        return $this->repository->getModel()
            ->where('parent_id', $parentId)
            ->get();
    }

    /**
     * Get all descendants (recursive) for a vendor
     */
    public function getDescendants(int $vendorId): array
    {
        $descendants = [];
        $children = $this->getChildren($vendorId);

        foreach ($children as $child) {
            $descendants[] = $child->id;
            $descendants = array_merge($descendants, $this->getDescendants($child->id));
        }

        return $descendants;
    }

    /**
     * Get vendors accessible by a vendor (self + all descendants)
     */
    public function getAccessibleVendors(int $vendorId)
    {
        $vendorIds = array_merge([$vendorId], $this->getDescendants($vendorId));

        return $this->repository->getModel()
            ->whereIn('id', $vendorIds)
            ->get();
    }

    /**
     * Check if vendor can access another vendor
     */
    public function canAccess(int $vendorId, int $targetVendorId): bool
    {
        if ($vendorId == $targetVendorId) {
            return true;
        }

        $targetVendor = $this->getById($targetVendorId);
        if (!$targetVendor) {
            return false;
        }

        return $targetVendor->isDescendantOf($vendorId);
    }

    /**
     * Check if vendor can create sub-vendors
     * Only top-level vendors (parent_id = null) can create vendors
     * This prevents infinite nesting
     */
    public function canCreateVendor(int $vendorId): bool
    {
        $vendor = $this->getById($vendorId);
        if (!$vendor) {
            return false;
        }

        // Only top-level vendor (no parent) can create vendors
        return is_null($vendor->parent_id);
    }

    /**
     * Get top-level vendors (parent_id = null)
     */
    public function getTopLevelVendors()
    {
        return $this->repository->getModel()
            ->whereNull('parent_id')
            ->where('status', 1)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get all accessible vendors for a parent vendor (only descendants, not parent itself)
     */
    public function getAccessibleVendorsForParent(int $parentId)
    {
        // Get only descendants, not the parent itself
        $vendorIds = $this->getDescendants($parentId);

        if (empty($vendorIds)) {
            return collect([]);
        }

        return $this->repository->getModel()
            ->whereIn('id', $vendorIds)
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Get child vendors with filters and pagination
     */
    public function getChildVendorsPaginated(int $parentId, array $filters = [])
    {
        $query = $this->repository->getModel()
            ->where('parent_id', $parentId);

        // Apply filters
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        if (!empty($filters['name'])) {
            $query->where('name', 'ilike', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['email'])) {
            $query->where('email', 'ilike', '%' . $filters['email'] . '%');
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['created_from'])) {
            $query->where('created_at', '>=', $filters['created_from']);
        }

        if (!empty($filters['created_to'])) {
            $query->where('created_at', '<=', $filters['created_to'] . ' 23:59:59');
        }

        $perPage = (int)($filters['limit'] ?? 25);
        $perPage = in_array($perPage, config('pagination.per_pages')) ? $perPage : 25;

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends($filters);
    }

    /**
     * Get vendor wallets
     */
    public function getVendorWallets(int $vendorId)
    {
        $vendor = $this->getById($vendorId);
        return $vendor ? $vendor->wallets : collect([]);
    }

    /**
     * Add deposit to vendor
     */
    public function addDeposit(int $vendorId, float $amount, ?string $note = null): bool
    {
        $vendor = $this->getById($vendorId);
        if (!$vendor) {
            return false;
        }

        $previousBalance = $vendor->deposit_amount ?? 0;
        $vendor->deposit_amount = $previousBalance + $amount;
        $vendor->save();

        // Create transaction record
        $this->depositTransactionRepository->create([
            'vendor_id' => $vendorId,
            'type' => VendorDepositTransactionType::ADD->value,
            'amount' => $amount,
            'previous_balance' => $previousBalance,
            'new_balance' => $vendor->deposit_amount,
            'note' => $note,
            'created_by' => auth()->id(),
        ]);

        // Log activity
        activity()
            ->performedOn($vendor)
            ->withProperties([
                'amount' => $amount,
                'previous_balance' => $previousBalance,
                'new_balance' => $vendor->deposit_amount,
                'note' => $note,
            ])
            ->log('Deposit added');

        return true;
    }

    /**
     * Subtract deposit from vendor
     */
    public function subtractDeposit(int $vendorId, float $amount, ?string $note = null): bool
    {
        $vendor = $this->getById($vendorId);
        if (!$vendor) {
            return false;
        }

        $currentDeposit = $vendor->deposit_amount ?? 0;
        if ($currentDeposit < $amount) {
            return false; // Insufficient deposit
        }

        $vendor->deposit_amount = $currentDeposit - $amount;
        $vendor->save();

        // Create transaction record
        $this->depositTransactionRepository->create([
            'vendor_id' => $vendorId,
            'type' => VendorDepositTransactionType::SUBTRACT->value,
            'amount' => $amount,
            'previous_balance' => $currentDeposit,
            'new_balance' => $vendor->deposit_amount,
            'note' => $note,
            'created_by' => auth()->id(),
        ]);

        // Log activity
        activity()
            ->performedOn($vendor)
            ->withProperties([
                'amount' => $amount,
                'previous_balance' => $currentDeposit,
                'new_balance' => $vendor->deposit_amount,
                'note' => $note,
            ])
            ->log('Deposit subtracted');

        return true;
    }

    /**
     * Process transaction deposit (decreases deposit when transaction is confirmed)
     */
    public function processTransactionDeposit(int $transactionId, float $amount, ?int $vendorId = null): bool
    {
        // If vendor_id is not provided, try to get it from wallet
        if (!$vendorId) {
            $transaction = Transaction::find($transactionId);
            if (!$transaction || !$transaction->wallet) {
                return false;
            }
            $vendorId = $transaction->wallet->vendor_id ?? null;
        }

        if (!$vendorId) {
            return false;
        }

        $vendor = $this->getById($vendorId);
        if (!$vendor) {
            return false;
        }

        // Calculate vendor fee: amount - (amount * transaction_fee / 100)
        $transactionFee = $vendor->transaction_fee ?? 0;
        $vendorAmount = $amount - ($amount * $transactionFee / 100);

        $previousBalance = $vendor->deposit_amount ?? 0;

        // Check if deposit is sufficient
        if ($previousBalance < $vendorAmount) {
            return false; // Insufficient deposit
        }

        $vendor->deposit_amount = $previousBalance - $vendorAmount;
        $vendor->save();

        // Create transaction record
        $this->depositTransactionRepository->create([
            'vendor_id' => $vendorId,
            'type' => VendorDepositTransactionType::TRANSACTION->value,
            'amount' => $vendorAmount,
            'previous_balance' => $previousBalance,
            'new_balance' => $vendor->deposit_amount,
            'note' => __('Transaction deposit processed'),
            'transaction_id' => $transactionId,
            'created_by' => auth()->id(),
        ]);

        // Log activity
        activity()
            ->performedOn($vendor)
            ->withProperties([
                'transaction_id' => $transactionId,
                'amount' => $vendorAmount,
                'transaction_fee' => $transactionFee,
                'original_amount' => $amount,
                'previous_balance' => $previousBalance,
                'new_balance' => $vendor->deposit_amount,
            ])
            ->log('Transaction deposit processed');

        return true;
    }

    /**
     * Process withdrawal deposit (increases deposit when withdrawal is confirmed)
     */
    public function processWithdrawalDeposit(int $withdrawalId, float $amount, ?int $vendorId = null): bool
    {
        // If vendor_id is not provided, try to get it from withdrawal
        if (!$vendorId) {
            $withdrawal = Withdrawal::find($withdrawalId);
            if (!$withdrawal) {
                return false;
            }
            $vendorId = $withdrawal->vendor_id ?? null;
        }

        if (!$vendorId) {
            return false;
        }

        $vendor = $this->getById($vendorId);
        if (!$vendor) {
            return false;
        }

        // Calculate vendor fee: amount + (amount * withdrawal_fee / 100)
        $withdrawalFee = $vendor->withdrawal_fee ?? 0;
        $vendorAmount = $amount + ($amount * $withdrawalFee / 100);

        $previousBalance = $vendor->deposit_amount ?? 0;
        $vendor->deposit_amount = $previousBalance + $vendorAmount;
        $vendor->save();

        // Create transaction record
        $this->depositTransactionRepository->create([
            'vendor_id' => $vendorId,
            'type' => VendorDepositTransactionType::WITHDRAWAL->value,
            'amount' => $vendorAmount,
            'previous_balance' => $previousBalance,
            'new_balance' => $vendor->deposit_amount,
            'note' => __('Withdrawal deposit processed'),
            'withdrawal_id' => $withdrawalId,
            'created_by' => auth()->id(),
        ]);

        // Log activity
        activity()
            ->performedOn($vendor)
            ->withProperties([
                'withdrawal_id' => $withdrawalId,
                'amount' => $vendorAmount,
                'withdrawal_fee' => $withdrawalFee,
                'original_amount' => $amount,
                'previous_balance' => $previousBalance,
                'new_balance' => $vendor->deposit_amount,
            ])
            ->log('Withdrawal deposit processed');

        return true;
    }
}
