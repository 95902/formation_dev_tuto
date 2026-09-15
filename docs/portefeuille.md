# Gestion de portefeuille

Cette moitié de l'application couvre le cycle `assurés → contrats →
garanties / sinistres → pièces`. Elle repose sur des contrôleurs de
ressources Laravel classiques au-dessus de modèles Eloquent, sans couche de
service intermédiaire (la logique métier vit soit dans les modèles, soit
directement dans les contrôleurs).

## Modèles et relations

### `Assure` (table `assures`)

Champs `$fillable` : `reference`, `civilite`, `nom`, `prenom`, `email`,
`telephone`, `date_naissance`, `adresse`, `code_postal`, `ville`.
`date_naissance` est casté en `date`.

- `contrats(): HasMany` vers `Contrat`.
- `sinistres(): HasManyThrough` vers `Sinistre` via `Contrat` (un assuré
  n'a donc pas de clé étrangère directe vers ses sinistres).
- `nomComplet(): string` — concatène `prenom` et `nom`.

### `Contrat` (table `contrats`)

Champs `$fillable` : `assure_id`, `reference`, `produit`, `formule`,
`date_effet`, `date_echeance`, `statut`, `prime_annuelle_cents`,
`franchise_cents`. Les montants sont castés en `integer` (centimes), les
dates en `date`.

Constantes de domaine :
- `PRODUITS` = `auto`, `moto`, `habitation`, `rc_pro`
- `FORMULES` = `essentiel`, `confort`, `premium`
- `STATUTS` = `actif`, `suspendu`, `resilie`

Relations : `assure(): BelongsTo`, `garanties(): HasMany` vers `Garantie`,
`sinistres(): HasMany` vers `Sinistre`.

Méthodes :
- `scopeActifs(Builder $query)` — filtre `statut = 'actif'`.
- `garantiePour(string $nature): ?Garantie` — retrouve, parmi les garanties
  déjà chargées du contrat (`$this->garanties`, collection en mémoire, pas
  de requête), celle dont le `code` correspond à la nature de sinistre via
  `Garantie::CODE_PAR_NATURE`. Retourne `null` si la nature n'a pas de code
  associé ou si le contrat ne porte pas cette garantie.

### `Garantie` (table `garanties`)

Champs `$fillable` : `contrat_id`, `code`, `libelle`, `plafond_cents`,
`franchise_cents`, `incluse`. `incluse` est casté en `boolean`,
`plafond_cents`/`franchise_cents` en `integer`.

Deux constantes de correspondance nature de sinistre / code garantie /
libellé commercial :

```php
CODE_PAR_NATURE = [
    'degat_des_eaux' => 'DDE',
    'vol' => 'VOL',
    'incendie' => 'INC',
    'bris_de_glace' => 'BDG',
    'collision' => 'COL',
    'rc' => 'RC',
];

