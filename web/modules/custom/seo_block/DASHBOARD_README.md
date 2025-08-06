# Tableau de bord SEO - Documentation

## Vue d'ensemble

Le tableau de bord SEO est une interface moderne et intuitive pour gérer et analyser le contenu de votre site Drupal. Il fournit des indicateurs clés, une liste détaillée des contenus et des outils d'action pour optimiser votre SEO.

## Fonctionnalités principales

### 📊 Indicateurs statistiques
- **Contenus publiés** : Nombre total de contenus publiés
- **Brouillons** : Nombre de contenus en brouillon
- **Score SEO moyen** : Moyenne des scores SEO de tous les contenus
- **Dernière purge cache** : Horodatage de la dernière purge du cache

### 📋 Liste des contenus
- Titre du contenu avec lien direct
- Type de contenu
- Auteur
- Date de création et dernière modification
- Statut de publication
- Score SEO avec barre de progression visuelle
- Actions rapides (voir, modifier)

### 🔍 Fonctionnalités de recherche et filtrage
- Recherche par titre de contenu
- Filtrage par statut (publié/brouillon)
- Compteur de résultats en temps réel

### ⚡ Actions rapides
- **Purger le cache** : Vide tous les caches du site
- **Vérification SEO** : Lance une analyse SEO des contenus récents

## Accès au tableau de bord

1. Connectez-vous à l'administration Drupal
2. Allez dans **Contenu** > **Tableau de bord SEO**
3. Ou accédez directement via l'URL : `/admin/content/seo-dashboard`

## Calcul du score SEO

Le score SEO est calculé sur 100 points selon les critères suivants :

- **Titre (20 points)** : Longueur optimale entre 30 et 60 caractères
- **Contenu (30 points)** : Présence d'un contenu de plus de 300 caractères
- **Alias URL (15 points)** : Présence d'un alias URL personnalisé
- **Meta description (20 points)** : Présence d'une meta description
- **Image (15 points)** : Présence d'une image dans le contenu

## Interface utilisateur

### Design responsive
- Interface adaptée aux écrans desktop, tablette et mobile
- Navigation intuitive avec icônes FontAwesome
- Animations fluides et feedback visuel

### Couleurs et indicateurs
- **Vert** : Scores SEO élevés (70-100)
- **Jaune** : Scores SEO moyens (40-69)
- **Rouge** : Scores SEO faibles (0-39)

### Notifications
- Messages de succès/erreur en temps réel
- Auto-disparition après 5 secondes
- Positionnement en haut à droite

## API et endpoints

### Endpoints disponibles
- `GET /admin/content/seo-dashboard` : Affichage du tableau de bord
- `POST /admin/content/seo-dashboard/clear-cache` : Purge du cache
- `POST /admin/content/seo-dashboard/seo-check` : Vérification SEO

### Réponses JSON
```json
{
  "success": true,
  "message": "Action réussie",
  "timestamp": "01/01/2024 12:00:00",
  "results": [...]
}
```

## Personnalisation

### Styles CSS
Les styles sont définis dans `css/seo-dashboard.css` et peuvent être personnalisés :
- Couleurs des thèmes
- Tailles et espacements
- Animations et transitions

### JavaScript
Les interactions sont gérées dans `js/seo-dashboard.js` :
- Gestion des événements
- Appels AJAX
- Manipulation du DOM

## Permissions requises

- **administer site configuration** : Accès complet au tableau de bord
- **access content** : Lecture des contenus
- **edit any content** : Modification des contenus

## Dépendances

- Drupal Core 9+
- jQuery (inclus dans Drupal)
- FontAwesome (pour les icônes)

## Support et maintenance

### Vérifications régulières
- Surveiller les scores SEO moyens
- Vérifier la fréquence des purges cache
- Analyser les contenus à faible score

### Optimisations recommandées
- Ajouter des meta descriptions manquantes
- Optimiser les titres trop courts/longs
- Inclure des images dans les contenus
- Personnaliser les alias URL

## Dépannage

### Problèmes courants
1. **Scores SEO manquants** : Vérifier les champs requis
2. **Erreurs de cache** : Vérifier les permissions
3. **Problèmes d'affichage** : Vider le cache du navigateur

### Logs et debugging
- Vérifier les logs Drupal pour les erreurs
- Utiliser les outils de développement du navigateur
- Tester les endpoints API directement

---

*Développé pour Drupal 9+ avec une approche moderne et responsive.* 