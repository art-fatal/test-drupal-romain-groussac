# Choix techniques – Test Développeur Drupal

## 1. Module `seo_block`

### Objectif :
Créer un bloc personnalisé affichant les derniers articles avec un champ personnalisé `score_seo` et un parametrage basique dans l'admin (nb de dernier article a afficher).

### Choix techniques :
- Module custom léger.
- Utilisation de Twig pour garantir une séparation claire logique / présentation.
- Paramétrage admin intégré dans la configuration du bloc.
- Dépendances à `core/drupal` pour le rendu standard Drupal.

### Avantages :
- Autonome, réutilisable, simple à maintenir.

---

## 2. Module `dashboard`

### Objectif :
Créer une page accessible listant les contenus avec des indicateurs, et ajout d'actions manuelles pour purger le cache.

### Choix techniques :
- Module custom.
- Ffichier `seo-dashboard.js` pour gérer les interactions manuelles (boutons de purge), pagination, et filtrage.
- **CSS** : Fichier `seo-dashboard.css` pour la mise en forme claire de la page.
- **Dépendances** :
  - `core/jquery`, `core/jquery.once` : pour la gestion des événements JS.
  - `core/drupal` : pour les appels Ajax.
  - `fontawesome/fontawesome` : pour avoir une interface ergonomique et améliorer l’UX avec des icônes.

### Avantages :
- Interface claire pour l’utilisateur métier.
- Extensible facilement avec d’autres fonctionnalités.
