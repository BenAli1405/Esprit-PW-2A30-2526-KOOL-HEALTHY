<?php
header('Content-Type: application/json');

// 1. Essayer l'API ngrok locale (port 4040)
$ngrokApi = @file_get_contents('http://127.0.0.1:4040/api/tunnels');
if ($ngrokApi) {
    $data = json_decode($ngrokApi, true);
    foreach ($data['tunnels'] ?? [] as $tunnel) {
        if (($tunnel['proto'] ?? '') === 'https') {
            echo json_encode(['success' => true, 'ngrok_url' => $tunnel['public_url']]);
            exit;
        }
    }
}

// 2. Lire tunnel_url.txt (localhost.run ou autre)
$txtFile = __DIR__ . '/tunnel_url.txt';
if (file_exists($txtFile)) {
    $url = trim(file_get_contents($txtFile));
    if ($url) {
        echo json_encode(['success' => true, 'ngrok_url' => $url]);
        exit;
    }
}

// 3. Aucun tunnel disponible
echo json_encode(['success' => false, 'ngrok_url' => null]);
