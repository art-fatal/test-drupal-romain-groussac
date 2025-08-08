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
#### Lien utilisé comme reference pour l'installation :
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
- Importation bdd:
  ```bash
  ddev import-db --file=backup.sql.gz
  ```

- Importation des configurations :
  ```bash
  ddev drush config:import --source=config/sync
  ```
- Cache rebuild:
  ```bash
  ddev drush cr
  ```

## Accès au site
- Frontend : aller sur le lien generer par
  ```bash
  ddev launch
  ```
- username: ```admin```
- password: ```admin```

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
