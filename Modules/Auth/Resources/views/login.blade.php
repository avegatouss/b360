<x-authmod::layouts.master :title="($mode ?? 'global') === 'instance'
    ? 'Connexion — ' . ($instance->name ?? $instance->slug ?? 'Instance')
    : 'Connexion — ' . config('app.name', 'B360')">


    <div class="card">
        <div class="card-body p-0 bg-black auth-header-box rounded-top">
            <div class="text-center p-3">
                <a href="#" class="logo logo-admin">
                    <img src="{{ asset('assets/images/logo-sm.png') }}" height="50" alt="logo" class="auth-logo">
                </a>
                <h4 class="mt-3 mb-1 fw-semibold text-white fs-18">Connexion</h4>

                @if(($mode ?? 'global') === 'instance' && isset($instance))

                    <p class="text-muted fw-medium mb-0">Connectez-vous à {{ $instance->name ?? $instance->slug }}</p>
                @else

                    <p class="text-muted fw-medium mb-0">Accédez au panneau {{ config('app.name', 'B360') }} avec votre e-mail et mot de passe.</p>
                @endif

            </div>
        </div>
        <div class="card-body">
            <form class="my-4" action="{{ ($mode ?? 'global') === 'instance' && isset($instance)
                          ? route('instance.login.post', $instance->slug)
                          : route('login.post') }}" method="POST">
                @csrf
                <div class="form-group mb-2">
                    <label class="form-label" for="email">E-mail</label>
                    <input type="text" class="form-control" id="email" name="email" placeholder="vous@exemple.com" autocomplete="email">
                </div><!--end form-group-->

                <div class="form-group">
                    <label class="form-label" for="userpassword">Mot de passe</label>
                    <input type="password" class="form-control" name="password" id="userpassword" placeholder="••••••••"  autocomplete="current-password">
                </div><!--end form-group-->

                <div class="form-group row mt-3">
                    <div class="col-sm-6">
                        <div class="form-check form-switch form-switch-primary">
                            <input class="form-check-input" type="checkbox" id="customSwitchPrimary" name="remember" value="1">
                            <label class="form-check-label" for="customSwitchPrimary">Se souvenir de moi</label>
                        </div>
                    </div><!--end col-->
                    <div class="col-sm-6 text-end">
                        <a href="{{ route('password.request') }}" class="text-muted font-13"><i class="dripicons-lock"></i> Mot de passe oublié?</a>
                    </div><!--end col-->
                </div><!--end form-group-->

                <div class="form-group mb-0 row">
                    <div class="col-12">
                        <div class="d-grid mt-3">
                            <button class="btn btn-primary" type="submit">Se connecter <i class="fas fa-sign-in-alt ms-1"></i></button>
                        </div>
                    </div><!--end col-->
                </div> <!--end form-group-->
            </form><!--end form-->

        </div><!--end card-body-->
    </div><!--end card-->

</x-authmod::layouts.master>
