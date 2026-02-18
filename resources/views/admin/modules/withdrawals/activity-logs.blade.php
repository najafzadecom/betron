@extends('admin.layouts.app')
@section('title', $title)
@section('content')
    <div class="content">
        <div class="card">
            <div class="card-header d-flex">
                <h5 class="mb-0">{{ $module }}</h5>
                <div class="ms-auto">
                    <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-secondary">
                        <i class="ph-arrow-left me-1"></i> {{ __('Back') }}
                    </a>
                </div>
            </div>

            <div class="card-body">
                <div class="alert alert-info">
                    <strong>{{ __('Withdrawal') }}:</strong> #{{ $withdrawal->id }} - {{ $withdrawal->order_id }}
                    ({{ $withdrawal->receiver }} - {{ $withdrawal->currency->code() }} {{ number_format($withdrawal->amount, 2) }})
                </div>

                <div class="table-responsive">
                    <table class="table table-xs text-nowrap">
                        <thead>
                        <tr>
                            <th>{{ __('ID') }}</th>
                            <th>{{ __('Description') }}</th>
                            <th>{{ __('Causer') }}</th>
                            <th>{{ __('Properties') }}</th>
                            <th>{{ __('Created At') }}</th>
                            <th class="text-center" style="width: 20px;">
                                <i class="ph-dots-three"></i>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>
                                    <span class="badge
                                        @switch($item->description)
                                            @case('created') bg-success @break
                                            @case('updated') bg-warning @break
                                            @case('deleted') bg-danger @break
                                            @case('restored') bg-info @break
                                            @default bg-dark
                                        @endswitch
                                        bg-opacity-10
                                        @switch($item->description)
                                            @case('created') text-success @break
                                            @case('updated') text-warning @break
                                            @case('deleted') text-danger @break
                                            @case('restored') text-info @break
                                            @default text-dark
                                        @endswitch
                                    ">
                                        {{ $item->description_display }}
                                    </span>
                                </td>
                                <td>
                                    <div>
                                        <span class="fw-semibold">{{ $item->causer_name }}</span>
                                        @if($item->causer && $item->causer->email)
                                            <div class="text-muted small">{{ $item->causer->email }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($item->properties && !$item->properties->isEmpty())
                                        <button type="button" class="btn btn-sm btn-outline-info"
                                                data-bs-toggle="modal"
                                                data-bs-target="#properties_modal_{{ $item->id }}">
                                            <i class="ph-eye"></i> {{ __('View') }}
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $item->created_at->isoFormat('DD MMM YYYY HH:mm') }}</td>
                                <td>
                                    @canany(['activity-logs.show'])
                                    <div class="dropdown">
                                        <a href="#" class="text-body" data-bs-toggle="dropdown" data-bs-boundary="viewport">
                                            <i class="ph-list"></i>
                                        </a>

                                        <div class="dropdown-menu dropdown-menu-end" data-popper-placement="top-start"
                                             data-popper-reference-hidden="">
                                            <div class="dropdown-header">{{ __('Options') }}</div>
                                            @can('activity-logs.show')
                                            <a href="#" class="dropdown-item"
                                               data-url="{{ route('admin.activity-logs.show', $item->id) }}"
                                               data-bs-toggle="modal" data-bs-target="#show_modal">
                                                <i class="ph-eye me-2"></i>
                                                {{ __('Show activity log') }}
                                            </a>
                                            @endcan
                                        </div>
                                    </div>
                                    @endcanany
                                </td>
                            </tr>

                            @if($item->properties && !$item->properties->isEmpty())
                            <div class="modal fade" id="properties_modal_{{ $item->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">{{ __('Properties') }} - {{ __('Activity Log') }} #{{ $item->id }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <pre class="bg-light p-3 rounded">{{ json_encode($item->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-link" data-bs-dismiss="modal">
                                                {{ __('Close') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">{{ __('No activity logs found for this withdrawal') }}</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer">
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-6 d-flex align-items-center">
                            <label for="limit" class="me-2 mb-0">{{ __('Display') }}:</label>
                            <select id="limit" name="limit" class="form-select w-auto" onchange="changeLimit(this.value)">
                                @foreach(config('pagination.per_pages') as $limit)
                                <option value="{{ $limit }}"{{ request('limit', 25) == $limit ? ' selected' : '' }}>{{ $limit }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            @if(method_exists($items, 'links'))
                                {{ $items->links() }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="show_modal" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Show activity log') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-4 fw-semibold text-muted">{{ __('ID') }}:</div>
                        <div class="col-8 text-end" id="id">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-semibold text-muted">{{ __('Log Name') }}:</div>
                        <div class="col-8 text-end" id="log-name">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-semibold text-muted">{{ __('Description') }}:</div>
                        <div class="col-8 text-end" id="description">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-semibold text-muted">{{ __('Subject Type') }}:</div>
                        <div class="col-8 text-end" id="subject-type">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-semibold text-muted">{{ __('Subject ID') }}:</div>
                        <div class="col-8 text-end" id="subject-id">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-semibold text-muted">{{ __('Causer Type') }}:</div>
                        <div class="col-8 text-end" id="causer-type">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-semibold text-muted">{{ __('Causer ID') }}:</div>
                        <div class="col-8 text-end" id="causer-id">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-semibold text-muted">{{ __('Causer Name') }}:</div>
                        <div class="col-8 text-end" id="causer-name">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-semibold text-muted">{{ __('Event') }}:</div>
                        <div class="col-8 text-end" id="event">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-semibold text-muted">{{ __('Batch UUID') }}:</div>
                        <div class="col-8 text-end" id="batch-uuid">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-semibold text-muted">{{ __('Created At') }}:</div>
                        <div class="col-8 text-end" id="created-at">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 fw-semibold text-muted">{{ __('Updated At') }}:</div>
                        <div class="col-8 text-end" id="updated-at">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12">
                            <hr>
                            <h6 class="fw-semibold text-muted mb-2">{{ __('Properties') }}:</h6>
                            <div id="properties" class="border p-3 bg-light">-</div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-link" data-bs-dismiss="modal">
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function changeLimit(limit) {
            const url = new URL(window.location);
            url.searchParams.set('limit', limit);
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }

        document.addEventListener('DOMContentLoaded', function () {
            const showModal = document.getElementById('show_modal');

            showModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const url = button.getAttribute('data-url');

                fetch(url)
                    .then(response => response.json())
                    .then(responseData => {
                        const data = responseData.item;

                        Object.keys(data).forEach(key => {
                            const element = document.getElementById(key.replace(/_/g, '-'));
                            if (element) {
                                if (key === 'created_at' || key === 'updated_at') {
                                    element.innerText = data[key] ? new Date(data[key]).toLocaleString() : '-';
                                } else if (key.endsWith('_display') || key.endsWith('_html')) {
                                    element.innerHTML = data[key] ?? '-';
                                } else {
                                    element.innerText = data[key] ?? '-';
                                }
                            }
                        });

                        const propertiesDiv = document.getElementById('properties');
                        if (data.properties && Object.keys(data.properties).length > 0) {
                            propertiesDiv.innerHTML = '<pre>' + JSON.stringify(data.properties, null, 2) + '</pre>';
                        } else {
                            propertiesDiv.innerText = '-';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                    });
            });
        });
    </script>
@endpush

