# Implementation Plan: Caractères cassés en fin de réponse (T-03)

**Branch**: `001-fix-utf8-truncation` | **Date**: 2026-09-15 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/001-fix-utf8-truncation/spec.md`

**Note**: This template is filled in by the `/speckit-plan` command; its definition describes the execution workflow.

## Summary

`ContextBuilder::truncate()` coupe les extraits de document avec `substr($text, 0, $maxChars)`,
une opération qui compte et découpe en **octets**. Un caractère accentué en UTF-8 (`é`, `à`,
`ç`...) occupe 2 octets ou plus ; si le point de coupure tombe au milieu de cette séquence
multi-octets, le fragment restant est un caractère brisé, affiché comme `Ã` ou `�`. Le
correctif doit rendre la troncature consciente des frontières de caractères UTF-8, sans
changer le comportement observable pour le texte non accentué ni le principe d'un budget
exprimé en nombre de caractères.

## Technical Context

**Language/Version**: PHP 8.3+ (contrainte du projet, cf. `composer.json` : `"php": "^8.3"`)

**Primary Dependencies**: Laravel 13.17 (framework) ; extension PHP `mbstring` (déjà chargée
et déjà listée comme dépendance transitive via `composer.lock`, aucune nouvelle dépendance
à ajouter)

**Storage**: N/A — le composant concerné (`ContextBuilder`) opère uniquement sur des chaînes
de caractères en mémoire, sans accès base de données

**Testing**: PHPUnit 12.5, tests unitaires purs dans `tests/Unit/ContextBuilderTest.php`
(pas de dépendance au framework Laravel — la classe testée n'en a pas besoin)

**Target Platform**: Application web Laravel (back-office), exécution serveur PHP standard

**Project Type**: Application web monolithique (single project) — pas de séparation
frontend/backend séparée pour ce composant, qui vit côté serveur dans le pipeline de
réponse du chat Compas

**Performance Goals**: Aucune exigence de performance nouvelle ; la troncature reste une
opération synchrone sur un texte de quelques milliers de caractères au plus (`DEFAULT_BUDGET
= 2000`), exécutée une fois par extrait retenu et par requête de chat

**Constraints**: Le budget de troncature reste exprimé en nombre de caractères (pas en
tokens ni en octets) ; le suffixe `...` et le comportement pour le texte sans accent ne
doivent pas changer (FR-003, FR-005) ; aucune nouvelle dépendance externe (Principe IV —
déterminisme, pas d'appel réseau, pas de service externe)

**Scale/Scope**: Un seul point de correctif (`ContextBuilder::truncate()`, et par
ricochet `build()` qui l'appelle) ; aucun autre composant du pipeline retrieval/answering
n'est dans le périmètre

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principe | Statut | Justification |
|----------|--------|----------------|
| I. Le symptôme n'est pas la cause | PASS | Cause localisée par lecture directe de `ContextBuilder.php` (troncature par octets via `substr`/`strlen`), sans consulter la section « Défauts volontaires » de `README.md`. |
| II. Le test rouge avant le correctif | PASS (à exécuter en Phase implémentation) | Le plan impose d'écrire un test reproduisant la coupure au milieu d'un caractère accentué avant tout correctif ; `tasks.md` portera cette séquence. |
| III. Discipline de périmètre | PASS | Correctif limité à `ContextBuilder::truncate()` (et son usage interne dans `build()`) ; aucun renommage, aucune unification des formatters, aucun refactor hors sujet. |
| IV. Déterminisme et reproductibilité | PASS | Aucune dépendance réseau ni service externe introduite ; `mbstring` est une extension PHP locale déjà chargée, le comportement reste déterministe pour un texte donné. |
| V. Arithmétique monétaire en centimes | N/A | Aucun calcul monétaire concerné par ce ticket. |
| VI. Isolation par branche d'atelier | ATTENTION | Le dépôt est actuellement sur `main`. Avant tout correctif, créer et basculer sur une branche `atelier/<prénom>/T-03` (aucun commit direct sur `main`). |

Aucune violation nécessitant la section Complexity Tracking.

**Re-check post Phase 1 (design)**: la décision actée dans `research.md` (mesure et
découpe conscientes de l'encodage UTF-8, sans nouvelle dépendance ni changement de
signature) et le contrat décrit dans `data-model.md` ne modifient aucune évaluation
ci-dessus — tous les statuts restent inchangés.

## Project Structure

### Documentation (this feature)

```text
specs/[###-feature]/
├── plan.md              # This file (/speckit-plan command output)
├── research.md          # Phase 0 output (/speckit-plan command)
├── data-model.md        # Phase 1 output (/speckit-plan command)
├── quickstart.md        # Phase 1 output (/speckit-plan command)
├── contracts/           # Phase 1 output (/speckit-plan command)
└── tasks.md             # Phase 2 output (/speckit-tasks command - NOT created by /speckit-plan)
```

### Source Code (repository root)

```text
app/Services/Retrieval/
├── ContextBuilder.php     # Composant concerné : truncate() et build()
├── DocumentSearch.php     # Hors périmètre
└── ScoredDocument.php     # Hors périmètre

tests/Unit/
└── ContextBuilderTest.php # Tests existants + nouveau test rouge (Principe II)
```

**Structure Decision**: Application Laravel monolithique existante (Option 1 - single
project, adapté à la convention Laravel `app/` + `tests/`). Aucun nouveau répertoire :
le correctif et son test s'insèrent dans les fichiers déjà en place ci-dessus.

## Complexity Tracking

> Aucune violation de la Constitution Check ne nécessite de justification. Seul un
> point d'attention (Principe VI, branche de travail) est signalé ci-dessus et relève
> du workflow de contribution, pas d'une dérogation de conception.
