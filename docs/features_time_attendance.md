# Extraction des fonctionnalites — Module Gestion du Temps (Pointage & Supervision) — B360

**Date d'analyse** : 2026-04-09
**Projet** : B360 (Laravel, architecture modulaire nwidart/laravel-modules)
**Module** : `Modules/Eshop360/` (sous-section HR)
**Methode** : Analyse statique du code source, migrations, routes, tests

---

## Resume

| Indicateur | Valeur |
|---|---|
| Fonctionnalites identifiees | **11** |
| Fonctionnelles (code complet + route + service) | **9** |
| Couvertes par des tests | **5** (via 2 fichiers de tests) |
| Implementees mais non verifiees fonctionnellement | **2** |
| Partiellement implementees | **2** |
| Non trouvees dans le code | **6** (mentionnees dans les vues demo mais absentes du backend) |

---

## Note importante sur l'architecture

Il n'existe **pas de module dedie** a la gestion du temps dans B360. Les fonctionnalites d'attendance sont integrees dans le module **Eshop360** (`Modules/Eshop360/`), sous la section RH (`Http/Controllers/HR/`). Le scope est **beaucoup plus restreint** que dans CCC360 : il s'agit d'un systeme basique de pointage (clock-in/clock-out) couple a la gestion des employes et salaires, sans supervision avancee, shifts, absences typees, anomalies ou QR codes.

Trois vues demo existent (`attendance-admin.blade.php`, `attendance-employee.blade.php`, `shift.blade.php`) dans `resources/views/` mais elles ne sont **pas connectees au backend** — ce sont des templates UI sans routes ni controleurs associes.

---

## Tableau de synthese

| # | Fonctionnalite | Type | Implementee | Fonctionnelle ? | Preuve (fichier / test) |
|---|---|---|---|---|---|
| 1 | Enregistrer une arrivee (clock-in) | Pointage | Oui | Oui | `AttendanceController::clockIn()`, `HRService::clockIn()`, test: `HRTest::test_clock_in_then_clock_out_records_hours_worked`, `HRControllerTest::test_clock_in_creates_attendance_record` |
| 2 | Enregistrer un depart (clock-out) | Pointage | Oui | Oui | `AttendanceController::clockOut()`, `HRService::clockOut()`, test: `HRTest::test_clock_in_then_clock_out_records_hours_worked` |
| 3 | Calcul automatique des heures travaillees | Pointage | Oui | Oui | `HRService::clockOut()` — `diffInMinutes / 60`, test: `HRTest::test_clock_in_then_clock_out_records_hours_worked` |
| 4 | Consulter le tableau de presence (index) | Pointage | Oui | Oui | `AttendanceController::index()`, route `GET /i/{slug}/hr/attendance`, vue `attendance/index.blade.php` |
| 5 | Rapport de presence par periode | Supervision | Oui | Oui | `AttendanceController::report()`, `HRService::getAttendanceReport()`, route `GET /i/{slug}/hr/attendance/report`, vue `attendance/report.blade.php` |
| 6 | CRUD employes | Transverse | Oui | Oui | `EmployeeController` (7 methodes), test: `HRControllerTest::test_can_create_employee`, `HRTest::test_store_employee_persists_salary_field`, `HRTest::test_employee_index_returns_ok` |
| 7 | Creation de compte utilisateur pour employe | Transverse | Oui | Oui | `EmployeeController::createUserAccount()`, route `POST /i/{slug}/hr/employees/{employee}/create-account` |
| 8 | Traitement des salaires | Transverse | Oui | Oui | `SalaryController::process()`, `HRService::processSalary()`, test: `HRControllerTest::test_can_process_salary`, `HRTest::test_process_salary_*` (3 tests) |
| 9 | Marquer salaire comme paye | Transverse | Oui | Oui | `SalaryController::markPaid()`, route `POST /i/{slug}/hr/salaries/{salary}/pay` |
| 10 | Calcul automatique des commissions | Transverse | Oui | [A VALIDER FONCTIONNELLEMENT] | `HRService::calculateCommissionForSale()`, `recordCommission()` — appele a la completion de commande, aucun test specifique |
| 11 | Filtrage par date dans le tableau de presence | Pointage | Oui | [A VALIDER FONCTIONNELLEMENT] | `AttendanceController::index()` — filtre `date_from/date_to`, pas de test specifique sur le filtrage |

---

## Detail par fonctionnalite

### 1. Pointage (employe)

