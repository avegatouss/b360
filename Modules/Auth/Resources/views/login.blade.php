<x-authmod::layouts.master :title="($mode ?? 'global') === 'instance'
    ? 'Connexion — ' . ($instance->name ?? $instance->slug ?? 'Instance')
    : 'Connexion — ' . config('app.name', 'B360')">

    @php
        $loginLogo = setting('branding.login_logo') ?: setting('branding.logo');
        $loginLogoDark = setting('branding.logo_dark');
        $loginCover = setting('branding.login_cover');
        $brandName = setting('branding.platform_name', config('app.name', 'B360'));
    @endphp

    <div class="account-content">
        <div class="login-wrapper bg-img" @if($loginCover) style="background-image: url('{{ asset('storage/' . $loginCover) }}');" @endif>
            <div class="login-content authent-content">

                <form method="POST"
                      action="{{ ($mode ?? 'global') === 'instance' && isset($instance)
                          ? route('instance.login.post', $instance->slug)
                          : route('login.post') }}"
                      data-recaptcha data-recaptcha-action="login">
                    @csrf

                    <div class="login-userset">

                        {{-- Logo --}}
                        <div class="login-logo logo-normal">
                            <img src="{{ $loginLogo ? asset('storage/' . $loginLogo) : asset('build/img/logo.svg') }}" alt="{{ $brandName }}">
                        </div>
                        <a href="{{ url('/') }}" class="login-logo logo-white">
                            <img src="{{ $loginLogoDark ? asset('storage/' . $loginLogoDark) : asset('build/img/logo-white.svg') }}" alt="{{ $brandName }}">
                        </a>

                        {{-- Heading --}}
                        <div class="login-userheading">
                            <h3>Connexion</h3>
                            @if(($mode ?? 'global') === 'instance' && isset($instance))
                                <h4 class="fs-16">
                                    Connectez-vous à <strong>{{ $instance->name ?? $instance->slug }}</strong>
                                </h4>
                            @else
                                <h4 class="fs-16">
                                    Accédez au panneau {{ config('app.name', 'B360') }} avec votre e-mail et mot de passe.
                                </h4>
                            @endif
                        </div>

                        {{-- Alerts --}}
                        @if($errors->any())
                            <div class="alert alert-danger" role="alert">
                                @foreach($errors->all() as $error)
                                    <p class="mb-0">{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif

                        @if(session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        {{-- Email --}}
                        <div class="mb-3">
                            <label class="form-label">E-mail <span class="text-danger"> *</span></label>
                            <div class="input-group">
                                <input type="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       class="form-control border-end-0"
                                       required
                                       autofocus
                                       autocomplete="email"
                                       placeholder="vous@exemple.com">
                                <span class="input-group-text border-start-0">
                                    <i class="ti ti-mail"></i>
                                </span>
                            </div>
                        </div>

                        {{-- Password --}}
                        <div class="mb-3">
                            <label class="form-label">Mot de passe <span class="text-danger"> *</span></label>
                            <div class="pass-group">
                                <input type="password"
                                       name="password"
                                       class="pass-input form-control"
                                       required
                                       autocomplete="current-password"
                                       placeholder="••••••••">
                                <span class="ti toggle-password ti-eye-off text-gray-9"></span>
                            </div>
                        </div>

                        {{-- Remember me + Forgot --}}
                        <div class="form-login authentication-check">
                            <div class="row">
                                <div class="col-12 d-flex align-items-center justify-content-between">
                                    <div class="custom-control custom-checkbox">
                                        <label class="checkboxs ps-4 mb-0 pb-0 line-height-1 fs-16 text-gray-6">
                                            <input type="checkbox" name="remember" value="1">
                                            <span class="checkmarks"></span>Se souvenir de moi
                                        </label>
                                    </div>
                                    <div class="text-end">
                                        <a class="text-orange fs-16 fw-medium"
                                           href="{{ route('password.request') }}">Mot de passe oublié ?</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <div class="form-login">
                            <button type="submit" class="btn btn-primary w-100">Se connecter</button>
                        </div>

                        {{-- Footer copyright --}}
                        <div class="my-4 d-flex justify-content-center align-items-center copyright-text">
                            <p>Copyright &copy; {{ date('Y') }} {{ $brandName }}</p>
                        </div>

                    </div>
                </form>

            </div>
        </div>
    </div>

</x-authmod::layouts.master>
