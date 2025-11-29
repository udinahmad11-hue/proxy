<?php
// index.php - ULTRA FAST PROXY
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: *');

// ⚡ FAST OPTIONS HANDLING
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// ⚡ FAST VALIDATION
if (!isset($_GET['url']) || empty($_GET['url'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    die(json_encode([
        'error' => 'URL parameter required',
        'usage' => '/index.php?url=https://example.com',
        'example' => '/index.php?url=https://api.github.com/users/octocat'
    ]));
}

$targetUrl = $_GET['url'];

// ⚡ FAST URL PROCESSING
if (!preg_match('/^https?:\/\//', $targetUrl)) {
    $targetUrl = 'https://' . $targetUrl;
}

// ⚡ FAST CURL INIT
$ch = curl_init();

// ⚡ MINIMAL CURL OPTIONS FOR MAXIMUM SPEED
curl_setopt_array($ch, [
    CURLOPT_URL => $targetUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    CURLOPT_ENCODING => '' // Auto-decode gzip/brotli
]);

// ⚡ FORWARD ESSENTIAL HEADERS ONLY
$forwardHeaders = [];
$skipHeaders = ['host', 'connection', 'accept-encoding'];

foreach (getallheaders() as $name => $value) {
    if (!in_array(strtolower($name), $skipHeaders)) {
        $forwardHeaders[] = "$name: $value";
    }
}

if (!empty($forwardHeaders)) {
    curl_setopt($ch, CURLOPT_HTTPHEADER, $forwardHeaders);
}

// ⚡ HANDLE POST DATA
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents('php://input'));
}

// ⚡ EXECUTE & GET RESPONSE
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

if (curl_error($ch)) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Proxy fetch failed',
        'message' => curl_error($ch),
        'url' => $targetUrl
    ]);
} else {
    // ⚡ FORWARD CONTENT TYPE
    if ($contentType) {
        header("Content-Type: $contentType");
    }
    
    http_response_code($httpCode);
    echo $response;
}

curl_close($ch);
?>
