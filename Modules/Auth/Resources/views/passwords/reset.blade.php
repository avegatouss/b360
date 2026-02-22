<x-authmod::layouts.master title="Réinitialisation du mot de passe — {{ config('app.name', 'B360') }}">

    <div class="auth-card">

        <div class="auth-logo">
            <span class="auth-logo-text">B360</span>
        </div>

        <h1 class="auth-title">Nouveau mot de passe</h1>

        @if($errors->any())
            <div class="auth-alert auth-alert-error">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="auth-field">
                <label for="email">Adresse e-mail</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email', $email ?? '') }}"
                    required
                    autofocus
                    autocomplete="email"
                >
            </div>

            <div class="auth-field">
                <label for="password">Nouveau mot de passe</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Minimum 8 caractères"
                >
            </div>

            <div class="auth-field">
                <label for="password_confirmation">Confirmer le mot de passe</label>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Répétez le mot de passe"
                >
            </div>

            <button type="submit" class="auth-btn">
                Réinitialiser le mot de passe
            </button>
        </form>

        <div class="auth-footer">
            <a href="{{ route('login') }}">&larr; Retour à la connexion</a>
        </div>

    </div>

</x-authmod::layouts.master>
