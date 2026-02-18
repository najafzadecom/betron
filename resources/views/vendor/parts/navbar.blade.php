<div class="navbar navbar-dark navbar-expand-lg navbar-static border-bottom border-bottom-white border-opacity-10">
    <div class="container-fluid">
        <div class="d-flex d-lg-none me-2">
            <button type="button" class="navbar-toggler sidebar-mobile-main-toggle rounded-pill text-white">
                <i class="ph-list"></i>
            </button>
        </div>

        <div class="navbar-brand flex-1 flex-lg-0">
            <a href="{{ route('vendor.dashboard') }}" class="d-inline-flex align-items-center">
                <img src="{{ asset('admin/assets/images/betron.png') }}" alt="">
                <span class="text-white ms-2 fw-bold">{{ __('Vendor Portal') }}</span>
            </a>
        </div>

        <ul class="nav flex-row flex-lg-1 order-2 order-lg-1 ">

        </ul>

        <ul class="nav flex-row justify-content-end order-1 order-lg-2">
            <!-- Theme buttons -->
            <li class="nav-item ms-lg-2 d-flex align-items-center">
                <a class="navbar-nav-link navbar-nav-link-icon rounded-pill text-white btn-dark-theme" href="#" title="{{ __('Dark Theme') }}">
                    <i class="ph-moon-stars"></i>
                </a>
                <a class="navbar-nav-link navbar-nav-link-icon rounded-pill text-white btn-light-theme" href="#" title="{{ __('Light Theme') }}">
                    <i class="ph-sun"></i>
                </a>
                <a class="navbar-nav-link navbar-nav-link-icon rounded-pill text-white btn-auto-theme" href="#" title="{{ __('Auto Theme') }}">
                    <i class="ph-circle-half"></i>
                </a>
            </li>

            <li class="nav-item nav-item-dropdown-lg dropdown ms-lg-2">
                <a href="#" class="navbar-nav-link align-items-center rounded-pill p-1 text-white" data-bs-toggle="dropdown">
                    <div class="status-indicator-container">
                        <div class="w-32px h-32px rounded-pill bg-white text-primary d-flex align-items-center justify-content-center">
                            <i class="ph-storefront"></i>
                        </div>
                        <span class="status-indicator bg-success"></span>
                    </div>
                    <span class="d-none d-lg-inline-block mx-lg-2">{{ auth('vendor')->user()->name }}</span>
                </a>

                <div class="dropdown-menu dropdown-menu-end">
                    <a href="{{ route('vendor.profile.password') }}" class="dropdown-item">
                        <i class="ph-lock-key me-2"></i>
                        {{ __('Change Password') }}
                    </a>
                    <a href="{{ route('vendor.auth.logout') }}" class="dropdown-item text-danger">
                        <i class="ph-sign-out me-2"></i>
                        {{ __('Logout') }}
                    </a>
                </div>
            </li>
        </ul>
    </div>
</div>
