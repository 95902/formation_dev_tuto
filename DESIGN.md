---
name: COMPAS
description: Poste de contrôle sobre pour la gestion de portefeuille et l'assistance documentaire d'un courtier en assurance.
colors:
  fond: "#f6f7f9"
  carte: "#ffffff"
  trait: "#e2e5ea"
  trait-fort: "#cbd1da"
  texte: "#1b2027"
  doux: "#5c6673"
  pale: "#8b95a3"
  acc: "#1f5f8b"
  acc-clair: "#eaf2f8"
  ok: "#1a7f5a"
  ok-clair: "#e6f4ee"
  alerte: "#a8630d"
  alerte-clair: "#fdf1e0"
  stop: "#a32c2c"
  stop-clair: "#fbeaea"
  rail-fond: "#141a21"
  rail-texte: "#c3ccd7"
  rail-survol: "#1c242e"
  rail-sous: "#6b7684"
  bouton-danger-bordure: "#e3bcbc"
  msg-ok-bordure: "#b9e0cf"
  msg-err-bordure: "#eec4c4"
  sonde-chaud: "#e8a33d"
typography:
  display:
    fontFamily: "system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif"
    fontSize: "24px"
    fontWeight: 650
    lineHeight: 1.2
  headline:
    fontFamily: "system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif"
    fontSize: "17px"
    fontWeight: 620
    lineHeight: 1.3
  body:
    fontFamily: "system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif"
    fontSize: "15px"
    fontWeight: 400
    lineHeight: 1.55
  label:
    fontFamily: "system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif"
    fontSize: "12px"
    fontWeight: 600
    letterSpacing: "0.4px"
  mono:
    fontFamily: "SFMono-Regular, Consolas, 'Liberation Mono', monospace"
    fontSize: "12.5px"
    fontWeight: 400
  marque:
    fontFamily: "system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif"
    fontSize: "19px"
    fontWeight: 700
    letterSpacing: "0.5px"
  micro-label:
    fontFamily: "system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif"
    fontSize: "11px"
    fontWeight: 400
    letterSpacing: "0.7px"
  small:
    fontFamily: "system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif"
    fontSize: "14px"
    fontWeight: 400
  compact:
    fontFamily: "system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif"
    fontSize: "13.5px"
    fontWeight: 400
  stat:
    fontFamily: "system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif"
    fontSize: "27px"
    fontWeight: 640
    lineHeight: 1.15
rounded:
  sm: "5px"
  md: "7px"
  pill: "11px"
  pill-lg: "13px"
  msg: "6px"
spacing:
  xs: "8px"
  sm: "10px"
  md: "16px"
  lg: "26px"
components:
  button-primary:
    backgroundColor: "{colors.acc}"
    textColor: "#ffffff"
    rounded: "{rounded.sm}"
    padding: "8px 15px"
  button-primary-hover:
    backgroundColor: "#194e73"
    textColor: "#ffffff"
  button-default:
    backgroundColor: "#ffffff"
    textColor: "{colors.texte}"
    rounded: "{rounded.sm}"
    padding: "8px 15px"
  button-danger:
    backgroundColor: "#ffffff"
    textColor: "{colors.stop}"
    rounded: "{rounded.sm}"
    padding: "8px 15px"
  card:
    backgroundColor: "{colors.carte}"
    rounded: "{rounded.md}"
    padding: "16px 18px"
  input:
    backgroundColor: "#ffffff"
    textColor: "{colors.texte}"
    rounded: "{rounded.sm}"
    padding: "8px 10px"
---

# Design System: COMPAS

## Overview

**Creative North Star: "Le Poste de Contrôle"**

