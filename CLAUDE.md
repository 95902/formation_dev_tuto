# CLAUDE.md

Ce fichier fournit des indications à Claude Code (claude.ai/code) pour travailler dans ce dépôt.

## Ce qu'est ce dépôt

COMPAS est un back-office fictif de courtier en assurance, construit comme **bac à sable de formation** (voir `README.md`). Le code est volontairement réaliste — services, tests, conventions, dette technique — mais il contient des **bugs plantés délibérément**, utilisés comme exercices d'atelier (`BACKLOG.md`, tickets T-01..T-11).

Conséquence importante sur la façon de travailler ici :
- Les tickets décrivent uniquement un **symptôme**, jamais la cause — trouver la cause fait partie de l'exercice. Ne raccourcis pas ce chemin en cherchant directement « le bug » ; investigue le comportement rapporté comme tu le ferais dans un vrai projet.
- `README.md` contient une section repliée « Défauts volontaires » qui liste le bug exact par fichier. Elle est destinée à la personne qui *anime* la séance, pas à qui résout les tickets — traite-la comme un corrigé, et évite de t'en servir pour raccourcir un ticket sauf si l'utilisateur te demande explicitement de révéler/confirmer un défaut connu.
- La suite de tests est verte au clone. Le déroulé d'un ticket est donc : écrire un test qui échoue et prouve le bug, puis corriger jusqu'à ce qu'il passe.
- Le travail se fait sur des branches nommées `atelier/<prénom>/<ticket>` (ex. `atelier/marshel/T-01`), jamais directement sur `main`.
- Ne « nettoie » pas et ne refactore pas au-delà de ce que demande un ticket — le désordre fait souvent partie de l'exercice (voir T-06, les cinq formatters quasi identiques).

## Commandes

Stack : PHP 8.3+, Laravel 13, Vite/Tailwind 4, SQLite par défaut, PHPUnit 12.

```bash
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed        # graine fixe, dates ancrées sur le 31/08/2026 — reproductible partout

php artisan serve                 # http://127.0.0.1:8000
composer dev                      # artisan dev (serve + queue + vite, via `php artisan dev`)
npm run dev                       # vite seul
npm run build

php artisan test                  # suite complète (composer test vide aussi le cache de config avant)
php artisan test --filter=NomDuTest
./vendor/bin/phpunit tests/Unit/DocumentSearchTest.php
./vendor/bin/phpunit tests/Unit/DocumentSearchTest.php --testdox

php artisan compas:ask "quel est le délai de déclaration d'un sinistre ?"   # point d'entrée CLI de la fonctionnalité Q&A
```

`docker-compose.yml` fournit une instance Postgres avec pgvector, utile seulement le jour où l'on branche la recherche vectorielle — pas nécessaire pour le travail courant en atelier.

## Architecture

Deux moitiés largement indépendantes cohabitent dans la même application :

### 1. Gestion du portefeuille (`assurés` → `contrats` → `garanties` / `sinistres` → `pièces`)

Contrôleurs de ressources Laravel classiques (`AssureController`, `ContratController`, `SinistreController`, plus `GarantieController`/`PieceController` en imbriqué) au-dessus des modèles Eloquent dans `app/Models`. Les routes sont déclarées dans `routes/web.php` via `Route::resource`. `DashboardController` et `StatsController` agrègent ces données — `StatsController` ne respecte volontairement **pas** les conventions API internes suivies par le reste de l'appli (nommage, exposition de clé primaire, style de pagination), par construction, comme exercice de revue.

`app/Support/QueryLog.php` + `AppServiceProvider::boot()` écoutent chaque événement `QueryExecuted` (uniquement quand `app.debug` est actif) et le partagent à toutes les vues, alimentant le bandeau de compteur de requêtes affiché dans le coin de chaque page — une façon volontaire et sans outillage de faire ressortir les problèmes N+1 (voir T-07).

### 2. Q&A par récupération augmentée (chat « Compas », `CompasController` / `AskController` / commande Artisan `compas:ask`)

Pipeline, point d'entrée `app/Services/Answering/Answerer.php::ask()` :

1. `DocumentSearch` (`app/Services/Retrieval/DocumentSearch.php`) — recherche par mots-clés sur les enregistrements `Document` (`content`, `title`, `path`). Pas d'embeddings, volontairement : la colonne pgvector existe déjà en base pour plus tard, mais le classement doit rester déterministe aujourd'hui pour que tests et réponses soient reproductibles.
2. `ContextBuilder` (`app/Services/Retrieval/ContextBuilder.php`) — concatène les documents retenus en un seul bloc de contexte sous un budget de caractères (`DEFAULT_BUDGET`), et estime le coût en tokens de ce contexte.
3. `LlmClient` (`app/Services/Llm/LlmClient.php`, interface) — lié dans `AppServiceProvider::register()` à `StubLlmClient` (déterministe, aucun appel réseau, aucune clé API requise). `HttpLlmClient` n'existe que comme support d'atelier et n'est **jamais** l'implémentation branchée — ne le raccorde pas.
4. `Citation::locate()` (`app/Services/Answering/Citation.php`) — fait correspondre un extrait trouvé à une référence `chemin:ligne` affichée à l'utilisateur.
5. `app/Services/Answering/Formatters/*` — cinq formatters quasi identiques (CLI/HTML/JSON/Markdown/Slack) qui rendent le même objet `Answer` différemment ; cette duplication est un élément de dette connu et volontaire (T-06), à ne pas unifier sauf si c'est l'objet du ticket en cours.

Les documents de seed (conditions de garantie, barème de vétusté, procédure sinistre, wiki technique) se trouvent dans `database/seeds/documents/` et sont chargés par `DocumentSeeder`.

### Éléments transverses

- `app/Support/Euro.php` — les calculs monétaires se font en **centimes entiers** dans tout le domaine (`Sinistre::indemniteCents()`, `montant_regle_cents`, etc.) ; ne jamais introduire d'arithmétique monétaire en flottant.
- `tests/Unit` couvre le pipeline retrieval/answering en isolation (`DocumentSearchTest`, `ContextBuilderTest`, `CitationTest`, `IndemniteTest`) ; `tests/Feature` couvre le comportement au niveau HTTP (`AskEndpointTest`, `PortailTest`, `SinistreCrudTest`).
