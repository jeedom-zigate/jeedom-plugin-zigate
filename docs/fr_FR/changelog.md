# Changelog

## v1.5.2 (2019-12-18)

* Attention à partir de la version 1.6, plus de support pour Python 3.4 (3.5 minimum)

## v1.5.1 (2019-12-16)

* Correction installation des dépendances

## v1.5.0 (2019-11-27)

* Support Firmware 3.1a et 3.1b
* Ajout carte du réseau
* Support Konke
* Meilleur support Profalux
* Support Sirène
* Diverses corrections
* Controle de la LED
* Support Jeedom v4 minimum

## v1.4.*

* Les versions v1.4.* seront les dernières compatibles Jeedom V3, il n'y aura plus d'évolution du plugin mais
 uniquement des corrections mineures

## v1.2.0 (2019-02-11)

* Enhancement #83: Support du firmware 3.0e.
* Enhancement #71 Utiliser l'adresse IEEE comme LogicalId.
* Enhancement #9: Ajout de la console (Merci @Deepcore93).
* Enhancement #115 & #118: Documentation plus à jour.
* Enhancement #90: Possibilité de changer la température du blanc d'une ampoule Philips Hue.
* Enhancement #105: Pas de contrôle de la temperature des ampoules IKEA Tradfri.
* Enhancement 71: Migration de CodeClimate vers SonarCloud.io.
* Bugfix #121: Interrupteur simple Aqara (Xiaomi) lumi.remote.b186acn01.
* Bugfix #95: Problème avec bouton xiaomi WXKG02LM lumi.remote.b286acn01.
* Bugfix #84: bouton xiaomi aqara v2 mal reconnu lumi.remote.b1acn01.
* Bugfix #53: Le demarrage du plugin lance les scenarios sur changement de valeur.
* Bugfix #46: Date de dernier message pas à jour ?
* Bugfix #108: Mijia Door Sensor no battery info.
* Bugfix #101: Hue : Pas de mise à jour sans resync.
* Bugfix #113: Volet Profalux Commande info.
* Bugfix #96: Sonde température ronde mal reconnue lumi.sensor_ht.

## v1.1.7 (2018-12-31)

* Bugfix #98: Problème lors de l'installation des dépendances sur debian Jessie

## v1.1.6 (2018-12-24)

* Now requires Python lib in v0.24.2

## v1.1.5 (2018-12-22)

* Fix pip2 VS pip3 issue.

## v1.1.4 (2018-11-29)

* Now requires Python lib in v0.22.0

## v1.1.3 (2018-11-17)

* Enhancement #47: Calcul du % de batterie.
* Enhancement #65: Installation des dépendances ne fonctionne pas.

## v1.1.2 (2018-11-06)

* Enhancement #49: Ajout du support du "Xiaomi Aqara vibration sensor".
* Enhancement #66: Update des dépendances lors de l'update du plugin.
* Enhancement #75 & #78: Centraliser les versions dans la page configuration.
* Enhancement #76: Griser les équipements désactivés dans la page health (merci @Deepcore93).
* Enhancement #77: Filtre sur la page listant les équipements.

## v1.1.1 (2018-10-20)

* Enhancement #50: Amélioration de la doc: liste les équipements supportés (merci @ioull).
* Enhancement #57: Travis: mise en place de Travis pour le Python.
* Enhancement #58: Améliorer la documentation du code.
* Enhancement #69: Ajout de la version du plugin sur la page de configuration.

## v1.1.0 (2018-09-09)

* Enhancement #41: Nouveau logo.
* Enhancement #59: Documentation contributeurs.
* Enhancement #42: Mise en place d'Intégration Continue et de QA.
* Enhancement #43: Nouvelle URLs pour le projet.
* Enhancement #33 / #51 : Page santé (merci @Deepcore93 & @ioull).
* Enhancement #48: Mettre la doc dans les standards Jeedom.
* Enhancement #40: Rajouter un champ pluginVersion.
* Enhancement #32: Ajouter le nom de l'équipement à rafraîchir (merci @Deepcore93).

## v1.0.18 (2018-07-20)

* Ajout de vignettes.
* Amélioration de la gestion du cube Xiaomi.
* Diverses amélioration/correction de bugs.

## v1.0.17 (2018-06-26)

* Ajout de vignettes.
* Ajout du type de le nom par défaut des équipements.
* Gestion des zone IAS (Détecteur d'inondation, de fumée, d'intrusion, etc).
* Ajout de la commande refresh.
* Correction bug multiclick pas à 0 au démarrage.
* Amélioration de la gestion du cube XIAOMI.
* Modification de l'information current_level varie maintenant de 0-100 au lieu de 0-254.

## v1.0.16 (2018-06-05)

* Publication des sources sur github.
* Ajout de la commande Toggle.

## v1.0.15 ( 2018-05-14)

* Ajout de vignettes.

## v1.0.14 (2018-04-20)

* Correction installation des dépendances.

## v1.0.13 (2018-04-18)

* Amélioration des équipements HUE.
* Corrections de bug.
* Ajout de vignettes.

## v1.0.12 (2018-04-16)

* Correction bug gestion des couleurs.

## v1.0.11 (2018-04-11)

* Commande des prises Legrand.
* Commande des ampoules Sunricher DIM Lighting.
* Information de présence sous forme binaire et non numérique.
* Correction des informations onoff manquantes pour les interrupteurs multiples.
* Correction d'un bug concernant les jeedom utilisant HTTPS.
* Gestion du cluster 0x0500 IAS Zone (capteur d'inondation, fumée, présence, etc).
* Gestion des couleurs pour les ampoules.
* Ajout de vignettes.
* Fonction de reconnexion automatique.

## v1.0.10 (2018-03-21)

* Ajout commande Touchlink.
* Ajout commande Network Scan.
* Ajout commande Identifier.
* Ajout de vignettes.
* Gestion des capteurs de luminosité.
* Ebauche information couleur des ampoules.

## v1.0.9 (2018-03-17)

* Correction d'un bug de sélection automatique du port usb.
* Correction d'un bug empéchant parfois les valeurs de remonter.

## v1.0.8 (2018-03-15)

* Remontée état des ampoules IKEA.
* Retour à 0 des capteurs de présence.
* Ajout de vignettes.

## v1.0.7(2018-03-08)

* Ajout de vignettes.
* Correction d'un bug dans la dépendance du démon provoquant son crash.

## v1.0.6 (2018-03-07)

* Ajout de vignettes.
* Ajout inversion pour les infos binaires.
* Ajout min/max pour les infos numériques.
* Corrections de bugs.

## v1.0.5 (2018-03-02)

* Petite mise à jour pour corriger un bug empéchant l'inclusion (erreur 128).

## v1.0.4 (2018-02-28)

* Correction de bugs.
* Ajout des vignettes (pour les équipements XIAOMI, les autres arriveront prochainement).
* Ajout d'un bouton pour rafraichir les informations d'un équipement.
* Amélioration de la gestion du niveau de batterie.

## v1.0.3 (2018-02-24)

* Correction de bugs.
* Support de la ZiGate Wifi.

## v1.0.2 (2018-02-22)

* Correction de bugs.
* Ajout niveau de batterie.

## v1.0.1 (2018-02-19)

* Ajout du support des commandes on/off et level control.
* Attention il est nécessaire de refaire l'appairage des équipements pour faire apparaitre les nouvelles commandes.
* il est nécessaire de mettre à jour le firmware de la clé zigate à la version **3.0d**.

## v 1.0.0 (2018-02-08) : Version initiale

* Support en lecture des équipements, testé avec des capteurs Xiaomi.
