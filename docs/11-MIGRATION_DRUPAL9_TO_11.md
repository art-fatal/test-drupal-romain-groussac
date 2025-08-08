# Plan de migration Drupal 9 vers Drupal 11

## 1. Préparation

- **Sauvegarde complète**
  - Base de données :
    ```bash
    ddev export-db --file=backup-drupal9.sql
    ```
  - Fichiers (code, fichiers utilisateurs, config)en archivant le projet ou en utilisant git et creant un nouvelle branche:
    ```bash
    tar czf backup-files-drupal9.tar.gz web/ sites/ modules/ themes/
    ```
    ```bash
    git checkout -b drupal9
    ```
- Il faut s'assurer que les environnement remplis les conditions requises pour la migration.
- **Lister les modules et thèmes**
  - Vérifier la compatibilité de chaque module/thème avec Drupal 11.
  - Désactiver ou remplacer les modules non compatibles.
- **Mettre à jour les dépendances custom/contrib**
  - Vérifier les versions PHP, Composer, DDEV requises par Drupal 11.
  - Mettre à jour DDEV et Composer si besoin.

---

## 2. Migration (Il faut d'abord migrer vers la version 10 de Drupal, puis la version 11.)
  - Mettre à jour les modules et projets contribués

### Migration vers la version 10
Utiliser le module ```Upgrade Status```
Une fois que Upgrade Status indique que tous les modules/projets contribués sont compatibles avec Drupal 10, et si Upgrade Status indique également que tous vos modules et thèmes personnalisés sont également compatibles, désinstallez le module Upgrade Status, puis supprimez-le :
  ``` bash
  drush pm-uninstall upgrade_status -y
  composer remove drupal/upgrade_status --no-update
  ```
  Supprimer Drush pour éviter les problèmes potentiels liés à la mise à jour simultanée du noyau Drupal et de Drush

- Si il y a des modules et des thèmes personnalisés, remplacer le code qui était obsolète dans Drupal 9 et supprimé dans Drupal 10. Mettez à jour les modules et thèmes personnalisés avec Upgrade Status et Drupal Rector
- Mettre à jour le coeur vers la version 10

Ajoutez temporairement un accès en écriture aux fichiers et répertoires protégés :
  ```
  chmod 777 web/sites/default
  chmod 666 web/sites/default/*settings.php
  chmod 666 web/sites/default/*services.yml
  ```
  Mettez à jour les versions requises des paquets.
  ```
  composer require 'drupal/core-recommended:^10' 'drupal/core-composer-scaffold:^10' 'drupal/core-project-message:^10' --no-update
  ```
  Si vous avez installé Drush (vérifiez la version recommandée ) :
  ```
  composer require 'drush/drush:^12' --no-update
  ```
  Maintenant, testez la mise à jour du code lui-même avec l' option :--dry-run
  ```
  composer update --dry-run
  ```

Maintenant, effectuez réellement la mise à jour du code lui-même :
```
composer update
```
Une fois l'exécution terminée composer update sans erreur, vérifiez que vous pouvez également exécuter  composer install. Cela garantira que les autres développeurs du projet et/ou vos scripts de déploiement ne généreront pas d'erreurs lors de l'installation des nouvelles dépendances.
```
composer install
```

Une fois que vous avez pu exécuter composer install sans erreurs, exécutez toutes les mises à jour de la base de données en attente, au cas où une nouvelle version d'un module aurait besoin de mettre à jour la base de données, soit en visitant /update.php dans le navigateur, soit avec Drush :
```
drush updatedb
```
S'il y a des erreurs, vous devrez les résoudre et les réexécuter  drush updatedb jusqu'à ce que toutes les mises à jour s'exécutent correctement.

Une fois terminé, restaurez l'accès en lecture seule au répertoire sites/default :
```
chmod 755 web/sites/default
chmod 644 web/sites/default/*settings.php
chmod 644 web/sites/default/*services.yml
```

### Migration vers la version 11
> Suivre les memes explications que pour la version 10 mais en prenant en compte la version 11 de Drupal.

- **Vérifier le site**
  - Tester les fonctionnalités principales, modules custom, thèmes, etc.

---

## 3. Vérification post-migration

- Logs (Watchdog, PHP, DDEV)
- Tester les formulaires, affichages, permissions, workflows
- Configuration exportée/importée
- Performances et Intégrité des données

---

## 5. Liens utiles

- [Guide officiel migration Drupal 9 to 10](https://www.drupal.org/docs/upgrading-drupal/upgrading-from-drupal-8-or-later/how-to-upgrade-from-drupal-9-to-drupal-10)
- [Guide officiel migration Drupal 10 to 11](https://www.drupal.org/docs/upgrading-drupal/upgrading-from-drupal-8-or-later/how-to-upgrade-from-drupal-10-to-drupal-11)

---

> Ce plan est à adapter selon la complexité de votre projet, la présence de modules custom, et vos procédures internes de QA/validation.
