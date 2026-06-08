<x-authmod::layouts.master :title="($mode ?? 'global') === 'instance'
    ? 'Connexion — ' . ($instance->name ?? $instance->slug ?? 'Instance')
    : 'Connexion — ' . config('app.name', 'B360')">

    @php
    $loginLogo = setting('branding.login_logo') ?: setting('branding.logo');
    $brandName = setting('branding.platform_name', config('app.name', 'B360'));
    @endphp

    <div class="w-100 overflow-hidden position-relative flex-wrap d-block vh-100">

        <div class="row justify-content-center align-items-center vh-100 overflow-auto flex-wrap">
            <div class="col-lg-4 mx-auto">

                <form method="POST" action="{{ ($mode ?? 'global') === 'instance' && isset($instance)
                      ? route('instance.login.post', $instance->slug)
                      : route('login.post') }}" data-recaptcha data-recaptcha-action="login"
                    class="d-flex justify-content-center align-items-center">
                    @csrf

                    <div class="d-flex flex-column justify-content-lg-center p-4 p-lg-0 pb-0 flex-fill">

                        {{-- Logo --}}
                        <div class="mx-auto mt-3 mb-4 text-center">
                            <a href="{{ url('/') }}">
                                <img src="{{ $loginLogo ? asset('storage/'.$loginLogo) : asset('assets/img/logo-large.svg') }}"
                                    class="img-fluid" alt="{{ $brandName }}">
                            </a>
                        </div>

                        <div class="card border-0 p-lg-3 shadow-lg">
                            <div class="card-body">

                                <div class="text-center mb-3">
                                    <h5 class="mb-2">Connexion</h5>

                                    @if(($mode ?? 'global') === 'instance' && isset($instance))
                                    <p class="mb-0">
                                        Connectez-vous à
                                        <strong>{{ $instance->name ?? $instance->slug }}</strong>
                                    </p>
                                    @else
                                    <p class="mb-0">
                                        Accédez à votre espace {{ $brandName }}
                                    </p>
                                    @endif
                                </div>

                                @if($errors->any())
                                <div class="alert alert-danger text-bg-danger alert-dismissible fade show" role="alert">
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"
                                        aria-label="Close"></button>

                                    <strong>Erreur - </strong>

                                    <ul class="mb-0 mt-1">
                                        @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif

                                @if(session('status'))
                                <div class="alert alert-success text-bg-success alert-dismissible fade show"
                                    role="alert">
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"
                                        aria-label="Close"></button>

                                    <strong>Succès - </strong>
                                    {{ session('status') }}
                                </div>
                                @endif

                                {{-- Email --}}
                                <div class="mb-3">
                                    <label class="form-label">Adresse e-mail</label>
                                    <div class="input-group">
                                        <span class="input-group-text border-end-0">
                                            <i class="isax isax-sms-notification"></i>
                                        </span>

                                        <input type="email" name="email" value="{{ old('email') }}"
                                            class="form-control border-start-0 ps-0" placeholder="vous@exemple.com"
                                            required autofocus autocomplete="email">
                                    </div>
                                </div>

                                {{-- Mot de passe --}}
                                <div class="mb-3">
                                    <label class="form-label">Mot de passe</label>

                                    <div class="pass-group input-group">
                                        <span class="input-group-text border-end-0">
                                            <i class="isax isax-lock"></i>
                                        </span>

                                        <span class="isax toggle-password isax-eye-slash"></span>

                                        <input type="password" name="password"
                                            class="pass-input form-control border-start-0 ps-0" placeholder="********"
                                            required autocomplete="current-password">
                                    </div>
                                </div>

                                {{-- Remember + Forgot --}}
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="form-check form-check-md mb-0">
                                            <input class="form-check-input me-2" id="remember_me" type="checkbox"
                                                name="remember" value="1">

                                            <label for="remember_me" class="form-check-label">
                                                Se souvenir de moi
                                            </label>
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <a href="{{ route('password.request') }}">
                                            Mot de passe oublié ?
                                        </a>
                                    </div>
                                </div>

                                {{-- Bouton connexion --}}
                                <div class="mb-1">
                                    <button type="submit" class="btn bg-primary-gradient text-white w-100">
                                        Se connecter
                                    </button>
                                </div>


                            </div>
                        </div>

                    </div>
                </form>

            </div>
        </div>

    </div>

    @include('authmod::components.recaptcha')

</x-authmod::layouts.master>
