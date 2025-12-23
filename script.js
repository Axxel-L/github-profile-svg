/**
 * Ce fichier JavaScript gère l'interface utilisateur du générateur de badges GitHub.
 * Il permet de générer des badges SVG, d'afficher les prévisualisations,
 * de gérer les onglets et de suivre les statistiques d'utilisation.
 */

// ============================================================================
// CONFIGURATION DES URLS API
// ============================================================================

/** URL de base de l'API pour générer les badges SVG */
const API_BASE = 'https://projets.agence-prestige-numerique.fr/github_stats/api/generate.php';
/** URL de l'API de statistiques */
const STATS_API = 'https://projets.agence-prestige-numerique.fr/github_stats/api/stats.php';

// ============================================================================
// RÉFÉRENCES AUX ÉLÉMENTS DOM
// ============================================================================

/** Champ de saisie pour le nom d'utilisateur GitHub */
const usernameInput = document.getElementById('username');

/** Bouton de génération du badge */
const generateBtn = document.getElementById('generate');

/** Section de prévisualisation du badge */
const preview = document.getElementById('preview');

/** Conteneur pour afficher le SVG généré */
const svgContainer = document.getElementById('svg-container');

/** Élément pour afficher le code Markdown */
const markdownCode = document.getElementById('markdown-code');

/** Élément pour afficher le code HTML */
const htmlCode = document.getElementById('html-code');

/** Élément pour afficher l'URL directe */
const urlCode = document.getElementById('url-code');

/** Conteneur des messages d'erreur */
const errorDiv = document.getElementById('error');

/** Élément pour le message d'erreur */
const errorMessage = document.getElementById('error-message');

/** Texte du bouton de génération */
const btnText = document.querySelector('.btn-text');

/** Indicateur de chargement du bouton */
const btnLoader = document.querySelector('.btn-loader');

/** Boutons des onglets (Markdown, HTML, URL) */
const tabBtns = document.querySelectorAll('.tab-btn');

/** Contenus des onglets */
const tabContents = document.querySelectorAll('.tab-content');

// ============================================================================
// GESTION DES ONGLETS
// ============================================================================

/**
 * Initialise la navigation par onglets entre Markdown, HTML et URL
 * 
 * Cette fonction permet de basculer entre les différents formats de code
 * affichés après la génération d'un badge.
 */
tabBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        const targetTab = btn.dataset.tab;
        
        // Désactiver tous les onglets
        tabBtns.forEach(b => b.classList.remove('active'));
        tabContents.forEach(c => c.classList.remove('active'));
        
        // Activer l'onglet sélectionné
        btn.classList.add('active');
        document.getElementById(`${targetTab}-tab`).classList.add('active');
    });
});

// ============================================================================
// GÉNÉRATION DU BADGE
// ============================================================================

/**
 * Gestionnaire d'événement pour le bouton de génération
 * 
 * Cette fonction :
 * 1. Récupère le nom d'utilisateur GitHub
 * 2. Appelle l'API pour générer le badge SVG
 * 3. Affiche le badge et les codes d'intégration
 * 4. Met à jour les statistiques
 * 5. Gère les erreurs éventuelles
 */
