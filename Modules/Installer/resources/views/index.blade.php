<x-installer::layouts.master>
    @section('content')
        <div class="page-wrapper cardhead m-0 p-0">
            <div class="content container-fluid p-0">
                <div class="row">
                    <div class="col-lg-12 mx-auto">
                        <div class="card">
                            {{-- EN-TÊTE AVEC PROGRESSION --}}
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <h4 class="card-title mb-0">Installation de B360</h4>
                                                <p class="text-muted mb-0 opacity-75">Assistant de configuration initiale
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated"
                                                id="globalProgress" style="width: 0%"></div>
                                        </div>
                                        <small class="text-muted"><span id="currentStep">1</span>/5 Étapes</small>
                                    </div>
                                </div>
                            </div>

                            {{-- CONTENU PRINCIPAL AVEC ONGLETS STYLE WIZARD --}}
                            <div class="card-body">
                                {{-- NAVIGATION DES ÉTAPES (Tab Style-2 + Tab Style-3) --}}
                                <ul class="nav nav-tabs justify-content-center mb-5 tab-style-2" id="installationTabs"
                                    role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="step1-tab" data-bs-toggle="tab"
                                            data-bs-target="#step1-content" type="button" role="tab"
                                            aria-selected="true">
                                            <i class="feather-checklist me-1 align-middle"></i>Prérequis
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="step2-tab" data-bs-toggle="tab"
                                            data-bs-target="#step2-content" type="button" role="tab"
                                            aria-selected="false">
                                            <i class="feather-database me-1 align-middle"></i>Base de données
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="step3-tab" data-bs-toggle="tab"
                                            data-bs-target="#step3-content" type="button" role="tab"
                                            aria-selected="false">
                                            <i class="feather-settings me-1 align-middle"></i>Configuration
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="step4-tab" data-bs-toggle="tab"
                                            data-bs-target="#step4-content" type="button" role="tab"
                                            aria-selected="false">
                                            <i class="feather-user-shield me-1 align-middle"></i>Administrateur
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="step5-tab" data-bs-toggle="tab"
                                            data-bs-target="#step5-content" type="button" role="tab"
                                            aria-selected="false">
                                            <i class="feather-rocket me-1 align-middle"></i>Installation
                                        </button>
                                    </li>
                                </ul>

                                {{-- CONTENU DES ÉTAPES --}}
                                <div class="tab-content twitter-bs-wizard-tab-content" id="installationContent">
                                    {{-- ÉTAPE 1: PRÉREQUIS --}}
                                    <div class="tab-pane fade show active" id="step1-content" role="tabpanel">
                                        <h5 class="mb-4">
                                            <i class="feather-checklist text-primary me-2"></i>
                                            Vérification des prérequis
                                        </h5>

                                        <div class="table-responsive">
                                            <table class="table table-bordered mb-4">
                                                <thead>
                                                    <tr>
                                                        <th>Exigence</th>
                                                        <th>Statut</th>
                                                        <th>Valeur</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>PHP ≥ 8.2</td>
                                                        <td>
                                                            <span class="badge bg-success" id="phpBadge">
                                                                <i class="fas fa-circle-notch me-1"></i>Vérification...
                                                            </span>
                                                        </td>
                                                        <td id="phpValue">{{ PHP_VERSION }}</td>
                                                    </tr>
                                                    <tr id="req-extensions">
                                                        <td>Extensions PHP requises</td>
                                                        <td>
                                                            <span class="badge bg-success">
                                                                <i class="fas fa-circle-notch me-1"></i>Vérification...
                                                            </span>
                                                        </td>
                                                        <td>Chargeables</td>
                                                    </tr>
                                                    <tr id="req-permissions">
                                                        <td>Permissions fichiers</td>
                                                        <td>
                                                            <span class="badge bg-success">
                                                                <i class="fas fa-circle-notch me-1"></i>Vérification...
                                                            </span>
                                                        </td>
                                                        <td>Écriture autorisée</td>
                                                    </tr>
                                                    <tr id="req-env">
                                                        <td>Fichier .env modifiable</td>
                                                        <td>
                                                            <span class="badge bg-success">
                                                                <i class="fas fa-circle-notch me-1"></i>Vérification...
                                                            </span>
                                                        </td>
                                                        <td>Configurable</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="alert alert-info">
                                            <i class="feather-info me-2"></i>
                                            Assurez-vous que tous les prérequis sont validés avant de continuer.
                                        </div>
                                    </div>

                                    {{-- ÉTAPE 2: BASE DE DONNÉES --}}
                                    <div class="tab-pane fade" id="step2-content" role="tabpanel">
                                        <h5 class="mb-4">
                                            <i class="feather-database text-primary me-2"></i>
                                            Configuration de la base de données
                                        </h5>

                                        <div class="mb-4">
                                            <h6 class="mb-3">
                                                <i class="feather-server me-2"></i>Connexion à la base de données
                                            </h6>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Type Installation</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="feather-database"></i>
                                                        </span>
                                                        <select name="environment_mode" class="form-control form-select"
                                                            id="environment_mode">
                                                            <option value="demo" selected>Demo</option>
                                                            <option value="prod">Production</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Créé une nouvelle base de données</label>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="create_database" role="switch"
                                                            id="flexSwitchCheckCreatDB">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Type de base</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="feather-database"></i>
                                                        </span>
                                                        <select name="db_connection" class="form-control form-select"
                                                            id="dbConnection">
                                                            <option value="mysql" selected>MySQL / MariaDB</option>
                                                            <option value="pgsql">PostgreSQL</option>
                                                            <option value="sqlsrv">SQL Server</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Hôte</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="feather-server"></i>
                                                        </span>
                                                        <input type="text" name="db_host" class="form-control"
                                                            value="{{ old('db_host', '127.0.0.1') }}" required>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">Port</label>
                                                    <input type="number" name="db_port" class="form-control"
                                                        value="{{ old('db_port', 3306) }}" required>
                                                </div>

                                                <div class="col-md-9 mb-3">
                                                    <label class="form-label">Nom de la base</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="feather-database"></i>
                                                        </span>
                                                        <input type="text" name="db_database" class="form-control"
                                                            value="{{ old('db_database') }}" required>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Utilisateur</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="feather-user"></i>
                                                        </span>
                                                        <input type="text" name="db_username" class="form-control"
                                                            value="{{ old('db_username') }}" required>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Mot de passe</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="feather-lock"></i>
                                                        </span>
                                                        <input type="password" name="db_password" class="form-control"
                                                            id="dbPassword">
                                                        <button class="btn btn-outline-secondary" type="button"
                                                            id="toggleDbPassword">
                                                            <i class="feather-eye"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <button type="button" class="btn btn-outline-primary test-connection-btn"
                                                id="testConnection">
                                                <i class="feather-plug me-2"></i>Tester la connexion
                                            </button>

                                            <div class="mt-3" id="connectionStatus"></div>
                                        </div>
                                    </div>

                                    {{-- ÉTAPE 3: CONFIGURATION --}}
                                    <div class="tab-pane fade" id="step3-content" role="tabpanel">
                                        <h5 class="mb-4">
                                            <i class="feather-settings text-primary me-2"></i>
                                            Configuration de l'application
                                        </h5>

                                        <div class="row">
                                            <div class="col-md-6 mb-4">
                                                <h6 class="mb-3">
                                                    <i class="feather-apps me-2"></i>Paramètres généraux
                                                </h6>

                                                <div class="mb-3">
                                                    <label class="form-label">Nom de l'application</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="feather-apps"></i>
                                                        </span>
                                                        <input type="text" name="app_name" class="form-control"
                                                            value="{{ old('app_name', 'B360') }}" required>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">URL de l'application</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="feather-link"></i>
                                                        </span>
                                                        <input type="url" name="app_url" class="form-control"
                                                            value="{{ old('app_url', url('/')) }}" required>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Fuseau horaire</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="feather-clock"></i>
                                                        </span>
                                                        <select name="timezone" class="form-select" required>
                                                            <option value="">Sélectionner un fuseau</option>
                                                            @foreach (timezone_identifiers_list() as $tz)
                                                                <option value="{{ $tz }}"
                                                                    @selected(old('timezone', config('app.timezone')) === $tz)>
                                                                    {{ $tz }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Langue par défaut</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="feather-globe"></i>
                                                        </span>
                                                        <select name="locale" class="form-select" required>
                                                            <option value="fr" selected>Français</option>
                                                            <option value="en">English</option>
                                                            <option value="es">Español</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-4">
                                                <h6 class="mb-3">
                                                    <i class="feather-layers me-2"></i>Mode d'instance
                                                </h6>

                                                <div class="mb-3">
                                                    <select name="instance_mode" class="form-select" id="instanceMode"
                                                        required>
                                                        <option value="">-- Choisir le mode --</option>
                                                        <option value="single" @selected(old('instance_mode') === 'single')>
                                                            Instance unique
                                                        </option>
                                                        <option value="multi" @selected(old('instance_mode') === 'multi')>
                                                            Instance multiples
                                                        </option>
                                                    </select>
                                                    <small class="text-muted">
                                                        <strong>Single:</strong> Une seule instance de l'application |
                                                        <strong>Multi:</strong> Plusieurs instances / Entreprises / Equipes / ou Equipes
                                                    </small>
                                                </div>

                                                <div class="mb-3">
                                                    <select name="instance_db_strategy" class="form-select" id="instance_db_strategy"
                                                        required>
                                                        <option value="">-- Choisir le mode --</option>
                                                        <option value="shared" @selected(old('instance_db_strategy') === 'single')>
                                                            Base de données unique et partagées
                                                        </option>
                                                        <option value="database-per-instance" @selected(old('instance_db_strategy') === 'multi')>
                                                            Bases de données multiples et unique par instance
                                                        </option>
                                                    </select>
                                                    <small class="text-muted">
                                                        <strong>Single:</strong> Toutes les données dans une base |
                                                        <strong>Multi:</strong> Base séparée par instance
                                                    </small>
                                                </div>

                                                <div id="prefixSuffixFields" class="mt-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">Préfixe DB (optionnel)</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">
                                                                <i class="feather-type"></i>
                                                            </span>
                                                            <input type="text" name="db_prefix" class="form-control"
                                                                value="{{ old('db_prefix') }}" placeholder="dreampos_">
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">Suffixe DB (optionnel)</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">
                                                                <i class="feather-type"></i>
                                                            </span>
                                                            <input type="text" name="db_suffix" class="form-control"
                                                                value="{{ old('db_suffix') }}" placeholder="_prod">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="alert alert-info mt-2">
                                                    <i class="feather-info me-2"></i>
                                                    En mode <strong>multi</strong>, il est conseillé de définir un
                                                    préfixe ou suffixe pour éviter les collisions entre instances.
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <button type="button" class="btn btn-outline-primary"
                                                            id="validateConfigBtn">
                                                            <i class="feather-check-circle me-2"></i>Valider la
                                                            configuration
                                                        </button>
                                                        <div class="mt-3" id="configStatus"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- ÉTAPE 4: ADMINISTRATEUR --}}
                                    <div class="tab-pane fade" id="step4-content" role="tabpanel">
                                        <h5 class="mb-4">
                                            <i class="feather-user-shield text-primary me-2"></i>
                                            Compte Super Administrateur
                                        </h5>

                                        <div class="row">
                                            <div class="col-md-6 mb-4">
                                                <h6 class="mb-3">
                                                    <i class="feather-user me-2"></i>Informations personnelles
                                                </h6>

                                                <div class="mb-3">
                                                    <label class="form-label">Prénom</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="feather-user"></i>
                                                        </span>
                                                        <input type="text" name="admin_firstname" class="form-control"
                                                            value="{{ old('admin_firstname') }}" required>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Nom</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="feather-user"></i>
                                                        </span>
                                                        <input type="text" name="admin_lastname" class="form-control"
                                                            value="{{ old('admin_lastname') }}" required>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Email</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="feather-mail"></i>
                                                        </span>
                                                        <input type="email" name="admin_email" class="form-control"
                                                            value="{{ old('admin_email') }}" required>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Nom d'utilisateur</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="feather-at-sign"></i>
                                                        </span>
                                                        <input type="text" name="admin_username" class="form-control"
                                                            value="{{ old('admin_username') }}" required>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-4">
                                                <h6 class="mb-3">
                                                    <i class="feather-lock me-2"></i>Sécurité du compte
                                                </h6>

                                                <div class="mb-3">
                                                    <label class="form-label">Mot de passe</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="feather-lock"></i>
                                                        </span>
                                                        <input type="password" name="admin_password" class="form-control"
                                                            id="adminPassword" required>
                                                        <button class="btn btn-outline-secondary" type="button"
                                                            id="toggleAdminPassword">
                                                            <i class="feather-eye"></i>
                                                        </button>
                                                    </div>
                                                    <small class="text-muted">Minimum 8 caractères avec majuscules,
                                                        minuscules et chiffres</small>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Confirmer le mot de passe</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="feather-lock"></i>
                                                        </span>
                                                        <input type="password" name="admin_password_confirmation"
                                                            class="form-control" id="adminPasswordConfirm" required>
                                                        <button class="btn btn-outline-secondary" type="button"
                                                            id="toggleAdminPasswordConfirm">
                                                            <i class="feather-eye"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="password-strength mt-4">
                                                    <label class="form-label">Force du mot de passe</label>
                                                    <div class="progress mb-2" style="height: 8px;">
                                                        <div class="progress-bar" id="passwordStrengthBar"
                                                            role="progressbar" style="width: 0%"></div>
                                                    </div>
                                                    <small class="text-muted" id="passwordStrengthText">Très
                                                        faible</small>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <button type="button" class="btn btn-outline-primary"
                                                            id="validateAdminBtn">
                                                            <i class="ti ti-user-check me-2"></i>Valider le compte
                                                            administrateur
                                                        </button>

                                                        <div class="mt-3" id="adminStatus"></div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- ÉTAPE 5: INSTALLATION --}}
                                    <div class="tab-pane fade" id="step5-content" role="tabpanel">
                                        <div class="text-center py-5">
                                            <div class="mb-4">
                                                <div class="spinner-border text-primary"
                                                    style="width: 3rem; height: 3rem;" role="status">
                                                    <span class="visually-hidden">Chargement...</span>
                                                </div>
                                            </div>

                                            <h5 class="mb-3">Installation de DreamPos...</h5>
                                            <p class="text-muted mb-4">Veuillez patienter pendant la configuration du
                                                système.</p>

                                            <div class="progress mb-4" style="height: 12px;">
                                                <div class="progress-bar bg-success progress-bar-striped progress-bar-animated"
                                                    id="installationProgress" role="progressbar" style="width: 0%">
                                                </div>
                                            </div>

                                            <div class="installation-log text-start">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h6 class="mb-0">Journal d'installation</h6>
                                                    </div>
                                                    <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                                                        <div id="installationLog">
                                                            <div class="log-entry mb-2">
                                                                <i class="feather-check-circle text-success me-2"></i>
                                                                <span>Initialisation de l'installation...</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- BOUTONS DE NAVIGATION --}}
                                <div class="mt-4 pt-3 border-top">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <button class="btn btn-outline-secondary" id="prevBtn" disabled>
                                                <i class="feather-arrow-left me-2"></i>Précédent
                                            </button>
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <button class="btn btn-primary" id="nextBtn">
                                                Suivant <i class="feather-arrow-right ms-2"></i>
                                            </button>
                                            <button class="btn btn-success" id="installBtn" style="display:none">
                                                <i class="feather-play me-2"></i>Démarrer l'installation
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', async function() {
                let currentStep = 1;
                const totalSteps = 5;

                // état de validation des étapes
                const stepValid = {
                    1: false,
                    2: false,
                    3: false,
                    4: false,
                    5: true
                };

                // DOM
                const nextBtn = document.getElementById('nextBtn');
                const prevBtn = document.getElementById('prevBtn');
                const installBtn = document.getElementById('installBtn');

                const progressBar = document.getElementById('globalProgress');
                const currentStepEl = document.getElementById('currentStep');

                const tabButtons = {
                    1: document.getElementById('step1-tab'),
                    2: document.getElementById('step2-tab'),
                    3: document.getElementById('step3-tab'),
                    4: document.getElementById('step4-tab'),
                    5: document.getElementById('step5-tab'),
                };

                // Exposer l'état et helpers au global pour testDatabaseConnection()
                window.__installer = window.__installer || {};
                window.__installer.stepValid = stepValid;
                window.__installer.syncUI = syncUI;
                window.__installer.goToStep = goToStep;

                //  Interdire le clic sur une étape non autorisée
                Object.entries(tabButtons).forEach(([step, btn]) => {
                    if (!btn) return;

                    btn.addEventListener('click', function(e) {
                        const targetStep = parseInt(step, 10);
                        const maxAllowed = getMaxAllowedStep();

                        if (targetStep > maxAllowed) {
                            e.preventDefault();
                            e.stopPropagation();
                            showAlert("Tu dois valider l'étape précédente avant de continuer.",
                                'danger');
                            return false;
                        }

                        currentStep = targetStep;
                        syncUI();
                    });
                });

                //  Boutons précédent/suivant
                prevBtn.addEventListener('click', function() {
                    if (currentStep <= 1) return;
                    currentStep--;
                    goToStep(currentStep);
                });

                nextBtn.addEventListener('click', async function() {
                    // Bloquer si étape courante invalide
                    const ok = await validateStep(currentStep);
                    if (!ok) {
                        showAlert("Impossible de continuer : l'étape courante n'est pas validée.",
                            'danger');
                        return;
                    }

                    if (currentStep < totalSteps) {
                        currentStep++;
                        goToStep(currentStep);
                    }
                });

                //netoyage des onglets precedents lors du changement d'onglet
                /* document.addEventListener('show.bs.tab', function (event) {
                     const container = document.getElementById('installationContent');
                     if (!container) return;

                     // Retire active/show uniquement dans CE tab-content
                     container.querySelectorAll('.tab-pane.show.active').forEach(p => {
                         p.classList.remove('show', 'active');
                     });
                 });*/

                //  Initial : vérifie prérequis dès le chargement
                await runRequirementsCheck();
                syncUI();

                // Hook bouton test connexion
                const testBtn = document.getElementById('testConnection');
                if (testBtn) {
                    testBtn.addEventListener('click', testDatabaseConnection);
                }

                // Hook bouton valider config
                const validateConfigBtn = document.getElementById('validateConfigBtn');
                if (validateConfigBtn) {
                    validateConfigBtn.addEventListener('click', validateConfigurationStep);
                }
                // Hook bouton valider admin
                const validateAdminBtn = document.getElementById('validateAdminBtn');
                if (validateAdminBtn) {
                    validateAdminBtn.addEventListener('click', validateAdminStep);
                }

                // -------------------------
                // Navigation / UI
                // -------------------------
                function goToStep(step) {
                    const btn = tabButtons[step];
                    if (!btn) return;

                    const maxAllowed = getMaxAllowedStep();
                    if (step > maxAllowed) {
                        showAlert("Étape verrouillée : valide l'étape précédente.", 'warning');
                        return;
                    }

                    bootstrap.Tab.getOrCreateInstance(btn).show();
                    syncUI();
                }

                function syncUI() {
                    // progress
                    const percent = Math.round((currentStep - 1) / (totalSteps - 1) * 100);
                    progressBar.style.width = percent + '%';
                    currentStepEl.textContent = currentStep;

                    // prev enabled
                    prevBtn.disabled = currentStep === 1;

                    // afficher Install btn uniquement à l'étape 5
                    if (currentStep === 5) {
                        nextBtn.style.display = 'none';
                        installBtn.style.display = 'inline-block';
                    } else {
                        nextBtn.style.display = 'inline-block';
                        installBtn.style.display = 'none';
                    }

                    // Désactiver visuellement les tabs non accessibles
                    const maxAllowed = getMaxAllowedStep();
                    for (let i = 1; i <= totalSteps; i++) {
                        tabButtons[i].classList.toggle('disabled', i > maxAllowed);
                        tabButtons[i].setAttribute('aria-disabled', i > maxAllowed ? 'true' : 'false');
                    }
                    // Griser "Suivant" selon l'étape courante
                    if (currentStep === 1) nextBtn.disabled = !stepValid[1];
                    else if (currentStep === 2) nextBtn.disabled = !stepValid[2];
                    else if (currentStep === 3) nextBtn.disabled = !stepValid[3];
                    else if (currentStep === 4) nextBtn.disabled = !stepValid[4];
                    else nextBtn.disabled = false;

                }

                function updatePrefixSuffixUI() {
                    const mode = document.getElementById('instanceMode')?.value;
                    const box = document.getElementById('prefixSuffixFields');
                    if (!box) return;

                    const isMulti = mode === 'multi';
                    box.style.display = isMulti ? 'block' : 'none';

                    // en single, on vide pour éviter qu'ils restent stockés par erreur côté front
                    if (!isMulti) {
                        box.querySelectorAll('input').forEach(i => i.value = '');
                    }
                }

                document.getElementById('instanceMode')?.addEventListener('change', updatePrefixSuffixUI);
                updatePrefixSuffixUI();

                function getMaxAllowedStep() {
                    let max = 1;
                    for (let s = 2; s <= totalSteps; s++) {
                        if (stepValid[s - 1] === true) max = s;
                        else break;
                    }
                    return max;
                }

                // -------------------------
                // Validation
                // -------------------------
                async function validateStep(step) {
                    if (step === 1) {
                        // si déjà validée, ok
                        if (stepValid[1] === true) return true;
                        // sinon relance check
                        return await runRequirementsCheck();
                    }
                    if (step === 2) {
                        //  Ne pas autoriser step3 tant que test DB pas OK
                        return stepValid[2] === true;
                    }
                    if (step === 3) {
                        //  Ne pas autoriser step4 tant que test DB pas OK
                        return stepValid[3] === true;
                    }
                    if (step === 4) {
                        //  Ne pas autoriser step5 tant que test DB pas OK
                        return stepValid[4] === true;
                    }

                    return true;
                }

                async function runRequirementsCheck() {
                    // UI -> loading (garde tes fonctions existantes)
                    setReqRowState('php', 'loading', 'Vérification...');
                    setReqRowState('req-extensions', 'loading', 'Vérification...');
                    setReqRowState('req-permissions', 'loading', 'Vérification...');
                    setReqRowState('req-env', 'loading', 'Vérification...');

                    try {
                        const res = await fetch("{{ route('installer.requirements') }}", {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const data = await res.json();

                        const phpText = data.php.ok ? 'OK' : 'KO';
                        setReqRowState('php', data.php.ok ? 'ok' : 'ko',
                            `${data.php.current} (min ${data.php.min})`, phpText);

                        if (data.extensions.ok) setReqRowState('req-extensions', 'ok', 'Toutes présentes',
                            'OK');
                        else setReqRowState('req-extensions', 'ko', 'Manquantes : ' + data.extensions.missing
                            .join(', '), 'KO');

                        if (data.permissions.ok) setReqRowState('req-permissions', 'ok', 'Écriture OK', 'OK');
                        else setReqRowState('req-permissions', 'ko', 'Non writable : ' + data.permissions
                            .not_writable.join(', '), 'KO');

                        if (data.env.ok) setReqRowState('req-env', 'ok', data.env.exists ? '.env writable' :
                            '.env à créer depuis .env.example', 'OK');
                        else setReqRowState('req-env', 'ko', 'Impossible d’écrire le .env / base_path()', 'KO');

                        stepValid[1] = !!data.ok;

                        if (stepValid[1]) showAlert("Prérequis validés. Tu peux passer à l'étape 2.",
                            'success');
                        else showAlert("Des prérequis sont en échec. Corrige-les avant de continuer.",
                            'danger');

                        syncUI();
                        return stepValid[1];
                    } catch (e) {
                        stepValid[1] = false;
                        showAlert("Erreur lors de la vérification des prérequis : " + e.message, 'danger');
                        syncUI();
                        return false;
                    }
                }

                function unlockTab(step) {
                    const btn = tabButtons[step];
                    if (!btn) return;
                    btn.classList.remove('disabled');
                    btn.setAttribute('aria-disabled', 'false');
                }

                function lockNext(lock) {
                    nextBtn.disabled = !!lock;
                }

                // helper: met à jour la ligne (badge + valeur)
                function setReqRowState(rowId, state, valueText, badgeText) {
                    // rowId peut être 'php' (cas spécial) ou un tr id=...
                    if (rowId === 'php') {
                        const badge = document.getElementById('phpBadge');
                        const value = document.getElementById('phpValue');
                        if (!badge || !value) return;

                        value.textContent = valueText || value.textContent;
                        applyBadgeState(badge, state, badgeText);
                        return;
                    }

                    const row = document.getElementById(rowId);
                    if (!row) return;

                    // suppose: 2e colonne contient le badge, 3e colonne contient la valeur
                    const badge = row.querySelector('.badge');
                    const valueCell = row.querySelectorAll('td')[2];

                    if (valueCell && valueText) valueCell.textContent = valueText;
                    if (badge) applyBadgeState(badge, state, badgeText);
                }

                function applyBadgeState(badgeEl, state, text) {
                    badgeEl.classList.remove('bg-success', 'bg-danger', 'bg-secondary');
                    if (state === 'ok') badgeEl.classList.add('bg-success');
                    else if (state === 'ko') badgeEl.classList.add('bg-danger');
                    else badgeEl.classList.add('bg-secondary');

                    badgeEl.innerHTML = state === 'ok' ?
                        `<i class="fas fa-check-circle me-1"></i>${text || 'OK'}` :
                        state === 'ko' ?
                        `<i class="fas fa-times-circle me-1"></i>${text || 'KO'}` :
                        `<i class="fas fa-circle-notch fa-spin me-1"></i>${text || '...'}`;
                }

                function showAlert(message, type = 'info') {
                    const container = document.querySelector('#step1-content') || document.querySelector(
                        '.card-body');
                    const alert = document.createElement('div');
                    alert.className = `alert alert-${type} alert-dismissible fade show mt-3`;
                    alert.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    container.prepend(alert);

                    setTimeout(() => {
                        if (alert && alert.parentNode) alert.remove();
                    }, 5000);
                }

                function forceTab(tabId) {
                    // Désactiver tous les panes
                    document.querySelectorAll('.tab-pane').forEach(pane => {
                        pane.classList.remove('show', 'active');
                    });

                    // Désactiver tous les tabs
                    document.querySelectorAll('.nav-link[data-bs-toggle="tab"]').forEach(tab => {
                        tab.classList.remove('active');
                    });

                    // Activer le pane ciblé
                    const targetPane = document.querySelector(tabId);
                    if (targetPane) {
                        targetPane.classList.add('show', 'active');
                    }

                    // Activer l’onglet correspondant
                    const targetTab = document.querySelector(
                        `.nav-link[data-bs-target="${tabId}"]`
                    );
                    if (targetTab) {
                        targetTab.classList.add('active');
                    }
                }

                // 1) on attache le click ici
                const btn = document.getElementById('testConnection');
                if (btn) {
                    btn.addEventListener('click', testDatabaseConnection);
                }
                window.__installer.syncUI = syncUI;
                window.__installer.goToStep = goToStep;
                window.__installer.tabButtons = tabButtons;

            });
            async function testDatabaseConnection() {
                const btn = document.getElementById('testConnection');
                const statusDiv = document.getElementById('connectionStatus');

                const installer = window.__installer;
                const stepValid = installer?.stepValid;

                if (!stepValid) {
                    console.error('Wizard non initialisé: window.__installer.stepValid manquant.');
                    return;
                }

                btn.disabled = true;
                btn.innerHTML = '<i class="ti ti-loader me-2"></i>Test en cours...';

                statusDiv.className = 'connection-status';
                statusDiv.innerHTML = '';

                const payload = {
                    environment_mode: document.querySelector('[name="environment_mode"]')?.value || 'prod',
                    db_connection: document.querySelector('[name="db_connection"]')?.value,
                    db_host: document.querySelector('[name="db_host"]')?.value,
                    db_port: document.querySelector('[name="db_port"]')?.value,
                    db_database: document.querySelector('[name="db_database"]')?.value,
                    db_username: document.querySelector('[name="db_username"]')?.value,
                    db_password: document.querySelector('[name="db_password"]')?.value,
                    create_database: document.querySelector('[name="create_database"]')?.checked ? 1 : 0,
                    db_prefix: document.querySelector('[name="db_prefix"]')?.value || '',
                    db_suffix: document.querySelector('[name="db_suffix"]')?.value || '',
                };

                try {
                    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    const csrf = csrfMeta ? csrfMeta.content : null;

                    if (!csrf) {
                        statusDiv.className = 'connection-status error';
                        statusDiv.innerHTML =
                            `<i class="ti ti-alert-circle me-2"></i>CSRF token introuvable (meta csrf-token manquante).`;
                        stepValid[2] = false;
                        installer?.syncUI?.();
                        return;
                    }

                    const res = await fetch("{{ route('installer.database.test') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    });

                    const data = await res.json();

                    if (!res.ok || !data.ok) {
                        statusDiv.className = 'connection-status error';
                        statusDiv.innerHTML =
                            `<i class="ti ti-alert-circle me-2"></i>${data.message || 'Échec de connexion'}`;
                        stepValid[2] = false;
                    } else {
                        statusDiv.className = 'connection-status success';
                        statusDiv.innerHTML = `<i class="ti ti-circle-check me-2"></i>${data.message}`;
                        stepValid[2] = true;
                    }

                    //  met à jour UI (déverrouille tab 3 + active "Suivant")
                    installer?.syncUI?.();

                } catch (e) {
                    statusDiv.className = 'connection-status error';
                    statusDiv.innerHTML = `<i class="ti ti-alert-circle me-2"></i>Erreur réseau: ${e.message}`;
                    stepValid[2] = false;
                    installer?.syncUI?.();
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ti ti-plug-connected me-2"></i>Tester la connexion';
                }
            }
            async function validateConfigurationStep() {
                const installer = window.__installer;
                const stepValid = installer?.stepValid;

                const statusDiv = document.getElementById('configStatus');
                const btn = document.getElementById('validateConfigBtn');

                if (!stepValid) return;

                btn.disabled = true;
                btn.innerHTML = '<i class="feather-loader me-2"></i>Validation...';

                statusDiv.className = '';
                statusDiv.innerHTML = '';

                const payload = {
                    app_name: document.querySelector('[name="app_name"]')?.value,
                    app_url: document.querySelector('[name="app_url"]')?.value,
                    timezone: document.querySelector('[name="timezone"]')?.value,
                    locale: document.querySelector('[name="locale"]')?.value,
                    instance_mode: document.querySelector('[name="instance_mode"]')?.value,
                    db_prefix: document.querySelector('[name="db_prefix"]')?.value || '',
                    db_suffix: document.querySelector('[name="db_suffix"]')?.value || '',
                };

                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                    const res = await fetch("{{ route('installer.configuration.validate') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    });

                    const data = await res.json();

                    if (!res.ok || !data.ok) {
                        stepValid[3] = false;
                        statusDiv.className = 'alert alert-danger';
                        statusDiv.innerHTML =
                            `<i class="feather-alert-circle me-2"></i>${data.message || 'Erreur validation'}`;

                        // Affichage erreurs champs si présentes
                        if (data.errors) {
                            const list = Object.values(data.errors).flat().map(e => `<li>${e}</li>`).join('');
                            statusDiv.innerHTML += `<ul class="mb-0 mt-2">${list}</ul>`;
                        }
                    } else {
                        stepValid[3] = true;
                        statusDiv.className = 'alert alert-success';
                        statusDiv.innerHTML = `<i class="feather-check-circle me-2"></i>${data.message}`;
                    }

                    installer?.syncUI?.();
                } catch (e) {
                    stepValid[3] = false;
                    statusDiv.className = 'alert alert-danger';
                    statusDiv.innerHTML = `<i class="feather-alert-circle me-2"></i>Erreur réseau: ${e.message}`;
                    installer?.syncUI?.();
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="feather-check-circle me-2"></i>Valider la configuration';
                }
            }
            async function validateAdminStep() {
                const statusDiv = document.getElementById('adminStatus');
                const btn = document.getElementById('validateAdminBtn');

                const installer = window.__installer;
                const stepValid = installer?.stepValid;

                if (!stepValid) {
                    console.error('Wizard non initialisé: window.__installer.stepValid manquant.');
                    return;
                }

                btn.disabled = true;
                btn.innerHTML = '<i class="ti ti-loader me-2"></i>Validation...';
                statusDiv.className = '';
                statusDiv.innerHTML = '';

                const payload = {
                    admin_firstname: document.querySelector('[name="admin_firstname"]')?.value,
                    admin_lastname: document.querySelector('[name="admin_lastname"]')?.value,
                    admin_email: document.querySelector('[name="admin_email"]')?.value,
                    admin_username: document.querySelector('[name="admin_username"]')?.value,
                    admin_password: document.querySelector('[name="admin_password"]')?.value,
                    admin_password_confirmation: document.querySelector('[name="admin_password_confirmation"]')?.value,
                };

                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                    if (!csrf) {
                        stepValid[4] = false;
                        statusDiv.className = 'alert alert-danger';
                        statusDiv.innerHTML = `<i class="ti ti-alert-circle me-2"></i>CSRF token introuvable.`;
                        installer?.syncUI?.();
                        return;
                    }

                    const res = await fetch("{{ route('installer.admin.validate') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    });

                    const data = await res.json();

                    if (!res.ok || !data.ok) {
                        stepValid[4] = false;
                        statusDiv.className = 'alert alert-danger';
                        statusDiv.innerHTML =
                            `<i class="ti ti-alert-circle me-2"></i>${data.message || 'Erreur validation'}`;

                        if (data.errors) {
                            const list = Object.values(data.errors).flat().filter(Boolean).map(e => `<li>${e}</li>`).join(
                                '');
                            if (list) statusDiv.innerHTML += `<ul class="mb-0 mt-2">${list}</ul>`;
                        }
                    } else {
                        stepValid[4] = true;
                        statusDiv.className = 'alert alert-success';
                        statusDiv.innerHTML = `<i class="ti ti-circle-check me-2"></i>${data.message}`;
                    }

                    installer?.syncUI?.(); // ✅ même refresh que les autres
                } catch (e) {
                    stepValid[4] = false;
                    statusDiv.className = 'alert alert-danger';
                    statusDiv.innerHTML = `<i class="ti ti-alert-circle me-2"></i>Erreur réseau: ${e.message}`;
                    installer?.syncUI?.();
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ti ti-user-check me-2"></i>Valider le compte administrateur';
                }
            }
            async function startInstallationAjax() {
                const installer = window.__installer;
                const stepValid = installer?.stepValid;
                if (!stepValid || stepValid[4] !== true) {
                    showAlert("Valide d'abord l'étape 4 (Super Admin).", "danger");
                    return;
                }

                // bascule Step 5
                installer.goToStep?.(5); // si tu as cette helper, sinon bootstrap.Tab.show sur step5-tab

                const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

                // 1) start -> get stream url
                const res = await fetch("{{ route('installer.start') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                });

                const data = await res.json();
                if (!res.ok || !data.ok) {
                    showAlert(data.message || "Impossible de démarrer l'installation.", "danger");
                    return;
                }

                // 2) SSE stream
                openInstallStream(data.stream_url);
            }

            function openInstallStream(streamUrl) {
                const progressBar = document.getElementById('installationProgress');
                const logContainer = document.getElementById('installationLog');

                const es = new EventSource(streamUrl);

                const addLog = (message, type = 'info') => {
                    const row = document.createElement('div');
                    row.className = 'log-entry mb-2';

                    const icon = type === 'error' ?
                        'feather-alert-circle text-danger' :
                        'feather-check-circle text-success';

                    row.innerHTML = `<i class="${icon} me-2"></i><span>${escapeHtml(message)}</span>`;
                    logContainer.appendChild(row);

                    // scroll bottom
                    logContainer.parentElement.scrollTop = logContainer.parentElement.scrollHeight;
                };

                es.addEventListener('log', (e) => {
                    const payload = JSON.parse(e.data);
                    addLog(payload.message);
                });

                es.addEventListener('progress', (e) => {
                    const payload = JSON.parse(e.data);
                    const p = Math.max(0, Math.min(100, parseInt(payload.percent, 10) || 0));
                    progressBar.style.width = p + '%';
                });

                es.addEventListener('done', (e) => {
                    const payload = JSON.parse(e.data);
                    addLog(payload.message || "Installation terminée.");
                    progressBar.style.width = '100%';
                    es.close();

                    setTimeout(() => {
                        window.location.href = payload.redirect || '/login';
                    }, 1200);
                });

                es.addEventListener('error', (e) => {
                    // SSE peut émettre "error" natif : on tente de lire payload
                    try {
                        const payload = e?.data ? JSON.parse(e.data) : null;
                        addLog(payload?.message || "Erreur pendant l'installation.", 'error');
                    } catch (_) {
                        addLog("Erreur pendant l'installation (stream interrompu).", 'error');
                    }
                    es.close();
                });
            }

            function escapeHtml(str) {
                return String(str).replace(/[&<>"']/g, (m) => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                } [m]));
            }
                document.addEventListener('DOMContentLoaded', function() {
                    const installBtn = document.getElementById('installBtn');
                    if (installBtn) installBtn.addEventListener('click', startInstallationAjax);
                });

        </script>
    @endpush

</x-installer::layouts.master>
