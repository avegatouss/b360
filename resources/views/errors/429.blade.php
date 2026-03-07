<!DOCTYPE html>
<html lang="fr" dir="ltr" data-bs-theme="light" data-color-theme="Blue_Theme" data-layout="vertical">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <!-- Favicon icon -->
  <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logos/favicon.png') }}" />
  <title>B360 - Trop de requêtes</title>
  <!-- Core Css -->
  <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}" />
  <style>
    body { background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; font-family: system-ui, -apple-system, sans-serif; }
    .error-box { max-width: 500px; text-align: center; padding: 2rem; background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .icon-wrapper { background: #ffebee; color: #d32f2f; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2rem; }
  </style>
</head>

<body>
  <div class="error-box">
    <div class="icon-wrapper">
        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-clock-pause">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M20.942 13.018a9 9 0 1 0 -7.909 7.922" />
            <path d="M12 7v5l2 2" />
            <path d="M17 17v5" />
            <path d="M21 17v5" />
        </svg>
    </div>
    <h1 class="mb-3">Trafic Limité ! (Erreur 429)</h1>
    <p class="text-muted mb-4">
      Vous avez atteint la limite de requêtes autorisées pour assurer la stabilité du service. 
      Veuillez patienter quelques instants avant de réessayer.
    </p>
    <div>
        <button onclick="window.location.reload()" class="btn btn-primary d-inline-flex align-items-center gap-2">
            Réessayer
        </button>
        <a href="{{ url('/') }}" class="btn btn-light d-inline-flex align-items-center gap-2 mt-2 mt-sm-0">
            Retour à l'accueil
        </a>
    </div>
  </div>
</body>

</html>
