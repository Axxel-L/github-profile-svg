<?php
/**
 * Ce script génère une image SVG personnalisée avec les statistiques GitHub d'un utilisateur.
 * 
 */

// En-têtes HTTP pour spécifier le type de contenu et autoriser les requêtes CORS
header('Content-Type: image/svg+xml');
header('Cache-Control: public, max-age=3600');
header('Access-Control-Allow-Origin: *');

/**
 * Fonction pour convertir une image en données URI Base64
 * 
 * Cette fonction télécharge une image depuis une URL et la convertit en format Base64
 * pour l'intégrer directement dans le SVG (nécessaire pour GitHub qui bloque les hotlinks).
 * 
 * @param string $url L'URL de l'image à convertir
 * @return string Les données de l'image en format Base64, ou une chaîne vide en cas d'erreur
 */
function imageToBase64($url) {
    if (empty($url)) {
        return '';
    }
    
    $options = [
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: PHP Script',
                'Accept: image/*'
            ],
            'timeout' => 5
        ]
    ];
    
    $context = stream_context_create($options);
    $imageData = @file_get_contents($url, false, $context);
    
    if ($imageData === false) {
        return '';
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_buffer($finfo, $imageData);
    finfo_close($finfo);
    
    $base64 = base64_encode($imageData);
    return "data:{$mimeType};base64,{$base64}";
}

/**
 * Fonction pour récupérer des données depuis l'API GitHub
 * 
 * Cette fonction effectue une requête HTTP vers l'API GitHub et retourne
 * les données JSON décodées sous forme de tableau associatif.
 * 
 * @param string $url L'URL de l'API GitHub à interroger
 * @return array|null Les données JSON décodées, ou null en cas d'erreur
 */
function fetchGitHubAPI($url) {
    $options = [
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: PHP Script',
                'Accept: application/vnd.github.v3+json'
            ],
            'timeout' => 10
        ]
    ];
    
    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        return null;
    }
    
    return json_decode($response, true);
}

/**
 * Fonction pour générer un SVG d'erreur
 * 
 * Cette fonction crée une image SVG simple pour afficher un message d'erreur
 * lorsque la génération du badge échoue.
 * 
 * @param string $message Le message d'erreur à afficher
 * @return string Le code SVG de l'erreur
 */
function generateErrorSVG($message) {
    return '<?xml version="1.0" encoding="UTF-8"?>
<svg width="600" height="200" xmlns="http://www.w3.org/2000/svg">
    <rect width="600" height="200" fill="#EF4444" rx="10"/>
    <text x="300" y="100" font-family="Arial" font-size="18" fill="white" text-anchor="middle">⚠️ ' . $message . '</text>
</svg>';
}

/**
 * Fonction pour formater les grands nombres
 * 
 * Cette fonction convertit les grands nombres en format lisible (k pour milliers, M pour millions).
 * 
 * @param int $num Le nombre à formater
 * @return string Le nombre formaté avec l'unité appropriée
 */
function formatNumber($num) {
    if ($num >= 1000000) {
        return round($num / 1000000, 1) . 'M';
    }
    if ($num >= 1000) {
        return round($num / 1000, 1) . 'k';
    }
    return $num;
}

// ============================================================================
// DÉBUT DU SCRIPT PRINCIPAL
// ============================================================================

// Vérifier et valider le paramètre username
$username = isset($_GET['username']) ? htmlspecialchars($_GET['username']) : '';
if (empty($username)) {
    echo generateErrorSVG('Veuillez spécifier un nom d\'utilisateur GitHub');
    exit;
}

// Récupérer les données de l'utilisateur depuis l'API GitHub
$userData = fetchGitHubAPI("https://api.github.com/users/{$username}");
if (!$userData || isset($userData['message'])) {
    echo generateErrorSVG('Utilisateur GitHub non trouvé');
    exit;
}

// Récupérer et convertir l'avatar en Base64
$avatarUrl = $userData['avatar_url'];
$avatarBase64 = imageToBase64($avatarUrl);