generateBtn.addEventListener('click', async () => {
    const username = usernameInput.value.trim();
    
    // Validation de l'entrée
    if (!username) {
        showError('Veuillez entrer un nom d\'utilisateur GitHub valide');
        return;
    }

    // Affichage de l'état de chargement
    setLoading(true);
    hideError();

    try {
        // Construction de l'URL de génération
        const svgUrl = `${API_BASE}?username=${encodeURIComponent(username)}`;
        
        // Appel à l'API de génération
        const response = await fetch(svgUrl);
        
        // Vérification de la réponse
        if (!response.ok) {
            throw new Error('Utilisateur GitHub non trouvé');
        }
        
        // Récupération du SVG généré
        const svgText = await response.text();
        
        // Affichage du SVG
        svgContainer.innerHTML = svgText;
        
        // Génération des codes d'intégration
        const markdownText = `![GitHub Stats](${svgUrl})`;
        const htmlText = `<img src="${svgUrl}" alt="GitHub Stats de ${username}">`;
        
        // Mise à jour des éléments DOM avec les codes
        markdownCode.textContent = markdownText;
        htmlCode.textContent = htmlText;
        urlCode.textContent = svgUrl;
        
        // Mise à jour des statistiques de génération
        await updateGenerationStats();
        
        // Affichage de la prévisualisation
        preview.classList.remove('hidden');
        
        // Défilement fluide vers la prévisualisation
        preview.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        
    } catch (error) {
        // Gestion des erreurs
        showError(error.message);
    } finally {
        // Réinitialisation de l'état de chargement
        setLoading(false);
    }
});

/**
 * Permet de déclencher la génération avec la touche Entrée
 */
usernameInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        generateBtn.click();
    }
});

// ============================================================================
 // FONCTIONNALITÉ DE COPIE DES CODES
 // ============================================================================

/**
 * Initialise les boutons de copie pour Markdown, HTML et URL
 * 
 * Cette fonction permet de copier les codes d'intégration dans le presse-papier
 * et affiche une confirmation visuelle pendant 2 secondes.
 */
document.querySelectorAll('.btn-copy').forEach(btn => {
    btn.addEventListener('click', () => {
        const targetId = btn.dataset.target;
        const code = document.getElementById(targetId).textContent;
        
        // Utilisation de l'API Clipboard pour copier le texte
        navigator.clipboard.writeText(code).then(() => {
            // Sauvegarde du contenu original
            const originalHTML = btn.innerHTML;
            
            // Affichage de la confirmation
            btn.innerHTML = '<span class="copy-icon">✓</span><span class="copy-text">Copié !</span>';
            btn.classList.add('copied');
            
            // Rétablissement du contenu original après 2 secondes
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.classList.remove('copied');
            }, 2000);
        });
    });
});

// ============================================================================
// FONCTIONS UTILITAIRES D'INTERFACE
// ============================================================================

/**
 * Affiche ou masque l'indicateur de chargement sur le bouton de génération
 * 
 * @param {boolean} isLoading - Indique si l'état de chargement doit être activé
 */
function setLoading(isLoading) {
    generateBtn.disabled = isLoading;
    
    if (isLoading) {
        btnText.classList.add('hidden');
        btnLoader.classList.remove('hidden');
    } else {
        btnText.classList.remove('hidden');
        btnLoader.classList.add('hidden');
    }
}

/**
 * Affiche un message d'erreur à l'utilisateur
 * 
 * @param {string} message - Le message d'erreur à afficher
 */
function showError(message) {
    errorMessage.textContent = message;
    errorDiv.classList.remove('hidden');
    preview.classList.add('hidden');
}

/**
 * Masque le message d'erreur
 */
function hideError() {
    errorDiv.classList.add('hidden');
}

/**
 * Anime un compteur numérique avec un effet de décompte
 * 
 * @param {string} elementId - L'ID de l'élément DOM à animer
 * @param {number} targetValue - La valeur finale du compteur
 */
function animateCounter(elementId, targetValue) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const duration = 500; // Durée de l'animation en millisecondes
    const steps = 60;     // Nombre d'étapes pour l'animation
    const stepValue = targetValue / steps;
    let currentValue = 0;
    
    // Animation par intervalles
    const interval = setInterval(() => {
        currentValue += stepValue;
        
        // Arrêt de l'animation lorsque la valeur cible est atteinte
        if (currentValue >= targetValue) {
            currentValue = targetValue;
            clearInterval(interval);
        }
        
        // Mise à jour de l'affichage avec formatage
        element.textContent = Math.floor(currentValue).toLocaleString();
    }, duration / steps);
}

// ============================================================================
// GESTION DES STATISTIQUES
// ============================================================================

