<x-authmod::layouts.master title="Sélection d'instance — {{ config('app.name', 'B360') }}">

    <div class="card">
        {{-- Header --}}
        <div class="card-body p-0 bg-black auth-header-box rounded-top">
            <div class="text-center p-3">
                <a href="#" class="logo logo-admin">
                    <img src="{{ asset('assets/images/logo-sm.png') }}" height="50" alt="logo" class="auth-logo">
                </a>

                <h4 class="mt-3 mb-1 fw-semibold text-white fs-18">
                    Choisir une instance
                </h4>

                <p class="text-muted fw-medium mb-0">
                    Sélectionnez l'espace de travail auquel vous souhaitez accéder.
                </p>
            </div>
        </div>

        {{-- Body --}}
        <div class="card-body">

            <form class="my-4" method="POST" action="{{ route('instances.choose') }}">
                @csrf

                {{-- Errors --}}
                @if($errors->any())
                    <div class="alert alert-danger" role="alert">
                        @foreach($errors->all() as $error)
                            <p class="mb-0">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                {{-- Instances --}}
                <div class="form-group mb-3">
                    @foreach($instances as $inst)
                        <label for="inst_{{ $inst->slug }}"
                               class="d-flex align-items-center border rounded p-3 mb-2 cursor-pointer"
                               style="cursor:pointer;">

                            <input type="radio"
                                   id="inst_{{ $inst->slug }}"
                                   name="slug"
                                   value="{{ $inst->slug }}"
                                   class="form-check-input me-3"
                                   {{ $loop->first ? 'checked' : '' }}>

                            <span class="d-flex flex-column">
                                <span class="fw-semibold">
                                    {{ $inst->name }}
                                </span>

                                <small class="text-muted">
                                    {{ $inst->slug }}
                                </small>
                            </span>
                        </label>
                    @endforeach
                </div>

                {{-- Submit --}}
                <div class="form-group mb-0 row">
                    <div class="col-12">
                        <div class="d-grid mt-3">
                            <button class="btn btn-primary" type="submit">
                                Continuer
                                <i class="fas fa-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            {{-- Logout --}}
            <div class="text-center mb-2">
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf

                    <button type="submit"
                            class="btn btn-link text-muted p-0 text-decoration-none">
                        Se déconnecter
                    </button>
                </form>
            </div>

        </div>
    </div>

</x-authmod::layouts.master>