#### 1.1 Enregistrer une arrivee (clock-in)
- **Controleur** : `Modules/Eshop360/Http/Controllers/HR/AttendanceController.php` — `clockIn(Request)`
- **Service** : `Modules/Eshop360/Services/HRService.php` — `clockIn(Employee)`
- **Validation** : `employee_id` required, must exist in `eshop_employees`
- **Route** : `POST /i/{slug}/hr/attendance/clock-in` (middleware: `can:eshop.hr.view`, `billing.feature:eshop360.hr`)
- **Logique** : Cree un enregistrement `Attendance` avec `date = today`, `clock_in = now()`
- **Tests** :
  - `HRControllerTest::test_clock_in_creates_attendance_record`
  - `HRTest::test_clock_in_then_clock_out_records_hours_worked`

#### 1.2 Enregistrer un depart (clock-out)
- **Controleur** : `AttendanceController::clockOut(Request, slug, ?Attendance)`
- **Service** : `HRService::clockOut(Attendance)`
- **Route** : `POST /i/{slug}/hr/attendance/clock-out` (par formulaire), `POST /i/{slug}/hr/attendance/{attendance}/clock-out` (par ID)
- **Logique** : Calcule `hours_worked = diffInMinutes(clock_in, now) / 60`, met a jour `clock_out` et `hours_worked` (arrondi a 2 decimales)
- **Verification** : Valide que l'enregistrement appartient a l'instance courante
- **Test** : `HRTest::test_clock_in_then_clock_out_records_hours_worked`

#### 1.3 Consulter le tableau de presence
- **Controleur** : `AttendanceController::index(Request)`
- **Route** : `GET /i/{slug}/hr/attendance`
- **Logique** :
  - Filtre par `date_from` / `date_to` ou date unique
  - Par defaut : donnees du jour, ou derniere date avec donnees si aujourd'hui est vide
  - Calcule des KPIs : presents, absents, heures moyennes, encore en cours
- **Vue** : `Modules/Eshop360/Resources/views/hr/attendance/index.blade.php` + composant `_card.blade.php`
- **Donnees affichees** : grille de cartes par employe (nom, poste, clock-in, clock-out, heures, badge statut, bouton clock-out)