/**
 * Met à jour les statistiques de génération de badges
 * 
 * Cette fonction appelle l'API pour incrémenter le compteur de générations
 * et met à jour l'affichage avec un effet visuel.
 */
async function updateGenerationStats() {
    try {
        const response = await fetch(`${STATS_API}?action=increment_generations`);
        
        if (response.ok) {
            const data = await response.json();
            const totalGenerationsElement = document.getElementById('total-generations');
            
            if (totalGenerationsElement) {
                const currentValue = parseInt(data.totalGenerations);
                totalGenerationsElement.textContent = currentValue.toLocaleString();
                totalGenerationsElement.setAttribute('data-count', currentValue);
                
                // Effet visuel de mise à jour
                totalGenerationsElement.classList.add('text-green-400');
                setTimeout(() => {
                    totalGenerationsElement.classList.remove('text-green-400');
                }, 1000);
            }
        }
    } catch (error) {
        console.error('Erreur lors de la mise à jour des statistiques de génération:', error);
    }
}

/**
 * Suit les visiteurs uniques du site
 * 
 * Cette fonction incrémente le compteur de visiteurs à chaque chargement de page
 * et met à jour l'affichage avec un effet visuel.
 */
async function trackVisitor() {
    try {
        const response = await fetch(`${STATS_API}?action=increment_visitors`);
        
        if (response.ok) {
            const data = await response.json();
            const totalVisitorsElement = document.getElementById('total-visitors');
            
            if (totalVisitorsElement) {
                const currentValue = parseInt(data.totalVisitors);
                totalVisitorsElement.textContent = currentValue.toLocaleString();
                totalVisitorsElement.setAttribute('data-count', currentValue);
                
                // Effet visuel de mise à jour
                totalVisitorsElement.classList.add('text-blue-400');
                setTimeout(() => {
                    totalVisitorsElement.classList.remove('text-blue-400');
                }, 1000);
            }
        }
    } catch (error) {
        console.error('Erreur lors du suivi des visiteurs:', error);
    }
}

/**
 * Initialise les compteurs de statistiques au chargement de la page
 * 
 * Cette fonction :
 * 1. Enregistre un nouveau visiteur
 * 2. Récupère les statistiques actuelles depuis l'API
 * 3. Anime les compteurs avec les valeurs récupérées
 * 4. Calcule et affiche les statistiques hebdomadaires
 */
async function initializeCounters() {
    try {
        // Enregistrement du visiteur actuel
        await trackVisitor();
        
        // Récupération des statistiques globales
        const response = await fetch(STATS_API);
        
        if (response.ok) {
            const stats = await response.json();
            
            // Animation des compteurs principaux
            animateCounter('total-generations', stats.totalGenerations);
            animateCounter('total-visitors', stats.totalVisitors);
            
            // Calcul des statistiques hebdomadaires (estimation)
            const weeklyIncrementElement = document.getElementById('weekly-increment');
            if (weeklyIncrementElement && stats.dailyGenerations) {
                weeklyIncrementElement.textContent = Math.round(stats.dailyGenerations * 7).toLocaleString();
            }
        }
    } catch (error) {
        console.error('Erreur lors du chargement initial des statistiques:', error);
        
        // Valeurs par défaut en cas d'erreur
        animateCounter('total-generations', 0);
        animateCounter('total-visitors', 0);
    }
}

// ============================================================================
// INITIALISATION DE L'APPLICATION
// ============================================================================

/**
 * Point d'entrée principal de l'application
 * 
 * Cette fonction est appelée lorsque la page est entièrement chargée.
 * Elle initialise les compteurs de statistiques et prépare l'interface.
 */
document.addEventListener('DOMContentLoaded', () => {
    // Initialisation des compteurs de statistiques
    initializeCounters();
    // Focus automatique sur le champ de saisie pour une meilleure UX
    usernameInput.focus();
    // Message de bienvenue dans la console (optionnel)
    console.log('GitHub Profile SVG Generator initialisé avec succès.');
});