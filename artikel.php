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
$authorId = $post['author'] ?? 0;
$featuredImage = $post['_embedded']['wp:featuredmedia'][0]['source_url'] ?? '';

// Hole Autor-Daten
$authorName = 'Unbekannt';
if ($authorId > 0) {
    $authorUrl = 'https://wp-lmnop.janicure.ch/wp-json/wp/v2/users/' . $authorId;
    $chAuthor = curl_init($authorUrl);
    curl_setopt($chAuthor, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($chAuthor, CURLOPT_TIMEOUT, 5);
    $authorResponse = curl_exec($chAuthor);
    curl_close($chAuthor);
    $author = json_decode($authorResponse, true);
    if ($author && isset($author['name'])) {
        $authorName = $author['name'];
    }
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
        <p><strong>Autor:</strong> <?= htmlspecialchars($authorName) ?></p>
        <p><strong>Datum:</strong> <?= htmlspecialchars($date) ?></p>
      </div>

      <?php if ($featuredImage): ?>
        <img src="<?= htmlspecialchars($featuredImage) ?>" alt="<?= htmlspecialchars($title) ?>" class="articleFeaturedImage">
      <?php endif; ?>

      <div class="articleBody">
        <?= $content ?>
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