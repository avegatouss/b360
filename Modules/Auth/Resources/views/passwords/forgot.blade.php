<x-authmod::layouts.master title="Mot de passe oublié — {{ config('app.name', 'B360') }}">

    <div class="auth-card">

        <div class="auth-logo">
            <span class="auth-logo-text">B360</span>
        </div>

        <h1 class="auth-title">Mot de passe oublié</h1>

        <p style="font-size:.875rem;color:#6b7280;text-align:center;margin-bottom:1.25rem;">
            Entrez votre adresse e-mail et nous vous enverrons un lien pour réinitialiser votre mot de passe.
        </p>

        @if(session('status'))
            <div class="auth-alert auth-alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="auth-alert auth-alert-error">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="auth-field">
                <label for="email">Adresse e-mail</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="vous@exemple.com"
                >
            </div>

            <button type="submit" class="auth-btn">
                Envoyer le lien de réinitialisation
            </button>
        </form>

        <div class="auth-footer">
            <a href="{{ route('login') }}">&larr; Retour à la connexion</a>
        </div>

    </div>

</x-authmod::layouts.master>
