# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Gestionnaires internes (sinistres/contrats) d'un courtier en assurance fictif, au quotidien, pour :
- consulter et gérer le portefeuille (assurés → contrats → garanties / sinistres → pièces) ;
- poser des questions à l'assistant « Compas » pendant leur travail (procédures, garanties, barèmes).

## Product Purpose

COMPAS gère le portefeuille d'un courtier en assurance (assurés, contrats, garanties, sinistres, pièces) et répond aux questions des gestionnaires en s'appuyant sur la documentation interne (conditions de garantie, barème de vétusté, procédure sinistre, wiki technique). Succès = un gestionnaire retrouve/modifie une donnée de portefeuille ou obtient une réponse fiable et sourcée sans quitter l'outil.

## Positioning

Back-office classique de gestion de portefeuille combiné à un assistant de questions-réponses documentaire qui cite précisément ses sources (`chemin:ligne`) — fiabilité et traçabilité de la réponse plutôt qu'une IA générative opaque. La recherche est par mots-clés (pas d'embeddings) : le classement doit rester déterministe et reproductible.

## Operating Context

- Deux moitiés largement indépendantes : gestion de portefeuille (CRUD Eloquent classique) et pipeline Q&A (`Answerer` → `DocumentSearch` → `ContextBuilder` → `LlmClient` (stub déterministe) → `Citation`).
- Un bandeau de compteur de requêtes SQL s'affiche sur chaque page (debug) pour faire ressortir les problèmes N+1 — élément d'atelier, pas un widget produit à retirer.
- Cinq formatters de réponse quasi identiques (CLI/HTML/JSON/Markdown/Slack) rendent le même objet `Answer` — duplication connue et volontaire.
- `StatsController` expose volontairement des conventions API différentes du reste de l'appli (nommage, clé primaire, pagination) — élément d'exercice, pas une incohérence à corriger d'office.

## Capabilities and Constraints

- Stack : PHP 8.3+, Laravel 13, Blade + Vite/Tailwind 4, SQLite par défaut.
- Calculs monétaires en centimes entiers (`app/Support/Euro.php`) — jamais de flottant.
- `HttpLlmClient` existe comme support d'atelier mais n'est jamais l'implémentation branchée.

## Brand Commitments

Aucune identité de marque réelle : « COMPAS » et le courtier sont fictifs, propres à ce bac à sable.

## Evidence on Hand

Pas de contenu marketing, témoignages, chiffres ou logo réels à exploiter — tout contenu de preuve doit rester cohérent avec les données de seed (`database/seeds/documents/`) ou être explicitement marqué fictif.

## Product Principles

- Fiabilité et traçabilité des réponses (citations `chemin:ligne`) priment sur l'esthétique de l'assistant.
- Le design sert des gestionnaires qui accomplissent une tâche (mode Operate) : scanabilité et cohérence priment sur l'expression.
- Ne jamais « nettoyer » silencieusement la dette ou les incohérences volontaires (bandeau de requêtes, formatters dupliqués, conventions `StatsController`) — elles sont le support des ateliers T-01..T-11 listés dans `BACKLOG.md`.
- Toute demande de design reste scopée à la surface/au ticket demandé, jamais un refactor global non sollicité.

## Accessibility & Inclusion

Aucune exigence spécifique confirmée à ce jour.
