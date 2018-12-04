# Magento 2 Module for Cocote.com website.

This module generates feed and communicates with cocote.com website to let it aggregate your products data.

Installation
To install the module just copy content of the module to app/code folder of your project on server.
After that clear cache and perform setup:upgrade.

There is a new cache type created, Cocote Cache  - you should enable it in admin after you’ve installed module.

First you need to configure the module in store->configuration->Cocote and from there you can also generate feed .xml file.
File will be refreshed each day at 3.00 A.M. by cron tasks.



# Plugin Cocote pour Magento 2

Ce module communique avec Cocote.com et genere un flux xml de vos offres produits.

Pour installer ce module:

1) Transfert et copie des fichiers

Telecharger ce module (via le boutton ci-dessus 'clone or dowload') sur votre serveur et copier son contenu dans la répertoire app/code de votre site magento 2.

2) Lignes de commandes

Ensuite, lancer en ligne de commande:

#clean cache

php bin/magento c:c

#upgrade

php bin/magento setup:upgrade

3) Activer le cache Cocote

Un nouveau type de cache est disponible 'Cocote Cache' que vous pouvez activer depuis l'interface d'aministration (System > Cache Management).

Le module cocote est ensuite accessible depuis store->configuration (l’icône doit apparaître sur la colonne de gauche)
