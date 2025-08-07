# Tableau de bord & Reporting - Documentation

## Accès au tableau de bord
1. Connectez-vous à l'administration Drupal
2. Allez dans **Contenu** > **Tableau de bord & Reporting**
3. Ou accédez directement via l'URL : `/admin/content/seo-dashboard`

## Fonctionnalités principales

### Indicateurs statistiques
- **Contenus publiés** : Nombre total de contenus publiés
- **Brouillons** : Nombre de contenus en brouillon
- **Score SEO moyen** : Moyenne des scores SEO de tous les contenus
- **Purge cache** : Action de purge du cache

### Liste des contenus
- Titre du contenu avec lien direct
- Type de contenu
- Auteur
- Date de création et dernière modification
- Statut de publication
- Score SEO avec barre de progression visuelle
- Actions rapides (voir, modifier)

### Fonctionnalités de recherche et filtrage
- Recherche par titre de contenu
- Filtrage par statut (publié/brouillon)
- Compteur de résultats

## Calcul du score SEO

Le score SEO est calculé sur 100 points selon les critères suivants :

- **Titre (20 points)** : Longueur optimale entre 30 et 60 caractères
- **Contenu (30 points)** : Présence d'un contenu de plus de 300 caractères
- **Alias URL (15 points)** : Présence d'un alias URL personnalisé
- **Meta description (20 points)** : Présence d'une meta description
- **Image (15 points)** : Présence d'une image dans le contenu

## Permissions requises

- **administer site configuration** : Accès complet au tableau de bord
- **access content** : Lecture des contenus
- **edit any content** : Modification des contenus
