<?php
// filepath: c:\Users\janic\OneDrive - FH Graubünden\Journalismus Multimedial\08_Formatentwicklung\02Webseite\LMNOP Webseite\LMNOP\PHP\transformIndex.php

// Hilfsfunktion: Extrahiere H6 (Spitzmarke)
function getH6($content) {
    $content = html_entity_decode($content);
    // Erlaube auch Tags innerhalb von H6 (wie <strong>)
    if (preg_match('/<h6[^>]*>(.*?)<\/h6>/is', $content, $matches)) {
        return trim(strip_tags($matches[1]));
    }
    return '';
}

// Hilfsfunktion: Extrahiere ersten Paragraphen (Lead)
function getFirstParagraph($content) {
    $content = html_entity_decode($content);
    if (preg_match('/<p[^>]*>(.*?)<\/p>/is', $content, $matches)) {
        return strip_tags($matches[1]);
    }
    return strip_tags($content);
}

/**
 * Hauptfunktion - kann direkt aufgerufen oder als JSON-Endpoint genutzt werden
 */
function getTransformedTopics() {
    // === PARALLELE API-REQUESTS mit curl_multi ===
    $postsUrl = 'https://wp-lmnop.janicure.ch/wp-json/wp/v2/posts?_embed&per_page=100';
    $catsUrl = 'https://wp-lmnop.janicure.ch/wp-json/wp/v2/categories?per_page=100';
    
    // Multi-Handle erstellen
    $mh = curl_multi_init();
    
    // Posts-Request
    $ch1 = curl_init($postsUrl);
    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch1, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch1, CURLOPT_FOLLOWLOCATION, true);
    curl_multi_add_handle($mh, $ch1);
    
    // Categories-Request
    $ch2 = curl_init($catsUrl);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true);
    curl_multi_add_handle($mh, $ch2);
    
    // Beide Requests parallel ausführen
    $running = null;
    do {
        curl_multi_exec($mh, $running);
        curl_multi_select($mh);
    } while ($running > 0);
    
    // Ergebnisse holen
    $postsJson = curl_multi_getcontent($ch1);
    $catsJson = curl_multi_getcontent($ch2);
    
    // Cleanup
    curl_multi_remove_handle($mh, $ch1);
    curl_multi_remove_handle($mh, $ch2);
    curl_close($ch1);
    curl_close($ch2);
    curl_multi_close($mh);
    
    // Posts verarbeiten
    $posts = json_decode($postsJson, true);
    if (!is_array($posts)) {
        return [];
    }
    
    // Categories verarbeiten
    $categories = json_decode($catsJson, true) ?: [];
    $catMap = [];
    $catSlugMap = [];
    $overviewCatId = null;
    
    foreach ($categories as $cat) {
        $catMap[$cat['id']] = $cat['name'];
        $catSlugMap[$cat['id']] = $cat['slug'];
        if (strtolower($cat['name']) === 'overview') {
            $overviewCatId = $cat['id'];
        }
    }
    
    // Trenne Overview-Posts und normale Posts in einem Durchlauf
    $overviewPosts = [];
    $grouped = [];
    
    foreach ($posts as $post) {
        $cats = $post['categories'] ?? [];
        $isOverview = in_array($overviewCatId, $cats, true);
        
        if ($isOverview) {
            $overviewPosts[] = $post;
        } else {
            // Direkt gruppieren statt zweimal iterieren
            foreach ($cats as $catId) {
                if ($catId !== $overviewCatId) {
                    $grouped[$catId][] = $post;
                }
            }
        }
    }
    
    // Finde neueste Overview-Post pro Category
    // Overview-Posts haben ZWEI Kategorien: Overview + die zugehörige Themen-Kategorie
    $overviewData = [];
    
    foreach ($overviewPosts as $op) {
        $content = $op['content']['rendered'] ?? '';
        $date = strtotime($op['date'] ?? '1970-01-01');
        $postCategories = $op['categories'] ?? [];
        
        // Finde die Nicht-Overview-Kategorie dieses Posts
        foreach ($postCategories as $catId) {
            // Überspringe die Overview-Kategorie selbst
            if ($catId === $overviewCatId) {
                continue;
            }
            
            // Prüfe ob diese Kategorie in unseren gruppierten Posts existiert
            if (isset($grouped[$catId])) {
                // Nur speichern wenn neuer als vorhandene Daten
                if (!isset($overviewData[$catId]) || $date > $overviewData[$catId]['date']) {
                    $overviewData[$catId] = [
                        'spitzmarke' => getH6($content),
                        'lead'       => getFirstParagraph($content),
                        'date'       => $date
                    ];
                }
            }
        }
    }
    
    // Baue Zielstruktur
    $result = [];
    foreach ($grouped as $catId => $postsInCat) {
        $articles = [];
        foreach ($postsInCat as $p) {
            $imageUrl = $p['_embedded']['wp:featuredmedia'][0]['source_url'] ?? '';
            $articles[] = [
                'title' => $p['title']['rendered'] ?? '',
                'slug'  => $p['slug'] ?? '',
                'link'  => $p['link'] ?? '',
                'image' => $imageUrl,
                'date'  => $p['date'] ?? ''
            ];
        }
        
        $result[] = [
            'title'       => $catMap[$catId] ?? '',
            'slug'        => $catSlugMap[$catId] ?? '',
            'spitzmarke'  => $overviewData[$catId]['spitzmarke'] ?? '',
            'lead'        => $overviewData[$catId]['lead'] ?? '',
            'articles'    => $articles
        ];
    }
    
    return $result;
}

// Wenn direkt aufgerufen (als API-Endpoint), JSON ausgeben
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'transformIndex.php') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(getTransformedTopics(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}
?>
