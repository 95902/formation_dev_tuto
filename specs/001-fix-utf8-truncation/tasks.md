---

description: "Task list template for feature implementation"
---

# Tasks: Caractères cassés en fin de réponse (T-03)

**Input**: Design documents from `/specs/001-fix-utf8-truncation/`

**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md

**Tests**: Requis pour cette feature — le Principe II (NON-NEGOTIABLE) de la constitution
du projet impose un test qui échoue avant tout correctif, indépendamment de la valeur
par défaut « tests optionnels » de ce template.

**Organization**: Une seule user story (P1) dans `spec.md` — le ticket T-03 est un
correctif ponctuel, sans découpage supplémentaire pertinent.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Peut s'exécuter en parallèle (fichiers différents, pas de dépendance)
- **[Story]**: User story concernée (US1)
- Chemins de fichiers exacts inclus dans chaque description

## Path Conventions

Projet Laravel single-project : `app/` pour le code applicatif, `tests/Unit` pour les
tests unitaires (voir `plan.md` → Structure Decision). Aucune autre convention de
chemin ne s'applique à ce correctif.

---

## Phase 1: Setup

**Purpose**: Isolation du travail conformément au Principe VI de la constitution
(aucun commit direct sur `main`).

- [X] T001 Créer et basculer sur la branche d'atelier dédiée (`git checkout -b atelier/<prenom>/T-03`) avant toute modification

**Note**: Pas de phase « Foundational » distincte pour ce ticket — il n'existe aucune
infrastructure partagée à construire avant de pouvoir attaquer la user story unique :
le correctif touche un seul fichier déjà existant (`app/Services/Retrieval/ContextBuilder.php`)
et son test associé.

---

## Phase 2: User Story 1 - Réponse tronquée lisible et correcte (Priority: P1) 🎯 MVP

**Goal**: Garantir que toute troncature d'extrait de texte produit une chaîne UTF-8
valide, sans jamais couper au milieu d'un caractère, tout en préservant le
comportement actuel pour le texte non accentué et pour les textes plus courts que le
budget.

**Independent Test**: Soumettre à `ContextBuilder::truncate()` un texte accentué avec
un budget positionné pour que la coupure par octets tomberait au milieu d'un caractère
multi-octets, et vérifier que le résultat reste une chaîne UTF-8 valide se terminant
par `...`.

### Tests for User Story 1 ⚠️ (Principe II — NON-NEGOTIABLE, à écrire et faire échouer AVANT le correctif)

- [X] T002 [P] [US1] Ajouter dans `tests/Unit/ContextBuilderTest.php` un test qui construit un texte se terminant par un caractère accentué multi-octets (ex. `é`, encodé sur 2 octets en UTF-8) et choisit `maxChars`/`budget` de sorte qu'une coupure par octets tomberait exactement à l'intérieur de ce caractère ; le test doit asserter que `mb_check_encoding($result, 'UTF-8')` est vrai, que `$result` se termine par `'...'`, et qu'aucun caractère de remplacement (`\u{FFFD}`, affiché `�`) ni séquence brisée n'apparaît dans `$result`
- [X] T003 [US1] Exécuter `./vendor/bin/phpunit tests/Unit/ContextBuilderTest.php --testdox` et confirmer que le test ajouté en T002 échoue (rouge) sur le code actuel, avant toute modification — dépend de T002

### Implementation for User Story 1

- [X] T004 [US1] Dans `app/Services/Retrieval/ContextBuilder.php`, corriger `ContextBuilder::truncate()` pour mesurer et découper le texte en conscience de l'encodage UTF-8 (ex. `mb_strlen`/`mb_substr` avec encodage `'UTF-8'` explicite) au lieu de `strlen`/`substr` (octets), de sorte que : (FR-001) le texte produit reste une chaîne UTF-8 valide de bout en bout, sans caractère corrompu ; (FR-002) la longueur du texte produit (contenu conservé + `'...'` éventuel) ne dépasse jamais `maxChars` **caractères** ; (FR-003) un texte dont la longueur est ≤ `maxChars` est retourné inchangé, sans suffixe ; (FR-004) un texte plus long est coupé uniquement à une frontière de caractère puis suffixé par `'...'` ; (FR-005) le comportement pour un texte entièrement composé de caractères ASCII reste inchangé par rapport à avant correctif — dépend de T003 (le test rouge doit exister avant ce correctif)
- [X] T005 [US1] Dans `app/Services/Retrieval/ContextBuilder.php`, si le calcul du budget restant dans `ContextBuilder::build()` (actuellement `$budget - $used - strlen($header)` et `$used += strlen($header) + strlen($body)`) reste basé sur des comptes en octets alors que `truncate()` mesure désormais en caractères UTF-8, aligner ces calculs sur la même mesure en caractères pour que le budget total réellement respecté corresponde à ce que documente `data-model.md` — dépend de T004
- [X] T006 [US1] Exécuter `./vendor/bin/phpunit tests/Unit/ContextBuilderTest.php --testdox` et confirmer que le test de T002 et les trois tests existants (`test_it_leaves_a_short_text_untouched`, `test_it_prefixes_each_block_with_its_path`, `test_it_stops_adding_blocks_once_the_budget_is_spent`) passent tous — dépend de T004, T005

