<x-authmod::layouts.master title="Ecran verrouillé — {{ config('app.name', 'B360') }}">

    @php
    $loginLogo = setting('branding.login_logo') ?: setting('branding.logo');
    $brandName = setting('branding.platform_name', config('app.name', 'B360'));
    @endphp

    <div class="w-100 overflow-hidden position-relative vh-100">

        <div class="row justify-content-center align-items-center vh-100 overflow-auto">

            <div class="col-lg-4 mx-auto">

                {{-- Logout form --}}
                <form method="POST" action="{{ route('logout') }}" id="lockscreen-logout-form">
                    @csrf
                </form>

                {{-- Unlock form --}}
                <form method="POST" action="{{ route('lockscreen.unlock') }}"
                    class="d-flex justify-content-center align-items-center">

                    @csrf

                    <div class="d-flex flex-column justify-content-lg-center p-4 p-lg-0 pb-0 flex-fill">

                        {{-- LOGO --}}
                        <div class="mx-auto mb-5 text-center">
                            <img src="{{ $loginLogo ? asset($loginLogo) : asset('assets/img/logo.svg') }}"
                                class="img-fluid" alt="{{ $brandName }}">
                        </div>

                        {{-- CARD --}}
                        <div class="card border-0 p-lg-3 shadow-lg rounded-2">

                            <div class="card-body">

                                {{-- TITLE --}}
                                <div class="text-center mb-3">
                                    <h5 class="mb-2">Ecran verrouillé</h5>
                                </div>

                                {{-- AVATAR + USER --}}
                                <div class="text-center mb-3">
                                    <span
                                        class="avatar avatar-xl rounded-circle flex-shrink-0 bg-primary text-white fw-bold d-inline-flex align-items-center justify-content-center"
                                        style="width:80px;height:80px;font-size:32px;">
                                        {{ strtoupper(substr($user->name ?? $user->email, 0, 1)) }}
                                    </span>
                                </div>

                                <div class="text-center mb-4">
                                    <h6 class="mb-1">{{ $user->name ?? $user->email }}</h6>
                                    <small class="text-muted">{{ $user->email }}</small>
                                </div>

                                {{-- ERRORS --}}
                                @if($errors->any())
                                <div class="alert alert-danger text-start">
                                    @foreach($errors->all() as $error)
                                    <p class="mb-0">{{ $error }}</p>
                                    @endforeach
                                </div>
                                @endif

                                {{-- PASSWORD --}}
                                <div class="mb-3">
                                    <label class="form-label">Mot de passe</label>

                                    <div class="pass-group input-group">

                                        <span class="input-group-text border-end-0">
                                            <i class="isax isax-lock"></i>
                                        </span>

                                        <input type="password" name="password"
                                            class="form-control border-start-0 ps-0 pass-input"
                                            placeholder="****************" required autofocus
                                            autocomplete="current-password">

                                        <span class="input-group-text border-start-0 toggle-password">
                                            <i class="isax isax-eye-slash"></i>
                                        </span>

                                    </div>
                                </div>

                                {{-- BUTTON --}}
                                <div class="mb-0">
                                    <button type="submit" class="btn bg-primary-gradient text-white w-100">
                                        <i class="ti ti-lock-open me-1"></i> Déverrouiller
                                    </button>
                                </div>

                                {{-- LOGOUT --}}
                                <div class="text-center mt-3">
                                    <a href="javascript:void(0);"
                                        onclick="document.getElementById('lockscreen-logout-form').submit();"
                                        class="text-muted fs-14">
                                        <i class="ti ti-logout me-1"></i> Se déconnecter
                                    </a>
                                </div>

                            </div>

                        </div>

                    </div>

                </form>

            </div>

        </div>

    </div>

</x-authmod::layouts.master>
