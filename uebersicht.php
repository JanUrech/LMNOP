<?php
// filepath: c:\Users\janic\OneDrive - FH Graubünden\Journalismus Multimedial\08_Formatentwicklung\02Webseite\LMNOP Webseite\LMNOP\uebersicht.php

// Hole Category-Slug aus URL
$categorySlug = $_GET['slug'] ?? '';
if (empty($categorySlug)) {
  header('Location: index.php');
  exit;
}

// === CACHING SYSTEM ===
$cacheFile = __DIR__ . '/cache/overview_' . md5($categorySlug) . '.json';
$cacheTime = 300; // 5 Minuten Cache

$cachedData = null;

// Prüfe ob Cache existiert und noch gültig ist
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
  // Cache ist gültig - direkt laden
  $cachedData = json_decode(file_get_contents($cacheFile), true);
}

if ($cachedData) {
  // Aus Cache laden
  $category = $cachedData['category'];
  $categoryId = $cachedData['categoryId'];
  $posts = $cachedData['posts'];
  $overviewCatId = $cachedData['overviewCatId'];
} else {
  // Von API laden - PARALLEL mit curl_multi für bessere Performance
  $catUrl = 'https://wp-lmnop.janicure.ch/wp-json/wp/v2/categories?slug=' . urlencode($categorySlug);
  $overviewCatUrl = 'https://wp-lmnop.janicure.ch/wp-json/wp/v2/categories?slug=overview';
  
  // Multi-Handle erstellen
  $mh = curl_multi_init();
  
  // Request 1: Category
  $ch1 = curl_init($catUrl);
  curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch1, CURLOPT_TIMEOUT, 5);
  curl_setopt($ch1, CURLOPT_CONNECTTIMEOUT, 3);
  curl_setopt($ch1, CURLOPT_ENCODING, 'gzip');
  curl_setopt($ch1, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
  curl_setopt($ch1, CURLOPT_TCP_KEEPALIVE, 1);
  curl_setopt($ch1, CURLOPT_DNS_CACHE_TIMEOUT, 600);
  curl_multi_add_handle($mh, $ch1);
  
  // Request 2: Overview Category
  $ch2 = curl_init($overviewCatUrl);
  curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch2, CURLOPT_TIMEOUT, 5);
  curl_setopt($ch2, CURLOPT_CONNECTTIMEOUT, 3);
  curl_setopt($ch2, CURLOPT_ENCODING, 'gzip');
  curl_setopt($ch2, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
  curl_setopt($ch2, CURLOPT_TCP_KEEPALIVE, 1);
  curl_setopt($ch2, CURLOPT_DNS_CACHE_TIMEOUT, 600);
  curl_multi_add_handle($mh, $ch2);
  
  // Beide Requests parallel ausführen
  $running = null;
  do {
    curl_multi_exec($mh, $running);
    curl_multi_select($mh);
  } while ($running > 0);
  
  // Ergebnisse holen
  $catResponse = curl_multi_getcontent($ch1);
  $ovResponse = curl_multi_getcontent($ch2);
  
  // Cleanup
  curl_multi_remove_handle($mh, $ch1);
  curl_multi_remove_handle($mh, $ch2);
  curl_close($ch1);
  curl_close($ch2);
  curl_multi_close($mh);

  // Category verarbeiten
  $category = null;
  $categoryId = null;
  if ($catResponse) {
    $cats = json_decode($catResponse, true);
    if (!empty($cats) && is_array($cats)) {
      $category = $cats[0];
      $categoryId = $category['id'];
    }
  }

  if (!$category) {
    header('HTTP/1.0 404 Not Found');
    echo '<h1>Thema nicht gefunden</h1>';
    exit;
  }
  
  // Overview Category ID
  $overviewCatId = null;
  if ($ovResponse) {
    $ovCats = json_decode($ovResponse, true);
    if (!empty($ovCats) && is_array($ovCats)) {
      $overviewCatId = $ovCats[0]['id'];
    }
  }

  // Jetzt Posts laden (muss nach Category ID bekannt ist)
  $postsUrl = 'https://wp-lmnop.janicure.ch/wp-json/wp/v2/posts?categories=' . $categoryId . '&_embed&per_page=100';
  $ch3 = curl_init($postsUrl);
  curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch3, CURLOPT_TIMEOUT, 5);
  curl_setopt($ch3, CURLOPT_CONNECTTIMEOUT, 3);
  curl_setopt($ch3, CURLOPT_ENCODING, 'gzip');
  curl_setopt($ch3, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
  curl_setopt($ch3, CURLOPT_TCP_KEEPALIVE, 1);
  curl_setopt($ch3, CURLOPT_DNS_CACHE_TIMEOUT, 600);
  $postsResponse = curl_exec($ch3);
  curl_close($ch3);

  $posts = [];
  if ($postsResponse) {
    $posts = json_decode($postsResponse, true) ?: [];
  }
  
  // Cache-Verzeichnis erstellen falls nicht vorhanden
  if (!is_dir(__DIR__ . '/cache')) {
    mkdir(__DIR__ . '/cache', 0755, true);
  }
  
  // In Cache speichern
  $cacheData = [
    'category' => $category,
    'categoryId' => $categoryId,
    'posts' => $posts,
    'overviewCatId' => $overviewCatId
  ];
  file_put_contents($cacheFile, json_encode($cacheData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

// Trenne Overview-Post und normale Posts
$overviewPost = null;
$normalPosts = [];
foreach ($posts as $post) {
  $postCats = $post['categories'] ?? [];
  if ($overviewCatId && in_array($overviewCatId, $postCats, true)) {
    // Nimm neuesten Overview-Post
    if (!$overviewPost || strtotime($post['date']) > strtotime($overviewPost['date'])) {
      $overviewPost = $post;
    }
  } else {
    $normalPosts[] = $post;
  }
}

// Hilfsfunktion: Datum im Format "11. Januar 2025"
function formatDateLong($dateString) {
    $ts = strtotime($dateString ?: 'now');
    $months = ['Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'];
    $d = date('j', $ts);
    $m = $months[intval(date('n', $ts)) - 1];
    $y = date('Y', $ts);
    return "{$d}. {$m} {$y}";
}

$categoryName = $category['name'] ?? 'Übersicht';
$overviewTitle = $overviewPost['title']['rendered'] ?? $categoryName;
$overviewContent = $overviewPost['content']['rendered'] ?? '';
$overviewImage = $overviewPost['_embedded']['wp:featuredmedia'][0]['source_url'] ?? '';
$overviewDate = formatDateLong($overviewPost['date'] ?? 'now');

// Trenne ersten Paragraphen vom Rest (für Overview)
$firstParagraph = '';
$remainingContent = $overviewContent;

if (preg_match('/<p[^>]*>(.*?)<\/p>/is', $overviewContent, $matches, PREG_OFFSET_CAPTURE)) {
  $firstParagraph = $matches[0][0];
  $position = $matches[0][1];
  $length = strlen($matches[0][0]);
  $remainingContent = substr($overviewContent, 0, $position) . substr($overviewContent, $position + $length);
}

// Hole Tag-Namen vom Overview-Post
$tags = [];
if (!empty($overviewPost['_embedded']['wp:term'])) {
  foreach ($overviewPost['_embedded']['wp:term'] as $termGroup) {
    foreach ($termGroup as $term) {
      if (isset($term['taxonomy']) && $term['taxonomy'] === 'post_tag') {
        $tags[] = strtolower(trim($term['name']));
      }
    }
  }
}

// Lade authorsList.json
$authorsJson = file_get_contents(__DIR__ . '/Data/authorsList.json');
$allAuthors = json_decode($authorsJson, true) ?: [];

// Finde Autoren, deren Namen in den Tags vorkommen (mit Foto)
$articleAuthors = [];
foreach ($allAuthors as $author) {
  $authorName = strtolower(trim($author['Name']));
  if (in_array($authorName, $tags)) {
    $articleAuthors[] = [
      'name' => $author['Name'],
      'photo' => $author['fotoStandard'] ?? ''
    ];
  }
}

// Generiere HTML für Autoren-Liste
$authorsHTML = '';
if (!empty($articleAuthors)) {
  foreach ($articleAuthors as $author) {
    $authorsHTML .= '<div class="authorItem">';
    if (!empty($author['photo'])) {
      $authorsHTML .= '<img src="' . htmlspecialchars($author['photo']) . '" alt="' . htmlspecialchars($author['name']) . '" class="authorPhoto">';
    }
    $authorsHTML .= '<span class="authorName">' . htmlspecialchars($author['name']) . '</span>';
    $authorsHTML .= '</div>';
  }
} else {
  $authorsHTML = '<span>Unbekannt</span>';
}

// Hilfsfunktion: Extrahiere ersten Paragraphen aus Content
function getFirstParagraph($content) {
  if (preg_match('/<p[^>]*>(.*?)<\/p>/is', $content, $matches)) {
    return strip_tags($matches[1]);
  }
  return strip_tags($content);
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($categoryName) ?> - LMNOP</title>
  <link rel="stylesheet" href="style/style.css">
  <link rel="stylesheet" href="https://use.typekit.net/hbr8dui.css">
  <link rel="icon" type="image/png" href="media/FaviconPrototype.png">
</head>


<body>
  <header>
    <!-- Header wird durch script.js befüllt -->
  </header>

  <main class="overviewPage">
    <article class="overviewContent">
      <h1><?= htmlspecialchars($overviewTitle) ?></h1>

      <div class="articleMeta">
        <div class="authorSection">
          <div class="authorList">
            <?= $authorsHTML ?>
          </div>
        </div>
      </div>

      <?php if ($firstParagraph): ?>
        <div class="overviewLead">
          <?= $firstParagraph ?>
        </div>
      <?php endif; ?>

      <?php if ($overviewImage): ?>
        <img src="<?= htmlspecialchars($overviewImage) ?>" alt="<?= htmlspecialchars($overviewTitle) ?>"
          class="overviewFeaturedImage">
      <?php endif; ?>

      <div class="overviewBody">
        <?= $remainingContent ?>
      </div>

      <h2>Alle Artikel zu <?= htmlspecialchars($categoryName) ?></h2>

      <div class="articleList">
        <?php foreach ($normalPosts as $post):
          $postSlug = $post['slug'] ?? basename(trim(parse_url($post['link'], PHP_URL_PATH), '/'));
          $postTitle = $post['title']['rendered'] ?? 'Kein Titel';
          $postContent = $post['content']['rendered'] ?? '';
          $postExcerpt = getFirstParagraph($postContent); // Nutze ersten Absatz statt WP-Excerpt
          $postImage = $post['_embedded']['wp:featuredmedia'][0]['source_url'] ?? '';
          $postDate = formatDateLong($post['date'] ?? 'now');
          ?>
          <a href="artikel.php?slug=<?= urlencode($postSlug) ?>" class="articlePreview">
            <?php if ($postImage): ?>
              <img src="<?= htmlspecialchars($postImage) ?>" alt="<?= htmlspecialchars($postTitle) ?>"
                class="previewImage">
            <?php endif; ?>
            <div class="previewContent">
              <h3><?= htmlspecialchars($postTitle) ?></h3>
              <p class="previewDate"><?= htmlspecialchars($postDate) ?></p>
              <p><?= htmlspecialchars($postExcerpt) ?></p>
            </div>
          </a>
        <?php endforeach; ?>
      </div>

      <a href="index.php" class="backLink">← Zurück zur Startseite</a>
    </article>
  </main>



  <footer>
    <!-- Footer wird durch script.js befüllt -->
  </footer>

  <script src="js/bubbles.js"></script>
  <script src="js/script.js"></script>
</body>

</html>

<style>
.overviewPage h6 {
    display: none;
}
</style>