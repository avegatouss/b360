<?php

namespace Modules\Installer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Installer\Services\EnvWriter;
use Modules\Installer\Services\InstallerRunner;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\StreamedResponse;

use PDO;
use PDOException;

class InstallerController extends Controller
{
    /**
     * Affiche l’écran principal de l’installateur.
     */
    public function index()
    {
        return view('installer::index');
    }

    /**
     * Traite la soumission du formulaire d’installation.
     *
     * À ce stade :
     * - aucune écriture disque
     * - aucune migration
     * - aucune création de base
     *
     * On valide et on prépare les données uniquement.
     */
    public function installv0(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validation stricte
        |--------------------------------------------------------------------------
        */

        $validator = Validator::make($request->all(), [

            // Application
            'app_name' => ['required', 'string', 'max:255'],
            'app_url'  => ['required', 'url'],
            'timezone' => ['required', 'string'],
            'locale'   => ['required', 'string', 'max:5'],

            // Base centrale
            'db_host'     => ['required', 'string'],
            'db_port'     => ['required', 'numeric'],
            'db_database' => ['required', 'string'],
            'db_username' => ['required', 'string'],
            'db_password' => ['nullable', 'string'],

            // Mode Instance
            'instance_mode' => ['required', 'in:single,multi'],
            'db_prefix'     => ['nullable', 'string'],
            'db_suffix'     => ['nullable', 'string'],

            // Super Admin
            'admin_firstname' => ['required', 'string', 'max:100'],
            'admin_lastname'  => ['required', 'string', 'max:100'],
            'admin_username'  => ['required', 'string', 'max:100'],
            'admin_password'  => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Stockage temporaire en session
        |--------------------------------------------------------------------------
        |
        | Ces données seront utilisées par les étapes suivantes :
        | - écriture du .env
        | - migrations
        | - seed
        |
        */

        session([
            'installer' => $validator->validated(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Étape suivante
        |--------------------------------------------------------------------------
        |
        | Pour l’instant on reste sur la même page.
        | Une confirmation ou une étape 2 sera ajoutée ensuite.
        |
        */
        try {

            app(EnvWriter::class)->write($validator->validated());
        } catch (\Throwable $e) {

            return back()
                ->withErrors(['env' => $e->getMessage()])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Nettoyage cache config
        |--------------------------------------------------------------------------
        */
        Artisan::call('config:clear');

        return redirect('/')
            ->with('success', 'Installation terminée. Application prête.');
    }

    public function install(Request $request)
    {
        $steps = session('installer.steps', []);
        $installer = session('installer', []);

        $required = [1, 2, 3, 4];
        foreach ($required as $s) {
            if (!($steps[$s] ?? false)) {
                return back()->withErrors(['install' => "Installation bloquée : étape {$s} non validée."]);
            }
        }

        if (!($installer['db_confirmed'] ?? false) || !($installer['config_confirmed'] ?? false) || !($installer['admin_confirmed'] ?? false)) {
            return back()->withErrors(['install' => "Installation bloquée : validations serveur incomplètes."]);
        }

        $validator = Validator::make($request->all(), [
            // règles inchangées
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            app(InstallerRunner::class)->run($validator->validated());
        } catch (\Throwable $e) {
            return back()
                ->withErrors(['install' => $e->getMessage()])
                ->withInput();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')
            ->with('success', 'Installation terminée avec succès.');
    }

    public function requirements(Request $request)
    {
        //  version php
        $phpMin = '8.2.0';
        $phpOk = version_compare(PHP_VERSION, $phpMin, '>=');

        $requiredExtensions = ['pdo', 'mbstring', 'tokenizer', 'xml', 'ctype', 'json', 'openssl'];
        $missingExtensions = array_values(array_filter($requiredExtensions, fn($ext) => !extension_loaded($ext)));
        $extensionsOk = count($missingExtensions) === 0;

        // Permissions
        $pathsToWrite = [
            storage_path(),
            base_path('bootstrap/cache'),
        ];

        $permissionErrors = [];
        foreach ($pathsToWrite as $p) {
            if (!is_writable($p)) {
                $permissionErrors[] = $p;
            }
        }
        $permissionsOk = count($permissionErrors) === 0;

        // .env writable / ou template .env.example
        $envPath = base_path('.env');
        $envExamplePath = base_path('.env.example');

        $envOk = File::exists($envPath)
            ? is_writable($envPath)
            : File::exists($envExamplePath) && is_writable(base_path());

        $allOk = $phpOk && $extensionsOk && $permissionsOk && $envOk;

        //  On enregistre l’état en session pour verrouiller la navigation
        session([
            'installer.steps' => array_replace(session('installer.steps', []), [
                1 => $allOk,
            ]),
        ]);

        return response()->json([
            'ok' => $allOk,
            'php' => [
                'min' => $phpMin,
                'current' => PHP_VERSION,
                'ok' => $phpOk,
            ],
            'extensions' => [
                'required' => $requiredExtensions,
                'missing' => $missingExtensions,
                'ok' => $extensionsOk,
            ],
            'permissions' => [
                'paths' => $pathsToWrite,
                'not_writable' => $permissionErrors,
                'ok' => $permissionsOk,
            ],
            'env' => [
                'path' => $envPath,
                'exists' => File::exists($envPath),
                'ok' => $envOk,
            ],
        ]);
    }

    public function testDatabase(Request $request)
    {
        //  Exiger étape 1 validée
        $steps = session('installer.steps', []);
        if (!($steps[1] ?? false)) {
            return response()->json([
                'ok' => false,
                'message' => 'Pré-requis non validés. Retourne à l’étape 1.',
            ], 422);
        }

        //  Validation step 2 + nouveaux paramètres
        $validator = Validator::make($request->all(), [
            'environment_mode' => ['required', 'in:demo,prod'], //  nouveau
            'db_connection'    => ['required', 'in:mysql,pgsql,sqlsrv'],
            'db_host'          => ['required', 'string', 'max:255'],
            'db_port'          => ['required', 'integer', 'min:1', 'max:65535'],
            'db_database'      => ['required', 'string', 'max:64'],
            'db_username'      => ['required', 'string', 'max:64'],
            'db_password'      => [
                'nullable',
                'string',
                'max:255',
                // prod => obligatoire
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('environment_mode') === 'prod' && ($value === null || $value === '')) {
                        $fail('Le mot de passe DB est obligatoire en mode production.');
                    }
                }
            ],
            'create_database'   => ['nullable', 'boolean'], //  si on doit créer si absente

            //  Futur : on stocke, sans appliquer ici
            'db_prefix'         => ['nullable', 'string', 'max:32'],
            'db_suffix'         => ['nullable', 'string', 'max:32'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Validation échouée',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        //  Hardening : éviter des noms DB dangereux
        // (tu peux élargir selon tes conventions)
        if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $data['db_database'])) {
            return response()->json([
                'ok' => false,
                'message' => 'Nom de base invalide (caractères autorisés: lettres, chiffres, _, -).',
            ], 422);
        }

        try {
            // 1) Connexion serveur (sans choisir forcément la DB cible)
            [$pdo, $serverDb] = $this->pdoConnectForAdminOps($data);

            // 2) Vérifier existence DB
            $exists = $this->databaseExists($pdo, $data['db_connection'], $data['db_database']);

            // 3) Si n’existe pas, créer si autorisé
            $created = false;
            $createWanted = (bool)($data['create_database'] ?? false);

            if (!$exists) {
                if (!$createWanted) {
                    // Bloque step 2
                    session([
                        'installer.steps' => array_replace(session('installer.steps', []), [2 => false]),
                    ]);

                    return response()->json([
                        'ok' => false,
                        'db_exists' => false,
                        'db_created' => false,
                        'message' => "La base '{$data['db_database']}' n'existe pas. Active l'option « Créer la base » ou crée-la manuellement.",
                    ], 422);
                }

                $this->createDatabase($pdo, $data['db_connection'], $data['db_database']);
                $created = true;

                // re-check
                $exists = $this->databaseExists($pdo, $data['db_connection'], $data['db_database']);
                if (!$exists) {
                    throw new \RuntimeException("Création demandée mais la base n'est toujours pas détectée.");
                }
            }

            // 4) Tester connexion directe à la DB cible
            $this->pdoConnectToTargetDb($data);

            //  Stockage session step2 + données
            session([
                'installer' => array_replace(session('installer', []), [
                    'environment_mode' => $data['environment_mode'],
                    'db_connection' => $data['db_connection'],
                    'db_host' => $data['db_host'],
                    'db_port' => $data['db_port'],
                    'db_database' => $data['db_database'],
                    'db_username' => $data['db_username'],
                    'db_password' => $data['db_password'] ?? '',
                    'db_confirmed' => true,
                    'create_database' => $createWanted,

                    // futur
                    'db_prefix' => $data['db_prefix'] ?? null,
                    'db_suffix' => $data['db_suffix'] ?? null,
                ]),
                'installer.steps' => array_replace(session('installer.steps', []), [2 => true]),
            ]);
            return response()->json([
                'ok' => true,
                'db_exists' => $created ? true : true,
                'db_created' => $created,
                'db_confirmed' => true,
                'message' => $created
                    ? "Connexion OK. Base créée et validée."
                    : "Connexion OK. Base détectée et validée.",
            ]);
        } catch (\Throwable $e) {
            session([
                'installer.steps' => array_replace(session('installer.steps', []), [2 => false]),
            ]);

            return response()->json([
                'ok' => false,
                'message' => $this->sanitizeDbError($e->getMessage()),
            ], 422);
        }
    }
    private function pdoConnectForAdminOps(array $data): array
    {
        $driver = $data['db_connection'];
        $host = $data['db_host'];
        $port = (int)$data['db_port'];
        $user = $data['db_username'];
        $pass = $data['db_password'] ?? '';

        // DB “système” selon SGBD
        $serverDb = match ($driver) {
            'mysql' => null,           // mysql: pas besoin de DB
            'pgsql' => 'postgres',      // base par défaut
            'sqlsrv' => 'master',       // base système
        };

        $dsn = match ($driver) {
            'mysql'  => "mysql:host={$host};port={$port};charset=utf8mb4",
            'pgsql'  => "pgsql:host={$host};port={$port};dbname={$serverDb}",
            'sqlsrv' => "sqlsrv:Server={$host},{$port};Database={$serverDb}",
        };

        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 5,
        ]);

        return [$pdo, $serverDb];
    }

    private function pdoConnectToTargetDb(array $data): void
    {
        $driver = $data['db_connection'];
        $host = $data['db_host'];
        $port = (int)$data['db_port'];
        $db = $data['db_database'];
        $user = $data['db_username'];
        $pass = $data['db_password'] ?? '';

        $dsn = match ($driver) {
            'mysql'  => "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4",
            'pgsql'  => "pgsql:host={$host};port={$port};dbname={$db}",
            'sqlsrv' => "sqlsrv:Server={$host},{$port};Database={$db}",
        };

        new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
    }

    private function databaseExists(PDO $pdo, string $driver, string $dbName): bool
    {
        return match ($driver) {
            'mysql' => (function () use ($pdo, $dbName) {
                $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
                $stmt->execute([$dbName]);
                return (bool)$stmt->fetchColumn();
            })(),
            'pgsql' => (function () use ($pdo, $dbName) {
                $stmt = $pdo->prepare("SELECT 1 FROM pg_database WHERE datname = ?");
                $stmt->execute([$dbName]);
                return (bool)$stmt->fetchColumn();
            })(),
            'sqlsrv' => (function () use ($pdo, $dbName) {
                $stmt = $pdo->prepare("SELECT 1 FROM sys.databases WHERE name = ?");
                $stmt->execute([$dbName]);
                return (bool)$stmt->fetchColumn();
            })(),
        };
    }

    private function createDatabase(PDO $pdo, string $driver, string $dbName): void
    {
        match ($driver) {
            'mysql' => $pdo->exec("CREATE DATABASE `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"),
            'pgsql' => $pdo->exec('CREATE DATABASE "' . str_replace('"', '""', $dbName) . '"'),
            //'sqlsrv' => $pdo->exec("CREATE DATABASE [" . str_replace(']', ']]', $dbName) . "]"),
            'sqlsrv' => $pdo->exec("CREATE DATABASE [{$dbName}]"),
        };
    }

    private function sanitizeDbError(string $msg): string
    {
        // On évite de renvoyer des infos sensibles / DSN
        $msg = preg_replace('/(password=)[^;]+/i', '$1***', $msg);
        return $msg;
    }

    public function validateConfiguration(Request $request)
    {
        //  Exiger étapes 1 & 2 validées (et DB confirmée)
        $steps = session('installer.steps', []);
        $installer = session('installer', []);

        if (!($steps[1] ?? false)) {
            return response()->json(['ok' => false, 'message' => "Étape 1 non validée."], 422);
        }

        if (!($steps[2] ?? false) || !($installer['db_confirmed'] ?? false)) {
            return response()->json(['ok' => false, 'message' => "Base de données non confirmée. Retourne à l’étape 2 et teste la connexion."], 422);
        }

        //  Validation step 3
        $validator = Validator::make($request->all(), [
            'app_name' => ['required', 'string', 'max:255'],
            'app_url'  => ['required', 'url'],
            'timezone' => ['required', 'string'],
            'locale'   => ['required', 'string', 'max:5'],

            'instance_mode' => ['required', 'in:single,multi'],

            // futur : db_prefix / db_suffix
            'db_prefix' => ['nullable', 'string', 'max:32', 'regex:/^[a-zA-Z0-9_]*$/'],
            'db_suffix' => ['nullable', 'string', 'max:32', 'regex:/^[a-zA-Z0-9_]*$/'],
        ], [
            'db_prefix.regex' => 'Le préfixe DB ne peut contenir que lettres, chiffres et underscore (_).',
            'db_suffix.regex' => 'Le suffixe DB ne peut contenir que lettres, chiffres et underscore (_).',
        ]);

        //  Règles conditionnelles
        $validator->after(function ($v) use ($request) {
            $mode = $request->input('instance_mode');

            // single => on ignore prefix/suffix (mais on peut les stocker vides)
            if ($mode === 'single') {
                return;
            }

            // multi => au moins l’un des deux conseillé (pas obligatoire, mais on peut imposer)
            // on ne bloque pas, mais on avertira côté UI.
            // Si tu veux imposer : on décommente
            /*
            $prefix = (string) $request->input('db_prefix');
            $suffix = (string) $request->input('db_suffix');
            if ($prefix === '' || $suffix === '') {
                $v->errors()->add('db_prefix', "En mode multi, définis un préfixe ou suffixe pour éviter les collisions.");
            }*/
        });

        if ($validator->fails()) {
            session([
                'installer.steps' => array_replace(session('installer.steps', []), [3 => false]),
                'installer' => array_replace(session('installer', []), ['config_confirmed' => false]),
            ]);
            return response()->json([
                'ok' => false,
                'message' => 'Validation échouée',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        //  Stockage en session
        session([
            'installer' => array_replace(session('installer', []), [
                'app_name' => $data['app_name'],
                'app_url' => $data['app_url'],
                'timezone' => $data['timezone'],
                'locale' => $data['locale'],
                'instance_mode' => $data['instance_mode'],

                // futur : juste stockage, pas d’application ici
                'db_prefix' => $data['instance_mode'] === 'multi' ? ($data['db_prefix'] ?? null) : null,
                'db_suffix' => $data['instance_mode'] === 'multi' ? ($data['db_suffix'] ?? null) : null,

                'config_confirmed' => true,
            ]),
            'installer.steps' => array_replace(session('installer.steps', []), [3 => true]),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Configuration validée. Tu peux passer à l’étape 4.',
        ]);
    }

    public function validateAdmin(Request $request)
    {
        //  Exiger étapes 1-2-3 validées + flags confirmés
        $steps = session('installer.steps', []);
        $installer = session('installer', []);

        if (!($steps[1] ?? false)) {
            return response()->json(['ok' => false, 'message' => "Étape 1 non validée."], 422);
        }
        if (!($steps[2] ?? false) || !($installer['db_confirmed'] ?? false)) {
            return response()->json(['ok' => false, 'message' => "Étape 2 non confirmée."], 422);
        }
        if (!($steps[3] ?? false) || !($installer['config_confirmed'] ?? false)) {
            return response()->json(['ok' => false, 'message' => "Étape 3 non confirmée."], 422);
        }

        //  Validation stricte step 4
        $validator = Validator::make($request->all(), [
            'admin_firstname' => ['required', 'string', 'max:100'],
            'admin_lastname'  => ['required', 'string', 'max:100'],
            'admin_email'     => ['required', 'email', 'max:190'],
            'admin_username'  => ['required', 'string', 'max:100'],
            'admin_password'  => ['required', 'string', 'min:8'],
            'admin_password_confirmation' => ['required', 'same:admin_password'],
        ], [
            'admin_password_confirmation.same' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        if ($validator->fails()) {
            session([
                'installer.steps' => array_replace(session('installer.steps', []), [4 => false]),
                'installer' => array_replace(session('installer', []), ['admin_confirmed' => false]),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Validation échouée',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        /**
         * vérifier unicité email/username si DB accessible
         * (à ce stade, la DB centrale existe et la connexion a été testée)
         *  Ici je suppose que la table users peut ne pas exister encore
         * (avant migrations). Donc on fait une vérif "safe" :
         * - si table users existe => check
         * - sinon on ignore (ce sera géré par migrations/seed)
         */
        try {
            // Configure une connexion runtime si besoin (selon ton architecture)
            // Si ta connexion Laravel est déjà prête à ce stade, OK.
            if (Schema::hasTable('users')) {
                $emailExists = DB::table('users')->where('email', $data['admin_email'])->exists();
                $userExists  = DB::table('users')->where('username', $data['admin_username'])->exists();

                if ($emailExists || $userExists) {
                    session([
                        'installer.steps' => array_replace(session('installer.steps', []), [4 => false]),
                        'installer' => array_replace(session('installer', []), ['admin_confirmed' => false]),
                    ]);

                    return response()->json([
                        'ok' => false,
                        'message' => 'Email ou nom d’utilisateur déjà utilisé.',
                        'errors' => [
                            'admin_email' => $emailExists ? ['Cet email existe déjà.'] : [],
                            'admin_username' => $userExists ? ['Ce nom d’utilisateur existe déjà.'] : [],
                        ],
                    ], 422);
                }
            }
        } catch (\Throwable $e) {
            // On n’échoue pas l’étape 4 si les migrations ne sont pas faites.
            // Mais si tu veux être strict : retourne une erreur ici.
        }

        // Stockage session + lock
        session([
            'installer' => array_replace(session('installer', []), [
                'admin_firstname' => $data['admin_firstname'],
                'admin_lastname'  => $data['admin_lastname'],
                'admin_email'     => $data['admin_email'],
                'admin_username'  => $data['admin_username'],
                'admin_password'  => $data['admin_password'], // hash plus tard dans InstallerRunner
                'admin_confirmed' => true,
            ]),
            'installer.steps' => array_replace(session('installer.steps', []), [4 => true]),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Compte Super Admin validé. Tu peux démarrer l’installation.',
        ]);
    }
    public function state()
    {
        return response()->json([
            'steps' => session('installer.steps', []),
            'flags' => [
                'db_confirmed' => session('installer.db_confirmed', session('installer', [])['db_confirmed'] ?? false),
                'config_confirmed' => session('installer', [])['config_confirmed'] ?? false,
                'admin_confirmed' => session('installer', [])['admin_confirmed'] ?? false,
            ],
        ]);
    }
    public function startInstall(Request $request)
    {
        // Vérifier steps 1..4 + flags confirmés
        $steps = session('installer.steps', []);
        $data  = session('installer', []);

        foreach ([1, 2, 3, 4] as $s) {
            if (!($steps[$s] ?? false)) {
                return response()->json([
                    'ok' => false,
                    'message' => "Installation bloquée : étape {$s} non validée."
                ], 422);
            }
        }

        foreach (['db_confirmed', 'config_confirmed', 'admin_confirmed'] as $flag) {
            if (!($data[$flag] ?? false)) {
                return response()->json([
                    'ok' => false,
                    'message' => "Installation bloquée : {$flag} manquant."
                ], 422);
            }
        }
        // Token anti-rejeu (lié à la session)
        $token = Str::random(40);
        session(['installer.install_token' => $token]);

        // URL SSE signée (valide 10 minutes)
        $streamUrl = URL::temporarySignedRoute(
            'installer.stream',
            now()->addMinutes(10),
            ['token' => $token]
        );

        return response()->json([
            'ok' => true,
            'stream_url' => $streamUrl,
        ]);
    }
    public function streamInstall(Request $request)
    {
        $token = (string) $request->query('token');

        //  Vérifier token session
        if (!$token || $token !== (string) session('installer.install_token')) {
            abort(403, 'Invalid install token');
        }

        //  Vérifier steps 1..4 encore une fois (sécurité serveur)
        $steps = session('installer.steps', []);
        $data  = session('installer', []);
        foreach ([1, 2, 3, 4] as $s) {
            if (!($steps[$s] ?? false)) abort(422, "Step {$s} not validated");
        }

        //  Empêche les buffers (important)
        @ini_set('output_buffering', 'off');
        @ini_set('zlib.output_compression', '0');

        return new StreamedResponse(function () use ($data) {

            $send = function (string $event, array $payload) {
                echo "event: {$event}\n";
                echo "data: " . json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n\n";
                @ob_flush();
                @flush();
            };

            $send('log', ['message' => "Initialisation de l'installation..."]);
            $send('progress', ['percent' => 5]);

            try {
                //  Ici : idéalement InstallerRunner accepte un callback de progression
                // Exemple : run($data, $callback)
                app(InstallerRunner::class)->run($data, function ($percent, $message) use ($send) {
                    $send('progress', ['percent' => (int)$percent]);
                    $send('log', ['message' => (string)$message]);
                });

                $send('progress', ['percent' => 100]);
                $send('done', [
                    'redirect' => url('/login'),
                    'message' => 'Installation terminée avec succès.'
                ]);
            } catch (\Throwable $e) {
                $send('error', [
                    'message' => $e->getMessage(),
                ]);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'X-Accel-Buffering' => 'no', // nginx
        ]);
    }
}
