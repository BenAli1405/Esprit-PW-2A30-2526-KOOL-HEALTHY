<?php
/**
 * API pour récupérer l'URL publique de ngrok
 * ngrok expose une API locale sur http://127.0.0.1:4040/api/tunnels
 * Cette API retourne les tunnels actifs avec leurs URLs publiques
 */

header('Content-Type: application/json');

// Essayer de récupérer l'URL ngrok depuis l'API locale d'ngrok
$ngrok_api = 'http://127.0.0.1:4040/api/tunnels';

try {
    $context = stream_context_create([
        'http' => [
            'timeout' => 2,
            'method' => 'GET'
        ]
    ]);
    
    $response = @file_get_contents($ngrok_api, false, $context);
    
    if ($response === false) {
        // ngrok n'est pas actif
        echo json_encode([
            'success' => false,
            'message' => 'ngrok n\'est pas en cours d\'exécution',
            'ngrok_url' => null
        ]);
        exit;
    }
    
    $data = json_decode($response, true);
    
    if (isset($data['tunnels']) && count($data['tunnels']) > 0) {
        // Chercher le tunnel HTTP (pas HTTPS)
        $ngrok_url = null;
        foreach ($data['tunnels'] as $tunnel) {
            if (strpos($tunnel['public_url'], 'https://') === 0) {
                $ngrok_url = $tunnel['public_url'];
                break;
            }
        }
        
        if ($ngrok_url) {
            echo json_encode([
                'success' => true,
                'message' => 'URL ngrok trouvée',
                'ngrok_url' => $ngrok_url,
                'status' => 'active'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Aucun tunnel ngrok accessible',
                'ngrok_url' => null
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Aucun tunnel ngrok actif',
            'ngrok_url' => null
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur : ' . $e->getMessage(),
        'ngrok_url' => null
    ]);
}
?>
