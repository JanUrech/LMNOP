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
</head>

<body>
  <header>
    <!-- Header wird durch script.js befüllt -->
  </header>

  <main>
    <menu class="menuIndex">
      <?php foreach ($topics as $topic): 
        $urlTitle = strtolower(preg_replace('/[^a-z0-9]+/', '-', $topic['title']));
        $urlTitle = trim($urlTitle, '-');
      ?>
        <li class="menuIndexItem"><a href="#<?= htmlspecialchars($urlTitle) ?>"><?= htmlspecialchars($topic['title']) ?></a></li>
      <?php endforeach; ?>
    </menu>

    <?php foreach ($topics as $topic): 
      $urlTitle = strtolower(preg_replace('/[^a-z0-9]+/', '-', $topic['title']));
      $urlTitle = trim($urlTitle, '-');
      $overviewUrl = "{$urlTitle}.html";
    ?>
      <section class="topicSection" id="<?= htmlspecialchars($urlTitle) ?>">
        <h1 class="topicTitle"><?= htmlspecialchars($topic['title']) ?></h1>
        
        <article class="heroArticle" onclick="window.location.href='<?= htmlspecialchars($overviewUrl) ?>'" style="cursor:pointer;">
          <p><?= htmlspecialchars($topic['description'] ?? '') ?></p>
          
          <?php 
          $sideArticles = array_slice($topic['articles'] ?? [], 0, 2);
          foreach ($sideArticles as $idx => $article): 
            $randRot = number_format((mt_rand(-80, 80) / 10), 1);
            $posX = ($idx === 0) ? '0%' : '85%';
            $randY = mt_rand(-60, 60);
          ?>
            <article class="sideArticle" style="--rot:<?= $randRot ?>deg; --posX:<?= $posX ?>; --y:<?= $randY ?>px;">
              <h2 class="sideArticleText"><?= htmlspecialchars($article['title']) ?></h2>
            </article>
          <?php endforeach; ?>
        </article>
      </section>
    <?php endforeach; ?>
  </main>

  <footer>
    <!-- Footer wird durch script.js befüllt -->
  </footer>

  <script src="js/script.js"></script>
</body>

</html>