# COMPAS — documentation technique

COMPAS est l'outil interne fictif d'un courtier en assurance. L'application
regroupe deux fonctionnalités largement indépendantes, qui cohabitent dans la
même codebase Laravel :

1. **Gestion de portefeuille** — assurés, contrats, garanties, sinistres,
   pièces jointes. Voir [`portefeuille.md`](portefeuille.md).
2. **Q&A par récupération augmentée (RAG)** — un chat « Compas » qui répond
   aux questions des gestionnaires en s'appuyant sur un corpus de
   documentation interne, et cite ses sources sous la forme `chemin:ligne`.
   Voir [`qa-rag.md`](qa-rag.md).

> Ce dépôt est un bac à sable de formation : il contient volontairement des
> défauts utilisés comme exercices d'atelier. Cette documentation décrit le
> comportement du code tel qu'il existe dans l'arbre de travail actuel ; elle
> ne recense pas ces défauts.

## Stack technique

| Composant | Version / choix |
|---|---|
| Langage / framework backend | PHP 8.3+, Laravel `^13.17` |
| Base de données par défaut | SQLite (`DB_CONNECTION=sqlite`) |
| Base optionnelle | PostgreSQL + pgvector (via `docker-compose.yml`), pour la recherche vectorielle future — non nécessaire au fonctionnement actuel |
| Frontend build | Vite `^8`, Tailwind CSS `^4` (`@tailwindcss/vite`), `laravel-vite-plugin` |
| Tests | PHPUnit `^12.5` (`php artisan test` / `composer test`) |
| Autres dépendances dev | Laravel Pint (style), Laravel Pail (logs), Mockery, Faker |

Pas de couche d'authentification : toutes les routes sont ouvertes (choix
assumé du bac à sable).

## Structure du code (vue d'ensemble)

- `app/Models/` — modèles Eloquent du portefeuille (`Assure`, `Contrat`,
  `Garantie`, `Sinistre`, `Piece`) et du corpus documentaire (`Document`).
- `app/Http/Controllers/` — contrôleurs HTTP des deux moitiés de
  l'application (portefeuille et Q&A).
- `app/Http/Requests/` — `FormRequest` de validation (`AssureRequest`,
  `ContratRequest`).
- `app/Services/Retrieval/` — recherche par mots-clés (`DocumentSearch`) et
  assemblage du contexte (`ContextBuilder`) pour le pipeline Q&A.
- `app/Services/Answering/` — orchestration de la réponse (`Answerer`),
  objet de réponse (`Answer`), localisation des citations (`Citation`), et
  cinq formatters de sortie (`Formatters/`).
- `app/Services/Llm/` — interface `LlmClient` et ses deux implémentations
  (`StubLlmClient` branchée par défaut, `HttpLlmClient` non branchée).
- `app/Support/` — utilitaires transverses : `Euro` (formatage monétaire) et
  `QueryLog` (compteur de requêtes SQL affiché en mode debug).
- `app/Console/Commands/AskCommand.php` — commande Artisan `compas:ask`.
- `database/seeds/documents/` — corpus Markdown chargé par
  `DocumentSeeder` (conditions de garantie, barème de vétusté, procédure
  sinistre, wiki technique...).
- `database/seeders/` — `DatabaseSeeder` appelle `DocumentSeeder` puis
  `PortefeuilleSeeder`.
- `routes/web.php` — l'intégralité des routes de l'application (pas de
  `routes/api.php` séparé).

## Installation et lancement en local

Prérequis : PHP 8.3+, Composer 2, extension `pdo_sqlite`.

```bash
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
```

Le seed (`DatabaseSeeder`) charge le corpus documentaire puis le
portefeuille (assurés, contrats, garanties, sinistres, pièces) avec une
graine fixe et des dates ancrées, pour que le jeu de données soit
reproductible d'une machine à l'autre.

Lancer l'application :

```bash
php artisan serve            # http://127.0.0.1:8000
composer dev                 # équivalent de `php artisan dev` (serve + queue + vite)
npm run dev                  # Vite seul, si besoin de recompiler les assets à la volée
```

Interroger le corpus en ligne de commande :

```bash
php artisan compas:ask "quel est le délai de déclaration d'un sinistre ?"
```

Lancer les tests :

```bash
php artisan test                                        # suite complète
php artisan test --filter=NomDuTest
./vendor/bin/phpunit tests/Unit/DocumentSearchTest.php
./vendor/bin/phpunit tests/Unit/DocumentSearchTest.php --testdox
```

`composer test` vide au préalable le cache de config (`artisan
config:clear`) avant de lancer `artisan test`.

### À propos des clés d'API

Aucune clé n'est nécessaire pour faire fonctionner le pipeline Q&A : la
liaison `LlmClient` → `StubLlmClient`, effectuée dans
`AppServiceProvider::register()`, ne fait aucun appel réseau et reformule le
contexte récupéré de façon déterministe. `HttpLlmClient` (appel HTTP réel)
existe dans le code mais n'est jamais l'implémentation liée par le
conteneur.

### PostgreSQL / pgvector

`docker-compose.yml` démarre une image `pgvector/pgvector:pg16` (base
`compas`, port hôte `55432`). Elle n'est utile que pour préparer un futur
branchement de la recherche vectorielle ; l'application par défaut
(SQLite + recherche par mots-clés) n'en dépend pas.

## Pour aller plus loin

- [`portefeuille.md`](portefeuille.md) — modèles, relations, routes et
  contrôleurs de la gestion de portefeuille.
- [`qa-rag.md`](qa-rag.md) — pipeline Q&A étape par étape, points d'entrée
  HTTP et CLI.
