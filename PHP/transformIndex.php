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

// 5) Finde neueste Overview-Description pro Category (nur erster Absatz)
$descriptions = [];
foreach ($overviewPosts as $op) {
    $cats = $op['categories'] ?? [];
    $otherCats = array_filter($cats, function($c) use ($overviewCatId) {
        return $c !== $overviewCatId;
    });
    
    // Nutze ersten Absatz aus Content statt Excerpt
    $content = $op['content']['rendered'] ?? '';
    $firstParagraph = getFirstParagraph($content);
    $date = strtotime($op['date'] ?? '1970-01-01');
    
    foreach ($otherCats as $catId) {
        if (!isset($descriptions[$catId]) || $date > $descriptions[$catId]['date']) {
            $descriptions[$catId] = [
                'text' => $firstParagraph,
                'date' => $date
            ];
        }
    }
}

// 6) Baue Zielstruktur
$result = [];
foreach ($grouped as $catId => $postsInCat) {
    if ($catId === $overviewCatId) continue;
    
    $catName = $catMap[$catId] ?? "Unbekannt";
    $catSlug = $catSlugMap[$catId] ?? strtolower(preg_replace('/[^a-z0-9]+/', '-', $catName));
    $description = $descriptions[$catId]['text'] ?? '';
    
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
        'title'       => $catName,
        'slug'        => $catSlug,
        'description' => $description,
        'articles'    => $articles
    ];
}

echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?>
