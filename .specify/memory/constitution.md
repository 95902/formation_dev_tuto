<!--
Sync Impact Report
- Version change: (none) → 1.0.0
- Modified principles: n/a (initial ratification)
- Added sections: Core Principles (I–VI), Contraintes techniques, Workflow de contribution, Governance
- Removed sections: none
- Templates requiring follow-up: none found referencing prior principle names (first ratification)
- Deferred placeholders: none — all tokens resolved from CLAUDE.md / README.md / BACKLOG.md context
-->

# COMPAS (bac à sable de formation) Constitution

## Core Principles

### I. Le symptôme n'est pas la cause (NON-NEGOTIABLE)
Les tickets (`BACKLOG.md`, T-01..T-11) décrivent uniquement un symptôme observé, jamais
le défaut qui le produit. Localiser la cause fait partie du travail : il est INTERDIT de
consulter la section repliée « Défauts volontaires » de `README.md` pour raccourcir un
ticket — elle est réservée à l'animateur de séance, sauf demande explicite de
l'utilisateur de révéler ou confirmer un défaut déjà identifié.
**Rationale**: le dépôt existe pour entraîner le diagnostic ; sauter cette étape annule
la valeur pédagogique de l'exercice.

### II. Le test rouge avant le correctif (NON-NEGOTIABLE)
La suite de tests est verte au clone. Tout ticket commence par écrire un test qui échoue
et prouve le bug ; le correctif n'est terminé que lorsque ce test — et le reste de la
suite — repasse au vert (`php artisan test`, ou `composer test`).
**Rationale**: le test rouge est la preuve que le bug existe autant que la preuve qu'il
est corrigé ; sans lui, rien ne distingue une correction réelle d'une coïncidence.

### III. Discipline de périmètre (pas de nettoyage à la volée)
Ne pas refactorer, unifier ou « améliorer » du code en dehors du périmètre exact du
ticket en cours, même en présence de duplication ou de dette visible (ex. les cinq
formatters quasi identiques de `app/Services/Answering/Formatters/`, dette connue
et volontaire — voir T-06). Le désordre observé est souvent lui-même l'exercice.
**Rationale**: élargir un correctif dilue la démonstration et complique la relecture
de l'atelier ; la dette n'est traitée que quand un ticket la cible explicitement.

### IV. Déterminisme et reproductibilité
Aucun appel réseau n'est introduit dans le pipeline Q&A : `LlmClient` reste lié à
`StubLlmClient` (jamais à `HttpLlmClient`, support d'atelier non branché). Le seed de
base de données (`--seed`) et ses dates ancrées (31/08/2026) ne sont pas rendus
dépendants de l'horloge système. Le classement de `DocumentSearch` reste par
mots-clés, sans dépendance à un service d'embeddings externe.
**Rationale**: la reproductibilité bit-à-bit entre postes est ce qui rend les tickets
et leurs chiffres vérifiables par tous, à toute date.

### V. Arithmétique monétaire en centimes entiers
Tout calcul monétaire du domaine (`Sinistre::indemniteCents()`, `montant_regle_cents`,
etc.) s'effectue en centimes entiers via `app/Support/Euro.php`. Aucune arithmétique
flottante n'est introduite pour représenter une somme d'argent.
**Rationale**: les flottants introduisent des erreurs d'arrondi silencieuses,
inacceptables pour des montants d'indemnisation.

### VI. Isolation par branche d'atelier
Tout travail se fait sur une branche `atelier/<prénom>/<ticket>` (ex.
`atelier/marshel/T-01`) ; `main` n'est jamais un commit direct.
**Rationale**: chaque participant doit pouvoir comparer sa branche à `main` sans
collision avec le travail des autres.

## Contraintes techniques

Stack imposée : PHP 8.3+, Laravel 13, Vite/Tailwind 4, SQLite par défaut, PHPUnit 12.
Aucune authentification n'est ajoutée aux pages du portail — c'est un choix assumé du
bac à sable, pas un défaut à corriger. Postgres/pgvector (`docker-compose.yml`) reste
un support latent pour une future recherche vectorielle et n'est pas une dépendance du
fonctionnement courant. `StatsController` déroge volontairement aux conventions API
internes suivies par le reste de l'application (nommage, exposition de clé primaire,
pagination) : c'est un exercice de revue délibéré, pas une régression à aligner
silencieusement.

## Workflow de contribution

Un ticket suit : lecture du symptôme → reproduction → test qui échoue (Principe II) →
investigation de la cause (Principe I) → correctif borné au périmètre (Principe III) →
suite de tests verte. Les commandes de référence (`php artisan test`,
`php artisan test --filter=...`, `./vendor/bin/phpunit ...`) sont documentées dans
`CLAUDE.md`, qui reste la source opérationnelle détaillée ; cette constitution en fixe
les principes non négociables, `CLAUDE.md` en détaille l'exécution.

## Governance

Cette constitution prévaut sur toute pratique ad hoc divergente au sein du dépôt. Toute
modification (ajout, retrait ou reformulation d'un principe) doit être répercutée le
même jour dans `CLAUDE.md` si son contenu opérationnel en dépend, et documentée dans le
Sync Impact Report en tête de ce fichier.

Versionnage sémantique de ce document :
- MAJOR : suppression ou redéfinition incompatible d'un principe existant.
- MINOR : ajout d'un principe ou extension matérielle d'une section.
- PATCH : clarification, reformulation, correction sans changement de fond.

Toute Pull Request ou revue doit pouvoir se justifier au regard des six principes
ci-dessus ; une dérogation (ex. refactor plus large que le ticket) doit être explicitée
et approuvée par l'utilisateur avant d'être exécutée.

**Version**: 1.0.0 | **Ratified**: 2026-09-15 | **Last Amended**: 2026-09-15
