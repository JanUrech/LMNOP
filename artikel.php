<?php
// filepath: c:\Users\janic\OneDrive - FH Graubünden\Journalismus Multimedial\08_Formatentwicklung\02Webseite\LMNOP Webseite\LMNOP\artikel.php

// Hole Slug aus URL
$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
  header('Location: index.php');
  exit;
}

// Hole Post-Daten von WP API
$postUrl = 'https://wp-lmnop.janicure.ch/wp-json/wp/v2/posts?slug=' . urlencode($slug) . '&_embed';
$ch = curl_init($postUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$post = null;
if ($http >= 200 && $http < 300 && $response) {
  $posts = json_decode($response, true);
  if (!empty($posts) && is_array($posts)) {
    $post = $posts[0];
  }
}

if (!$post) {
  header('HTTP/1.0 404 Not Found');
  echo '<h1>Artikel nicht gefunden</h1>';
  exit;
}

// Daten extrahieren
$title = $post['title']['rendered'] ?? 'Kein Titel';
$content = $post['content']['rendered'] ?? '';
$excerpt = $post['excerpt']['rendered'] ?? '';
$date = date('d.m.Y', strtotime($post['date'] ?? 'now'));
$featuredImage = $post['_embedded']['wp:featuredmedia'][0]['source_url'] ?? '';

// Trenne ersten Paragraphen vom Rest
$firstParagraph = '';
$remainingContent = $content;

// Finde ersten <p>-Tag
if (preg_match('/<p[^>]*>(.*?)<\/p>/is', $content, $matches, PREG_OFFSET_CAPTURE)) {
  $firstParagraph = $matches[0][0]; // Kompletter <p>-Tag mit Inhalt
  $position = $matches[0][1]; // Position im String
  $length = strlen($matches[0][0]);
  
  // Entferne ersten Paragraphen aus dem restlichen Content
  $remainingContent = substr($content, 0, $position) . substr($content, $position + $length);
}

// Hole Tag-Namen (bereits in _embed enthalten)
$tags = [];
if (!empty($post['_embedded']['wp:term'])) {
  foreach ($post['_embedded']['wp:term'] as $termGroup) {
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
?>
<!DOCTYPE html>
<html lang="de">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title) ?> - LMNOP</title>
  <link rel="stylesheet" href="style/style.css">
  <link rel="stylesheet" href="https://use.typekit.net/hbr8dui.css">
</head>

<body>
  <header>
    <!-- Header wird durch script.js befüllt -->
  </header>

  <main class="articlePage">
    <article class="articleContent">
      <h1><?= htmlspecialchars($title) ?></h1>

      <div class="articleMeta">
        <div class="authorSection">
          <div class="authorList">
            <?= $authorsHTML ?>
          </div>
        </div>
        <p id="articleDate"> <?= htmlspecialchars($date) ?></p>
      </div>

      <?php if ($firstParagraph): ?>
        <div class="articleLead">
          <?= $firstParagraph ?>
        </div>
      <?php endif; ?>

      <?php if ($featuredImage): ?>
        <img src="<?= htmlspecialchars($featuredImage) ?>" alt="<?= htmlspecialchars($title) ?>"
          class="articleFeaturedImage">
      <?php endif; ?>

      <div class="articleBody">
        <?= $remainingContent ?>
      </div>

      <a href="index.php" class="backLink">← Zurück zur Startseite</a>
    </article>
  </main>

  <footer>
    <!-- Footer wird durch script.js befüllt -->
  </footer>

  <script src="js/script.js"></script>
</body>

</html>