# COMPAS — bac à sable de la formation

> **Ce dépôt contient du code volontairement défectueux.**
> Ce n'est pas du travail bâclé : les défauts sont le support des ateliers.
> Ne recopiez rien d'ici dans un projet réel.

COMPAS est l'outil interne fictif d'un courtier en assurance. Il fait deux
choses :

- **gérer le portefeuille** — assurés, contrats, garanties, sinistres, pièces ;
- **répondre aux questions des gestionnaires** en s'appuyant sur la
  documentation interne (conditions de garantie, barème de vétusté, procédure
  sinistre, wiki technique), et en citant ses sources sous la forme
  `chemin:ligne`.

Il sert de terrain d'entraînement pour la formation « ingénierie assistée par
l'IA ». On y travaille sur du code qui ressemble à du vrai code — services,
tests, conventions, dette — sans risquer un projet de production.

---

## Démarrer

Prérequis : PHP 8.3+, Composer 2, et l'extension **`pdo_sqlite`**.

```bash
# Debian / Ubuntu, si php -m | grep sqlite ne renvoie rien :
sudo apt install php8.3-sqlite3

git clone https://github.com/95902/formation_dev_tuto.git compas-lab
cd compas-lab
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan test
```

Puis :

```bash
php artisan serve            # puis http://127.0.0.1:8000
php artisan compas:ask "quel est le délai de déclaration d'un sinistre ?"
```

Le portefeuille chargé par `--seed` est **reproductible** : graine fixe, dates
ancrées sur le 31/08/2026 et non sur « aujourd'hui ». Les chiffres cités dans
les tickets tombent donc juste chez tout le monde, aujourd'hui comme dans six
mois. Après un `migrate:fresh --seed`, vous avez exactement la même base que
vos collègues.

### Le portefeuille

| | |
|---|---|
| 40 assurés | 74 contrats · 4 produits (auto, moto, habitation, RC pro) |
| 239 garanties | plafonds et franchises par garantie |
| 130 sinistres | 6 natures, 5 statuts, 309 pièces |
| 8 documents | conditions de garantie, barème, procédure, wiki technique |

Le bandeau noir en bas à droite affiche le nombre de requêtes SQL déclenchées
par la page en cours. Il n'est là qu'en mode debug, et il sert : une page qui
en déclenche cent doit se voir.

### Aucune clé n'est nécessaire

Le bac à sable ne fait **aucun appel réseau**. `LlmClient` est branché sur
`StubLlmClient`, qui reformule le contexte de façon déterministe. C'est ce qui
rend les tests reproductibles — et ce qui vous évite de payer pour vous
entraîner. `HttpLlmClient` existe comme support d'atelier, il n'est pas branché.

### Postgres

`docker-compose.yml` fournit une base pgvector, utile seulement le jour où l'on
branche la recherche vectorielle. Elle n'est pas nécessaire pour démarrer.

---

## Comment on travaille ici

Chacun travaille sur sa branche : `atelier/<prénom>/<ticket>`, par exemple
`atelier/marshel/T-01`. On ne pousse jamais sur `main`.

Les tickets sont dans [`BACKLOG.md`](BACKLOG.md). Ils décrivent un **symptôme**,
jamais la cause : trouver la cause fait partie de l'exercice.

La suite de tests est **verte au clone**. Un ticket commence donc par écrire le
test qui échoue — c'est lui qui prouve que le bug existe, et c'est lui qui
prouvera qu'il est corrigé.

---

## Défauts volontaires

Ils sont listés ici pour que personne ne les prenne pour des accidents. **Ne
lisez cette section que si vous animez la séance** — elle donne les réponses.

<details>
<summary>Afficher (spoilers)</summary>

| Où | Nature |
|---|---|
| `DocumentSearch::tokenize()` | ne met pas les termes en minuscules, alors que `score()` compare à un texte en minuscules |
| `DocumentSearch::dedupe()` | dédoublonne sur le titre au lieu du chemin — les deux `README` du corpus s'écrasent |
| `ContextBuilder::truncate()` | coupe en octets (`substr`/`strlen`) et casse les accents en fin de bloc |
| `ContextBuilder::estimateTokens()` | `str_word_count × 0,75` — la fonction ignore les accents et le ratio est inversé |
| `Citation::locate()` | compte les sauts de ligne sans `+ 1` : tous les numéros de ligne sont décalés de un |
| `HttpLlmClient::API_KEY` | clé en dur dans le code (valeur factice, inoffensive) |
| `StatsController` | enfreint les conventions API internes : `camelCase`, clé primaire exposée, pagination par offset |
| `app/Services/Answering/Formatters/` | cinq classes quasi identiques, dont une méthode morte |
| `SinistreController::index()` | aucun `with()` : la vue résout `contrat.assure` et `pieces` ligne par ligne (N+1) |
| `Sinistre::indemniteCents()` | `(int)` tronque au lieu d'arrondir — un centime perdu sur un dossier sur deux |
| `SinistreController::index()` | la borne haute compare une colonne `datetime` à une date, donc à minuit |
| `DashboardController` | compte `statut = 'cloture'`, alors que la valeur en base est `clos` |
| `SinistreController::update()` | `$request->all()` au lieu de `validated()` — affectation de masse |

</details>

---

## Ce que ce dépôt ne contient pas

Pas de déroulé d'atelier, pas d'objectifs pédagogiques, pas de corrigés. Tout
cela vit dans le pack de formation, à côté. Ici il n'y a que le produit et son
backlog — comme dans un vrai projet.

Pas d'authentification non plus : toutes les pages sont ouvertes. C'est un choix
de bac à sable, pas un modèle. Ne le reprenez nulle part.
