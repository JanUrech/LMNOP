<?php
// filepath: c:\Users\janic\OneDrive - FH Graubünden\Journalismus Multimedial\08_Formatentwicklung\02Webseite\LMNOP Webseite\LMNOP\PHP\transformIndex.php
header('Content-Type: application/json; charset=utf-8');

// Hilfsfunktion: Extrahiere ersten Paragraphen
function getFirstParagraph($content) {
    if (preg_match('/<p[^>]*>(.*?)<\/p>/is', $content, $matches)) {
        return strip_tags($matches[1]);
    }
    return strip_tags($content);
}

// 1) Posts mit _embed holen
$postsUrl = 'https://wp-lmnop.janicure.ch/wp-json/wp/v2/posts?_embed&per_page=100';
$ch = curl_init($postsUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$postsJson = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode < 200 || $httpCode >= 300 || !$postsJson) {
    http_response_code(502);
    echo json_encode(['error' => 'Failed to fetch posts', 'http_code' => $httpCode]);
    exit;
}

$posts = json_decode($postsJson, true);
if (!is_array($posts)) {
    http_response_code(500);
    echo json_encode(['error' => 'Invalid JSON from WP API']);
    exit;
}

// 2) Hole alle Categories
$catsUrl = "https://wp-lmnop.janicure.ch/wp-json/wp/v2/categories?per_page=100";
$ch2 = curl_init($catsUrl);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_TIMEOUT, 10);
$catsJson = curl_exec($ch2);
curl_close($ch2);

$categories = json_decode($catsJson, true) ?: [];
$catMap = [];
$catSlugMap = [];
$overviewCatId = null;
foreach ($categories as $cat) {
    $catMap[$cat['id']] = $cat['name'];
    $catSlugMap[$cat['id']] = $cat['slug'];
    if (strtolower($cat['name']) === 'overview') {
        $overviewCatId = $cat['id'];
    }
}

// Debug log after categories fetch
$debugLog = [
    'total_categories' => count($categories),
    'category_names' => array_map(function($cat) {
        return $cat['name'];
    }, $categories),
    'posts_per_category' => []
];

// 3) Trenne Overview-Posts und normale Posts
$overviewPosts = [];
$normalPosts = [];
foreach ($posts as $post) {
    $cats = $post['categories'] ?? [];
    if (in_array($overviewCatId, $cats, true)) {
        $overviewPosts[] = $post;
    } else {
        $normalPosts[] = $post;
    }
}

// 4) Gruppiere normale Posts nach Category-ID
$grouped = [];
foreach ($normalPosts as $post) {
    $cats = $post['categories'] ?? [];
    foreach ($cats as $catId) {
        if ($catId === $overviewCatId) continue;
        if (!isset($grouped[$catId])) {
            $grouped[$catId] = [];
        }
        $grouped[$catId][] = $post;
    }
}

// Debug log after grouping posts
foreach ($grouped as $catId => $posts) {
    $debugLog['posts_per_category'][$catMap[$catId]] = count($posts);
}

// 5) Finde neueste Overview-Description pro Category (nur erster Absatz)
$descriptions = [];
$overviewSubtitles = []; // NEU: Speichere H6 pro Kategorie

foreach ($overviewPosts as $op) {
    $content = $op['content']['rendered'] ?? '';
    $firstParagraph = getFirstParagraph($content);
    $secondParagraph = getSecondParagraph($content);
    $date = strtotime($op['date'] ?? '1970-01-01');
    
    // Extrahiere H6 Untertitel aus diesem Post
    $h6Subtitle = '';
    if (preg_match('/<h6[^>]*>([^<]+)<\/h6>/i', $content, $matches)) {
        $h6Subtitle = trim(strip_tags(html_entity_decode($matches[1])));
    }
    
    // GEÄNDERT: Für JEDE Kategorie (ausser Overview) einen Description speichern
    // Basierend auf Post-Titel oder slug Matching
    foreach ($grouped as $catId => $postsInCat) {
        // Prüfe ob dieser Overview-Post zu dieser Kategorie gehört
        // (z.B. Post-Slug oder Titel enthält Kategorie-Namen)
        $postTitle = strtolower($op['title']['rendered'] ?? '');
        $postSlug = strtolower($op['slug'] ?? '');
        $catName = strtolower($catMap[$catId] ?? '');
        
        // Match wenn Kategorie-Name im Post-Slug oder Titel vorkommt
        if (strpos($postSlug, str_replace(' ', '-', $catName)) !== false ||
            strpos($postTitle, str_replace('-', ' ', $catName)) !== false) {
            
            if (!isset($descriptions[$catId]) || $date > $descriptions[$catId]['date']) {
                $descriptions[$catId] = [
                    'text' => !empty($secondParagraph) ? $secondParagraph : $firstParagraph,
                    'date' => $date
                ];
                $overviewSubtitles[$catId] = $h6Subtitle;
            }
        }
    }
}

// 6) Baue Zielstruktur
$result = [];
foreach ($grouped as $catId => $postsInCat) {
    $description = $descriptions[$catId]['text'] ?? '';
    $subtitle = $overviewSubtitles[$catId] ?? ''; // NEU: Hole H6 aus Array
    
    $articles = [];
    foreach ($postsInCat as $p) {
        $imageUrl = '';
        if (!empty($p['_embedded']['wp:featuredmedia'][0]['source_url'])) {
            $imageUrl = $p['_embedded']['wp:featuredmedia'][0]['source_url'];
        }
        $articles[] = [
            'title' => $p['title']['rendered'] ?? '',
            'slug'  => $p['slug'] ?? '',
            'link'  => $p['link'] ?? '',
            'image' => $imageUrl
        ];
    }
    
    $result[] = [
        'title'              => $catMap[$catId],
        'slug'               => $catSlugMap[$catId],
        'description'        => $description,
        'overviewSubtitle'   => $subtitle, // NEU: H6 Untertitel
        'articles'           => $articles
    ];
}

// Debug log file output
$debugLog['overviewSubtitles'] = $overviewSubtitles;
$debugLog['grouped_categories'] = array_keys($grouped);
file_put_contents('debug_categories.json', json_encode($debugLog, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?>