LIBELLES = [
    'DDE' => 'Dégât des eaux',
    'VOL' => 'Vol et vandalisme',
    'INC' => 'Incendie et évènements assimilés',
    'BDG' => 'Bris de glace',
    'COL' => 'Dommages collision',
    'RC' => 'Responsabilité civile',
];
```

Relation : `contrat(): BelongsTo`.

### `Sinistre` (table `sinistres`)

Champs `$fillable` : `contrat_id`, `reference`, `nature`, `survenu_le`,
`declare_le`, `description`, `statut`, `montant_estime_cents`,
`montant_regle_cents`, `gestionnaire`. `survenu_le` est casté en `date`,
`declare_le` en `datetime`, les montants en `integer`.

Constantes :
- `NATURES` (nature vers libellé) : `degat_des_eaux`, `vol`, `incendie`,
  `bris_de_glace`, `collision`, `rc`.
- `STATUTS` (code vers libellé) : `declare`, `en_cours`, `expertise`,
  `clos`, `refuse`.
- `TAUX_VETUSTE = 0.87` — abattement de vétusté appliqué au montant estimé.

Relations : `contrat(): BelongsTo`, `pieces(): HasMany` vers `Piece`.

Scopes :
- `scopeEnInstruction` — `statut` dans `declare`, `en_cours`, `expertise`.
- `scopeClos` — `statut = 'clos'`.

Méthodes de calcul métier :

- `delaiDeclarationJours(): int` — nombre de jours pleins entre
  `survenu_le` et `declare_le` (`diffInDays` sur les débuts de journée des
  deux dates).
- `franchiseApplicableCents(): int` — franchise de la garantie mobilisée
  (`contrat->garantiePour($nature)`) si elle existe, sinon la franchise
  générale du contrat (`contrat->franchise_cents`).
- `indemniteCents(): int` — calcule l'indemnité due :
  1. `apresVetuste = montant_estime_cents * TAUX_VETUSTE` (0.87) ;
  2. `net = apresVetuste - franchiseApplicableCents()` ;
  3. si une garantie mobilisée a un `plafond_cents`, `net` est plafonné à
     cette valeur ;
  4. le résultat final passe par un cast `(int)` puis `max(0, …)` (ne peut
     pas être négatif).
- `resteAChargeCents(): int` — `max(0, montant_estime_cents -
  indemniteCents())`.

### `Piece` (table `pieces`)

Champs `$fillable` : `sinistre_id`, `libelle`, `type`, `recue_le` (casté en
`date`).

Constantes : `TYPES` = `constat`, `facture`, `photo`, `rapport_expertise`,
`proces_verbal` ; `LIBELLES` associe chaque type à son libellé lisible.

Relation : `sinistre(): BelongsTo`.

### `Document` (table `documents`)

Champs `$fillable` : `title`, `source_type`, `path`, `content`. C'est le
modèle utilisé par le pipeline Q&A (voir `qa-rag.md`), pas par le
portefeuille — il est listé ici pour mémoire car il partage
`app/Models/`.

`lines(): array` — découpe `content` en lignes (`\r\n`, `\n` ou `\r`).

## Schéma relationnel

```
Assure 1 --< Contrat 1 --< Garantie
                 |
                 +--< Sinistre 1 --< Piece
