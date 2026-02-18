<?php

namespace App\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Interfaces\ActivityLogInterface;
use App\Models\ActivityLog as Model;
use Illuminate\Pagination\LengthAwarePaginator;

class ActivityLogRepository extends BaseRepository implements ActivityLogInterface
{
    public function __construct(Model $model)
    {
        parent::__construct($model);
    }

    public function paginate(): LengthAwarePaginator
    {
        $perPage = (int)request('limit', config('pagination.per_page'));
        $perPage = in_array($perPage, config('pagination.per_pages')) ? $perPage : config('pagination.per_page');

        $query = $this->model->query();

        // Apply filters
        $this->applyFilters($query);

        // Apply sorting
        $this->applySorting($query);

        return $query->paginate($perPage)->appends(request()->query());
    }

    private function applyFilters($query)
    {
        // General search
        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('log_name', 'like', "%{$search}%")
                    ->orWhere('subject_type', 'like', "%{$search}%")
                    ->orWhere('event', 'like', "%{$search}%")
                    ->orWhere('batch_uuid', 'like', "%{$search}%");
            });
        }

        // Subject type filter
        if ($subjectType = request('subject_type')) {
            $query->where('subject_type', $subjectType);
        }

        // Description filter
        if ($description = request('description')) {
            $query->where('description', $description);
        }

        // Causer filter
        if ($causer = request('causer')) {
            $query->whereHas('causer', function ($q) use ($causer) {
                $q->where('name', 'like', "%{$causer}%")
                    ->orWhere('email', 'like', "%{$causer}%");
            });
        }

        // Subject ID filter
        if ($subjectId = request('subject_id')) {
            $query->where('subject_id', $subjectId);
        }

        // Log name filter
        if ($logName = request('log_name')) {
            $query->where('log_name', 'like', "%{$logName}%");
        }

        // Event filter
        if ($event = request('event')) {
            $query->where('event', 'like', "%{$event}%");
        }

        // Batch UUID filter
        if ($batchUuid = request('batch_uuid')) {
            $query->where('batch_uuid', 'like', "%{$batchUuid}%");
        }

        // Date range filter
        if ($createdFrom = request('created_from')) {
            $query->whereDate('created_at', '>=', $createdFrom);
        }

        if ($createdTo = request('created_to')) {
            $query->whereDate('created_at', '<=', $createdTo);
        }

        return $query;
    }

    private function applySorting($query)
    {
        $sortField = request('sort', 'id');
        $sortDirection = request('direction', 'desc');

        // Define allowed sort fields
        $allowedSortFields = config('sortable.sortable_fields.activity-logs', ['id']);

        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('id', 'desc');
        }

        return $query;
    }

    /**
     * Get activity logs by subject type and ID with pagination
     *
     * @param string $subjectType
     * @param int $subjectId
     * @param int|null $perPage
     * @return LengthAwarePaginator
     */
    public function getBySubject(string $subjectType, int $subjectId, ?int $perPage = null): LengthAwarePaginator
    {
        $perPage = $perPage ?? (int)request('limit', config('pagination.per_page'));
        $perPage = in_array($perPage, config('pagination.per_pages')) ? $perPage : config('pagination.per_page');

        return $this->model->query()
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());
    }
}
