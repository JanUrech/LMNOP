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

// Hilfsfunktion: Datum im Format "11. Januar 2025"
function formatDateLong($dateString) {
    $ts = strtotime($dateString ?: 'now');
    $months = ['Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'];
    $d = date('j', $ts);
    $m = $months[intval(date('n', $ts)) - 1];
    $y = date('Y', $ts);
    return "{$d}. {$m} {$y}";
}

$date = formatDateLong($post['date'] ?? 'now');
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

// --- NEU: Ersetze WP-File-Blöcke durch klickbare Download-Boxen ---
$pdfFiles = [];

// Suche alle wp-block-file divs und extrahiere PDF-URLs
if (preg_match_all('/<div[^>]*class=["\'][^"\']*wp-block-file[^"\']*["\'][^>]*>([\s\S]*?)<\/div>/i', $remainingContent, $matches)) {
    foreach ($matches[1] as $blockContent) {
        // Extrahiere Datei-URL - zuerst aus data attribute, dann aus href
        $url = '';
        $fileName = 'PDF Dokument';
        
        // Versuche data attribute zu finden (aus object tag)
        if (preg_match('/data=["\']([^"\']+\.pdf)["\']/', $blockContent, $urlMatch)) {
            $url = $urlMatch[1];
        }
        // Fallback: Suche href in <a> Tags
        elseif (preg_match('/href=["\']([^"\']+\.pdf)["\']/', $blockContent, $urlMatch)) {
            $url = $urlMatch[1];
        }
        
        // Extrahiere Datei-Namen aus Text zwischen <a> Tags
        if (preg_match('/<a[^>]*href=["\'][^"\']+\.pdf["\'][^>]*>([^<]+)<\/a>/i', $blockContent, $nameMatch)) {
            $fileName = trim(strip_tags($nameMatch[1]));
        }
        
        if (!empty($url)) {
            $pdfFiles[] = [
                'url' => $url,
                'name' => !empty($fileName) ? $fileName : basename($url)
            ];
        }
    }
    
    // Ersetze wp-block-file Blöcke durch neue klickbare Box-Links
    $remainingContent = preg_replace_callback(
        '/<div[^>]*class=["\'][^"\']*wp-block-file[^"\']*["\'][^>]*>([\s\S]*?)<\/div>/i',
        function($m) use (&$pdfFiles) {
            if (empty($pdfFiles)) return $m[0];
            $file = array_shift($pdfFiles); // nimm nächste PDF aus Liste
            $fileName = htmlspecialchars($file['name']);
            $fileUrl = htmlspecialchars($file['url']);
            $downloadName = basename($file['name'], '.pdf') . '.pdf';
            
            return '
            <a href="' . $fileUrl . '" class="wp-block-file-box" download="' . htmlspecialchars($downloadName) . '" title="' . $fileName . ' herunterladen">
                <div class="wp-block-file-box-content">
                    <div class="wp-block-file-box-text">
                        <div class="wp-block-file-box-name">' . $fileName . '</div>
                        <div class="wp-block-file-box-action">Herunterladen</div>
                    </div>
                </div>
            </a>
            ';
        },
        $remainingContent
    );
}

// --- ENDE WP-File-Block Umwandlung ---
?>
<!DOCTYPE html>
<html lang="de">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title) ?> - LMNOP</title>
  <link rel="stylesheet" href="style/style.css">
  <link rel="stylesheet" href="https://use.typekit.net/hbr8dui.css">
  <link rel="icon" type="image/png" href="media/FaviconPrototype.png">
</head>

<body>
  <header>
    <!-- Header wird durch script.js befüllt -->
  </header>

  <main class="articlePage">
    <article class="articleContent">
      <?php if ($featuredImage): ?>
        <img src="<?= htmlspecialchars($featuredImage) ?>" alt="<?= htmlspecialchars($title) ?>"
          class="articleFeaturedImage">
      <?php endif; ?>

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



      <div class="articleBody">
        <?= $remainingContent ?>
      </div>

      <a href="index.php" class="backLink">← Zurück zur Startseite</a>
    </article>
  </main>

  <!-- Bubble Container für animierte Hintergrund-Bubbles -->
  <div class="bubble-container">
    <img src="media/Background/bubble_vektor_05.svg" alt="" class="bubble bubble-1">
    <img src="media/Background/bubble_vektor_06.svg" alt="" class="bubble bubble-2">
    <img src="media/Background/bubble_vektor_08.svg" alt="" class="bubble bubble-3">
    <img src="media/Background/bubble_vektor_09.svg" alt="" class="bubble bubble-4">
    <img src="media/Background/bubbles_vektor_02.svg" alt="" class="bubble bubble-5">
    <img src="media/Background/bubble_vektor_05.svg" alt="" class="bubble bubble-6">
    <img src="media/Background/bubble_vektor_06.svg" alt="" class="bubble bubble-7">
  </div>

  <footer>
    <!-- Footer wird durch script.js befüllt -->
  </footer>

  <script src="js/bubbles.js"></script>
  <script src="js/script.js"></script>
</body>

</html>