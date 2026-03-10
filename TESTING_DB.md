# Test DB Setup (MySQL + PostgreSQL)

Ce guide configure des bases de test locales via Docker et active les tests conditionnels.

## 1) Demarrer les containers

```bash
docker compose -f docker-compose.test.yml up -d
```

Ports:
- MySQL: `localhost:3307`
- PostgreSQL: `localhost:5433`

Databases creees au demarrage:
- MySQL: `b360_test_system`
- PostgreSQL: `b360_test_system`

## 2) Variables d'environnement pour les tests

Ces variables activent les tests DB reelles. A definir dans votre shell avant d'executer PHPUnit.

### MySQL (pour Installer + provisioning DB dediee)
```bash
TEST_MYSQL_HOST=127.0.0.1
TEST_MYSQL_PORT=3307
TEST_MYSQL_DATABASE=b360_test_system
TEST_MYSQL_USERNAME=root
TEST_MYSQL_PASSWORD=rootpass
```

### PostgreSQL (pour Installer uniquement)
```bash
TEST_PGSQL_HOST=127.0.0.1
TEST_PGSQL_PORT=5433
TEST_PGSQL_DATABASE=b360_test_system
TEST_PGSQL_USERNAME=postgres
TEST_PGSQL_PASSWORD=postgrespass
```

### DB system pour tests "ExternalDbTestCase" (Instances DB dediee)
```bash
TEST_DB_DRIVER=mysql
TEST_DB_HOST=127.0.0.1
TEST_DB_PORT=3307
TEST_DB_DATABASE=b360_test_system
TEST_DB_USERNAME=root
TEST_DB_PASSWORD=rootpass
```

## 3) Lancer les tests

```bash
php artisan test
```

Les tests DB reelles seront **skippes** si les variables ne sont pas definies.
