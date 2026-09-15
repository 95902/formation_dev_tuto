# Quickstart: Valider le correctif T-03

**Note sur `/contracts`**: `ContextBuilder::truncate()`/`build()` sont des méthodes
internes au pipeline retrieval/answering, non exposées comme API publique, endpoint
HTTP ou CLI. Conformément au guide de planification, l'étape « contracts » est donc
omise pour cette fonctionnalité — le contrat pertinent est décrit dans
[data-model.md](./data-model.md) (règles de validité de la troncature).

## Prérequis

- Dépôt cloné, dépendances installées (`composer install`), `.env` configuré (voir
  `CLAUDE.md`).
- Être sur une branche d'atelier dédiée, jamais sur `main` (Constitution, Principe VI) :

  ```bash
  git checkout -b atelier/<prenom>/T-03
  ```

## 1. Reproduire le symptôme (test rouge attendu, Principe II)

Ajouter dans `tests/Unit/ContextBuilderTest.php` un cas qui choisit un texte accentué et
un budget dont la coupure « par octets » tomberait au milieu d'un caractère multi-octets
(ex. un mot se terminant par `é`, avec `maxChars` positionné pile sur le premier des
deux octets de ce `é`). Vérifier :

- que le résultat est une chaîne UTF-8 valide (`mb_check_encoding($result, 'UTF-8')`
  doit être vrai) ;
- que le résultat se termine par `...` ;
- qu'aucun octet de remplacement (`\xEF\xBF\xBD`, rendu `�`) ni séquence tronquée n'y
  apparaît.

Lancer uniquement ce test et confirmer qu'il échoue avant tout correctif :

```bash
./vendor/bin/phpunit tests/Unit/ContextBuilderTest.php --testdox
```

## 2. Appliquer le correctif

Modifier `app/Services/Retrieval/ContextBuilder.php` selon la décision actée dans
[research.md](./research.md) (mesure et découpe conscientes de l'encodage UTF-8),
strictement dans le périmètre de `truncate()`/`build()` (Principe III — pas de
renommage, pas de refactor des formatters ou d'autres composants).

## 3. Valider

```bash
./vendor/bin/phpunit tests/Unit/ContextBuilderTest.php --testdox   # le nouveau test passe
php artisan test                                                   # suite complète verte
```

## 4. Vérification manuelle (bout en bout)

```bash
php artisan compas:ask "quelles sont les conditions de garantie dégât des eaux ?"
```

Confirmer visuellement que toute réponse tronquée se termine par un mot lisible suivi
de `...`, sans caractère parasite, même quand le contexte source contient des mots
accentués proches de la limite du budget par défaut (`ContextBuilder::DEFAULT_BUDGET`).

## Résultat attendu

- Le test ajouté à l'étape 1 est vert.
- `php artisan test` est intégralement vert (aucune régression).
- Aucune modification hors de `ContextBuilder.php` et de son test associé.
