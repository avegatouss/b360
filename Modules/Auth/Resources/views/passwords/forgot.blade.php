<x-authmod::layouts.master title="Mot de passe oublié — {{ config('app.name', 'B360') }}">

    <div class="card">
        <div class="card-body p-0 bg-black auth-header-box rounded-top">
            <div class="text-center p-3">
                <a href="index.html" class="logo logo-admin">
                    <img src="{{ asset('assets/images/logo-sm.png') }}" height="50" alt="logo" class="auth-logo">
                </a>
                <h4 class="mt-3 mb-1 fw-semibold text-white fs-18">Mot de passe oublié ?</h4>
                <p class="text-muted fw-medium mb-0"> Entrez votre adresse e-mail et nous vous enverrons un lien pour réinitialiser votre mot de passe.</p>
            </div>
        </div>
        <div class="card-body">
            <form class="my-4" action="{{ route('password.email') }}" method="POST">
                @csrf
                <div class="form-group mb-2">
                    <label class="form-label" for="userEmail">E-mail</label>
                    <input type="text" class="form-control" id="userEmail" name="email" value="{{ old('email') }}" placeholder="vous@exemple.com">
                </div><!--end form-group-->

                <div class="form-group mb-0 row">
                    <div class="col-12">
                        <div class="d-grid mt-3">
                            <button class="btn btn-primary" type="submit">Envoyer le lien de réinitialisation <i class="fas fa-sign-in-alt ms-1"></i></button>
                        </div>
                    </div><!--end col-->
                </div> <!--end form-group-->
            </form><!--end form-->
            <div class="text-center  mb-2">
                <p class="text-muted">
                    Vous vous en souvenez ?
                    <a href="{{ route('login') }}" class="text-primary ms-2">Connectez-vous ici</a>
                </p>
            </div>
        </div><!--end card-body-->
    </div><!--end card-->

</x-authmod::layouts.master>
