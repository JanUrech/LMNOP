<?php
//header('Content-Type: application/json; charset=utf-8');
$url = 'https://wp-lmnop.janicure.ch/wp-json/wp/v2/posts';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http >= 200 && $http < 300 && $response !== false) {
} else {
  http_response_code(502);
  echo json_encode(['error' => 'Upstream fetch failed', 'http_code' => $http]);
} ?>


<?php
// filepath: c:\Users\janic\OneDrive - FH Graubünden\Journalismus Multimedial\08_Formatentwicklung\02Webseite\LMNOP Webseite\LMNOP\index.php

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
?>
<!DOCTYPE html>
<html lang="de">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LMNOP Home</title>
  <link rel="stylesheet" href="style/style.css">
  <link rel="stylesheet" href="https://use.typekit.net/hbr8dui.css">
  <link rel="icon" type="image/png" href="media/FaviconPrototype.png">
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

        <article class="heroArticle" onclick="window.location.href='<?= htmlspecialchars($overviewUrl) ?>'"
          style="cursor:pointer;">
          <p><?= htmlspecialchars($topic['description'] ?? '') ?></p>

          <?php
          // Alle Artikel der Kategorie (Fallback leeres Array)
          $articlesAll = $topic['articles'] ?? [];

          // Nach Datum absteigend sortieren (neueste zuerst). Prüft mehrere mögliche Datum-Felder.
          usort($articlesAll, function($a, $b) {
            $da = $a['date'] ?? $a['published'] ?? $a['post_date'] ?? '';
            $db = $b['date'] ?? $b['published'] ?? $b['post_date'] ?? '';
            $ta = strtotime($da) ?: 0;
            $tb = strtotime($db) ?: 0;
            return $tb <=> $ta; // newest first
          });

          // Immer die zwei neuesten Side-Articles verwenden (falls vorhanden)
          $sideArticles = array_slice($articlesAll, 0, 2);
          // Hero/Overview bleibt immer sichtbar (auch wenn $sideArticles leer)
          foreach ($sideArticles as $idx => $article):
             $articleSlug = $article['slug'] ?? basename(trim(parse_url($article['link'], PHP_URL_PATH), '/'));
             $articleUrl = "artikel.php?slug=" . urlencode($articleSlug);
             $articleImage = $article['image'] ?? '';

             // Debug output
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