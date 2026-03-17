<x-authmod::layouts.master :title="'Code de recuperation — ' . config('app.name', 'B360')">

    <div class="account-content">
        <div class="login-wrapper">
            <div class="login-content authent-content">
                <div class="login-userset" style="max-width: 420px;">

                    <div class="login-userheading">
                        <h3>Code de recuperation</h3>
                        <h4 class="fs-16">
                            Entrez un de vos codes de recuperation pour acceder a votre compte.
                        </h4>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger" role="alert">
                            @foreach($errors->all() as $error)
                                <p class="mb-0">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('two-factor.recovery') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Code de recuperation <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="recovery_code"
                                   class="form-control text-center font-monospace"
                                   maxlength="10"
                                   autocomplete="off"
                                   placeholder="xxxxxxxxxx"
                                   required
                                   autofocus>
                        </div>

                        <div class="form-login">
                            <button type="submit" class="btn btn-primary w-100">Verifier</button>
                        </div>
                    </form>

                    <div class="mt-3 text-center">
                        <a href="{{ route('two-factor.challenge') }}" class="text-muted">
                            Utiliser le code de l'application
                        </a>
                    </div>

                    <div class="mt-2 text-center">
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-link text-muted p-0">Se deconnecter</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

</x-authmod::layouts.master>
