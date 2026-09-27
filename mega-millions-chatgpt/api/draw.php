<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=300');

$cacheFile = __DIR__ . '/draw.json';
$cacheLifetime = 300; // 5 minutos de cache

// Se o cache existir e for recente (menos de 5 minutos), usa o cache
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheLifetime)) {
    echo file_get_contents($cacheFile);
    exit;
}

// Tenta buscar o sorteio mais recente online
$apiUrl = 'https://mega-millions-play.netlify.app/api/draw';
$context = stream_context_create([
    'http' => [
        'timeout' => 4,
        'header' => "User-Agent: Mozilla/5.0 (compatible; MegaMillionsProxy/1.0)\r\nAccept: application/json\r\n"
    ]
]);

$response = @file_get_contents($apiUrl, false, $context);

if ($response && ($json = json_decode($response, true)) && isset($json['annuity'])) {
    @file_put_contents($cacheFile, $response);
    echo $response;
} elseif (file_exists($cacheFile)) {
    // Fallback para cache existente
    echo file_get_contents($cacheFile);
} elseif (file_exists(__DIR__ . '/draw')) {
    // Fallback para arquivo original
    echo file_get_contents(__DIR__ . '/draw');
} else {
    echo json_encode([
        'annuity' => '$300 Million',
        'cash' => '$124.1 million',
        'nextLabel' => 'Tuesday @ 11 p.m. ET',
        'source' => 'fallback'
    ]);
}
