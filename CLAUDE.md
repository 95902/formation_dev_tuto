# CLAUDE.md

Ce fichier donne à Claude Code (claude.ai/code) les repères nécessaires pour travailler dans ce dépôt.

## Nature de ce dépôt

COMPAS est l'outil interne fictif d'un courtier en assurance (Laravel 13 / PHP 8.3), utilisé comme **bac à sable de formation**. Il fait deux choses :

- **gestion de portefeuille** — assurés, contrats, garanties, sinistres, pièces (écrans CRUD classiques) ;
- **réponse aux questions sur la documentation interne** — s'appuie sur un petit corpus interne (conditions de garantie, barème de vétusté, procédure sinistre, wiki technique) et cite ses sources sous la forme `chemin:ligne`.

**Les bugs de ce code sont volontaires.** Ce dépôt est le support d'exercice d'une formation « ingénierie assistée par l'IA » — les tickets de `BACKLOG.md` décrivent un symptôme, jamais la cause, et trouver la cause fait partie de l'exercice. Ne « nettoyez » pas et ne corrigez pas ce qui dépasse le ticket demandé, et ne présumez pas qu'un code qui semble étrange est accidentel — vérifiez `BACKLOG.md` et la section spoilers du `README.md` avant de décider qu'un bug mérite d'être touché. Si on vous confie un ticket, suivez le fonctionnement du dépôt : écrire d'abord le test qui échoue (la suite est verte au clone), puis corriger la cause.

Aucune authentification n'existe dans l'application : toutes les pages sont ouvertes. C'est un choix de bac à sable, pas un modèle à reproduire ailleurs.

## Commandes

```bash
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed        # graine fixe, dates ancrées sur le 31/08/2026 — reproductible partout
php artisan test                  # suite complète (Unit + Feature), sqlite :memory:
php artisan test --filter=NomDuTest   # un seul test
php artisan test tests/Unit/DocumentSearchTest.php   # un seul fichier

php artisan serve                 # http://127.0.0.1:8000
php artisan compas:ask "quel est le délai de déclaration d'un sinistre ?"

vendor/bin/pint                   # style de code (Laravel Pint)
```

`docker-compose.yml` fournit une base Postgres pgvector, utile seulement pour brancher la recherche vectorielle — pas nécessaire pour faire tourner l'application.

## Architecture

### Chaîne de réponse aux questions (`app/Services/`)

Le flux d'interrogation est un pipeline linéaire, sans recherche vectorielle (volontairement — la recherche par mots-clés garde un classement déterministe pour des tests reproductibles) :

```
question -> DocumentSearch (score par mots-clés sur les Document)
         -> ContextBuilder (assemble les extraits retenus sous un budget de caractères)
         -> LlmClient::complete(system, prompt)   -> Answerer -> Answer (texte + Citations + estimation de tokens)
```

- `App\Services\Retrieval\DocumentSearch` — tokenize la requête, note chaque `Document` selon le nombre d'occurrences des termes (corps ×1, titre ×3), dédoublonne, trie, garde les N premiers.
- `App\Services\Retrieval\ContextBuilder` — concatène les extraits les mieux notés jusqu'à un budget de caractères (`DEFAULT_BUDGET = 2000`), et estime le coût en tokens.
- `App\Services\Llm\LlmClient` — interface à une seule méthode, `complete(system, user): string`. Liée dans `AppServiceProvider` à `StubLlmClient` (reformulation déterministe, **aucun appel réseau, aucune clé API nécessaire**). `HttpLlmClient` n'existe que comme support d'atelier et n'est jamais branché.
- `App\Services\Answering\Answerer` — orchestre recherche → contexte → appel LLM, construit les `Citation` (via `Citation::locate()`, qui fait correspondre un extrait à un `chemin:ligne`).
- `App\Services\Answering\Formatters\*` — cinq formatters quasi identiques (Cli/Html/Json/Markdown/Slack) pour l'affichage d'une `Answer` ; cette duplication est une dette volontaire et connue (voir `BACKLOG.md` T-06), pas quelque chose à factoriser sauf si c'est précisément la tâche demandée.

Points d'entrée de ce pipeline : `php artisan compas:ask` (`AskCommand`), et `POST /api/ask` (`AskController`).

### Domaine portefeuille (`app/Models/`, `app/Http/Controllers/`)

Chaîne du domaine : `Assure` → `Contrat` → `Garantie` (conditions de garantie par contrat) et `Contrat` → `Sinistre` (dossiers sinistre) → `Piece` (pièces justificatives). Les montants sont toujours stockés en centimes entiers (colonnes `*_cents`) ; voir `App\Support\Euro` pour le formatage.

`Sinistre` porte le calcul de règlement (`indemniteCents()`, `franchiseApplicableCents()`, `resteAChargeCents()`) — taux de vétusté `TAUX_VETUSTE = 0.87` appliqué au montant estimé, moins la franchise, plafonné par la garantie mobilisée. L'exemple de référence du barème vit dans `database/seeds/documents/doc-bareme-vetuste.md`.

Les routes sont déclarées dans `routes/web.php` : routes resource classiques pour `assures`, `contrats`, `sinistres`, plus les actions store/destroy imbriquées `garanties`/`pieces`, plus les endpoints JSON `/api/ask` et `/api/stats`. Pas de versionnage d'API ni de middleware d'authentification.

### Journalisation des requêtes SQL

`App\Support\QueryLog` est un singleton lié dans `AppServiceProvider`, alimenté via un hook `DB::listen` quand `app.debug` est actif, et partagé à toutes les vues via `$queryLog`. Il alimente le bandeau noir affichant le compteur de requêtes SQL sur chaque page en mode debug — le nombre de requêtes d'une page doit rester visible sans outil externe ; à surveiller sur toute vue liste/index modifiée.

### Seeding

`database/seeders/PortefeuilleSeeder.php` et `DocumentSeeder.php` construisent le portefeuille reproductible (40 assurés, 74 contrats sur 4 produits, 239 garanties, 130 sinistres, 309 pièces) ainsi que le corpus de 8 documents sous `database/seeds/documents/`. Les dates sont ancrées sur une date de référence fixe (31/08/2026), pas sur `now()`, afin que les chiffres restent stables après un `migrate:fresh --seed`, quelle que soit la machine ou la date du jour.

## Conventions de travail

- Nommage de branche utilisé en formation : `atelier/<prénom>/<ticket>` (ex. `atelier/marshel/T-01`). Ne jamais pousser sur `main`.
- Un ticket de `BACKLOG.md` est terminé quand il existe un test qui échouait avant le correctif et qui passe après — écrire ce test en premier.
- Les commentaires et docblocks du code sont en français ; conserver ce style lors de la modification de fichiers existants.
