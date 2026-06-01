<x-authmod::layouts.master :title="'Authentification a deux facteurs — ' . config('app.name', 'B360')">

    <div class="account-content">
        <div class="login-wrapper">
            <div class="login-content authent-content">
                <div class="login-userset" style="max-width: 480px;">

                    <div class="login-userheading">
                        <h3>Authentification a deux facteurs</h3>
                        <h4 class="fs-16">
                            @if($enabled)
                                La 2FA est actuellement <strong class="text-success">activee</strong>.
                            @else
                                Protegez votre compte avec un code TOTP (Google Authenticator, Authy, etc.).
                            @endif
                        </h4>
                    </div>

                    {{-- Flash messages --}}
                    @if(session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger" role="alert">
                            @foreach($errors->all() as $error)
                                <p class="mb-0">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    {{-- Recovery codes after first activation --}}
                    @if(session('recovery_codes'))
                        <div class="alert alert-warning">
                            <strong>Codes de recuperation</strong>
                            <p class="mb-2">Conservez ces codes en lieu sur. Chaque code ne peut etre utilise qu'une seule fois.</p>
                            <div class="bg-light p-3 rounded font-monospace">
                                @foreach(session('recovery_codes') as $code)
                                    <div>{{ $code }}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(!$enabled)
                        @if($qrCodeUri && $secret)
                            {{-- Step 2: QR code displayed, user must enter TOTP to confirm --}}
                            <div class="mb-3">
                                <p class="mb-2">Scannez le QR code ci-dessous avec votre application d'authentification, ou entrez la cle manuellement.</p>

                                <div class="text-center mb-3">
                                    {{-- QR code via inline image (otpauth:// URI for manual entry) --}}
                                    <div class="p-3 bg-light rounded d-inline-block">
                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($qrCodeUri) }}"
                                             alt="QR Code 2FA"
                                             width="200" height="200"
                                             class="img-fluid">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Cle secrete (saisie manuelle)</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control font-monospace" value="{{ $secret }}" readonly>
                                        <button type="button" class="btn btn-outline-secondary"
                                                onclick="navigator.clipboard.writeText('{{ $secret }}')">
                                            <i class="ti ti-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('two-factor.confirm') }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Code de verification <span class="text-danger">*</span></label>
                                    <input type="text"
                                           name="code"
                                           class="form-control text-center font-monospace fs-5"
                                           maxlength="6"
                                           pattern="[0-9]{6}"
                                           inputmode="numeric"
                                           autocomplete="one-time-code"
                                           placeholder="000000"
                                           required
                                           autofocus>
                                    <div class="form-text">Entrez le code a 6 chiffres affiche dans votre application.</div>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Confirmer et activer</button>
                            </form>
                        @else
                            {{-- Step 1: Enable button --}}
                            <form method="POST" action="{{ route('two-factor.enable') }}">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ti ti-shield-lock me-1"></i> Configurer l'authentification 2FA
                                </button>
                            </form>
                        @endif
                    @else
                        {{-- Already enabled: show recovery codes + disable form --}}
                        @if($recoveryCodes && count($recoveryCodes) > 0)
                            <div class="mb-4">
                                <h5>Codes de recuperation restants</h5>
                                <div class="bg-light p-3 rounded font-monospace">
                                    @foreach($recoveryCodes as $code)
                                        <div>{{ $code }}</div>
                                    @endforeach
                                </div>
                                <div class="form-text">{{ count($recoveryCodes) }} code(s) restant(s).</div>
                            </div>
                        @endif

                        <hr>

                        <h5 class="text-danger">Desactiver l'authentification 2FA</h5>
                        <form method="POST" action="{{ route('two-factor.disable') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Mot de passe actuel <span class="text-danger">*</span></label>
                                <div class="pass-group">
                                    <input type="password"
                                           name="password"
                                           class="pass-input form-control"
                                           required
                                           autocomplete="current-password"
                                           placeholder="Confirmez votre mot de passe">
                                    <span class="ti toggle-password ti-eye-off text-gray-9"></span>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="ti ti-shield-off me-1"></i> Desactiver la 2FA
                            </button>
                        </form>
                    @endif

                    <div class="mt-3 text-center">
                        <a href="{{ url('/') }}" class="text-muted">Retour</a>
                    </div>

                </div>
            </div>
        </div>
    </div>

</x-authmod::layouts.master>
