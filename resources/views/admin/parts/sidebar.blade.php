<div class="sidebar sidebar-dark sidebar-main sidebar-expand-lg">
    <div class="sidebar-content">
        <div class="sidebar-section">
            <div class="sidebar-section-body d-flex justify-content-center">
                <h5 class="sidebar-resize-hide flex-grow-1 my-auto">{{ __('Navigation') }}</h5>

                <div>
                    <button type="button"
                            class="btn btn-flat-white btn-icon btn-sm rounded-pill border-transparent sidebar-control sidebar-main-resize d-none d-lg-inline-flex">
                        <i class="ph-arrows-left-right"></i>
                    </button>

                    <button type="button"
                            class="btn btn-flat-white btn-icon btn-sm rounded-pill border-transparent sidebar-mobile-main-toggle d-lg-none">
                        <i class="ph-x"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="sidebar-section">
            <ul class="nav nav-sidebar" data-nav-type="accordion">

                <li class="nav-item-header pt-0">
                    <div class="text-uppercase fs-sm lh-sm opacity-50 sidebar-resize-hide">{{ __('Main') }}</div>
                    <i class="ph-dots-three sidebar-resize-show"></i>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}"
                       class="nav-link @if(request()->routeIs('admin.dashboard')) active @endif">
                        <i class="ph-house"></i>
                        <span>
                            {{ __('Dashboard') }}
                        </span>
                    </a>
                </li>

                @canany(['users-index', 'roles-index'])
                    <!-- Accesss Management -->
                    <li class="nav-item-header">
                        <div
                            class="text-uppercase fs-sm lh-sm opacity-50 sidebar-resize-hide">{{ __('Access Management') }}</div>
                        <i class="ph-dots-three sidebar-resize-show"></i>
                    </li>
                    @can('users-index')
                        <li class="nav-item">
                            <a href="{{ route('admin.users.index') }}"
                               class="nav-link @if(request()->routeIs('admin.users.*')) active @endif">
                                <i class="ph-user-circle"></i>
                                <span>{{ __('Users') }}</span>
                            </a>
                        </li>
                    @endcan
                    @can('roles-index')
                        <li class="nav-item">
                            <a href="{{ route('admin.roles.index') }}" class="nav-link">
                                <i class="ph-users-three"></i>
                                <span>{{ __('Roles') }}</span>
                            </a>
                        </li>
                    @endcan
                @endcanany

                <!-- Invoice Management -->
                <li class="nav-item-header">
                    <div
                        class="text-uppercase fs-sm lh-sm opacity-50 sidebar-resize-hide">{{ __('Invoice Management') }}</div>
                    <i class="ph-dots-three sidebar-resize-show"></i>
                </li>
                @can('transactions-index')
                    <li class="nav-item">
                        <a href="{{ route('admin.transactions.index') }}"
                           class="nav-link @if(request()->routeIs('admin.transactions.*') && !request()->has('status')) active @endif">
                            <i class="ph-coins"></i>
                            <span>{{ __('Transactions') }}</span>
                        </a>
                    </li>
                    @if(!auth()->user()->hasRole('Merchant'))
                        <li class="nav-item">
                            <a href="{{ route('admin.transactions.index') }}?status=0"
                               class="nav-link  @if(request()->routeIs('admin.transactions.*') && request('status') === '0') active @endif">
                                <i class="ph-coins"></i>
                                <span>{{ __('Draft Transactions') }}</span>
                            </a>
                        </li>
                    @endif
                @endcan
                @can('withdrawals-index')
                    <li class="nav-item">
                        <a href="{{ route('admin.withdrawals.index') }}"
                           class="nav-link  @if(request()->routeIs('admin.withdrawals.*') && !request()->routeIs('admin.withdrawals.send')) active @endif">
                            <i class="ph-money"></i>
                            <span>{{ __('Withdrawals') }}</span>
                        </a>
                    </li>
                @endcan
                @can('wallets-index')
                    <li class="nav-item">
                        <a href="{{ route('admin.wallets.index') }}"
                           class="nav-link  @if(request()->routeIs('admin.wallets.*')) active @endif">
                            <i class="ph-wallet"></i>
                            <span>{{ __('Wallets') }}</span>
                        </a>
                    </li>
                @endcan
                @can('withdrawals-send')
                    <li class="nav-item">
                        <a href="{{ route('admin.withdrawals.send') }}"
                           class="nav-link  @if(request()->routeIs('admin.withdrawals.send')) active @endif">
                            <i class="ph-coin-vertical"></i>
                            <span>{{ __('Manual Money Send') }}</span>
                        </a>
                    </li>
                @endcan
                @can('providers-index')
                    <li class="nav-item">
                        <a href="{{ route('admin.providers.index') }}"
                           class="nav-link  @if(request()->routeIs('admin.providers.*')) active @endif">
                            <i class="ph-plugs-connected"></i>
                            <span>{{ __('Providers') }}</span>
                        </a>
                    </li>
                @endcan
                @can('banks-index')
                    <li class="nav-item">
                        <a href="{{ route('admin.banks.index') }}"
                           class="nav-link @if(request()->routeIs('admin.banks.*')) active @endif">
                            <i class="ph-bank"></i>
                            <span>{{ __('Banks') }}</span>
                        </a>
                    </li>
                @endcan
                @can('sites-index')
                    <li class="nav-item">
                        <a href="{{ route('admin.sites.index') }}"
                           class="nav-link @if(request()->routeIs('admin.sites.*')) active @endif">
                            <i class="ph-browser"></i>
                            <span>{{ __('Sites') }}</span>
                        </a>
                    </li>
                @endcan
                @can('vendors-index')
                    <li class="nav-item">
                        <a href="{{ route('admin.vendors.index') }}"
                           class="nav-link @if(request()->routeIs('admin.vendors.*')) active @endif">
                            <i class="ph-storefront"></i>
                            <span>{{ __('Vendors') }}</span>
                        </a>
                    </li>
                @endcan
                @can('vendor-users-index')
                    <li class="nav-item">
                        <a href="{{ route('admin.vendor-users.index') }}"
                           class="nav-link @if(request()->routeIs('admin.vendor-users.*')) active @endif">
                            <i class="ph-user-circle-gear"></i>
                            <span>{{ __('Vendor Users') }}</span>
                        </a>
                    </li>
                @endcan

                @canany(['statistics-index','activity-logs-index','blacklists-index','settings-index'])
                    <li class="nav-item-header">
                        <div class="text-uppercase fs-sm lh-sm opacity-50 sidebar-resize-hide">{{ __('System') }}</div>
                        <i class="ph-dots-three sidebar-resize-show"></i>
                    </li>
                    @can('statistics-index')
                        <li class="nav-item">
                            <a href="{{ route('admin.statistics.index') }}"
                               class="nav-link @if(request()->routeIs('admin.statistics.*')) active @endif">
                                <i class="ph-chart-bar"></i>
                                <span>{{ __('Statistics') }}</span>
                            </a>
                        </li>
                    @endcan
                    @can('activity-logs-index')
                        <li class="nav-item">
                            <a href="{{ route('admin.activity-logs.index') }}"
                               class="nav-link @if(request()->routeIs('admin.activity-logs.*')) active @endif">
                                <i class="ph-list-checks"></i>
                                <span>{{ __('Activity Logs') }}</span>
                            </a>
                        </li>
                    @endcan
                    @can('blacklists-index')
                        <li class="nav-item">
                            <a href="{{ route('admin.blacklists.index') }}"
                               class="nav-link @if(request()->routeIs('admin.blacklists.*')) active @endif">
                                <i class="ph-lock-key"></i>
                                <span>{{ __('Blacklists') }}</span>
                            </a>
                        </li>
                    @endcan
                    @can('settings-index')
                        <li class="nav-item">
                            <a href="{{ route('admin.settings.index') }}" class="nav-link">
                                <i class="ph-gear"></i>
                                <span>
                            {{ __('Settings') }}
                        </span>
                            </a>
                        </li>
                    @endcan
                @endcanany
            </ul>
        </div>
    </div>
</div>
