# SEO Block Module

Ce module Drupal affiche les 5 derniers articles avec leur score SEO calculé automatiquement dans une liste verticale épurée.

## Fonctionnalités

- Affiche les derniers articles publiés dans une liste verticale (nombre configurable)
- Calcule automatiquement un score SEO (0-100) basé sur :
  - Longueur du titre (optimal : 30-70 caractères)
  - Longueur du contenu (minimum 300 mots recommandé)
  - Présence d'un résumé
  - Présence de tags (si le champ existe)
  - Présence d'une image (si le champ existe)
- Design épuré avec contours simples et radius
- Effets de survol subtils
- Responsive design
- Support du mode sombre
- Cache intelligent (5 minutes)
- Configuration administrative pour personnaliser le nombre d'articles

## Installation

1. Placez le module dans `web/modules/custom/seo_block/`

2. Activez le module via l'interface d'administration ou Drush :
   ```bash
   drush en seo_block -y
   ```

3. Videz le cache :
   ```bash
   drush cr
   ```

## Configuration

Après l'installation, vous pouvez configurer le module via l'interface d'administration :

1. Allez dans **Configuration > Contenu > SEO Block Settings**
2. Entrez le nombre d'articles à afficher (entre 1 et 15)
3. Sauvegardez la configuration

Le bloc utilisera automatiquement cette configuration pour afficher le bon nombre d'articles.

## Utilisation

1. Allez dans Structure > Layout des blocs
2. Cliquez sur "Placer le bloc"
3. Recherchez "SEO Articles Block" dans la catégorie "SEO"
4. Placez le bloc dans la région de votre choix
5. Configurez les paramètres du bloc si nécessaire

## Structure des fichiers

```
seo_block/
├── seo_block.info.yml          # Informations du module
├── seo_block.module            # Hooks Drupal
├── seo_block.libraries.yml     # Bibliothèques CSS/JS
├── src/
│   └── Plugin/
│       └── Block/
│           └── SeoArticlesBlock.php  # Classe du bloc
├── templates/
│   └── seo-articles-block.html.twig  # Template Twig
└── css/
    ├── seo-block.scss          # Styles SCSS (source)
    └── seo-block.css           # Styles CSS (compilé)
```


## Dépendances

- Drupal 9/10/11
- Type de contenu "Article" avec champ "body"
