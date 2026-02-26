<x-authmod::layouts.master title="Sélection d'instance — {{ config('app.name', 'B360') }}">

    <div class="account-content">
        <div class="login-wrapper bg-img">
            <div class="login-content authent-content">

                <form method="POST" action="{{ route('instances.choose') }}">
                    @csrf

                    <div class="login-userset">

                        {{-- Logo --}}
                        <div class="login-logo logo-normal">
                            <img src="{{ asset('build/img/logo.svg') }}" alt="{{ config('app.name', 'B360') }}">
                        </div>

                        {{-- Heading --}}
                        <div class="login-userheading">
                            <h3>Choisir une instance</h3>
                            <h4 class="fs-16">Sélectionnez l'espace de travail auquel vous souhaitez accéder.</h4>
                        </div>

                        @if($errors->any())
                            <div class="alert alert-danger" role="alert">
                                @foreach($errors->all() as $error)
                                    <p class="mb-0">{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif

                        {{-- Instance list as radio cards --}}
                        <div class="mb-3">
                            @foreach($instances as $inst)
                                <div class="mb-2">
                                    <label class="d-flex align-items-center p-3 border rounded cursor-pointer"
                                           style="cursor:pointer;"
                                           for="inst_{{ $inst->slug }}">
                                        <input type="radio"
                                               id="inst_{{ $inst->slug }}"
                                               name="slug"
                                               value="{{ $inst->slug }}"
                                               class="me-3"
                                               {{ $loop->first ? 'checked' : '' }}>
                                        <span class="d-flex flex-column">
                                            <span class="fw-medium">{{ $inst->name }}</span>
                                            <small class="text-muted">{{ $inst->slug }}</small>
                                        </span>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        {{-- Submit --}}
                        <div class="form-login">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ti ti-arrow-right me-1"></i>Continuer
                            </button>
                        </div>

                        {{-- Logout link --}}
                        <div class="my-3 text-center">
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit"
                                        class="btn btn-link text-muted p-0 text-decoration-none fs-14">
                                    Se déconnecter
                                </button>
                            </form>
                        </div>

                    </div>
                </form>

            </div>
        </div>
    </div>

</x-authmod::layouts.master>