COMPAS s'habille comme un poste de contrôle opérationnel, pas comme un produit à vendre : une sidebar sombre fait office de panneau de commande fixe, le contenu se lit en cartes plates et tableaux denses, et la couleur n'intervient que pour signaler un état ou une action — jamais pour décorer. Rien n'est ombré, rien ne flotte ; la surface entière est au même niveau, et seule une bordure fine ou un fond légèrement teinté distingue une carte du fond de page. La police mono n'apparaît que là où la traçabilité compte (références de pièces, citations `chemin:ligne` de l'assistant, bandeau de requêtes SQL), renforçant l'idée que chaque donnée affichée a une origine vérifiable.

L'anti-référence est explicite : ni gradient, ni glassmorphism, ni palette « SaaS marketing » saturée. COMPAS est un outil de travail interne pour des gestionnaires, pas une page de vente.

**Key Characteristics:**
- Sidebar sombre fixe (panneau de commande) contre un contenu clair et plat.
- Aucune ombre portée nulle part ; la profondeur vient de la couleur de fond et des bordures 1px.
- Couleur réservée au statut, à l'action et à la navigation active — jamais décorative.
- Police mono dédiée à tout ce qui doit rester traçable et vérifiable.
- Densité assumée (tableaux serrés, tuiles chiffrées) plutôt qu'espacement généreux.

## Colors

Palette quasi neutre (gris-bleu clairs pour la structure, texte proche du noir) ponctuée d'un unique bleu-acier sourd et de trois couleurs sémantiques strictement réservées au statut.

### Primary
- **Bleu Acier Sourd** (`#1f5f8b`): liens, navigation active (bordure gauche + fond survol), boutons primaires, cellule courante de pagination, texte des pastilles « déclaré » et pills de pistes de l'assistant. Son fond clair associé, **Bleu Acier Voilé** (`#eaf2f8`), habille les pastilles et les pills liées à cet accent.

### Secondary (statuts sémantiques)
- **Vert Réglé** (`#1a7f5a` / fond `#e6f4ee`): succès — messages de confirmation, statuts « actif », « clos ».
- **Ambre Vigilance** (`#a8630d` / fond `#fdf1e0`): alerte — statuts « en cours », « suspendu ». Réutilisé en accent chaud (`#e8a33d`) sur le bandeau de requêtes SQL quand le compteur dépasse le seuil.
- **Rouge Refus** (`#a32c2c` / fond `#fbeaea`): danger — messages d'erreur, bouton destructif, statut « refusé ».
- **Violet Expertise** (`#5b3d8f` / fond `#efeaf7`): unique couleur hors palette racine, réservée au seul statut « en expertise ».

### Neutral
- **Fond Page** (`#f6f7f9`): fond global de l'application.
- **Carte** (`#ffffff`): fond de toute carte, tuile, champ de formulaire.
- **Trait** (`#e2e5ea`) / **Trait Fort** (`#cbd1da`): bordures de cartes/lignes de tableau (trait), bordures d'en-tête/champs/boutons (trait fort).
- **Texte** (`#1b2027`): texte principal.
- **Doux** (`#5c6673`): texte secondaire — libellés, sous-titres, légendes de tuiles.
- **Pâle** (`#8b95a3`): texte tertiaire — fil d'ariane, aide, états vides.
- **Rail Fond** (`#141a21`) / **Rail Texte** (`#c3ccd7`) / **Rail Survol** (`#1c242e`): unique zone sombre de l'interface, réservée à la sidebar de navigation et au bandeau de bas de page (`.sonde`).
- **Rail Sous-titre** (`#6b7684`): variante encore plus sourde du texte de sidebar, réservée au sous-titre « Gestion sinistres » sous la marque.
- **Bordures de message/bouton** (`bouton-danger-bordure` `#e3bcbc`, `msg-ok-bordure` `#b9e0cf`, `msg-err-bordure` `#eec4c4`): variantes de bordure plus douces que les couleurs sémantiques pleines, utilisées uniquement en filet 1px sur le bouton destructif et les bandeaux de message.
- **Sonde Chaud** (`sonde-chaud` `#e8a33d`): seul indicateur d'alerte hors palette sémantique standard, réservé au compteur de requêtes SQL quand il dépasse le seuil — signal de diagnostic développeur, pas un statut métier.

### Named Rules
**La Règle de la Couleur Utile.** Une couleur n'apparaît que pour porter un sens (statut, action, sélection) ; en dehors du bleu acier et des trois teintes sémantiques, tout reste gris-neutre.

## Typography

**Display/Body Font:** system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", sans-serif (empilement système, pas de police chargée)
**Label/Mono Font:** SFMono-Regular, Consolas, "Liberation Mono", monospace

**Character:** Une seule famille système pour tout le texte lisible ; le mono n'intervient que comme marqueur de traçabilité, jamais pour du texte courant.

### Hierarchy
- **Display** (650, 24px, 1.2): titres de page (`h1`).
- **Headline** (620, 17px, 1.3): titres de section (`h2`).
- **Marque** (700, 19px, letter-spacing 0.5px): logo « COMPAS » en tête de sidebar — seul usage de ce poids/taille, jamais réutilisé ailleurs.
- **Body** (400, 15px, 1.55): texte courant.
- **Small** (400, 14px): registre le plus réutilisé de l'interface — liens de sidebar, chapeaux, titres de carte, cellules de tableau, états vides, bandeaux de message. Si un texte hésite entre Body et Label, c'est ce palier.
- **Compact** (400, 13.5px): boutons, pagination, lignes de sources de l'assistant — légèrement sous Small, jamais en dessous.
- **Label** (600, 12px, letter-spacing 0.4px, majuscules): en-têtes de tableau, titres de carte, groupes de navigation.
- **Micro-label** (400, 11px, letter-spacing 0.7px, majuscules): groupes de navigation de la sidebar (`.rail .groupe`) — un cran sous Label, réservé à la structure de navigation.
- **Stat** (640, 27px, 1.15, tabular-nums): chiffres des tuiles du tableau de bord — seul usage de cette taille, jamais pour un titre.
- **Mono** (400, 12.5px): références de pièces (`.ref`), citations `chemin:ligne` de l'assistant (`.sources .lieu`), bandeau de requêtes SQL.

### Named Rules
**La Règle du Mono Justifié.** La police mono ne sert jamais d'esthétique ; elle marque exclusivement une donnée vérifiable (chemin de fichier, ligne, référence de pièce).

## Layout

Grille CSS deux colonnes fixes : sidebar de 220px sticky pleine hauteur, contenu en `1fr` plafonné à `max-width: 1180px` avec un padding de 26px/30px. Les tuiles de statistiques et les champs de formulaire utilisent `grid-template-columns: repeat(auto-fit, minmax(...))` (158px pour les tuiles, 230px pour les champs) pour se réorganiser sans media query dédiée. Un seul point de rupture explicite à 820px : la sidebar passe de colonne sticky à bandeau horizontal en haut de page (flex-wrap), et les paddings du contenu se resserrent (18px/15px).

## Elevation & Depth

**La Règle du Plat par Défaut.** Aucune ombre portée (`box-shadow`) n'existe nulle part dans la feuille de style. La profondeur est entièrement conventionnelle : une carte se distingue du fond de page par sa couleur (blanc sur gris `#f6f7f9`) et une bordure 1px (`--trait`), jamais par un flou ou un décalage. C'est un choix délibéré, pas un oubli — cohérent avec un outil de travail où rien ne doit distraire de la donnée.

## Shapes

Coins légèrement arrondis et jamais pointus ni totalement carrés : 5px pour boutons/champs/bulles de pagination, 6px pour les bandeaux de message (`rounded.msg`), 7px pour cartes et tuiles. Les éléments de statut et de navigation contextuelle vont jusqu'au plein arrondi en pilule : 11px pour les pastilles de statut (`rounded.pill`), 13px pour les pills de relance de l'assistant (`rounded.pill-lg`, légèrement plus généreux car elles portent du texte cliquable, pas juste un mot d'état). La sidebar et le bandeau de requêtes SQL sont les seules zones à angle droit strict, renforçant leur rôle de structure fixe plutôt que de contenu.

## Components

### Buttons
- **Shape:** coins arrondis (5px).
- **Primary (`.b-p`):** fond bleu acier sourd, texte blanc, padding `8px 15px` ; survol `#194e73`.
- **Default (`.b`):** fond blanc, bordure trait-fort, texte principal ; survol fond gris très clair `#f2f4f7`.
- **Danger (`.b-d`):** fond blanc, bordure rouge atténuée `#e3bcbc`, texte rouge refus ; survol fond rouge clair.
- **Small (`.b-s`):** même famille, padding réduit `5px 10px`, texte 12.5px — actions secondaires en ligne dans les tableaux.

### Pastilles (badges de statut)
- **Style:** pilule pleine (`border-radius: 11px`), fond clair + texte de la couleur sémantique correspondante, jamais de bordure.
- **Mapping:** `déclaré`/`actif` → bleu acier ou vert selon le domaine ; `en_cours`/`suspendu` → ambre ; `expertise` → violet (seule teinte hors palette racine) ; `clos` → vert ; `refuse` → rouge ; `résilié`/`neutre` → gris neutre.

### Cards / Containers
- **Corner Style:** 7px.
- **Background:** blanc sur fond de page gris clair.
- **Shadow Strategy:** aucune — voir Élévation & Profondeur.
- **Border:** 1px `--trait`.
- **Internal Padding:** `16px 18px`.

### Inputs / Fields
- **Style:** bordure `--trait-fort`, fond blanc, coins 5px.
- **Focus:** anneau extérieur bleu clair (`outline: 2px solid --acc-clair`) + bordure qui passe à l'accent.
- **Error:** texte d'erreur rouge refus sous le champ (`.err`), pas de bordure rouge sur le champ lui-même.

### Navigation (sidebar)
- **Style:** fond `#141a21`, liens gris-bleu clair (`#c3ccd7`), padding `9px 20px`.
- **Survol:** fond `#1c242e`, texte blanc.
- **Actif:** même fond que survol + bordure gauche 3px bleu acier sourd — seul repère de position dans la sidebar.
- **Mobile (≤820px):** la sidebar devient un bandeau horizontal flex-wrap en haut de page, liens sans bordure gauche, coins légèrement arrondis à la place.

### Console COMPAS (signature)
La zone de réponse de l'assistant (`.reponse`) reprend la carte standard mais ajoute une bordure gauche 3px bleu acier sourd — signal visuel « ceci est une réponse, pas une donnée de portefeuille ». Chaque source citée affiche son chemin:ligne en mono bleu acier (`.sources .lieu`) suivi de l'extrait en texte doux ; les pistes de relance apparaissent en pills bleu acier voilé (`.pistes a`).

### Bandeau de requêtes SQL (signature, debug uniquement)
Élément fixe en bas à droite (`.sonde`), fond sombre identique à la sidebar, texte mono. Le compteur passe en ambre (`#e8a33d`) au-delà de 30 requêtes — seul indicateur d'alerte qui ne suit pas la palette sémantique standard (alerte/rouge), car c'est un outil de diagnostic pour développeurs, pas un statut métier.

## Do's and Don'ts

### Do:
- **Do** réserver la couleur au statut, à l'action ou à la navigation active (**La Règle de la Couleur Utile**).
- **Do** garder la profondeur strictement conventionnelle : fond + bordure 1px, jamais d'ombre (**La Règle du Plat par Défaut**).
- **Do** utiliser la police mono uniquement pour une donnée traçable/vérifiable (**La Règle du Mono Justifié**).
- **Do** garder les coins entre 5px (contrôles) et 7px (cartes), et réserver le plein arrondi en pilule aux badges/pills.

### Don't:
- **Don't** introduire de gradient, glassmorphism, ou palette « SaaS marketing » saturée — anti-référence confirmée pour un outil interne de gestion.
- **Don't** ajouter d'ombre portée à un composant existant pour lui donner du « relief » ; utiliser une bordure ou un changement de fond à la place.
- **Don't** inventer une nouvelle couleur sémantique en dehors de bleu acier / vert / ambre / rouge / violet (ce dernier strictement réservé au statut « expertise »).
