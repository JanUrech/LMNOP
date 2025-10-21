<?php
header('Content-Type: application/json; charset=utf-8');

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
$overviewCatId = null;
foreach ($categories as $cat) {
    $catMap[$cat['id']] = $cat['name'];
    if (strtolower($cat['name']) === 'overview') {
        $overviewCatId = $cat['id'];
    }
}

// 3) Trenne Overview-Posts und normale Posts (strikt)
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

// 4) Gruppiere normale Posts nach Category-ID (ignoriere Overview-Category komplett)
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

// 5) Finde für jede Category die neueste Overview-Description
$descriptions = [];
foreach ($overviewPosts as $op) {
    $cats = $op['categories'] ?? [];
    $otherCats = array_filter($cats, function($c) use ($overviewCatId) {
        return $c !== $overviewCatId;
    });
    $excerpt = strip_tags($op['excerpt']['rendered'] ?? '');
    $date = strtotime($op['date'] ?? '1970-01-01');
    
    foreach ($otherCats as $catId) {
        if (!isset($descriptions[$catId]) || $date > $descriptions[$catId]['date']) {
            $descriptions[$catId] = [
                'text' => $excerpt,
                'date' => $date
            ];
        }
    }
}

// 6) Baue Zielstruktur — filtere Overview-Category komplett raus
$result = [];
foreach ($grouped as $catId => $postsInCat) {
    // Überspringe Overview selbst als Thema
    if ($catId === $overviewCatId) continue;
    
    $catName = $catMap[$catId] ?? "Unbekannt";
    $description = $descriptions[$catId]['text'] ?? '';
    
    $articles = [];
    foreach ($postsInCat as $p) {
        $imageUrl = '';
        if (!empty($p['_embedded']['wp:featuredmedia'][0]['source_url'])) {
            $imageUrl = $p['_embedded']['wp:featuredmedia'][0]['source_url'];
        }
        $articles[] = [
            'title' => $p['title']['rendered'] ?? '',
            'link'  => $p['link'] ?? '',
            'image' => $imageUrl
        ];
    }
    
    $result[] = [
        'title'       => $catName,
        'description' => $description,
        'articles'    => $articles
    ];
}

echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?>