// Déterminer la source de l'image à afficher (Base64 ou icône par défaut)
$avatarDisplay = '';
if (!empty($avatarBase64)) {
    // Utiliser l'avatar encodé en Base64
    $avatarDisplay = "data:image/jpeg;base64," . base64_encode(file_get_contents($avatarUrl));
} else {
    // Fallback : utiliser une icône utilisateur SVG encodée en Base64
    $avatarDisplay = "data:image/svg+xml;base64," . base64_encode('<?xml version="1.0" encoding="UTF-8"?>
    <svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
        <circle cx="50" cy="50" r="50" fill="#6c757d"/>
        <path d="M50 55c-13.8 0-25 11.2-25 25h50c0-13.8-11.2-25-25-25z" fill="#ffffff" opacity="0.7"/>
        <circle cx="50" cy="35" r="20" fill="#ffffff" opacity="0.7"/>
    </svg>');
}

// Récupérer les dépôts de l'utilisateur
$repos = fetchGitHubAPI("https://api.github.com/users/{$username}/repos?sort=updated&per_page=30");
if (!$repos) {
    $repos = [];
}

// Calculer les statistiques des dépôts
$totalStars = 0;
$totalForks = 0;
$languages = [];

foreach ($repos as $repo) {
    $totalStars += $repo['stargazers_count'] ?? 0;
    $totalForks += $repo['forks_count'] ?? 0;

    if (isset($repo['language']) && $repo['language']) {
        $lang = $repo['language'];
        $languages[$lang] = isset($languages[$lang]) ? $languages[$lang] + 1 : 1;
    }
}

// Trier les langages par fréquence et garder les 5 premiers
uasort($languages, function ($a, $b) {
    return $b <=> $a;
});
$topLanguages = array_slice($languages, 0, 5, true);

// Palette de couleurs pour les langages de programmation
$languageColors = [
    'JavaScript' => '#F7DF1E',
    'TypeScript' => '#3178C6',
    'Python' => '#3776AB',
    'Java' => '#007396',
    'C++' => '#00599C',
    'C' => '#A8B9CC',
    'C#' => '#239120',
    'PHP' => '#777BB4',
    'Ruby' => '#CC342D',
    'Go' => '#00ADD8',
    'Rust' => '#DEA584',
    'Swift' => '#FA7343',
    'Kotlin' => '#7F52FF',
    'HTML' => '#E34F26',
    'CSS' => '#1572B6',
    'Vue' => '#4FC08D',
    'React' => '#61DAFB',
    'Shell' => '#4EAA25',
];

// Extraire les informations de l'utilisateur
$name = !empty($userData['name']) ? htmlspecialchars($userData['name']) : htmlspecialchars($userData['login']);
$login = htmlspecialchars($userData['login']);
$publicRepos = $userData['public_repos'];
$followers = $userData['followers'];
$following = $userData['following'];
$company = !empty($userData['company']) ? htmlspecialchars(preg_replace('/^@/', '', $userData['company'])) : null;
$location = !empty($userData['location']) ? htmlspecialchars($userData['location']) : null;
$createdAt = date('M Y', strtotime($userData['created_at']));

// ============================================================================
// CALCUL DYNAMIQUE DE LA POSITION Y
// ============================================================================

// Calculer le nombre de lignes d'informations
$infoLines = 0;
if ($company) $infoLines++;
if ($location) $infoLines++;
$infoLines++; // Pour la date d'inscription

// Position Y de base pour les informations
$infoY = 110;

// Position Y pour les langages : ajuster dynamiquement en fonction du nombre de lignes
$langY = $infoY + ($infoLines * 18) + 10; // 18px par ligne d'info + 10px d'espace

// Position Y pour les cartes de statistiques : ajuster dynamiquement
$cardY = $langY + 50; // 50px après les langages

// ============================================================================
// GÉNÉRATION DES INFORMATIONS UTILISATEUR
// ============================================================================

$infoHTML = '';
$currentInfoY = $infoY;

// Ajouter un espacement initial
$infoHTML .= '
<g transform="translate(50, ' . $currentInfoY . ')">
    <text x="0" y="0" font-family="Arial, sans-serif" font-size="12" fill="transparent"> </text>
</g>';
$currentInfoY += 5;

if ($company) {
    $infoHTML .= '
    <g transform="translate(50, ' . $currentInfoY . ')">
        <text x="0" y="0" font-family="Arial, sans-serif" font-size="12" fill="#9CA3AF">🏢 ' . $company . '</text>
    </g>';
    $currentInfoY += 18;
}

if ($location) {
    $infoHTML .= '
    <g transform="translate(50, ' . $currentInfoY . ')">
        <text x="0" y="0" font-family="Arial, sans-serif" font-size="12" fill="#9CA3AF">📍 ' . $location . '</text>
    </g>';
    $currentInfoY += 18;
}

$infoHTML .= '
<g transform="translate(50, ' . $currentInfoY . ')">
    <text x="0" y="0" font-family="Arial, sans-serif" font-size="12" fill="#9CA3AF">📅 Inscrit en ' . $createdAt . '</text>
</g>';

// ============================================================================
// GÉNÉRATION DU SVG
// ============================================================================

// Générer les éléments SVG pour les langages
$langHTML = '';
$index = 0;

foreach ($topLanguages as $lang => $count) {
    $color = isset($languageColors[$lang]) ? $languageColors[$lang] : '#6B7280';
    $langSafe = htmlspecialchars($lang);
    $percentage = count($languages) > 0 ? round(($count / array_sum($languages)) * 100) : 0;
    $x = 50 + ($index * 110);

    $langHTML .= '
    <g transform="translate(' . $x . ', ' . $langY . ')">
        <circle cx="10" cy="10" r="6" fill="' . $color . '"/>
        <text x="25" y="13" font-family="Arial, sans-serif" font-size="12" fill="#E5E7EB" font-weight="600">' . $langSafe . '</text>
        <text x="25" y="28" font-family="Arial, sans-serif" font-size="11" fill="#9CA3AF">' . $percentage . '%</text>
    </g>';

    $index++;
}

// Générer les cartes de statistiques
$statsCards = '';
$statsData = [
    ['icon' => '📦', 'value' => $publicRepos, 'label' => 'Repos', 'color' => '#3B82F6'],
    ['icon' => '⭐', 'value' => $totalStars, 'label' => 'Stars', 'color' => '#F59E0B'],
    ['icon' => '🔀', 'value' => $totalForks, 'label' => 'Forks', 'color' => '#10B981'],
    ['icon' => '👥', 'value' => $followers, 'label' => 'Followers', 'color' => '#8B5CF6'],
    ['icon' => '👤', 'value' => $following, 'label' => 'Following', 'color' => '#EC4899'],
];

$cardX = 50;
$cardWidth = 100;
$cardHeight = 60;
$cardSpacing = 15;

// Calculer le nombre de lignes de cartes
$cardsPerRow = 3;
$totalCards = count($statsData);
$cardRows = ceil($totalCards / $cardsPerRow);

foreach ($statsData as $index => $stat) {
    $row = floor($index / $cardsPerRow);
    $col = $index % $cardsPerRow;
    
    $x = $cardX + ($col * ($cardWidth + 20));
    $y = $cardY + ($row * ($cardHeight + $cardSpacing));
    $formattedValue = formatNumber($stat['value']);

    $statsCards .= '
    <g transform="translate(' . $x . ', ' . $y . ')">
        <rect width="' . $cardWidth . '" height="' . $cardHeight . '" rx="8" fill="rgba(255,255,255,0.05)" stroke="rgba(255,255,255,0.1)" stroke-width="1"/>
        <text x="' . ($cardWidth / 2) . '" y="25" font-family="Arial, sans-serif" font-size="18" fill="#F3F4F6" text-anchor="middle" font-weight="700">' . $formattedValue . '</text>
        <text x="' . ($cardWidth / 2) . '" y="45" font-family="Arial, sans-serif" font-size="12" fill="#9CA3AF" text-anchor="middle">' . $stat['icon'] . ' ' . $stat['label'] . '</text>
    </g>';
}

// ============================================================================
// CALCUL DE LA HAUTEUR TOTALE DU SVG
// ============================================================================

// Calculer la hauteur totale nécessaire
$cardsTotalHeight = $cardRows * $cardHeight + ($cardRows - 1) * $cardSpacing;
$totalHeight = $cardY + $cardsTotalHeight + 40; // 40px de marge en bas

// Ajuster la hauteur du SVG
$svgHeight = max(400, $totalHeight); // Minimum 400px, sinon calculé dynamiquement

// ============================================================================
// CONSTRUCTION FINALE DU SVG
// ============================================================================

$svg = '<?xml version="1.0" encoding="UTF-8"?>
<svg width="600" height="' . $svgHeight . '" xmlns="http://www.w3.org/2000/svg">
    <!-- Fond du badge -->
    <rect width="600" height="' . $svgHeight . '" fill="#292929" rx="12"/>
    
    <!-- Avatar de l\'utilisateur (encodé en Base64) -->
    <g transform="translate(30, 30)">
        <defs>
            <clipPath id="avatarClip">
                <circle cx="35" cy="35" r="35"/>
            </clipPath>
        </defs>
        <circle cx="35" cy="35" r="38" fill="rgba(255,255,255,0.1)"/>
        <image href="' . $avatarDisplay . '" x="0" y="0" width="70" height="70" clip-path="url(#avatarClip)"/>
    </g>
    
    <!-- Nom et pseudonyme GitHub -->
    <g transform="translate(120, 45)">
        <text x="0" y="0" font-family="Arial, sans-serif" font-size="22" fill="#F9FAFB" font-weight="700">' . $name . '</text>
        <text x="0" y="25" font-family="Arial, sans-serif" font-size="14" fill="#6B7280">@' . $login . '</text>
    </g>
    
    <!-- Ligne de séparation -->
    <g transform="translate(120, 85)">
        <line x1="0" y1="0" x2="200" y2="0" stroke="rgba(255,255,255,0.1)" stroke-width="1" stroke-dasharray="4,4"/>
    </g>
    
    <!-- Informations utilisateur -->
    ' . $infoHTML . '
    
    <!-- Langages de programmation principaux -->
    ' . $langHTML . '
    
    <!-- Cartes de statistiques GitHub -->
    ' . $statsCards . '
</svg>';

// Afficher le SVG généré
echo $svg;