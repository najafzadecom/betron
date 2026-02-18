<?php

namespace App\Services;

use App\Core\Services\BaseService;
use App\Repositories\ActivityLogRepository as Repository;

class ActivityLogService extends BaseService
{
    public function __construct(protected Repository $repository)
    {
    }

    public function getUniqueSubjectTypes(): array
    {
        return $this->repository->getModel()->query()
            ->select('subject_type')
            ->distinct()
            ->whereNotNull('subject_type')
            ->pluck('subject_type')
            ->map(function ($type) {
                return [
                    'value' => $type,
                    'label' => class_basename($type),
                ];
            })
            ->toArray();
    }

    public function getUniqueDescriptions(): array
    {
        return $this->repository->getModel()->query()
            ->select('description')
            ->distinct()
            ->whereNotNull('description')
            ->pluck('description')
            ->map(function ($description) {
                return [
                    'value' => $description,
                    'label' => ucfirst($description),
                ];
            })
            ->toArray();
    }

    /**
     * Get activity logs by subject type and ID with pagination
     *
     * @param string $subjectType
     * @param int $subjectId
     * @param int|null $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getBySubject(string $subjectType, int $subjectId, ?int $perPage = null): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->repository->getBySubject($subjectType, $subjectId, $perPage);
    }

    public static function make(): self
    {
        return app(self::class);
    }
}
