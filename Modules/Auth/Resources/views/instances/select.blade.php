<x-authmod::layouts.master title="Sélection d'instance — {{ config('app.name', 'B360') }}">

    @php
    $loginLogo = setting('branding.login_logo') ?: setting('branding.logo');
    $brandName = setting('branding.platform_name', config('app.name', 'B360'));
    @endphp

    <div class="w-100 overflow-hidden position-relative flex-wrap d-block vh-100">

        <div class="row justify-content-center align-items-center vh-100 overflow-auto flex-wrap">
            <div class="col-lg-4 mx-auto">

                <form method="POST" action="{{ route('instances.choose') }}"
                    class="d-flex justify-content-center align-items-center">
                    @csrf

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
                                    <h5 class="mb-2">Choisir une instance</h5>
                                    <p class="mb-0">
                                        Sélectionnez l'espace de travail auquel vous souhaitez accéder.
                                    </p>
                                </div>

                                {{-- Erreurs --}}
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

                                {{-- Liste des instances --}}
                                <div class="mb-3">
                                    @foreach($instances as $inst)
                                    <label class="card border mb-2 cursor-pointer w-100">
                                        <div class="card-body py-3">
                                            <div class="form-check m-0">
                                                <input class="form-check-input" type="radio" name="slug"
                                                    id="inst_{{ $inst->slug }}" value="{{ $inst->slug }}" {{
                                                    $loop->first ? 'checked' : '' }}>

                                                <label class="form-check-label w-100" for="inst_{{ $inst->slug }}">
                                                    <div class="fw-semibold">
                                                        {{ $inst->name }}
                                                    </div>
                                                    <small class="text-muted">
                                                        {{ $inst->slug }}
                                                    </small>
                                                </label>
                                            </div>
                                        </div>
                                    </label>
                                    @endforeach
                                </div>

                                {{-- Bouton --}}
                                <div class="mb-3">
                                    <button type="submit" class="btn btn-primary text-white w-100">
                                        Continuer
                                    </button>
                                </div>

                                {{-- Déconnexion --}}
                                <div class="text-center mt-3">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="btn btn-link text-decoration-none p-0 fw-medium">
                                            Se déconnecter
                                        </button>
                                    </form>
                                </div>

                            </div>
                        </div>

                    </div>

                </form>

            </div>
        </div>

    </div>

</x-authmod::layouts.master>
