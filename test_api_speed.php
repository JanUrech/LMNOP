<?php
// Test-Skript um API-Performance zu messen

$testUrls = [
    'Single Post' => 'https://wp-lmnop.janicure.ch/wp-json/wp/v2/posts?slug=test&_embed',
    'Category' => 'https://wp-lmnop.janicure.ch/wp-json/wp/v2/categories?slug=test',
    'Posts by Category' => 'https://wp-lmnop.janicure.ch/wp-json/wp/v2/posts?per_page=10&_embed'
];

echo "<h1>API Performance Test</h1>";
echo "<style>body{font-family:Arial;padding:20px}table{border-collapse:collapse;width:100%}th,td{border:1px solid #ddd;padding:8px;text-align:left}th{background:#f2f2f2}</style>";
echo "<table><tr><th>Request</th><th>DNS Lookup</th><th>Connect</th><th>Total Time</th><th>HTTP Code</th></tr>";

foreach ($testUrls as $name => $url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    // Performance-Messung aktivieren
    curl_setopt($ch, CURLOPT_CERTINFO, true);
    
    $start = microtime(true);
    $response = curl_exec($ch);
    $end = microtime(true);
    
    $info = curl_getinfo($ch);
    curl_close($ch);
    
    $totalTime = round(($end - $start) * 1000, 2);
    $dnsTime = round($info['namelookup_time'] * 1000, 2);
    $connectTime = round($info['connect_time'] * 1000, 2);
    $httpCode = $info['http_code'];
    
    $color = $totalTime > 1000 ? '#ffcccc' : ($totalTime > 500 ? '#ffffcc' : '#ccffcc');
    
    echo "<tr style='background:$color'>";
    echo "<td><strong>$name</strong><br><small>" . substr($url, 0, 60) . "...</small></td>";
    echo "<td>{$dnsTime}ms</td>";
    echo "<td>{$connectTime}ms</td>";
    echo "<td><strong>{$totalTime}ms</strong></td>";
    echo "<td>$httpCode</td>";
    echo "</tr>";
}

echo "</table>";
echo "<br><p><strong>Legende:</strong> Grün = schnell (&lt;500ms), Gelb = mittel (500-1000ms), Rot = langsam (&gt;1000ms)</p>";
?>
