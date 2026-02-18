<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ env('APP_NAME') }}</title>

    <!-- Global stylesheets -->
    <link href="{{ asset('admin/assets/fonts/inter/inter.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('admin/assets/icons/phosphor/styles.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('admin/assets/css/all.min.css') }}" id="stylesheet" rel="stylesheet" type="text/css">
    <!-- /global stylesheets -->

    <!-- Core JS files -->
    <script src="{{ asset('admin/assets/demo/demo_configurator.js') }}"></script>
    <script src="{{ asset('admin/assets/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <!-- /core JS files -->

    <!-- Theme JS files -->
    <script src="{{ asset('admin/assets/js/app.js') }}"></script>
    <script src="{{ asset('admin/assets/js/custom.js') }}"></script>
    <!-- /theme JS files -->

</head>
<body>
<div class="navbar navbar-dark navbar-static py-2">
    <div class="container-fluid">
        <div class="navbar-brand">
            <a href="{{ route('admin.dashboard') }}" class="d-inline-flex align-items-center">
                <img src="{{ asset('admin/assets/images/expressbank.png') }}" alt="">
            </a>
        </div>
    </div>
</div>


<div class="page-content">
    <div class="content-wrapper">
        <div class="content-inner">
            <div class="content d-flex justify-content-center align-items-center">
                <form class="login-form" method="POST" action="{{ route('admin.auth.login') }}">
                    @csrf
                    <div class="card mb-0">
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <div class="d-inline-flex align-items-center justify-content-center mb-4 mt-2">
                                    <img src="{{ asset('admin/assets/images/expressbank.png') }}" class="h-48px" alt="">
                                </div>
                                <h5 class="mb-0">{{ __('Login to your account') }}</h5>
                                <span class="d-block text-muted">{{ __('Enter your credentials below') }}</span>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">{{ __('E-mail') }}</label>
                                <div class="form-control-feedback form-control-feedback-start">
                                    <input
                                        type="email"
                                        name="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        placeholder="{{ __('E-mail') }}"
                                    />
                                    <div class="form-control-feedback-icon">
                                        <i class="ph-user-circle text-muted"></i>
                                    </div>
                                    @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">{{ __('Password') }}</label>
                                <div class="form-control-feedback form-control-feedback-start input-group">
                                    <input
                                        type="password"
                                        name="password"
                                        id="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        placeholder="{{ __('Password') }}"
                                    />
                                    <div class="form-control-feedback-icon">
                                        <i class="ph-lock text-muted"></i>
                                    </div>
                                    <span class="input-group-text cursor-pointer" onclick="togglePasswordVisibility()"
                                          data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Show Password') }}"
                                          data-show-text="{{ __('Show Password') }}" data-hide-text="{{ __('Hide Password') }}"
                                    >
                                         <i class="ph-eye" id="togglePassword"></i>
                                     </span>
                                    @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary w-100">{{ __('Sign in') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            @include('admin.parts.footer')
        </div>
    </div>
</div>
</body>
</html>
