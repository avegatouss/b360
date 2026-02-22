<x-authmod::layouts.master title="{{ ($mode ?? 'global') === 'instance' ? 'Connexion — ' . ($instance->name ?? $instance->slug) : 'Connexion' }}">

    <div class="auth-card">

        <div class="auth-logo">
            <span class="auth-logo-text">B360</span>
        </div>

        <h1 class="auth-title">
            @if(($mode ?? 'global') === 'instance' && isset($instance))
                Connexion à <strong>{{ $instance->name ?? $instance->slug }}</strong>
            @else
                Connexion
            @endif
        </h1>

        @if($errors->any())
            <div class="auth-alert auth-alert-error">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if(session('status'))
            <div class="auth-alert auth-alert-success">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST"
              action="{{ ($mode ?? 'global') === 'instance' && isset($instance)
                ? route('instance.login.post', $instance->slug)
                : route('login.post') }}">
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

            <div class="auth-field">
                <label for="password">
                    Mot de passe
                    <a href="{{ route('password.request') }}" class="auth-link-right">Mot de passe oublié ?</a>
                </label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                >
            </div>

            <div class="auth-checkbox">
                <input type="checkbox" name="remember" value="1" id="remember">
                <label for="remember">Se souvenir de moi</label>
            </div>

            <button type="submit" class="auth-btn">
                Se connecter
            </button>
        </form>

    </div>

</x-authmod::layouts.master>
