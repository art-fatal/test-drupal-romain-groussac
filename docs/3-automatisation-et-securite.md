# Partie 3 – Automatisation & Sécurité
1. Script ou cron pour sauvegarder la base et purger le cache regulierement

2. headers de sécurité ou configuration serveur nécessaires pour durcir l’instance Drupal.
   1. Headers de Sécurité Recommandés
    - **Content-Security-Policy**: contrer les attaques XSS
    - **Strict-Transport-Security**: Forcer les connexions en HTTPS
    - **X-Frame-Options**: Protege contre le clickjacking
    - **X-Content-Type-Options**: Empeche le MIME sniffing
    - **Referrer-Policy**: Contrôle les informations envoyées via l’en-tête
    - **Permissions-Policy**: Désactive certaines fonctionnalités web (géolocalisation, caméra).
  2. Configuration serveur recommandée
     - Désactiver l'affichage des erreurs PHP
     - Verifier les permissions sur les fichiers et repertoires
     - Mettre à jour Drupal/core/modules, Appliquer les patches de sécurité dès leur sortie.


------

### [Partie 1 – Modélisation & Migration](./1-modelage-et-migration.md)
### [Partie 2 – Développement d’un module custom](./2-developpement-du-module-custom.md)
### [Partie 4 – Tableau de bord & Reporting](./4-dashboard-et-reporting.md)
