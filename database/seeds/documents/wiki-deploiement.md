# Procédure de déploiement

Le déploiement en production se fait uniquement depuis la branche `main`,
après validation de l'intégration continue.

## Étapes

1. Vérifier que la CI est au vert sur `main`.
2. Créer un tag `vX.Y.Z` signé.
3. Lancer `./deploy.sh production` depuis la machine de build.
4. Surveiller les journaux pendant dix minutes.

## Fenêtre de déploiement

Les déploiements sont autorisés du lundi au jeudi, entre 9 h et 16 h.
Aucun déploiement le vendredi : personne n'est d'astreinte le week-end.

## Retour arrière

En cas d'incident, `./deploy.sh rollback` restaure la version précédente.
La bascule prend environ quatre minutes. Prévenir le canal #incidents avant.
