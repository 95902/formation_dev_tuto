# Guide — CLAUDE.md, hooks, permissions, skills & MCP sur COMPAS

> Tutoriel pratique pour l'atelier 2 : tout est câblé sur le vrai projet
> COMPAS (bugs volontaires du `BACKLOG.md`), pas sur un bac à sable générique.
> Copiez-collez, testez, cassez, recommencez.

---

## 0. Avant de commencer

```bash
cd compas-lab   # ou formation_dev_tuto
claude
```

Dans la session Claude Code, vérifiez que rien n'est encore configuré :

```
/hooks
/permissions
/mcp
```

Les trois doivent être vides. C'est le point de départ.

---

## 1. CLAUDE.md — les règles qui méritent d'être écrites

Générez d'abord un brouillon :

```
> /init
```

Puis élaguez-le pour ne garder que ce que Claude ne peut **pas** deviner en
lisant le code. Voici la version cible pour COMPAS — comparez-la à ce que
`/init` a produit, coupez le reste :

```markdown
# COMPAS — instructions

## Argent
Tous les montants sont stockés en **centimes entiers** (`*_cents`). Ne jamais
stocker ou comparer un montant en float — voir `Sinistre::indemniteCents()`
pour l'arrondi correct (`round()`, pas `(int)`).

## Statuts
Les statuts valides d'un sinistre sont ceux de `Sinistre::STATUTS`. La valeur
de clôture est `clos`, jamais `cloture`.

## LLM
`StubLlmClient` est le seul client branché (voir `AppServiceProvider`) :
aucun appel réseau dans ce dépôt. `HttpLlmClient` est un support d'atelier,
il ne doit jamais être activé ici.

## Dates
Les seeders sont ancrés sur le 31/08/2026, jamais sur `now()` — le
portefeuille doit rester reproductible d'une machine à l'autre.

## Git
Une branche par personne et par ticket : `atelier/<prénom>/<ticket>`. Jamais
de push sur `main`.

## Tests
`php artisan test` (ou `composer test`). Un ticket commence par un test qui
échoue, jamais par le correctif en premier.
```

**Vérifiez que ça tient** : moins d'une page, une seule règle par paragraphe,
rien qui soit déjà visible dans le code.

---

## 2. Hooks — les garanties, pas les conseils

Configurés dans `.claude/settings.json`, à la racine du projet. Trois hooks
utiles ici, du plus simple au plus démonstratif.

### a. Bloquer l'écriture dans les migrations déjà jouées

C'est l'interdit dur du prérequis d'atelier, et il est réel ici : la base
sqlite est seedée une fois pour toutes, modifier une migration existante la
désynchronise silencieusement chez tout le monde.

```
> Écris un hook PreToolUse qui bloque tout Write ou Edit sur database/migrations/**
```

Claude propose un script (souvent en bash ou en node) branché sur
`PreToolUse` avec un matcher sur `Write|Edit`, qui lit le chemin ciblé et
retourne un code de sortie non nul si le chemin commence par
`database/migrations/`. Testez-le en demandant à Claude de modifier une
migration existante : ça doit être refusé, visiblement.

### b. Lint automatique après chaque édition PHP

Laravel Pint est déjà dans `composer.json` (`require-dev`) — autant l'utiliser
en continu plutôt qu'à la fin.

```
> Écris un hook PostToolUse qui lance ./vendor/bin/pint --dirty après chaque édition de fichier .php
```

### c. (Plus corsé) Bloquer `$request->all()` en pré-commit

Le bug T-11 du backlog est exactement une assignation de masse via
`$request->all()`. Un hook qui grep le diff staged avant un `git commit` et
bloque s'il trouve `->all()` dans un contrôleur est un excellent exercice de
"garde-fou qui prévient le bug qu'on est en train de corriger" :

```
> Écris un hook qui bloque le commit si le diff staged contient "$request->all()" dans app/Http/Controllers/
```

Attention au faux positif : le hook doit lire le **diff**, pas grep tout le
repo, sinon il bloque même quand la ligne n'a pas changé.

---

## 3. Permissions — allowlist et deny

À la main dans `.claude/settings.json` (ou via `/permissions` pendant la
session, qui écrit le même fichier) :

