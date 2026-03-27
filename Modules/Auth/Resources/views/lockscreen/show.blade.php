<x-authmod::layouts.master title="Ecran verrouill&eacute; &mdash; {{ config('app.name', 'B360') }}">

    <div class="account-content">
        <div class="login-wrapper"
             style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.65);">

            <div class="login-content authent-content" style="max-width:420px;width:100%;">

                {{-- Hidden logout form (outside the unlock form to avoid nesting) --}}
                <form method="POST" action="{{ route('logout') }}" id="lockscreen-logout-form">
                    @csrf
                </form>

                <form method="POST" action="{{ route('lockscreen.unlock') }}">
                    @csrf

                    <div class="login-userset text-center">

                        {{-- User avatar --}}
                        <div class="mb-3">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white fw-bold"
                                  style="width:80px;height:80px;font-size:32px;line-height:1;">
                                {{ strtoupper(substr($user->name ?? $user->email, 0, 1)) }}
                            </span>
                        </div>

                        {{-- User info --}}
                        <h4 class="fw-semibold mb-1">{{ $user->name ?? $user->email }}</h4>
                        <p class="text-muted fs-14 mb-4">{{ $user->email }}</p>

                        {{-- Alerts --}}
                        @if($errors->any())
                            <div class="alert alert-danger text-start" role="alert">
                                @foreach($errors->all() as $error)
                                    <p class="mb-0">{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif

                        {{-- Password --}}
                        <div class="mb-3 text-start">
                            <label class="form-label">Mot de passe <span class="text-danger">*</span></label>
                            <div class="pass-group">
                                <input type="password"
                                       name="password"
                                       class="pass-input form-control"
                                       required
                                       autofocus
                                       autocomplete="current-password"
                                       placeholder="Entrez votre mot de passe">
                                <span class="ti toggle-password ti-eye-off text-gray-9"></span>
                            </div>
                        </div>

                        {{-- Unlock button --}}
                        <div class="form-login">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ti ti-lock-open me-1"></i> D&eacute;verrouiller
                            </button>
                        </div>

                        {{-- Logout link --}}
                        <div class="mt-3">
                            <a href="javascript:void(0);"
                               onclick="document.getElementById('lockscreen-logout-form').submit();"
                               class="text-muted fs-14">
                                <i class="ti ti-logout me-1"></i>Se d&eacute;connecter
                            </a>
                        </div>

                    </div>
                </form>

            </div>
        </div>
    </div>

</x-authmod::layouts.master>
