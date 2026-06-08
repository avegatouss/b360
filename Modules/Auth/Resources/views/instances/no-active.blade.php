<x-authmod::layouts.master title="Accès refusé — {{ config('app.name', 'B360') }}">

    @php
    $loginLogo = setting('branding.login_logo') ?: setting('branding.logo');
    $brandName = setting('branding.platform_name', config('app.name', 'B360'));
    @endphp

    <div class="w-100 overflow-hidden position-relative flex-wrap d-block vh-100">

        <div class="row justify-content-center align-items-center vh-100 overflow-auto flex-wrap">
            <div class="col-lg-4 mx-auto">

                <div class="d-flex justify-content-center align-items-center">

                    <div class="d-flex flex-column justify-content-lg-center p-4 p-lg-0 pb-0 flex-fill">

                        {{-- Logo --}}
                        <div class="mx-auto mt-3 mb-4 text-center">
                            <a href="#">
                                <img src="{{ $loginLogo ? asset('storage/'.$loginLogo) : asset('assets/img/logo-large.svg') }}"
                                    class="img-fluid" alt="{{ $brandName }}">
                            </a>
                        </div>

                        <div class="card border-0 p-lg-3 shadow-lg rounded-2">
                            <div class="card-body">

                                {{-- Titre --}}
                                <div class="text-center mb-4">
                                    <h5 class="mb-2">Accès non autorisé</h5>
                                    <p class="mb-0">
                                        Votre compte n'est membre d'aucune instance active.
                                    </p>
                                </div>

                                {{-- Message --}}
                                <div class="alert alert-warning d-flex align-items-start gap-2">
                                    <i class="ti ti-alert-triangle fs-18 mt-1"></i>
                                    <div>
                                        <strong>Aucune instance disponible</strong><br>
                                        Veuillez contacter un administrateur afin d'obtenir
                                        l'accès à un espace de travail.
                                    </div>
                                </div>

                                {{-- Déconnexion --}}
                                <div class="mt-4">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-secondary w-100">
                                            <i class="ti ti-logout me-1"></i>
                                            Se déconnecter
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </div>

    </div>

</x-authmod::layouts.master>
