# Audit fonctionnalite - RH, projets et communication

## Reference plan

Sections `23`, `24`, `25`.

## Niveau d'implementation

`1/5 - RH present, projets casses, communication simple`

## Parcours client / utilisateur

1. Le responsable RH cree un employe.
2. Il traite salaires et pointages.
3. Un utilisateur cree un projet et des taches.
4. Les utilisateurs s'envoient des messages internes ou suivent des tickets.

## Ce qui est en place

- CRUD employes.
- Traitement salaires, pointage, rapport presence.
- CRUD projets et taches cote controleurs / vues.
- Messagerie interne et tickets de support.

## Manquements et anomalies

- `Project` et `Task` sont cassants au chargement PHP : ils utilisent le trait inexistant `Modules\Core\Traits\BelongsToInstance`.
- Donc toute la fonctionnalite projets / taches doit etre consideree comme non fiable en execution.
- Les commissions RH existent en modele / service, mais elles ne sont pas automatiquement declenchees dans les flux de vente.
- La communication du plan comprend email, SMS, notifications systeme ; dans le code, seuls messages internes et tickets simples sont visibles cote UI.
- `SmsService` existe, mais aucune interface Eshop claire ne permet de configurer et exploiter des campagnes SMS.
- Les tickets sont des tickets back-office, pas un vrai support self-service client.

## Niveau reel face au plan

- RH simple : oui
- commissions automatiques : non
- projets / taches : casses
- communication email/SMS avancee : tres partielle

## Impact client

Le module RH peut depanner pour un suivi simple. En revanche, les projets / taches ne doivent pas etre annonces comme fonctionnels tant que le probleme de trait n'est pas corrige.
