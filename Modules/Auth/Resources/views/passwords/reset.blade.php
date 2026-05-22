<x-authmod::layouts.master title="Réinitialisation du mot de passe — {{ config('app.name', 'B360') }}">

    <div class="card">
        <div class="card-body p-0 bg-black auth-header-box rounded-top">
            <div class="text-center p-3">
                <a href="index.html" class="logo logo-admin">
                    <img src="{{ asset('assets/images/logo-sm.png') }}" height="50" alt="logo" class="auth-logo">
                </a>
                <h4 class="mt-3 mb-1 fw-semibold text-white fs-18">Nouveau mot de passe</h4>
                <p class="text-muted fw-medium mb-0"> Choisissez un nouveau mot de passe sécurisé pour votre compte.</p>
            </div>
        </div>
        <div class="card-body">
            <form class="my-4" action="{{ route('password.update') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-group mb-2">
                    <label class="form-label" for="userEmail">E-mail</label>
                    <input type="text" class="form-control" id="userEmail" name="email" value="{{ old('email', $email ?? '') }}" placeholder="vous@exemple.com">
                </div><!--end form-group-->

                 <div class="form-group mb-2">
                    <label class="form-label" for="userPassword">Nouveau mot de passe</label>
                    <input type="password" class="form-control" id="userPassword" name="password"
                                       required
                                       autocomplete="new-password"
                                       placeholder="Minimum 8 caractères">
                </div><!--end form-group-->


                 <div class="form-group mb-2">
                    <label class="form-label" for="userPasswordConfirm">Confirmer le nouveau mot de passe</label>
                    <input type="password" class="form-control" id="userPasswordConfirm"  name="password_confirmation"
                                       required
                                       autocomplete="new-password"
                                       placeholder="Répétez le mot de passe">
                </div><!--end form-group-->

                <div class="form-group mb-0 row">
                    <div class="col-12">
                        <div class="d-grid mt-3">
                            <button class="btn btn-primary" type="submit">Réinitialiser le mot de passe <i class="fas fa-sign-in-alt ms-1"></i></button>
                        </div>
                    </div><!--end col-->
                </div> <!--end form-group-->
            </form><!--end form-->
            <div class="text-center  mb-2">
                <p class="text-muted">
                    {{-- Vous vous en souvenez ? --}}
                    <a href="{{ route('login') }}" class="text-primary ms-2">Connectez-vous ici</a>
                </p>
            </div>
        </div><!--end card-body-->
    </div><!--end card-->

</x-authmod::layouts.master>
