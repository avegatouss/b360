<x-authmod::layouts.master title="Accès refusé — {{ config('app.name', 'B360') }}">

    <div class="account-content">
        <div class="login-wrapper bg-img">
            <div class="login-content authent-content">

                <div class="login-userset">

                    {{-- Logo --}}
                    <div class="login-logo logo-normal">
                        <img src="{{ asset('build/img/logo.svg') }}" alt="{{ config('app.name', 'B360') }}">
                    </div>

                    {{-- Heading --}}
                    <div class="login-userheading">
                        <h3>Accès non autorisé</h3>
                        <h4 class="fs-16">Votre compte n'est membre d'aucune instance active.</h4>
                    </div>

                    {{-- Alert --}}
                    <div class="alert alert-warning d-flex align-items-start gap-2" role="alert">
                        <i class="ti ti-alert-triangle fs-18 mt-1"></i>
                        <div>
                            <strong>Aucune instance disponible</strong><br>
                            Veuillez contacter un administrateur pour obtenir l'accès à un espace de travail.
                        </div>
                    </div>

                    {{-- Logout --}}
                    <div class="form-login mt-2">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary w-100">
                                <i class="ti ti-logout me-1"></i>Se déconnecter
                            </button>
                        </form>
                    </div>

                    <div class="my-4 d-flex justify-content-center align-items-center copyright-text">
                        <p>Copyright &copy; {{ date('Y') }} {{ config('app.name', 'B360') }}</p>
                    </div>

                </div>

            </div>
        </div>
    </div>

</x-authmod::layouts.master>