**Checkpoint**: À ce stade, le symptôme T-03 est corrigé et vérifié indépendamment par
un test automatisé rouge → vert.

---

## Phase 3: Polish & Cross-Cutting Concerns

**Purpose**: Validation finale de non-régression sur l'ensemble du pipeline.

- [X] T007 Exécuter `php artisan test` depuis la racine du projet et confirmer que la suite complète est verte (SC-003 — aucune régression sur le reste du pipeline retrieval/answering) — dépend de T006
- [X] T008 [P] Suivre l'étape 4 de `specs/001-fix-utf8-truncation/quickstart.md` (`php artisan compas:ask "quelles sont les conditions de garantie dégât des eaux ?"`) et confirmer visuellement qu'aucune réponse tronquée n'affiche de caractère parasite (`Ã`, `�`) — dépend de T006

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: Aucune dépendance — à faire en premier (bascule de branche)
- **User Story 1 (Phase 2)**: Dépend de Setup — seule et unique story de ce ticket
- **Polish (Phase 3)**: Dépend de l'achèvement de la Phase 2 (T006)

### Within User Story 1

- T002 (test rouge) avant T003 (confirmation de l'échec)
- T003 avant T004 (le correctif ne commence qu'une fois le rouge constaté — Principe II)
- T004 avant T005 (cohérence de mesure caractères vs octets)
- T005 avant T006 (validation finale de la story)

### Parallel Opportunities

- Aucune réelle opportunité de parallélisation au sein de la Phase 2 : le flux
  test-rouge → correctif → test-vert est strictement séquentiel par construction
  (Principe II). T002 est marqué `[P]` uniquement parce qu'il n'a pas encore de
  dépendance amont (T001 excepté), mais T003 doit attendre T002.
- T008 (vérification manuelle) peut s'exécuter en parallèle de T007 (suite automatisée)
  puisque les deux ne modifient aucun fichier et ne dépendent que de T006.

---

## Parallel Example: User Story 1

```bash
# T002 peut démarrer dès que la branche d'atelier (T001) existe :
Task: "Ajouter le test rouge UTF-8 dans tests/Unit/ContextBuilderTest.php"

# T007 et T008 peuvent s'exécuter en parallèle une fois T006 vert :
Task: "php artisan test"
Task: "Validation manuelle via php artisan compas:ask (quickstart.md étape 4)"
```

---

## Implementation Strategy

### MVP First (et unique périmètre)

1. Compléter la Phase 1 : Setup (bascule de branche)
2. Compléter la Phase 2 : User Story 1 (test rouge → correctif → test vert) — c'est
   l'intégralité du correctif attendu pour le ticket T-03
3. **STOP et VALIDER** : `php artisan test` doit rester intégralement vert
4. Compléter la Phase 3 : Polish (validation croisée automatisée + manuelle)

Il n'y a pas d'incrément supplémentaire à livrer au-delà de cette unique user story :
le périmètre du ticket T-03 est volontairement borné à la troncature UTF-8 de
`ContextBuilder` (Principe III — Discipline de périmètre).

---

## Notes

- [P] tasks = fichiers différents ou absence de dépendance immédiate
- [US1] est la seule étiquette de story utilisée dans ce plan
- Le test de T002 DOIT échouer avant le correctif (Principe II, NON-NEGOTIABLE)
- Committer après chaque tâche ou groupe logique de tâches
- Ne pas dépasser le périmètre : aucune tâche ne touche aux formatters, à
  `DocumentSearch`, ou à tout autre composant hors de `ContextBuilder`
