# test-drupal-romain-groussac

Projet Drupal 9 (version 9.5.11) initialisé avec `composer create-project drupal/recommended-project:^9.5` et configuré pour  DDEV.

## Prérequis

- [Docker](https://www.docker.com/) (requis par DDEV)
- [DDEV](https://ddev.readthedocs.io/en/stable/) (outil de développement local)
- [Composer](https://getcomposer.org/) (gestionnaire de dépendances PHP)
- Système compatible (Linux, macOS, Windows)

## Configuration DDEV

- PHP : 7.3
- Serveur web : nginx-fpm
- Base de données : MariaDB 10.11

## Installation
#### Lien utilisé comme reference pour l'installatio :
  - https://www.drupal.org/docs/getting-started/installing-drupal/install-drupal-using-ddev-for-local-development
  - https://drupalize.me/tutorial/install-drupal-locally-ddev

1. **Cloner le dépôt**
   ```bash
   git clone https://github.com/art-fatal/test-drupal-romain-groussac.git test-drupal-romain-groussac
   cd test-drupal-romain-groussac
   ```

2. **Démarrer l’environnement DDEV**
   ```bash
   ddev start
   ```

3. **Installer les dépendances PHP**
   ```bash
   ddev composer install
   ```

4. **Installer Drupal**
  - Accéder à l’URL locale :
   ```bash
   ddev launch
   ```

  - Installation Drupal en utilisant Drush :
    ```bash
    ddev drush site:install --account-name=admin --account-pass=admin -y
    ```

## Accès au site
- Frontend : aller sur le lien generer par ```ddev start```

## Commandes utiles
- Redémarrer DDEV :
  ```bash
  ddev restart
  ```
- Mettre à jour les dépendances :
  ```bash
  ddev composer update
  ```
- Pour vider le cache :
  ```bash
  ddev drush cr
  ```


## Migration vers Drupal 11

[Plan de migration Drupal 9 → 11 et rollback](./MIGRATION_DRUPAL9_TO_11.md)