```json
{
  "permissions": {
    "allow": [
      "Bash(php artisan test *)",
      "Bash(php artisan tinker *)",
      "Bash(./vendor/bin/pint *)",
      "Bash(git commit *)"
    ],
    "ask": [
      "Bash(php artisan migrate:fresh *)"
    ],
    "deny": [
      "Read(./.env)",
      "Bash(git push *)",
      "Bash(rm -rf *)",
      "Write(database/migrations/**)"
    ]
  }
}
```

`Write(database/migrations/**)` en `deny` est redondant avec le hook de
l'étape 2a — c'est volontaire : ça montre que permission et hook sont deux
mécanismes différents (l'un empêche l'outil d'être appelé, l'autre laisse
l'outil s'exécuter puis annule). Faites tester les deux en binôme et notez
lequel réagit, et comment le message d'erreur diffère.

---

## 4. Skills — les deux chemins

### a. En créer une : `compas-ticket`

Elle encode le workflow du README : lire un ticket du `BACKLOG.md`, écrire
d'abord le test qui échoue, confirmer qu'il échoue pour la bonne raison, puis
corriger.

`.claude/skills/compas-ticket/SKILL.md` :

```markdown
---
name: compas-ticket
description: Traite un ticket du BACKLOG.md de COMPAS en commençant par le test qui échoue. Utiliser quand on donne un identifiant de ticket (T-01 à T-11) ou qu'on demande de corriger un bug du backlog.
---

# Traiter un ticket COMPAS

1. Lire le ticket demandé dans BACKLOG.md — noter le symptôme, ne pas
   chercher la cause tout de suite.
2. Reproduire le symptôme (commande donnée dans le ticket, ou test manuel
   via `php artisan compas:ask` / le serveur).
3. Écrire un test qui échoue et qui prouve le symptôme. Le faire échouer
   pour la bonne raison — pas une erreur de syntaxe.
4. Trouver la cause dans le code (voir les classes citées dans le ticket
   comme piste, pas comme réponse).
5. Corriger. Le test doit passer.
6. Lancer `php artisan test` en entier pour vérifier l'absence de
   régression.
7. Résumer en une phrase : quel était le bug, où, et pourquoi il ne cassait
   pas la compilation.
```

Outil associé (optionnel) : un script `scripts/check-ticket.sh` qui lance
`php artisan test --filter=<pattern>` et affiche juste le résultat, pour que
Claude l'appelle sans charger tout `phpunit.xml` en contexte.

### b. En créer une deuxième : `laravel-pint-review`

Pour formaliser un geste de revue au-delà du simple lint automatique du
hook 2b — celle-ci sert **avant un commit volontaire**, pas à chaque
sauvegarde.

`.claude/skills/laravel-pint-review/SKILL.md` :

```markdown
---
name: laravel-pint-review
description: Relit les fichiers PHP modifiés avec Pint et les conventions Laravel du projet avant un commit. Utiliser avant de committer un changement PHP, ou sur demande explicite de revue de style.
---

# Revue de style avant commit

1. Lister les fichiers .php modifiés non commités (`git diff --name-only
   --diff-filter=ACM -- '*.php'`).
2. Lancer `./vendor/bin/pint --test` dessus (mode dry-run, n'écrit rien).
3. Pour chaque écart signalé, expliquer la règle Laravel concernée avant
   de proposer la correction — ne pas corriger en silence.
4. Vérifier au passage les conventions du projet qui ne sont pas du
   ressort de Pint : centimes entiers pour les montants, `validated()`
   plutôt que `all()`, `with()` sur les relations chargées dans une boucle
   Blade.
5. Ne lancer `./vendor/bin/pint` en mode écriture qu'après validation
   explicite de l'utilisateur.
```

### c. En chercher une externe et la tester

1. Chercher une skill Laravel/PHP publique existante (dépôt GitHub d'un
   auteur reconnu, ou une collection de skills communautaire).
2. La copier dans `.claude/skills/<nom>/` **sans l'activer**.
3. La scanner avant toute activation :
   ```bash
   skillspector scan .claude/skills/<nom>/
   ```
   (ou revue manuelle du `SKILL.md` et des scripts si l'outil n'est pas
   disponible) — chercher en particulier des appels réseau non annoncés,
   des `eval`/exécution dynamique, ou des instructions qui poussent à
   contourner les permissions.
4. Ne l'activer qu'une fois la revue faite, et noter dans le
   compte-rendu d'atelier ce qui a été vérifié.

