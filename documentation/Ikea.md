# Ikea (Trådfri)

## Compatibilité (non exhaustive)

* Ampoule : oui
* Ampoule couleur : oui
* Bouton : oui (partiel)

## Références

* [Liste des vidéos tutorielles Ikea sur YouTube](https://www.youtube.com/watch?v=0z1KikVkHsw&list=PLdOi3lRbWE5JW3sM8vlHZRt16MquGiBvz)

## Mode opératoire

### Ajout d'une ampoule

1. Allumer l'ampoule
1. Passer le plugin en mode *Inclusion*
1. Eteindre et allumer l'ampoule (par le bouton physique) 6 fois (donc 12 appuis)
1. L'ampoule doit clignoter pour signifier que l'inclusion a réussi
1. Dans Jeedom, l'ampoule doit apparaître avec un nom aléatoire. La renommer et choisir le parent si besoin
1. Sauvegarder l'ampoule
1. Rafraîchir l'ampoule
1. Patienter quelques temps que les données soient bien remontées dans Jeedom. Si les données mettent du temps, demander une synchronisation dans le plugin ZiGate

### Ajout d'un bouton

Attention, pour associer un bouton avec une ampoule sans rompre le lien entre l'ampoule et ZiGate, il faut d'abord associer le bouton avec Zigate.

1. Ouvrir le bouton pour accéder au bouton d'association
1. Passer le plugin en mode *Inclusion*
1. Appuyer 4 fois sur le bouton d'association du bouton
1. Le bouton doit apparaître dans Jeedom avec un nom aléatoire
1. Sauvegarder le bouton
1. Associer le bouton avec les ampoules une à une [voir la vidéo](https://www.youtube.com/watch?v=_XxYk6Twm34)
    1. Approcher le bouton à 2 cm de l'ampoule
    1. Appuyer pendant environ 10 secondes sur le bouton d'association, jusqu'à ce que l'ampoule varie sa luminosité