#### 1.4 Rapport de presence
- **Controleur** : `AttendanceController::report(Request)`
- **Service** : `HRService::getAttendanceReport(instanceId, from, to)`
- **Route** : `GET /i/{slug}/hr/attendance/report`
- **Logique** : Pour chaque employe actif, calcule `days_present` (count), `total_hours` (sum), `average_hours`
- **Parametres** : `from` (default: debut du mois), `to` (default: aujourd'hui)
- **Vue** : `Modules/Eshop360/Resources/views/hr/attendance/report.blade.php`
- **KPIs affiches** : nombre employes, jours de presence, total heures

---

### 2. Gestion des employes

#### 2.1 CRUD employes
- **Controleur** : `Modules/Eshop360/Http/Controllers/HR/EmployeeController.php`
- **Methodes** : `index()`, `create()`, `store()`, `show()`, `edit()`, `update()`, `destroy()`
- **Routes** : 7 routes RESTful sous `/i/{slug}/hr/employees`
- **Model** : `Modules/Eshop360/Models/Employee.php`
  - **Fillable** : channel_id, instance_id, user_id, name, email, phone, position, department, salary, commission_rate, joined_at, status
  - **Relations** : `user()`, `salaries()`, `commissions()`, `attendances()`
  - **Statuts** : active, inactive, terminated
  - **Soft deletes** : oui
- **Filtres index** : status, department, search (nom/email/poste)
- **KPIs** : total, actifs, inactifs, masse salariale, salaire moyen, avec compte, commissions du mois, presents aujourd'hui
- **Vues** : `employees/index.blade.php`, `employees/create.blade.php`, `employees/edit.blade.php`, `employees/show.blade.php`
- **Tests** : `HRControllerTest::test_can_create_employee`, `HRTest::test_store_employee_persists_salary_field`, `HRTest::test_employee_index_returns_ok`

#### 2.2 Creation de compte utilisateur
- **Controleur** : `EmployeeController::createUserAccount()`
- **Route** : `POST /i/{slug}/hr/employees/{employee}/create-account` (middleware: `can:eshop.hr.manage`)
- **Validation** : email (required, unique dans system.users), password (required, min:8, confirmed)
- **Logique** : Cree un `User` + `instance_user` association + met a jour `employee.user_id`

---

### 3. Gestion des salaires

#### 3.1 Traitement des salaires
- **Controleur** : `Modules/Eshop360/Http/Controllers/HR/SalaryController.php` — `process()`
- **Service** : `HRService::processSalary(employee, period, bonus, deductions, notes)`
- **Route** : `POST /i/{slug}/hr/salaries/process` (middleware: `can:eshop.hr.manage`)
- **Validation** : employee_id required, period required (max:7), bonus/deductions nullable|numeric|min:0
- **Logique** : `net_amount = salary + bonus - deductions`
- **Model** : `EmployeeSalary` — Fillable: channel_id, employee_id, amount, period, bonus, deductions, net_amount, paid_at, notes
- **Tests** :
  - `HRControllerTest::test_can_process_salary`
  - `HRTest::test_process_salary_net_equals_base_plus_bonus_minus_deductions`
  - `HRTest::test_process_salary_with_no_bonus_no_deductions`

#### 3.2 Marquer comme paye
- **Controleur** : `SalaryController::markPaid()`
- **Route** : `POST|PATCH /i/{slug}/hr/salaries/{salary}/pay`
- **Logique** : Met `paid_at = now()`

---

### 4. Commissions

#### 4.1 Calcul automatique des commissions
- **Service** : `HRService::calculateCommissionForSale(Order)`, `recordCommission(Employee, Order)`
- **Logique** :
  - Verifie que la commande a un `employee_id` et que l'employe a un `commission_rate > 0`
  - Garde contre les doublons (verifie si une commission existe deja pour order+employee)
  - Calcule : `amount = order.total * (commission_rate / 100)`
- **Model** : `EmployeeCommission` — Fillable: channel_id, employee_id, order_id, rate, amount, paid_at
- **Declencheur** : Appele a la completion de commande (integration dans le workflow commande)
- **[A VALIDER FONCTIONNELLEMENT]** : Pas de test specifique pour `calculateCommissionForSale`

---

## Structures de donnees (4 tables)

| Table | Migration | Colonnes principales | Relations |
|---|---|---|---|
| `eshop_employees` | `2026_03_12_100033` | instance_id, user_id, name, email, phone, position, department, salary, commission_rate, joined_at, status | HasMany salaries, commissions, attendances; BelongsTo user |
| `eshop_attendance` | `2026_03_12_100036` | employee_id, date, clock_in, clock_out, hours_worked, notes | BelongsTo employee |
| `eshop_employee_salaries` | `2026_03_12_100034` | employee_id, amount, period, bonus, deductions, net_amount, paid_at, notes | BelongsTo employee |
| `eshop_employee_commissions` | `2026_03_12_100035` | employee_id, order_id, rate, amount, paid_at | BelongsTo employee, order |

---

## Routes (13 routes)

### Employes (8 routes)
| Methode | URI | Nom | Controleur | Middleware |
|---|---|---|---|---|
| GET | `/i/{slug}/hr/employees` | `eshop360.hr.employees.index` | `EmployeeController@index` | `can:eshop.hr.view` |
| GET | `/i/{slug}/hr/employees/create` | `eshop360.hr.employees.create` | `EmployeeController@create` | `can:eshop.hr.manage` |
| POST | `/i/{slug}/hr/employees` | `eshop360.hr.employees.store` | `EmployeeController@store` | `can:eshop.hr.manage` |
| GET | `/i/{slug}/hr/employees/{employee}` | `eshop360.hr.employees.show` | `EmployeeController@show` | `can:eshop.hr.view` |
| GET | `/i/{slug}/hr/employees/{employee}/edit` | `eshop360.hr.employees.edit` | `EmployeeController@edit` | `can:eshop.hr.manage` |
| PUT | `/i/{slug}/hr/employees/{employee}` | `eshop360.hr.employees.update` | `EmployeeController@update` | `can:eshop.hr.manage` |
| DELETE | `/i/{slug}/hr/employees/{employee}` | `eshop360.hr.employees.destroy` | `EmployeeController@destroy` | `can:eshop.hr.manage` |
| POST | `/i/{slug}/hr/employees/{employee}/create-account` | `eshop360.hr.employees.create-account` | `EmployeeController@createUserAccount` | `can:eshop.hr.manage` |

### Salaires (3 routes)
| Methode | URI | Nom | Controleur | Middleware |
|---|---|---|---|---|
| GET | `/i/{slug}/hr/salaries` | `eshop360.hr.salaries.index` | `SalaryController@index` | `can:eshop.hr.view` |
| POST | `/i/{slug}/hr/salaries/process` | `eshop360.hr.salaries.process` | `SalaryController@process` | `can:eshop.hr.manage` |
| POST/PATCH | `/i/{slug}/hr/salaries/{salary}/pay` | `eshop360.hr.salaries.pay` | `SalaryController@markPaid` | `can:eshop.hr.manage` |

### Presence (5 routes)
| Methode | URI | Nom | Controleur | Middleware |
|---|---|---|---|---|
| GET | `/i/{slug}/hr/attendance` | `eshop360.hr.attendance.index` | `AttendanceController@index` | `can:eshop.hr.view` |
| POST | `/i/{slug}/hr/attendance/clock-in` | `eshop360.hr.attendance.clock-in` | `AttendanceController@clockIn` | `can:eshop.hr.view` |
| POST | `/i/{slug}/hr/attendance/clock-out` | `eshop360.hr.attendance.clock-out` | `AttendanceController@clockOut` | `can:eshop.hr.view` |
| POST | `/i/{slug}/hr/attendance/{attendance}/clock-out` | `eshop360.hr.attendance.clock-out-by-id` | `AttendanceController@clockOut` | `can:eshop.hr.view` |
| GET | `/i/{slug}/hr/attendance/report` | `eshop360.hr.attendance.report` | `AttendanceController@report` | `can:eshop.hr.view` |

---

## Vues Blade (9+ fichiers)

| Fichier | Description |
|---|---|
| `hr/attendance/index.blade.php` | Tableau de presence avec KPIs, filtres date, grille de cartes |
| `hr/attendance/report.blade.php` | Rapport avec KPIs, filtre periode, tableau detaille par employe |
| `hr/attendance/_card.blade.php` | Composant carte employe (nom, poste, clock-in/out, heures, badge statut) |
| `hr/employees/index.blade.php` | Liste employes avec KPIs, filtres, grille de cartes |
| `hr/employees/create.blade.php` | Formulaire creation employe |
| `hr/employees/edit.blade.php` | Formulaire edition employe |
| `hr/employees/show.blade.php` | Detail employe avec stats |
| `hr/employees/commission.blade.php` | Vue commissions employe |
| `hr/salaries/index.blade.php` | Liste salaires + formulaire traitement |

### Vues demo (NON CONNECTEES au backend)
| Fichier | Description |
|---|---|
| `resources/views/attendance-admin.blade.php` | Template admin attendance — **pas de route ni controleur associe** |
| `resources/views/attendance-employee.blade.php` | Template employe attendance — **pas de route ni controleur associe** |
| `resources/views/shift.blade.php` | Template gestion des shifts — **pas de route ni controleur associe** |

---

## Fichiers de tests (2 fichiers, 5+ cas)

### HRControllerTest
**Fichier** : `Modules/Eshop360/Tests/Feature/HRControllerTest.php`

| Test | Couverture |
|---|---|
| `test_can_create_employee()` | Creation employe avec tous les champs |
| `test_can_process_salary()` | Traitement salaire avec bonus |
| `test_clock_in_creates_attendance_record()` | Clock-in cree un enregistrement |

### HRTest
**Fichier** : `Modules/Eshop360/Tests/Feature/HRTest.php`

| Test | Couverture |
|---|---|
| `test_store_employee_persists_salary_field()` | Persistence du champ salary |
| `test_process_salary_net_equals_base_plus_bonus_minus_deductions()` | Calcul net = base + bonus - deductions |
| `test_process_salary_with_no_bonus_no_deductions()` | Salaire sans bonus/deductions |
| `test_clock_in_then_clock_out_records_hours_worked()` | Cycle complet clock-in + clock-out |
| `test_employee_index_returns_ok()` | Page index accessible |

---

## Permissions

| Permission | Description |
|---|---|
| `eshop.hr.view` | Consulter les donnees RH (employes, presence, salaires) |
| `eshop.hr.manage` | Gerer les donnees RH (creer, modifier, supprimer, traiter salaires, clock) |

### Feature flag
- `billing.feature:eshop360.hr` — Middleware de facturation qui conditionne l'acces au module HR

---

## Seeder

### DemoHRSeeder
**Fichier** : `Modules/Eshop360/Database/Seeders/DemoHRSeeder.php`
- **Methode `run(instanceId)`** : Cree 10 employes demo avec noms ivoiriens et postes realistes (Directeur Entrepot, Responsable Commerciale, etc.), salaires de 200,000 a 800,000
- **Methode `reset(instanceId)`** : Supprime tous les employes de l'instance

---

## Fonctionnalites mentionnees dans les vues demo mais [NON TROUVEES DANS LE CODE]

Les fichiers `attendance-admin.blade.php`, `attendance-employee.blade.php` et `shift.blade.php` presentent des interfaces UI avancees qui ne sont **pas implementees** cote backend :

| Fonctionnalite | Vue demo | Backend |
|---|---|---|
| Gestion des shifts (creation, modification, affectation) | `shift.blade.php` | [NON TROUVEE DANS LE CODE] — Aucune table `shifts`, aucun controleur |
| Gestion des pauses (debut/fin pause) | `attendance-employee.blade.php` | [NON TROUVEE DANS LE CODE] — Aucune colonne pause dans `eshop_attendance` |
| Historique de pointage employe | `attendance-employee.blade.php` | [NON TROUVEE DANS LE CODE] — Pas de vue self-service pour l'employe |
| Validation/rejet des pointages | `attendance-admin.blade.php` | [NON TROUVEE DANS LE CODE] — Aucun champ status/approved_by dans `eshop_attendance` |
| Heures supplementaires (calcul, seuil) | `attendance-admin.blade.php` | [NON TROUVEE DANS LE CODE] — Aucune logique d'overtime |
| Export CSV/PDF des presences | `attendance-admin.blade.php` | [NON TROUVEE DANS LE CODE] — Aucune route d'export |

---

## Conclusion — Etat de sante du module

### Points forts
- **Code fonctionnel** : Le cycle basique clock-in/clock-out fonctionne et est teste
- **Calcul automatique** : Les heures travaillees sont calculees automatiquement a la deconnexion
- **Tests** : 5+ tests couvrent les operations critiques (employes, salaires, clock-in/out)
- **Multi-tenant** : Isolation par instance et channel via traits `BelongsToInstance` et `BelongsToChannel`
- **Rapport de presence** : Vue rapport avec metriques par employe et periode

### Points faibles et manques
1. **Scope tres limite** : Uniquement clock-in/clock-out basique. Pas de statuts multiples, pas de changement de statut en cours de journee.
2. **Pas de gestion des shifts** : Les vues demo existent mais aucune implementation backend (table, model, controleur, route).
3. **Pas de gestion des pauses** : Aucune colonne `break_start`/`break_end` ou `break_minutes` dans la table `eshop_attendance`.
4. **Pas de supervision** : Aucun tableau de bord superviseur, aucune validation de pointage par un manager.
5. **Pas de gestion des absences** : Aucune table `absences` ou `justificatifs`, aucun workflow de validation.
6. **Pas d'export** : Aucune fonctionnalite d'export CSV ou PDF.
7. **Pas de detection d'anomalies** : Aucune verification automatique (retards, absences injustifiees, etc.).
8. **Pas de QR code ou geolocalisation** : Pointage uniquement via interface web.
9. **Permission clock-in/out trop permissive** : Utilise `can:eshop.hr.view` au lieu d'une permission dediee `eshop.hr.attendance`.
10. **Schema minimal** : La table `eshop_attendance` n'a que 7 colonnes — pas de `status`, `approved_by`, `source`, `break_minutes`, ni `overtime_minutes`.

### Recommandations immediates
1. **Ajouter une colonne `status`** a `eshop_attendance` (present, absent, late, early_leave) pour distinguer les types de presence.
2. **Ajouter `break_minutes`** pour tracker les pauses.
3. **Creer une permission dediee** `eshop.hr.attendance` pour le pointage, distincte de `eshop.hr.view`.
4. **Implementer les shifts** si les vues demo doivent etre utilisees — creer la table, le model et le controleur.
5. **Ajouter un export CSV** du rapport de presence.
6. **Ecrire des tests pour le filtrage par date** dans `AttendanceController::index()` et pour le calcul des commissions.

### Comparaison avec CCC360
| Critere | CCC360 | B360 |
|---|---|---|
| Fonctionnalites | 42 | 11 |
| Tables dediees | 12 | 1 (+ 3 RH) |
| Tests | 21 fichiers | 2 fichiers |
| Shifts | Oui (legacy + groupes) | Non |
| Absences | Oui (avec justificatifs) | Non |
| Supervision | Oui (avancee) | Non |
| Anomalies | Oui (8 types) | Non |
| QR Code | Oui | Non |
| Export | PDF | Non |
| Statuts multiples | Oui (8 statuts) | Non (clock-in/out uniquement) |
