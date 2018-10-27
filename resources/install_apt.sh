#!/bin/bash
PROGRESS_FILE=/tmp/jeedom/zigate/dependance
if [ ! -z $1 ]; then
	PROGRESS_FILE=$1
fi
touch ${PROGRESS_FILE}
echo 0 > ${PROGRESS_FILE}
echo "Installation des dépendances"
sudo apt-get update
echo 10 > ${PROGRESS_FILE}
sudo apt-get install -y python3
echo 20 > ${PROGRESS_FILE}
sudo apt-get remove -y python3-serial
echo 30 > ${PROGRESS_FILE}
sudo apt-get install -y python3-pip
echo 40 > ${PROGRESS_FILE}
sudo apt-get install -y python3-requests
echo 50 > ${PROGRESS_FILE}
sudo apt-get install -y python3-setuptools
echo 60 > ${PROGRESS_FILE}
sudo pip3 install pip --upgrade
echo 80 > ${PROGRESS_FILE}
BASEDIR=$(dirname "$0")
zigate_version=$(head -1 $BASEDIR/zigate_version.txt)
sudo pip3 install zigate==$zigate_version.* --upgrade
echo 100 > ${PROGRESS_FILE}
echo "Installation des dépendances terminé !"
rm ${PROGRESS_FILE}
