<x-badge
    :color="$paid ? 'bg-success bg-opacity-10 text-success' : 'bg-danger  bg-opacity-10 text-danger'"
    :title="$paid ? __('Paid') : __('Unpaid')"
/>
