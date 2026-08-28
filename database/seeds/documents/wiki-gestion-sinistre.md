# Gestion d'un sinistre

Ce guide décrit le circuit d'un dossier, de la déclaration à la clôture.

## Délais de déclaration

L'assuré dispose de **cinq jours ouvrés** à compter de la survenance pour
déclarer un sinistre. Deux exceptions :

- **Vol** : deux jours ouvrés, dépôt de plainte obligatoire.
- **Catastrophe naturelle** : dix jours après publication de l'arrêté.

Passé le délai, le dossier reste recevable mais la déchéance de garantie
peut être opposée si le retard a causé un préjudice à l'assureur.

## Statuts d'un dossier

Un dossier passe par les statuts suivants, et par aucun autre :

- `declare` — reçu, non encore instruit.
- `en_cours` — instruction ouverte, pièces en cours de collecte.
- `expertise` — expert mandaté, rapport attendu.
- `clos` — indemnité réglée ou dossier sans suite.
- `refuse` — garantie non mobilisable, refus motivé notifié.

Le statut de clôture s'écrit `clos`. Aucun autre libellé n'est valide en base.

## Pièces attendues

Le constat amiable et la facture de réparation sont exigés dans tous les cas.
Le procès-verbal de police s'ajoute pour un vol, le rapport d'expertise dès
que le montant estimé dépasse 1 500 €.