```

`Assure::sinistres()` traverse `Contrat` (`hasManyThrough`) : il n'y a pas
de clé étrangère `assure_id` sur la table `sinistres`.

## Routes et contrôleurs

Déclarées dans `routes/web.php`, toutes montées à la racine (pas de
préfixe `/api` sauf les deux endpoints explicitement préfixés) :

| Route | Contrôleur | Notes |
|---|---|---|
| `GET /` | `DashboardController` (invocable) | tableau de bord agrégé |
| `resource assures` | `AssureController` | CRUD standard |
| `resource contrats` | `ContratController` | CRUD standard |
| `resource sinistres` | `SinistreController` | CRUD standard |
| `POST contrats/{contrat}/garanties` | `GarantieController@store` | route nommée `garanties.store` |
| `DELETE contrats/{contrat}/garanties/{garantie}` | `GarantieController@destroy` | route nommée `garanties.destroy` |
| `POST sinistres/{sinistre}/pieces` | `PieceController@store` | route nommée `pieces.store` |
| `DELETE sinistres/{sinistre}/pieces/{piece}` | `PieceController@destroy` | route nommée `pieces.destroy` |
| `GET /api/stats` | `StatsController` (invocable) | voir remarque ci-dessous |

`GarantieController` et `PieceController` ne sont pas des ressources
complètes : seuls `store` et `destroy` existent, montés en routes
imbriquées sous `contrats` et `sinistres` respectivement.

### `AssureController`

- `index` — recherche (`q`, sur `nom`/`prenom`/`reference`/`ville` via
  `LIKE`), tri par `nom`, pagination de 20, avec `withCount('contrats')`.
- `store` — valide via `AssureRequest`, génère la référence via
  `prochaineReference()` (format `ASS-2026-%03d`, basé sur
  `Assure::max('id') + 1`).
- `show` — précharge `contrats.garanties` et `contrats.sinistres`.
- `destroy` — refuse la suppression si l'assuré porte encore des contrats
  (`$assure->contrats()->exists()`).

### `ContratController`

- `index` — filtres `produit` et `statut`, tri par `date_effet`
  décroissante, pagination de 20, avec `with('assure')` et
  `withCount('sinistres')`.
- `create`/`edit` — fournissent la liste complète des assurés
  (`Assure::orderBy('nom')->get()`) pour le formulaire.
- `store`/`update` passent par la méthode privée `donnees()`, qui convertit
  les champs saisis en euros (`prime_annuelle_euros`, `franchise_euros`)
  en centimes entiers (`(int) round(... * 100)`) avant persistance.
- `destroy` — refuse la suppression si des sinistres sont rattachés au
  contrat.

### `SinistreController`

- `index` — filtres `statut`, `nature`, bornes de date `du`/`au` sur
  `declare_le`, recherche texte `q` (sur `reference`/`description`), tri
  par `declare_le` décroissante, pagination de 25.
- `create`/`edit` — fournissent la liste des contrats
  (`Contrat::with('assure')->orderBy('reference')->get()`).
- `store`/`update` passent par `enCentimes()` (convertit `montant_estime`
  en euros vers `montant_estime_cents`) puis valident via `regles()`
  (règles de validation inline, pas de `FormRequest` dédié).
- `store` génère la référence via `prochaineReference()` (format
  `SIN-2026-%05d`).
- `show` précharge `contrat.assure`, `contrat.garanties`, `pieces`.
- `destroy` redirige vers la fiche du contrat parent après suppression.

### `GarantieController`

- `store($request, Contrat $contrat)` — valide `code` (parmi les valeurs de
  `Garantie::CODE_PAR_NATURE`, unique pour le contrat), `plafond_euros`,
  `franchise_euros` ; crée la garantie avec `libelle` déduit de
  `Garantie::LIBELLES[$code]` et `incluse = true`.
- `destroy($contrat, $garantie)` — vérifie l'appartenance de la garantie au
  contrat (`abort_unless`, 404 sinon) avant suppression.

### `PieceController`

- `store($request, Sinistre $sinistre)` — valide `type` (parmi
  `Piece::TYPES`), `libelle` optionnel (déduit de `Piece::LIBELLES[$type]`
  si absent), `recue_le`.
- `destroy($sinistre, $piece)` — vérifie l'appartenance de la pièce au
  sinistre avant suppression.

### `DashboardController` (invocable, `GET /`)

Agrège pour la vue `dashboard` : nombre d'assurés, nombre de contrats
actifs (`Contrat::actifs()`), nombre de documents, nombre de sinistres en
instruction (`Sinistre::enInstruction()`), nombre de sinistres clos et
refusés, somme des montants estimés des sinistres en instruction, les 8
derniers sinistres déclarés (avec `contrat.assure` préchargé), et la
répartition des sinistres par nature (`GROUP BY nature`).

### `StatsController` (invocable, `GET /api/stats`)

Endpoint JSON qui indique lui-même, dans son docblock, qu'il s'écarte
volontairement des conventions internes suivies par le reste de l'API (voir
`database/seeds/documents/doc-conventions-api.md`). Concrètement :
pagination par offset pilotée par les paramètres de requête `page` et
`perPage` (défaut `perPage=200`, calcul manuel via `skip()`/`take()` plutôt
que le paginator Eloquent), clés de réponse en `camelCase`
(`totalCount`, `currentPage`, `sourceType`, `lineCount`), et exposition de
la clé primaire (`id`) de chaque document dans la réponse.

## Formatage monétaire

Tous les montants sont stockés en **centimes entiers** (`*_cents`) dans les
tables `contrats`, `garanties`, `sinistres`. `app/Support/Euro::format(?int
$cents): string` est le seul point de conversion vers un affichage en
euros (`number_format($cents / 100, 2, ',', ' ').' €'`, ou `—` si `null`) ;
la classe est documentée comme ne devant jamais servir au calcul, seulement
à l'affichage.

## Bandeau de requêtes SQL

`app/Support/QueryLog.php`, instancié en singleton et écouté depuis
`AppServiceProvider::boot()` (uniquement si `config('app.debug')` est
actif) via `DB::listen(...)`, comptabilise le nombre et la durée totale des
requêtes SQL exécutées pendant la requête HTTP en cours. L'objet est
partagé à toutes les vues (`View::share('queryLog', $log)`) pour alimenter
un bandeau d'information affiché en bas de chaque page.
