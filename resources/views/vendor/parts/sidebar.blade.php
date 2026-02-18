<div class="sidebar sidebar-dark sidebar-main sidebar-expand-lg">
    <div class="sidebar-content">
        <div class="sidebar-section">
            <div class="sidebar-section-body d-flex justify-content-center">
                <h5 class="sidebar-resize-hide flex-grow-1 my-auto text-white">{{ __('Navigation') }}</h5>

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
                    <a href="{{ route('vendor.dashboard') }}"
                       class="nav-link @if(request()->routeIs('vendor.dashboard')) active @endif">
                        <i class="ph-house"></i>
                        <span>
                            {{ __('Dashboard') }}
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('vendor.deposit-transactions') }}"
                       class="nav-link @if(request()->routeIs('vendor.deposit-transactions')) active @endif">
                        <i class="ph-bank"></i>
                        <span>
                            {{ __('Deposit Transactions') }}
                        </span>
                    </a>
                </li>

                <!-- Wallet Management -->
                @canany(['vendor-wallets-index', 'vendor-transactions-index', 'vendor-withdrawals-index'])
                    <li class="nav-item-header">
                        <div
                            class="text-uppercase fs-sm lh-sm opacity-50 sidebar-resize-hide">{{ __('Wallet Management') }}</div>
                        <i class="ph-dots-three sidebar-resize-show"></i>
                    </li>
                    @can('vendor-wallets-index')
                        <li class="nav-item">
                            <a href="{{ route('vendor.wallets.index') }}"
                               class="nav-link @if(request()->routeIs('vendor.wallets.*')) active @endif">
                                <i class="ph-wallet"></i>
                                <span>{{ __('Wallets') }}</span>
                            </a>
                        </li>
                    @endcan

                    @can('vendor-transactions-index')
                        <li class="nav-item">
                            <a href="{{ route('vendor.transactions.index') }}"
                               class="nav-link @if(request()->routeIs('vendor.transactions.*')) active @endif">
                                <i class="ph-coins"></i>
                                <span>{{ __('Transactions') }}</span>
                            </a>
                        </li>
                    @endcan

                    @can('vendor-withdrawals-index')
                        <li class="nav-item">
                            <a href="{{ route('vendor.withdrawals.index') }}"
                               class="nav-link @if(request()->routeIs('vendor.withdrawals.*')) active @endif">
                                <i class="ph-coins"></i>
                                <span>{{ __('Withdrawals') }}</span>
                            </a>
                        </li>
                    @endcan

                    <li class="nav-item">
                        <a href="{{ route('vendor.statistics.index') }}"
                           class="nav-link @if(request()->routeIs('vendor.statistics.*')) active @endif">
                            <i class="ph-chart-bar-horizontal"></i>
                            <span>{{ __('Statistics') }}</span>
                        </a>
                    </li>

                @endcanany

                <!-- Vendor Management -->
                @if(auth('vendor')->check() && is_null(auth('vendor')->user()->parent_id))
                    <li class="nav-item-header">
                        <div
                            class="text-uppercase fs-sm lh-sm opacity-50 sidebar-resize-hide">{{ __('Vendor Management') }}</div>
                        <i class="ph-dots-three sidebar-resize-show"></i>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('vendor.vendors.index') }}"
                           class="nav-link @if(request()->routeIs('vendor.vendors.*')) active @endif">
                            <i class="ph-storefront"></i>
                            <span>{{ __('Vendors') }}</span>
                        </a>
                    </li>
                @endif

                <!-- User Management -->
                @canany(['vendor-users-index', 'vendor-roles-index'])
                    <li class="nav-item-header">
                        <div
                            class="text-uppercase fs-sm lh-sm opacity-50 sidebar-resize-hide">{{ __('User Management') }}</div>
                        <i class="ph-dots-three sidebar-resize-show"></i>
                    </li>

                    @can('vendor-users-index')
                        <li class="nav-item">
                            <a href="{{ route('vendor.users.index') }}"
                               class="nav-link @if(request()->routeIs('vendor.users.*')) active @endif">
                                <i class="ph-users"></i>
                                <span>{{ __('Users') }}</span>
                            </a>
                        </li>
                    @endcan

                    @can('vendor-roles-index')
                        <li class="nav-item">
                            <a href="{{ route('vendor.roles.index') }}"
                               class="nav-link @if(request()->routeIs('vendor.roles.*')) active @endif">
                                <i class="ph-shield-check"></i>
                                <span>{{ __('Roles') }}</span>
                            </a>
                        </li>
                    @endcan
                @endcanany
            </ul>
        </div>
    </div>
</div>
