<?php
/**
 * API de Statistiques pour le GitHub Profile SVG Generator
 * 
 * Ce script gère le suivi des statistiques du générateur.
 */

// En-têtes HTTP pour les requêtes API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

/**
 * Chemin vers le fichier de stockage des statistiques
 * @var string
 */
$statsFile = 'stats.json';

/**
 * Initialise ou charge le fichier de statistiques
 * 
 * Si le fichier n'existe pas, il est créé avec une structure initiale.
 * Sinon, les données existantes sont chargées.
 */
if (!file_exists($statsFile)) {
    $initialStats = [
        'totalGenerations' => 0,
        'totalVisitors' => 0,
        'dailyStats' => [],
        'monthlyStats' => [],
        'lastGeneration' => null,
        'lastVisit' => null,
        'firstGeneration' => null
    ];
    file_put_contents($statsFile, json_encode($initialStats, JSON_PRETTY_PRINT));
    $stats = $initialStats;
} else {
    $stats = json_decode(file_get_contents($statsFile), true);
}

/**
 * Détermine l'action à exécuter à partir du paramètre GET 'action'
 * 
 * Actions disponibles :
 * - increment_generations : Incrémente le compteur de générations
 * - increment_visitors : Incrémente le compteur de visiteurs
 * - get_stats : Retourne toutes les statistiques (action par défaut)
 */
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'increment_generations':
        incrementGenerations($stats, $statsFile);
        break;
    
    case 'increment_visitors':
        incrementVisitors($stats, $statsFile);
        break;
    
    case 'get_stats':
        getStats($stats);
        break;
    
    default:
        // Action par défaut : retourner toutes les statistiques
        getStats($stats);
        break;
}

/**
 * Incrémente le compteur de générations de badges
 * 
 * Cette fonction met à jour :
 * - Le nombre total de générations
 * - Les statistiques quotidiennes
 * - Les statistiques mensuelles
 * - Les horodatages de première et dernière génération
 * 
 * @param array &$stats Référence au tableau des statistiques
 * @param string $statsFile Chemin vers le fichier de statistiques
 */
function incrementGenerations(&$stats, $statsFile) {
    $today = date('Y-m-d');
    $currentMonth = date('Y-m');
    
    // Incrémenter le total des générations
    $stats['totalGenerations']++;
    
    // Mettre à jour l'horodatage de la dernière génération
    $stats['lastGeneration'] = date('Y-m-d H:i:s');
    
    // Initialiser la première génération si c'est la première
    if (empty($stats['firstGeneration'])) {
        $stats['firstGeneration'] = $stats['lastGeneration'];
    }
    
    // Statistiques quotidiennes
    if (!isset($stats['dailyStats'][$today])) {
        $stats['dailyStats'][$today] = 0;
    }
    $stats['dailyStats'][$today]++;
    
    // Statistiques mensuelles
    if (!isset($stats['monthlyStats'][$currentMonth])) {
        $stats['monthlyStats'][$currentMonth] = 0;
    }
    $stats['monthlyStats'][$currentMonth]++;
    
    // Sauvegarder les modifications
    if (file_put_contents($statsFile, json_encode($stats, JSON_PRETTY_PRINT))) {
        echo json_encode([
            'success' => true, 
            'totalGenerations' => $stats['totalGenerations'],
            'dailyGenerations' => $stats['dailyStats'][$today],
            'monthlyGenerations' => $stats['monthlyStats'][$currentMonth]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Impossible d\'écrire dans le fichier de statistiques']);
    }
}

/**
 * Incrémente le compteur de visiteurs uniques
 * 
 * Cette fonction met à jour :
 * - Le nombre total de visiteurs
 * - Les statistiques quotidiennes de visiteurs
 * - Les statistiques mensuelles de visiteurs
 * - L'horodatage de la dernière visite
 * 
 * @param array &$stats Référence au tableau des statistiques
 * @param string $statsFile Chemin vers le fichier de statistiques
 */
function incrementVisitors(&$stats, $statsFile) {
    $today = date('Y-m-d');
    $currentMonth = date('Y-m');
    
    // Incrémenter le total des visiteurs
    $stats['totalVisitors']++;
    
    // Mettre à jour l'horodatage de la dernière visite
    $stats['lastVisit'] = date('Y-m-d H:i:s');
    
    // Statistiques quotidiennes des visiteurs
    if (!isset($stats['dailyVisitors'][$today])) {
        $stats['dailyVisitors'][$today] = 0;
    }
    $stats['dailyVisitors'][$today]++;
    
    // Statistiques mensuelles des visiteurs
    if (!isset($stats['monthlyVisitors'][$currentMonth])) {
        $stats['monthlyVisitors'][$currentMonth] = 0;
    }
    $stats['monthlyVisitors'][$currentMonth]++;
    
    // Sauvegarder les modifications
    if (file_put_contents($statsFile, json_encode($stats, JSON_PRETTY_PRINT))) {
        echo json_encode([
            'success' => true,
            'totalVisitors' => $stats['totalVisitors']
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Impossible d\'écrire dans le fichier de statistiques']);
    }
}

/**
 * Retourne toutes les statistiques actuelles
 * 
 * Cette fonction calcule et retourne :
 * - Les totaux cumulés (générations et visiteurs)
 * - Les statistiques du jour et du mois en cours
 * - Les horodatages des derniers événements
 * 
 * @param array $stats Tableau des statistiques
 */
function getStats($stats) {
    $today = date('Y-m-d');
    $currentMonth = date('Y-m');
    
    // Calculer les générations d'aujourd'hui
    $dailyGenerations = isset($stats['dailyStats'][$today]) ? $stats['dailyStats'][$today] : 0;
    
    // Calculer les générations du mois en cours
    $monthlyGenerations = isset($stats['monthlyStats'][$currentMonth]) ? $stats['monthlyStats'][$currentMonth] : 0;
    
    // Retourner les statistiques au format JSON
    echo json_encode([
        'totalGenerations' => $stats['totalGenerations'] ?? 0,
        'totalVisitors' => $stats['totalVisitors'] ?? 0,
        'dailyGenerations' => $dailyGenerations,
        'monthlyGenerations' => $monthlyGenerations,
        'lastGeneration' => $stats['lastGeneration'] ?? null,
        'lastVisit' => $stats['lastVisit'] ?? null,
        'firstGeneration' => $stats['firstGeneration'] ?? null
    ]);
}

/**
 * Mode débogage
 * 
 * Affiche des informations détaillées sur le fichier de statistiques
 * lorsque le paramètre GET 'debug' est présent.
 */
if (isset($_GET['debug'])) {
    echo "<pre>";
    echo "Fichier de statistiques : " . realpath($statsFile) . "\n";
    echo "Permissions : " . substr(sprintf('%o', fileperms($statsFile)), -4) . "\n";
    echo "Contenu actuel :\n";
    print_r($stats);
    echo "</pre>";
}
?>