# Feature Specification: Caractères cassés en fin de réponse (T-03)

**Feature Branch**: `[001-fix-utf8-truncation]`

**Created**: 2026-09-15

**Status**: Draft

**Input**: User description: "Ticket BACKLOG.md T-03 — Caractères cassés en fin de réponse

Symptôme observé :
Quand la réponse est longue, le dernier mot avant "..." finit parfois par un caractère
parasite (Ã, �). Toujours sur un mot accentué, jamais sur un mot sans accent. Voir
ContextBuilder.

Critère d'acceptation (« c'est bon quand ») :
Aucune troncature ne peut produire une chaîne invalide en UTF-8, quel que soit le budget
et le texte.

Contraintes (constitution du projet) :
- N'ouvre pas la section « Défauts volontaires » de README.md : trouve la cause par
  investigation du code, pas par lecture du corrigé.
- Écris d'abord un test qui échoue et prouve le bug, avant tout correctif.
- Corrige strictement dans le périmètre de ce symptôme ; aucun refactor, renommage ou
  nettoyage hors sujet."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Réponse tronquée lisible et correcte (Priority: P1)

Un utilisateur du chat Compas pose une question dont la réponse s'appuie sur un extrait
de document dépassant le budget de contexte alloué. Le système tronque l'extrait pour
respecter ce budget. L'utilisateur doit voir un texte tronqué propre, se terminant par
un mot complet et lisible suivi de `...`, jamais par un caractère tronqué au milieu
(affiché comme `Ã`, `�`, ou tout autre symbole illisible).

**Why this priority**: C'est le symptôme exact remonté par le support — il dégrade la
confiance dans les réponses du chat et touche potentiellement toute réponse longue,
donc un grand nombre d'utilisateurs.

**Independent Test**: Peut être testé isolément en soumettant à la troncature un texte
contenant des caractères accentués (français) positionnés de sorte que la coupure
« naïve » tomberait au milieu d'un caractère multi-octet, et en vérifiant que le
résultat est un texte valide se terminant proprement.

**Acceptance Scenarios**:

1. **Given** un extrait de document contenant des caractères accentués et un budget de
   troncature qui, appliqué naïvement, couperait au milieu d'un caractère accentué,
   **When** le système tronque cet extrait pour l'inclure dans le contexte de réponse,
   **Then** le texte produit est une chaîne valide de bout en bout (aucun caractère
   corrompu) et se termine par `...`.
2. **Given** un extrait de document ne contenant que des caractères sans accent,
   **When** le système le tronque au même budget,
   **Then** le comportement observable (longueur du texte conservé, présence de `...`)
   reste identique à celui d'aujourd'hui.
3. **Given** un extrait de document plus court que le budget alloué,
   **When** le système traite cet extrait,
   **Then** l'extrait est conservé intégralement, sans troncature ni ajout de `...`.

---

### Edge Cases

- Que se passe-t-il quand le point de coupure tombe exactement à la frontière entre
  deux caractères (accentué ou non) ? Le texte conservé doit rester valide et ne pas
  dépasser le budget alloué.
- Que se passe-t-il quand le budget restant est si petit qu'aucun caractère complet ne
  peut être conservé avant `...` ? Le système doit produire un résultat valide (au
  besoin un extrait vide suivi de `...`, ou l'omission de l'extrait) plutôt qu'un texte
  corrompu.
- Que se passe-t-il avec des caractères accentués composés de plusieurs octets en fin
  d'extrait exactement à la limite du budget (ni avant, ni après) ?
- Le comportement doit rester identique quel que soit le document source (procédure
  sinistre, barème de vétusté, wiki technique, etc.) et quel que soit le budget de
  contexte configuré.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Le système DOIT garantir que tout texte tronqué pour respecter un budget
  de contexte reste une chaîne de caractères valide, sans caractère corrompu ni
  fragment de caractère illisible, quel que soit le contenu source (accentué ou non).
- **FR-002**: Le système DOIT respecter la limite de budget donnée : le texte produit
  (contenu conservé + suffixe `...` le cas échéant) ne doit jamais dépasser le budget
  alloué.
- **FR-003**: Lorsque le texte source tient intégralement dans le budget, le système
  DOIT le restituer sans le modifier et sans ajouter de `...`.
- **FR-004**: Lorsque le texte source dépasse le budget, le système DOIT couper
  uniquement entre deux caractères complets, jamais à l'intérieur d'un caractère, et
  ajouter le suffixe `...` pour signaler la troncature.
- **FR-005**: Le comportement de troncature pour un texte ne contenant que des
  caractères sans accent ne doit pas changer par rapport au comportement actuel.

### Key Entities

- **Extrait de document (contexte)** : portion de texte d'un `Document` retenue pour
  construire la réponse, potentiellement tronquée pour respecter un budget de
  caractères avant d'être présentée à l'utilisateur ou transmise en contexte.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 100 % des réponses tronquées affichées aux utilisateurs se terminent par
  un mot lisible suivi de `...`, sans aucun caractère corrompu, quel que soit le budget
  configuré ou la présence de caractères accentués dans le texte source.
- **SC-002**: Un test automatisé reproduisant le symptôme (texte accentué + budget
  provoquant une coupure au milieu d'un caractère) échoue avant correctif et passe
  après correctif, et reste vert de manière stable (aucune régression aléatoire).
- **SC-003**: L'ensemble de la suite de tests existante continue de passer après le
  correctif (aucune régression sur le comportement de troncature pour les textes sans
  accent ni sur le reste du pipeline de réponse).

## Assumptions

- Le périmètre du correctif se limite à la troncature d'extraits de texte pour le
  budget de contexte (composant `ContextBuilder`, cf. ticket) ; aucun autre point du
  pipeline de réponse n'est concerné.
- « Chaîne invalide en UTF-8 » désigne tout texte contenant un caractère coupé au
  milieu de sa représentation multi-octets, tel qu'observé dans le symptôme (`Ã`,
  `�`) — la définition précise de la validité s'appuie sur l'encodage UTF-8 déjà en
  usage dans le reste de l'application, sans introduire d'encodage différent.
- Le format du suffixe de troncature (`...`) et le principe d'un budget en nombre de
  caractères restent inchangés ; seule la garantie de ne pas couper au milieu d'un
  caractère est ajoutée.
- Aucun changement d'interface utilisateur, d'API publique ou de signature de méthode
  visible en dehors du périmètre de troncature n'est attendu par ce ticket.
