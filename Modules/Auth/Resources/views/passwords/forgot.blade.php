<x-authmod::layouts.master title="Mot de passe oublié — {{ config('app.name', 'B360') }}">

    <div class="account-content">
        <div class="login-wrapper bg-img">
            <div class="login-content authent-content">

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="login-userset">

                        {{-- Logo --}}
                        <div class="login-logo logo-normal">
                            <img src="{{ asset('build/img/logo.svg') }}" alt="{{ config('app.name', 'B360') }}">
                        </div>

                        {{-- Heading --}}
                        <div class="login-userheading">
                            <h3>Mot de passe oublié ?</h3>
                            <h4 class="fs-16">
                                Entrez votre adresse e-mail et nous vous enverrons un lien pour réinitialiser votre mot de passe.
                            </h4>
                        </div>

                        @if(session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

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

                        {{-- Submit --}}
                        <div class="form-login">
                            <button type="submit" class="btn btn-primary w-100">
                                Envoyer le lien de réinitialisation
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
