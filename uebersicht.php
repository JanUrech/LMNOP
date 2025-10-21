<?php
// filepath: c:\Users\janic\OneDrive - FH Graubünden\Journalismus Multimedial\08_Formatentwicklung\02Webseite\LMNOP Webseite\LMNOP\übersicht.php

// Hole Category-Slug aus URL
$categorySlug = $_GET['slug'] ?? '';
if (empty($categorySlug)) {
    header('Location: index.php');
    exit;
}

// Hole Category ID
$catUrl = 'https://wp-lmnop.janicure.ch/wp-json/wp/v2/categories?slug=' . urlencode($categorySlug);
$ch = curl_init($catUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$catResponse = curl_exec($ch);
curl_close($ch);

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

// Hole alle Posts dieser Category
$postsUrl = 'https://wp-lmnop.janicure.ch/wp-json/wp/v2/posts?categories=' . $categoryId . '&_embed&per_page=100';
$ch2 = curl_init($postsUrl);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_TIMEOUT, 10);
$postsResponse = curl_exec($ch2);
curl_close($ch2);

$posts = [];
if ($postsResponse) {
    $posts = json_decode($postsResponse, true) ?: [];
}

// Hole Overview-Category ID
$overviewCatUrl = 'https://wp-lmnop.janicure.ch/wp-json/wp/v2/categories?slug=overview';
$chOv = curl_init($overviewCatUrl);
curl_setopt($chOv, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chOv, CURLOPT_TIMEOUT, 5);
$ovResponse = curl_exec($chOv);
curl_close($chOv);

$overviewCatId = null;
if ($ovResponse) {
    $ovCats = json_decode($ovResponse, true);
    if (!empty($ovCats) && is_array($ovCats)) {
        $overviewCatId = $ovCats[0]['id'];
    }
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

$categoryName = $category['name'] ?? 'Übersicht';
$overviewTitle = $overviewPost['title']['rendered'] ?? $categoryName;
$overviewContent = $overviewPost['content']['rendered'] ?? '';
$overviewImage = $overviewPost['_embedded']['wp:featuredmedia'][0]['source_url'] ?? '';
?>
<!DOCTYPE html>
<html lang="de">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($categoryName) ?> - LMNOP</title>
  <link rel="stylesheet" href="style/style.css">
  <link rel="stylesheet" href="https://use.typekit.net/hbr8dui.css">
</head>

<body>
  <header>
    <!-- Header wird durch script.js befüllt -->
  </header>

  <main class="overviewPage">
    <article class="overviewContent">
      <h1><?= htmlspecialchars($overviewTitle) ?></h1>

      <?php if ($overviewImage): ?>
        <img src="<?= htmlspecialchars($overviewImage) ?>" alt="<?= htmlspecialchars($overviewTitle) ?>" class="overviewFeaturedImage">
      <?php endif; ?>

      <div class="overviewBody">
        <?= $overviewContent ?>
      </div>

      <h2>Alle Artikel zu <?= htmlspecialchars($categoryName) ?></h2>
      
      <div class="articleList">
        <?php foreach ($normalPosts as $post): 
          $postSlug = basename(trim(parse_url($post['link'], PHP_URL_PATH), '/'));
          $postTitle = $post['title']['rendered'] ?? 'Kein Titel';
          $postExcerpt = strip_tags($post['excerpt']['rendered'] ?? '');
          $postImage = $post['_embedded']['wp:featuredmedia'][0]['source_url'] ?? '';
          $postDate = date('d.m.Y', strtotime($post['date'] ?? 'now'));
        ?>
          <article class="articlePreview">
            <?php if ($postImage): ?>
              <img src="<?= htmlspecialchars($postImage) ?>" alt="<?= htmlspecialchars($postTitle) ?>" class="previewImage">
            <?php endif; ?>
            <div class="previewContent">
              <h3><a href="artikel.php?slug=<?= urlencode($postSlug) ?>"><?= htmlspecialchars($postTitle) ?></a></h3>
              <p class="previewDate"><?= htmlspecialchars($postDate) ?></p>
              <p><?= htmlspecialchars($postExcerpt) ?></p>
            </div>
          </article>
        <?php endforeach; ?>
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