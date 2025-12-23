# 🚀 GitHub Profile SVG Generator

Un générateur de badges SVG élégant et personnalisable pour vos profils GitHub. Créez des badges visuels avec vos statistiques GitHub et intégrez-les facilement dans vos README.md.

![Badge Example](https://projets.agence-prestige-numerique.fr/github_stats/api/generate.php?username=Axxel-L)
*Exemple de badge généré pour l'utilisateur GitHub "Axxel-L"*

## ✨ Fonctionnalités

### 📊 Statistiques complètes
- **Repositories** : Nombre total de dépôts publics
- **Stars** : Total des étoiles reçues
- **Forks** : Total des forks
- **Followers/Following** : Suiveurs et comptes suivis
- **Top languages** : Les 5 langages de programmation les plus utilisés

### 🎨 Design moderne
- Interface utilisateur élégante avec effets glassmorphism
- Thème sombre professionnel
- SVG responsive et optimisé
- Avatars GitHub intégrés en Base64 (compatible GitHub)

### 🔧 Intégration facile
- **Markdown** : Pour vos README.md GitHub
- **HTML** : Pour vos sites web et portfolios
- **URL directe** : Pour une utilisation flexible

## 🚀 Comment l'utiliser ?

### Méthode 1 : La plus simple (URL directe)
Utilisez directement notre API en ligne :
```markdown
![GitHub Stats](https://projets.agence-prestige-numerique.fr/github_stats/api/generate.php?username=VOTRE_NOM_GITHUB)
```

### Méthode 2 : Interface web
Rendez-vous sur notre site web et utilisez l'interface intuitive :
**[https://projets.agence-prestige-numerique.fr/github_stats/](https://projets.agrestige-numerique.fr/github_stats/)**

1. Entrez votre nom d'utilisateur GitHub
2. Cliquez sur "Générer le badge SVG"
3. Copiez le code dans le format de votre choix

### Méthode 3 : Auto-hébergement
Clonez le dépôt et hébergez votre propre instance :

```bash
# Clonez le repository
git clone https://github.com/Axxel-L/github-profile-svg.git

# Configurez les URLs dans les fichiers
# - generate.php
# - stats.php
# - script.js

# Déployez sur votre serveur PHP
```

## 🛠️ Technologies utilisées

### Backend
- **PHP 7.4+** : API de génération SVG
- **GitHub API v3** : Récupération des données utilisateur
- **JSON** : Stockage des statistiques

### Frontend
- **HTML5 / CSS3** : Structure et style
- **JavaScript (ES6+)** : Interactions dynamiques
- **Tailwind CSS** : Framework CSS utilitaire
- **SVG** : Format d'image vectorielle

### Infrastructure
- **API REST** : Architecture modulaire
- **CORS activé** : Accès cross-origin autorisé
- **Cache intelligent** : Performance optimisée (1h)

## 📦 Installation pour auto-hébergement

### Prérequis
- Serveur web avec PHP 7.4+
- Accès à Internet (pour GitHub API)

### Étapes d'installation

1. **Cloner le repository**
```bash
git clone https://github.com/Axxel-L/github-profile-svg.git
```

2. **Configurer les URLs**
Modifiez les fichiers suivants :
```javascript
// script.js
const API_BASE = 'https://votre-domaine.com/github_stats/api/generate.php';
const STATS_API = 'https://votre-domaine.com/github_stats/api/stats.php';
```

3. **Permissions du système de fichiers**
```bash
# Donnez les permissions d'écriture pour le fichier de statistiques
chmod 755 api/
chmod 664 api/stats.json
```

4. **Configuration PHP (optionnel)**
Si nécessaire, augmentez les limites PHP :
```ini
max_execution_time = 30
memory_limit = 128M
```

5. **Déployer sur votre serveur**
Uploader les fichiers sur votre serveur web accessible via HTTPS.

## 🔐 Sécurité et confidentialité

### Ce que nous collectons
✅ **Statistiques anonymes** :
- Nombre total de générations
- Nombre total de visiteurs
- Statistiques quotidiennes et mensuelles

❌ **Ce que nous ne collectons PAS** :
- Données personnelles des utilisateurs GitHub
- Informations de connexion
- Historique des requêtes
- Adresses IP

### GitHub API
- Utilisation de l'API GitHub officielle
- Rate limiting respecté (60 requêtes/heure sans token)
- Données affichées publiquement sur GitHub

## 📈 Statistiques en temps réel

Le générateur inclut un système de statistiques qui affiche :
- ✅ Nombre total de badges générés
- 👥 Nombre de visiteurs uniques
- 📅 Générations du mois en cours
- 🕐 Dernière génération

## 🎯 Utilisation avancée

### Personnalisation
Vous pouvez modifier les fichiers suivants pour personnaliser l'apparence :

- `generate.php` : Modifier les couleurs, la disposition du SVG
- `index.html` : Changer l'interface utilisateur
- `script.js` : Ajouter de nouvelles fonctionnalités

### Exemples d'intégration

**Markdown (GitHub README)** :
```markdown
# Mon Profil GitHub

![GitHub Stats](https://projets.agence-prestige-numerique.fr/github_stats/api/generate.php?username=votre_nom)
```

**HTML (Site personnel)** :
```html
<div class="github-stats">
  <img src="https://projets.agence-prestige-numerique.fr/github_stats/api/generate.php?username=votre_nom" 
       alt="Statistiques GitHub">
</div>
```

## 🤝 Contribuer

Les contributions sont les bienvenues ! Pour contribuer :

1. Forkez le projet
2. Créez une branche pour votre fonctionnalité (`git checkout -b feature/AmazingFeature`)
3. Committez vos changements (`git commit -m 'Add some AmazingFeature'`)
4. Pushez vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrez une Pull Request

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.


## 🌟 Remerciements

Un grand merci à :
- [GitHub](https://github.com) pour leur API
- La communauté open source pour les outils et bibliothèques