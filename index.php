<?php
// === CACHING SYSTEM ===
$cacheFile = __DIR__ . '/cache/topics_cache.json';
$cacheTime = 300; // 5 Minuten Cache

$topics = [];

// Prüfe ob Cache existiert und noch gültig ist
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    // Cache ist gültig - direkt laden
    $topics = json_decode(file_get_contents($cacheFile), true) ?: [];
} else {
    // Cache fehlt oder abgelaufen - neu laden
    require_once __DIR__ . '/PHP/transformIndex.php';
    $topics = getTransformedTopics();
    
    // Cache-Verzeichnis erstellen falls nicht vorhanden
    if (!is_dir(__DIR__ . '/cache')) {
        mkdir(__DIR__ . '/cache', 0755, true);
    }
    
    // In Cache speichern
    file_put_contents($cacheFile, json_encode($topics, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

// Für JSON-LD: Erstelle BreadcrumbList und Organization Schema
$siteUrl = 'https://lmnop.janicure.ch';
$organizationSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => 'LMNOP',
    'url' => $siteUrl,
    'logo' => $siteUrl . '/media/FaviconPrototype.png',
    'description' => 'LMNOP - Ein Journalismus Multimedial Projekt',
    'sameAs' => []
];

// Sammle WebPage Schema
$webPageSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => 'LMNOP',
    'url' => $siteUrl,
    'potentialAction' => [
        '@type' => 'SearchAction',
        'target' => [
            '@type' => 'EntryPoint',
            'urlTemplate' => $siteUrl . '/suche.php?q={search_term_string}'
        ],
        'query-input' => 'required name=search_term_string'
    ]
];

// Sammle CollectionPage mit Topics
$collectionItems = [];
$articleSchemas = [];

foreach ($topics as $topic) {
    $topicUrl = $siteUrl . '/uebersicht.php?slug=' . urlencode($topic['slug']);
    // Nutze 'lead' als Description (description existiert nicht in den Daten)
    $topicDescription = $topic['lead'] ?? $topic['description'] ?? '';
    $collectionItems[] = [
        '@type' => 'NewsArticle',
        'headline' => $topic['title'],
        'description' => substr(strip_tags($topicDescription), 0, 160),
        'url' => $topicUrl,
        'image' => isset($topic['articles'][0]) ? $topic['articles'][0]['image'] : ''
    ];
    
    // NEU: Für jeden Artikel ein DetaillierteSchema
    foreach ($topic['articles'] ?? [] as $article) {
        $articleUrl = $siteUrl . '/artikel.php?slug=' . urlencode($article['slug']);
        $articleSchemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => $article['title'],
            'url' => $articleUrl,
            'image' => $article['image'] ?? '',
            'description' => 'Artikel über ' . $topic['title'],
            'articleSection' => $topic['title']
        ];
    }
}

$collectionPageSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => 'LMNOP - Journalismus Multimedial',
    'description' => 'LMNOP Magazin mit Themen rund um gesellschaftliche Themen',
    'url' => $siteUrl,
    'hasPart' => $collectionItems
];
?>
<!DOCTYPE html>
<html lang="de">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Journalismus Multimedial mit Fokus auf gesellschaftliche Themen">
  <meta name="keywords" content="Journalismus, Multimedial, Magazin, Gesellschaft, Politik, Kultur">
  <meta property="og:title" content="LMNOP Magazin">
  <meta property="og:description" content="LMNOP - Ein multimediales Journalismus Projekt">
  <meta property="og:url" content="https://lmnop.janicure.ch">
  <meta property="og:type" content="website">
  
  <title>LMNOP - Home</title>
  <link rel="canonical" href="https://lmnop.janicure.ch">
  <link rel="stylesheet" href="style/style.css">
  <link rel="stylesheet" href="https://use.typekit.net/hbr8dui.css">
  <link rel="icon" type="image/png" href="media/FaviconPrototype.png">

  <!-- JSON-LD Schema Markup -->
  <script type="application/ld+json">
    <?= json_encode($organizationSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
  </script>

  <script type="application/ld+json">
    <?= json_encode($webPageSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
  </script>

  <script type="application/ld+json">
    <?= json_encode($collectionPageSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
  </script>

  <!-- JSON-LD Schema für jeden Artikel -->
  <?php foreach ($articleSchemas as $schema): ?>
    <script type="application/ld+json">
      <?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
    </script>
  <?php endforeach; ?>
</head>

<body>
  <!-- Bubble Container für animierte Hintergrund-Bubbles -->
  <div class="bubble-container"></div>

  <header>
    <!-- Header wird durch script.js befüllt -->
  </header>

  <main id="indexMain">
    <menu class="menuIndex">
      <?php foreach ($topics as $topic):
        $urlTitle = $topic['slug'] ?? strtolower(preg_replace('/[^a-z0-9]+/', '-', trim($topic['title'], '-')));
        ?>
        <li class="menuIndexItem"><a
            href="#<?= htmlspecialchars($urlTitle) ?>"><?= htmlspecialchars($topic['title']) ?></a></li>
      <?php endforeach; ?>
    </menu>

    <?php foreach ($topics as $topic):
      $urlTitle = $topic['slug'] ?? strtolower(preg_replace('/[^a-z0-9]+/', '-', trim($topic['title'], '-')));
      $overviewUrl = "uebersicht.php?slug=" . urlencode($urlTitle);
      ?>
      <section class="topicSection" id="<?= htmlspecialchars($urlTitle) ?>">
        <h2 class="topicTitle"><?= htmlspecialchars($topic['title']) ?></h2>
        
        <!-- NEU: Spitzmarke aus transformIndex.php -->
        <?php if (!empty($topic['spitzmarke'])): ?>
          <h6 class="spitzmarke"><?= htmlspecialchars($topic['spitzmarke']) ?></h6>
        <?php endif; ?>

        <article class="heroArticle" onclick="window.location.href='<?= htmlspecialchars($overviewUrl) ?>'"
          style="cursor:pointer;">
          <p><?= htmlspecialchars($topic['lead'] ?? '') ?></p>

          <?php
          $articlesAll = $topic['articles'] ?? [];

          usort($articlesAll, function($a, $b) {
            $da = $a['date'] ?? $a['published'] ?? $a['post_date'] ?? '';
            $db = $b['date'] ?? $b['published'] ?? $b['post_date'] ?? '';
            $ta = strtotime($da) ?: 0;
            $tb = strtotime($db) ?: 0;
            return $tb <=> $ta;
          });

          $sideArticles = array_slice($articlesAll, 0, 2);
          foreach ($sideArticles as $idx => $article):
             $articleSlug = $article['slug'] ?? basename(trim(parse_url($article['link'], PHP_URL_PATH), '/'));
             $articleUrl = "artikel.php?slug=" . urlencode($articleSlug);
             $articleImage = $article['image'] ?? '';

             error_log("Article {$idx}: " . ($articleImage ? "Has image: {$articleImage}" : "No image"));

             $randRot = number_format((mt_rand(-80, 80) / 10), 1);
             $randY = mt_rand(-60, 60);
             ?>
            <a href="<?= htmlspecialchars($articleUrl) ?>" 
               class="sideArticle <?= !empty($articleImage) ? 'hasImage' : '' ?>"
               style="--rot: <?= $randRot ?>deg; --y: <?= $randY ?>px; <?= !empty($articleImage) ? '--bg-image: url(\'' . htmlspecialchars($articleImage) . '\');' : '' ?>">
                <h2 class="sideArticleText"><?= htmlspecialchars($article['title']) ?></h2>
            </a>
          <?php endforeach; ?>
        </article>
      </section>
    <?php endforeach; ?>
  </main>

  <footer>

  </footer>

  <script src="js/bubbles.js"></script>
  <script src="js/script.js"></script>
</body>

</html>