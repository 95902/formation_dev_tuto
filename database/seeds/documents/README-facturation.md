# README

Service de facturation. Calcule les échéances, émet les avoirs et
transmet les écritures au système comptable chaque nuit à 2 h 30.

## Lancer en local

    make up
    make migrate
    make seed

## Point d'attention

Les montants sont stockés en centimes, en entier. Ne jamais introduire
de flottant dans ce service : les écarts d'arrondi remontent en comptabilité.