C'est l'exercice le plus proche de la vraie vie : la majorité des skills
qu'une équipe utilisera un jour viendront de l'extérieur, jamais du dépôt
qu'on maîtrise.

---

## 5. MCP — outils externes, lecture seule par défaut

### a. Laravel Boost

Package officiel Laravel, pensé pour les agents IA : expose en MCP l'état
réel de l'application (routes, migrations, modèles Eloquent, config,
exécution Tinker, logs) au lieu de forcer l'agent à tout déduire du code
source.

```bash
composer require laravel/boost --dev
php artisan boost:install
```

Puis `/mcp` pour vérifier qu'il apparaît et lister ses outils. Pertinent
sur COMPAS parce que plusieurs bugs du backlog ne se voient qu'à
l'exécution (T-07 le N+1 sur `/sinistres`, T-09 la borne de date) — un
outil qui interroge l'état réel de l'app aide à les repérer plus vite
qu'une lecture de code seule.

### b. MCP Git

Serveur de référence du protocole :

```bash
claude mcp add git -- uvx mcp-server-git --repository $(pwd)
```

Utile pour l'exercice "règle non écrite" du README : demander à Claude de
retrouver, via l'historique (`git log`, `git blame`), quel commit a
introduit un des bugs du backlog — sans lui donner la réponse à l'avance.

### c. Autres MCP à essayer

| MCP | Usage sur COMPAS |
|---|---|
| **Filesystem, scopé** sur `database/seeds/documents/` | Manipuler le corpus RAG sans donner accès au reste du dépôt — bon exemple de périmètre restreint plutôt que d'un accès total au filesystem |
| **Postgres / pgvector** (`docker-compose.yml` déjà fourni) | Pour le jour où l'on branche la recherche vectorielle — déjà prévu par le projet, pas un exemple artificiel |
| **GitHub** (`github/github-mcp-server`) | Si le dépôt est poussé sur GitHub : lire issues/PR liées au backlog, pratiquer un MCP en lecture seule sur un service externe réel |
| **Playwright / navigateur** | Vérifier visuellement le N+1 de T-07 (bandeau de requêtes SQL en bas de page) et le filtre de date de T-09 sur `/sinistres`, plutôt que de se fier au seul code |
| **SQLite en lecture seule** sur `database/database.sqlite` | Alternative plus légère à Postgres pour interroger le portefeuille (40 assurés, 130 sinistres) sans passer par `artisan tinker` |

---

## 6. Idées complémentaires (bonus, si le temps le permet)

- **Hook de garde sur les statuts** : bloquer tout `Write`/`Edit` qui
  introduit la chaîne littérale `'cloture'` dans `app/` — piège direct sur
  T-10, et bon exemple de hook qui encode une règle métier plutôt qu'une
  règle de sécurité.
- **Skill `n-plus-one-detector`** : un outil (script PHP ou requête au MCP
  Laravel Boost) qui compte les requêtes SQL déclenchées par une route
  donnée et alerte au-delà d'un seuil — formalise le bandeau de debug déjà
  présent dans l'app en règle systématique.
- **Sous-agent de revue de citations** : un sous-agent dédié qui vérifie
  qu'une réponse de `Answerer` cite bien des `chemin:ligne` valides
  (pertinent vu les bugs T-03/T-05 sur les citations) — bascule vers les
  notions de l'atelier 3, mais faisable en amont si le groupe est rapide.
- **MCP "time"** (serveur de référence du protocole) pour manipuler des
  dates de test sans dépendre de l'horloge système — utile pour rejouer
  T-09 (borne de date) de façon déterministe.

---

## 7. Checklist de fin d'atelier

- [ ] `CLAUDE.md` commité, tient sur une page
- [ ] Hook migrations testé : une tentative d'édition a été bloquée à l'écran
- [ ] `.claude/settings.json` avec allow/ask/deny commité
- [ ] Skill `compas-ticket` créée et utilisée sur au moins un ticket du backlog
- [ ] Skill `laravel-pint-review` créée et testée sur un fichier modifié
- [ ] Une skill externe cherchée, scannée, puis activée (ou rejetée si le scan pose problème)
- [ ] MCP Laravel Boost visible dans `/mcp`
- [ ] MCP git ajouté, testé sur une recherche d'historique
- [ ] Test croisé en binôme fait : au moins une tentative de contournement a échoué
