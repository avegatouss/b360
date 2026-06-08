<x-authmod::layouts.master title="Mot de passe oublié — {{ config('app.name', 'B360') }}">

    @php
    $loginLogo = setting('branding.login_logo') ?: setting('branding.logo');
    $brandName = setting('branding.platform_name', config('app.name', 'B360'));
    @endphp



    <div class="w-100 overflow-hidden position-relative flex-wrap d-block vh-100">

        <!-- start row -->
        <div class="row justify-content-center align-items-center vh-100 overflow-auto flex-wrap ">
            <div class="col-lg-4 mx-auto">
                <form action="{{ route('password.email') }}" data-recaptcha data-recaptcha-action="forgot_password"
                    method="POST" class="d-flex justify-content-center align-items-center">
                    @csrf
                    <div class="d-flex flex-column justify-content-lg-center p-4 p-lg-0 pb-0 flex-fill">
                        <div class=" mx-auto mt-3 mb-4 text-center">
                            <a href="#"><img
                                    src="{{ $loginLogo ? asset('storage/'.$loginLogo) : asset('assets/img/logo-large.svg') }}"
                                    class="img-fluid" alt="{{ $brandName }}"></a>
                        </div>
                        <div class="card border-0 p-lg-3 shadow-lg rounded-2">
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    <h5 class="mb-2">Mot de passe oublié ?</h5>
                                    <p class="mb-0">Entrez votre adresse e-mail et nous vous enverrons un lien pour
                                        réinitialiser votre mot
                                        de passe.
                                    </p>
                                </div>

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

                                @if(session('status'))
                                <div class="alert alert-success text-bg-success alert-dismissible fade show"
                                    role="alert">
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"
                                        aria-label="Close"></button>

                                    <strong>Succès - </strong>
                                    {{ session('status') }}
                                </div>
                                @endif

                                <div class="mb-3">
                                    <label class="form-label">E-mail</label>
                                    <div class="input-group">
                                        <span class="input-group-text border-end-0">
                                            <i class="isax isax-sms-notification"></i>
                                        </span>
                                        <input type="email" name="email" value="{{ old('email') }}"
                                            class="form-control border-start-0 ps-0" placeholder="vous@exemple.com"
                                            required autofocus autocomplete="email">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <button type="submit" class="btn bg-primary-gradient text-white w-100">Envoyer le
                                        lien de réinitialisation</button>
                                </div>
                                {{-- <div class="text-center">
                                    <h6 class="fw-normal fs-14 text-dark mb-0">Return to
                                        <a href="{{ route('login') }}" class="hover-a"> Sign In</a>
                                    </h6>
                                </div> --}}
                                <div class="text-center mt-3">
                                    <span class="text-muted fs-14">
                                        Retour à la
                                        <a href="{{ route('login') }}" class="hover-a fw-medium">
                                            page de connexion
                                        </a>
                                    </span>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div>
                </form>
            </div><!-- end col -->
        </div>
        <!-- end row -->

    </div>

</x-authmod::layouts.master>
