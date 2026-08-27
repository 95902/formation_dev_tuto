# Conventions API

Toutes les réponses de l'API sont en JSON, encodées en UTF-8.

## Nommage

Les champs sont en `snake_case`. Les identifiants exposés sont des UUID v7,
jamais les clés primaires internes.

## Pagination

La pagination est par curseur. Le client renvoie le `next_cursor` reçu.
La taille de page par défaut est 25, le maximum est 100.

## Erreurs

Le corps d'erreur contient toujours `code`, `message` et `request_id`.
Le `request_id` est le seul élément à communiquer au support.
