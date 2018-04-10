Plugin ZiGate pour Jeedom
=========================

Documentation et changlog du plugin ZiGate pour Jeedom


# ChangeLog

* BETA :
    - Commande des prises Legrand (en attendant la mise à jour il suffit de relancer l'installation des dépendances)
    - Commande des ampoules Sunricher DIM Lighting
    - Information de présence sous forme binaire et non numérique
    - Correction des informations onoff manquantes pour les interrupteurs multiples
    - Correction d'un bug concernant les jeedom utilisant HTTPS
    - Gestion du cluster 0x0500 IAS Zone (capteur d'inondation, fumée, présence, etc)
    - Gestion des couleurs pour les ampoules

* 2018-03-21 :
    - Ajout commande Touchlink
    - Ajout commande Network Scan
    - Ajout commande Identifier
    - Ajout de vignettes
    - Gestion des capteurs de luminosité
    - Ebauche information couleur des ampoules

* 2018-03-17 :
    - Correction d'un bug de sélection automatique du port usb
    - Correction d'un bug empéchant parfois les valeurs de remonter

* 2018-03-15 :
    - Remontée état des ampoules IKEA
    - Retour à 0 des capteurs de présence
    - Ajout de vignettes

* 2018-03-08 :
    - Ajout de vignettes
    - Correction d'un bug dans la dépendance du démon provoquant son crash

* 2018-03-07 :
    - Ajout de vignettes
    - Ajout inversion pour les infos binaires
    - Ajout min/max pour les infos numériques
    - Corrections de bugs

* 2018-03-02 :
    - Petite mise à jour pour corriger un bug empéchant l'inclusion (erreur 128)

* 2018-02-28 :
    - Correction de bugs
    - Ajout des vignettes (pour les équipements XIAOMI, les autres arriveront prochainement)
    - Ajout d'un bouton pour rafraichir les informations d'un équipement
    - Amélioration de la gestion du niveau de batterie

* 2018-02-24 :
    - Correction de bugs
    - Support de la ZiGate Wifi

* 2018-02-22 :
    - Correction de bugs
    - Ajout niveau de batterie

* 2018-02-19 :
    - Ajout du support des commandes on/off et level control
    - Attention il est nécessaire de refaire l'appairage des équipements pour faire apparaitre les nouvelles commandes
    - il est nécessaire de mettre à jour le firmware de la clé zigate à la version **3.0d**

* 2018-02-08 : Version initiale
    - Support en lecture des équipements, testé avec des capteurs Xiaomi
