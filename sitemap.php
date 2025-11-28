<?php
// filepath: c:\Users\janic\OneDrive - FH Graubünden\Journalismus Multimedial\08_Formatentwicklung\02Webseite\LMNOP Webseite\LMNOP\sitemap.xml
header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: public, max-age=3600');

$siteUrl = 'https://lmnop.janicure.ch';

// Hole transformierte Topics
$url = 'https://lmnop.janicure.ch/PHP/transformIndex.php';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$topics = [];
if ($http >= 200 && $http < 300 && $response !== false) {
  $topics = json_decode($response, true) ?: [];
}

// Sammle alle URLs
$urls = [];

// 1. Homepage
$urls[] = [
    'loc' => $siteUrl,
    'changefreq' => 'weekly',
    'priority' => '1.0'
];

// 2. Übersichtseiten (Topics) + 3. Einzelne Artikel
foreach ($topics as $topic) {
    // NEU: Verwende saubere URLs ohne Query-Parameter
    $overviewUrl = $siteUrl . '/uebersicht/' . urlencode($topic['slug']);
    $urls[] = [
        'loc' => $overviewUrl,
        'changefreq' => 'weekly',
        'priority' => '0.9'
    ];
    
    // Artikel pro Topic
    foreach ($topic['articles'] ?? [] as $article) {
        // NEU: Verwende saubere URLs ohne Query-Parameter
        $articleUrl = $siteUrl . '/artikel/' . urlencode($article['slug']);
        $urls[] = [
            'loc' => $articleUrl,
            'changefreq' => 'monthly',
            'priority' => '0.8'
        ];
    }
}

// 4. Statische Seiten
$staticPages = [
    'Datenschutz.html',
    'Impressum.html'
];

foreach ($staticPages as $page) {
    $urls[] = [
        'loc' => $siteUrl . '/' . $page,
        'changefreq' => 'yearly',
        'priority' => '0.5'
    ];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $urlItem): ?>
  <url>
    <loc><?= htmlspecialchars($urlItem['loc']) ?></loc>
    <changefreq><?= htmlspecialchars($urlItem['changefreq']) ?></changefreq>
    <priority><?= htmlspecialchars($urlItem['priority']) ?></priority>
  </url>
<?php endforeach; ?>
</urlset>