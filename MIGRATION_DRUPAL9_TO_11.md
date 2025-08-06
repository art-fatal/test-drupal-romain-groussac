# Plan de migration Drupal 9 vers Drupal 11

## Objectif
Migrer le projet de Drupal 9 (9.5.11) vers Drupal 11 tout en assurant la continuité de service et la possibilité de rollback en cas d’échec.

---

## 1. Préparation

- **Sauvegarde complète**
  - Base de données :
    ```bash
    ddev export-db --file=backup-drupal9.sql
    ```
  - Fichiers (code, fichiers utilisateurs, config) :
    ```bash
    tar czf backup-files-drupal9.tar.gz web/ sites/ modules/ themes/
    ```
- **Lister les modules et thèmes**
  - Vérifier la compatibilité de chaque module/thème avec Drupal 11.
  - Désactiver ou remplacer les modules non compatibles.
- **Mettre à jour les dépendances custom/contrib**
  - Vérifier les versions PHP, Composer, DDEV requises par Drupal 11.
  - Mettre à jour DDEV et Composer si besoin.

---

## 2. Migration

- **Mettre à jour le core et les dépendances**
  - Modifier `composer.json` :
    - Remplacer `drupal/core-recommended`, `drupal/core-composer-scaffold`, `drupal/core-project-message` par la version ^11.
  - Mettre à jour les modules contribs compatibles.
  - Lancer :
    ```bash
    ddev composer require "drupal/core-recommended:^11" "drupal/core-composer-scaffold:^11" "drupal/core-project-message:^11" --update-with-dependencies
    ddev composer update
    ```
- **Mettre à jour la base de données**
  - Appliquer les updates :
    ```bash
    ddev drush updb -y
    ```
- **Vider les caches**
    ```bash
    ddev drush cr
    ```
- **Vérifier le site**
  - Tester les fonctionnalités principales, modules custom, thèmes, etc.

---

## 3. Vérification post-migration

- Vérifier les logs (Watchdog, PHP, DDEV)
- Tester les formulaires, affichages, permissions, workflows
- Vérifier la configuration exportée/importée
- Vérifier les performances et l’intégrité des données

---

## 4. Rollback en cas d’échec

- **Restaurer la base de données**
  ```bash
  ddev import-db --src=backup-drupal9.sql
  ```
- **Restaurer les fichiers**
  ```bash
  tar xzf backup-files-drupal9.tar.gz
  ```
- **Revenir à la branche git Drupal 9**
  ```bash
  git checkout <branche-drupal9>
  ddev composer install
  ddev drush cr
  ```
- **Vérifier le retour à l’état initial**

---

## 5. Liens utiles

- [Guide officiel migration Drupal](https://www.drupal.org/docs/upgrading-drupal/upgrading-from-drupal-9-or-10-to-drupal-11)
- [Compatibilité modules contrib](https://www.drupal.org/project/project_module)
- [DDEV documentation](https://ddev.readthedocs.io/en/stable/)

---

> Ce plan est à adapter selon la complexité de votre projet, la présence de modules custom, et vos procédures internes de QA/validation.