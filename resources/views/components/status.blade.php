<x-badge
    :color="$status ? 'bg-success bg-opacity-10 text-success' : 'bg-danger  bg-opacity-10 text-danger'"
    :title="$status ? __('Active') : __('Deactive')"
/>
