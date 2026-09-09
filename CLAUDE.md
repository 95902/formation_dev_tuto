# CLAUDE.md

Ce fichier donne à Claude Code (claude.ai/code) les repères nécessaires pour travailler dans ce dépôt.

## Nature de ce dépôt

COMPAS est l'outil interne fictif d'un courtier en assurance (Laravel 13 / PHP 8.3), utilisé comme **bac à sable de formation**. Il fait deux choses :

- **gestion de portefeuille** — assurés, contrats, garanties, sinistres, pièces (écrans CRUD classiques) ;
- **réponse aux questions sur la documentation interne** — s'appuie sur un petit corpus interne et cite ses sources sous la forme `chemin:ligne`.

**Les bugs de ce code sont volontaires.** C'est le support d'exercice d'une formation « ingénierie assistée par l'IA » — les tickets de `BACKLOG.md` décrivent un symptôme, jamais la cause, trouver la cause fait partie de l'exercice. Ne corrigez pas ce qui dépasse le ticket demandé, et ne présumez pas qu'un code étrange est accidentel — vérifiez `BACKLOG.md` et la section spoilers du `README.md` avant de toucher à un bug. Pour un ticket : écrire d'abord le test qui échoue (la suite est verte au clone), puis corriger la cause.

Aucune authentification n'existe dans l'application : c'est un choix de bac à sable, pas un modèle à reproduire ailleurs.

## Commandes

```bash
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed        # graine fixe, dates ancrées sur le 31/08/2026 — reproductible partout
php artisan test                  # suite complète (Unit + Feature), sqlite :memory:
php artisan test --filter=NomDuTest   # un seul test

php artisan serve                 # http://127.0.0.1:8000
php artisan compas:ask "quel est le délai de déclaration d'un sinistre ?"

vendor/bin/pint                   # style de code (Laravel Pint)
```

`docker-compose.yml` fournit une base Postgres pgvector, pas nécessaire pour faire tourner l'application.

## Architecture

### Chaîne de réponse aux questions (`app/Services/`)

Pipeline linéaire, sans recherche vectorielle (volontairement — la recherche par mots-clés garde un classement déterministe pour des tests reproductibles) :

```
question -> DocumentSearch (score par mots-clés sur les Document)
         -> ContextBuilder (assemble les extraits retenus sous un budget de caractères)
         -> LlmClient::complete(system, prompt)   -> Answerer -> Answer (texte + Citations + estimation de tokens)
```

- `App\Services\Llm\LlmClient` — liée dans `AppServiceProvider` à `StubLlmClient` (reformulation déterministe, **aucun appel réseau, aucune clé API**). `HttpLlmClient` n'existe que comme support d'atelier et n'est jamais branché.
- `App\Services\Answering\Answerer` — orchestre recherche → contexte → appel LLM, construit les `Citation` via `Citation::locate()` (extrait → `chemin:ligne`).
- `App\Services\Answering\Formatters\*` — cinq formatters quasi identiques (Cli/Html/Json/Markdown/Slack) ; duplication volontaire et connue (`BACKLOG.md` T-06), pas à factoriser sauf si c'est la tâche demandée.

Points d'entrée : `php artisan compas:ask` (`AskCommand`) et `POST /api/ask` (`AskController`).

### Domaine portefeuille (`app/Models/`, `app/Http/Controllers/`)

Chaîne du domaine : `Assure` → `Contrat` → `Garantie` et `Contrat` → `Sinistre` → `Piece`. Montants toujours stockés en centimes entiers (colonnes `*_cents`, voir `App\Support\Euro` pour le formatage).

`Sinistre` porte le calcul de règlement (`indemniteCents()`, `franchiseApplicableCents()`, `resteAChargeCents()`) — taux de vétusté `TAUX_VETUSTE = 0.87` appliqué au montant estimé, moins la franchise, plafonné par la garantie mobilisée. Exemple de référence : `database/seeds/documents/doc-bareme-vetuste.md`.

Routes dans `routes/web.php` : resources classiques pour `assures`, `contrats`, `sinistres`, plus store/destroy imbriqués pour `garanties`/`pieces`, plus `/api/ask` et `/api/stats`.

### Journalisation des requêtes SQL

`App\Support\QueryLog`, alimenté via un hook `DB::listen` en mode debug, partagé à toutes les vues (`$queryLog`). Alimente le bandeau affichant le compteur de requêtes SQL de chaque page — à surveiller sur toute vue liste/index modifiée.

## Conventions de travail

- Nommage de branche utilisé en formation : `atelier/<prénom>/<ticket>` (ex. `atelier/marshel/T-01`). Ne jamais pousser sur `main`.
- Un ticket de `BACKLOG.md` est terminé quand il existe un test qui échouait avant le correctif et qui passe après — écrire ce test en premier.
