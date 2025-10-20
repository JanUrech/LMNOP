<?php
header('Content-Type: application/json; charset=utf-8');
$url = 'https://wp-lmnop.janicure.ch/wp-json/wp/v2/posts';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http >= 200 && $http < 300 && $response !== false) {
    echo $response;
} else {
    http_response_code(502);
    echo json_encode(['error' => 'Upstream fetch failed', 'http_code' => $http]);
}