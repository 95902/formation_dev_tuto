# README

Service de notifications. Envoie les courriels transactionnels et les
notifications poussées. Ne gère pas les campagnes marketing.

## Lancer en local

    make up
    make worker

## Point d'attention

Le service est idempotent par `notification_id`. Un renvoi avec le même
identifiant ne produit pas de second envoi. Cette garantie porte sur 24 h.
