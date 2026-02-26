<x-authmod::layouts.master title="Réinitialisation du mot de passe — {{ config('app.name', 'B360') }}">

    <div class="account-content">
        <div class="login-wrapper bg-img">
            <div class="login-content authent-content">

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="login-userset">

                        {{-- Logo --}}
                        <div class="login-logo logo-normal">
                            <img src="{{ asset('build/img/logo.svg') }}" alt="{{ config('app.name', 'B360') }}">
                        </div>

                        {{-- Heading --}}
                        <div class="login-userheading">
                            <h3>Nouveau mot de passe</h3>
                            <h4 class="fs-16">Choisissez un nouveau mot de passe sécurisé pour votre compte.</h4>
                        </div>

                        @if($errors->any())
                            <div class="alert alert-danger" role="alert">
                                @foreach($errors->all() as $error)
                                    <p class="mb-0">{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif

                        {{-- Email --}}
                        <div class="mb-3">
                            <label class="form-label">E-mail <span class="text-danger"> *</span></label>
                            <div class="input-group">
                                <input type="email"
                                       name="email"
                                       value="{{ old('email', $email ?? '') }}"
                                       class="form-control border-end-0"
                                       required
                                       autofocus
                                       autocomplete="email">
                                <span class="input-group-text border-start-0">
                                    <i class="ti ti-mail"></i>
                                </span>
                            </div>
                        </div>

                        {{-- New password --}}
                        <div class="mb-3">
                            <label class="form-label">Nouveau mot de passe <span class="text-danger"> *</span></label>
                            <div class="pass-group">
                                <input type="password"
                                       name="password"
                                       class="pass-input form-control"
                                       required
                                       autocomplete="new-password"
                                       placeholder="Minimum 8 caractères">
                                <span class="ti toggle-password ti-eye-off text-gray-9"></span>
                            </div>
                        </div>

                        {{-- Confirm password --}}
                        <div class="mb-3">
                            <label class="form-label">Confirmer le mot de passe <span class="text-danger"> *</span></label>
                            <div class="pass-group">
                                <input type="password"
                                       name="password_confirmation"
                                       class="pass-input form-control"
                                       required
                                       autocomplete="new-password"
                                       placeholder="Répétez le mot de passe">
                                <span class="ti toggle-password ti-eye-off text-gray-9"></span>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <div class="form-login">
                            <button type="submit" class="btn btn-primary w-100">
                                Réinitialiser le mot de passe
                            </button>
                        </div>

                        {{-- Back to login --}}
                        <div class="signinform mt-3">
                            <h4>
                                <a href="{{ route('login') }}" class="hover-a">
                                    <i class="ti ti-arrow-left me-1"></i>Retour à la connexion
                                </a>
                            </h4>
                        </div>

                        <div class="my-4 d-flex justify-content-center align-items-center copyright-text">
                            <p>Copyright &copy; {{ date('Y') }} {{ config('app.name', 'B360') }}</p>
                        </div>

                    </div>
                </form>

            </div>
        </div>
    </div>

</x-authmod::layouts.master>
