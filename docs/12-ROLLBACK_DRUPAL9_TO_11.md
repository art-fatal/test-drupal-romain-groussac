# Rollback en cas d’échec

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
