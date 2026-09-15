# Feature — Export CSV des sinistres

> Support d'atelier Spec Kit : démo `/speckit-specify` → `/speckit-plan` →
> `/speckit-tasks` → `/speckit-implement` sur une **fonctionnalité neuve**
> (par opposition aux tickets `BACKLOG.md`, qui sont des corrections de bugs
> et produisent presque toujours une seule user story). Objectif de la
> séance : montrer le découpage en plusieurs stories priorisées (P1/P2/P3),
> le MVP-first, et la livraison incrémentale.

## Pourquoi cette feature plutôt qu'un ticket du backlog

- Les tickets T-01..T-11 décrivent un symptôme ponctuel : le spec généré n'a
  presque jamais plus d'une user story (voir `specs/001-fix-utf8-truncation/`,
  issu de T-03). Utile pour le rouge/vert TDD, pas pour montrer le
  MVP-first ni le découpage par priorité.
- L'export CSV a trois besoins réellement indépendants et démontrables
  séparément : livrer P1 seul est déjà utile, P2 et P3 s'ajoutent sans
  casser ce qui précède.
- Elle engage la constitution du projet pour de vrai (contrairement à
  T-03) : Principe V (centimes entiers, `app/Support/Euro.php`) sur le
  format des montants exportés, Principe III (discipline de périmètre)
  testable entre chaque story.

## Brief à donner aux stagiaires (à coller tel quel dans `/speckit-specify`)

```text
Export CSV des sinistres

Contexte (voix de la gestion sinistres) : La liste /sinistres affiche jusqu'à
25 dossiers par page. L'équipe a besoin d'extraire ces données pour les
partager avec le réassureur et pour son suivi trimestriel. Aujourd'hui, la
seule solution est de recopier à la main depuis l'écran.

Besoin P1 : pouvoir télécharger, depuis /sinistres, un fichier CSV contenant
les dossiers actuellement affichés (numéro de dossier, assuré, contrat,
statut, montant réglé, date de déclaration).

Besoin P2 : quand des filtres sont actifs sur la page (période de
déclaration, statut), l'export ne doit contenir que les dossiers filtrés —
jamais plus que ce que l'utilisateur voit à l'écran au moment du clic.

Besoin P3 : pouvoir choisir, avant l'export, quelles colonnes inclure (par
exemple exclure le montant réglé pour un envoi à un tiers qui n'a pas à le
voir).

Contraintes : montants exportés en euros lisibles, pas en centimes bruts
(cohérent avec app/Support/Euro.php) ; fichier exploitable par un tableur
standard, encodage et séparateur corrects pour les caractères accentués.
```

## Points à observer / provoquer en direct pendant l'atelier

- **`/speckit-specify`** : vérifier que le spec généré produit bien 3 user
  stories distinctes et priorisées, chacune avec son propre test
  d'indépendance — pas une seule story fourre-tout.
- **Clarification volontaire** : le P3 ne précise pas les colonnes par
  défaut ni le nom du fichier téléchargé — bon point d'entrée pour lancer
  `/speckit-clarify` si le spec généré ne le relève pas déjà d'initiative.
- **`/speckit-plan`** : la Constitution Check doit référencer explicitement
  le Principe V (centimes entiers) sur le format des montants — si le plan
  généré le passe en `N/A`, c'est l'occasion de montrer comment challenger
  un plan incomplet.
- **`/speckit-tasks`** : comparer la structure avec `specs/001-fix-utf8-truncation/tasks.md`
  (T-03) — ici on doit voir une vraie Phase 2 "Foundational" si un service
  d'export commun est mutualisé entre les 3 stories, et trois phases de
  story distinctes au lieu d'une seule.
- **MVP-first** : montrer qu'on peut s'arrêter après la Phase "User Story 1"
  et avoir une fonctionnalité livrable, avant de décider (ou non) d'ajouter
  P2/P3 selon le temps restant de la séance.

## Ce que ce fichier n'est pas

Il ne contient volontairement ni spec, ni plan, ni tasks : ces artefacts
doivent être générés **en direct** pendant la formation via les commandes
`/speckit-specify`, `/speckit-plan`, `/speckit-tasks` et `/speckit-implement`,
pas préparés à l'avance.
