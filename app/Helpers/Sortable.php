<?php

if (!function_exists('getSortDirection')) {
    /**
     * Get current sort direction from request
     */
    function getSortDirection(string $defaultDirection = 'asc'): string
    {
        return request()->get('direction', $defaultDirection);
    }
}

if (!function_exists('getCurrentSort')) {
    /**
     * Get current sort field from request
     */
    function getCurrentSort(string $defaultSort = 'id'): string
    {
        return request()->get('sort', $defaultSort);
    }
}

if (!function_exists('getOppositeDirection')) {
    /**
     * Get the opposite direction for toggling
     */
    function getOppositeDirection(string $field, ?string $currentSort = null, ?string $currentDirection = null): string
    {
        $currentSort = $currentSort ?? getCurrentSort();
        $currentDirection = $currentDirection ?? getSortDirection();

        if ($currentSort === $field && $currentDirection === 'asc') {
            return 'desc';
        }

        return 'asc';
    }
}

if (!function_exists('generateSortUrl')) {
    /**
     * Generate sort URL for a field
     */
    function generateSortUrl(string $field, ?string $currentSort = null, ?string $currentDirection = null): string
    {
        $direction = getOppositeDirection($field, $currentSort, $currentDirection);
        $params = request()->query();
        $params['sort'] = $field;
        $params['direction'] = $direction;

        return request()->url() . '?' . http_build_query($params);
    }
}

if (!function_exists('isCurrentSort')) {
    /**
     * Check if a field is currently being sorted
     */
    function isCurrentSort(string $field, ?string $currentSort = null): bool
    {
        $currentSort = $currentSort ?? getCurrentSort();

        return $currentSort === $field;
    }
}

if (!function_exists('getSortIcon')) {
    /**
     * Get sort icon class for a field
     */
    function getSortIcon(string $field, ?string $currentSort = null, ?string $currentDirection = null): string
    {
        if (!isCurrentSort($field, $currentSort)) {
            return 'ph-arrows-down-up text-muted';
        }

        $currentDirection = $currentDirection ?? getSortDirection();

        return $currentDirection === 'asc'
            ? 'ph-sort-ascending text-primary'
            : 'ph-sort-descending text-primary';
    }
}

if (!function_exists('getSortableFields')) {
    /**
     * Get sortable fields for a table from config
     */
    function getSortableFields(string $table): array
    {
        return config("sortable.sortable_fields.{$table}", []);
    }
}

if (!function_exists('sortableTableHeader')) {
    /**
     * Generate sortable table header
     */
    function sortableTableHeader(string $field, string $title, ?string $table = null): string
    {
        $sortableFields = [];

        if ($table) {
            $sortableFields = getSortableFields($table);
        }

        if (!empty($sortableFields) && !in_array($field, $sortableFields)) {
            return '<th>' . __($title) . '</th>';
        }

        $url = generateSortUrl($field);
        $icon = getSortIcon($field);

        return '<th class="sortable-header">
                    <a href="' . $url . '" class="text-decoration-none text-dark d-flex align-items-center w-100 px-2 py-1">
                        ' . __($title) . '
                        <i class="ms-1 ' . $icon . '"></i>
                    </a>
                </th>';
    }
}
