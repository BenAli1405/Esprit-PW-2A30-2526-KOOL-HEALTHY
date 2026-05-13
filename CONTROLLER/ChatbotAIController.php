<?php
// CONTROLLER/ChatbotAIController.php
header('Content-Type: application/json');

// Load API key from environment or config
$apiKey = getenv('GROQ_API_KEY') ?: (defined('GROQ_API_KEY') ? GROQ_API_KEY : ''); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $messages = $input['messages'] ?? [];

    if (empty($messages)) {
        echo json_encode(['error' => 'Les messages sont vides']);
        exit;
    }

    $ch = curl_init();
    
    $systemPrompt = "Tu es un assistant de santé expert en gamification, très empathique et professionnel. Ton but est de proposer des défis de santé personnalisés.
RÈGLES DE DISCUSSION :
1. Tu dois TOUJOURS commencer par poser des questions sur la santé de l'utilisateur : a-t-il des maladies chroniques, des allergies ou des limitations physiques ?
2. Sois très attentif aux contre-indications (ex: pas de sucre pour un diabétique, pas de course pour un problème de genou).
3. Une fois que tu as compris son profil santé, propose un défi ADAPTÉ et SÉCURISÉ.
4. Tu dois TOUJOURS répondre UNIQUEMENT avec un objet JSON valide.

Format JSON obligatoire pour CHAQUE réponse :
{
  \"message\": \"Ta réponse textuelle à l'utilisateur (questions ou conseils)\",
  \"challenge_proposed\": true ou false (si tu proposes un défi adapté maintenant),
  \"titre\": \"Titre du défi adapté\",
  \"type\": \"Santé/Nutrition/Sport\",
  \"points\": 50,
  \"description\": \"Description précise du défi\"
}
N'ajoute JAMAIS de texte en dehors du JSON.";

    array_unshift($messages, ["role" => "system", "content" => $systemPrompt]);

    $data = [
        "model" => "llama-3.3-70b-versatile",
        "messages" => $messages,
        "temperature" => 0.7
    ];

    curl_setopt($ch, CURLOPT_URL, 'https://api.groq.com/openai/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);
    
    // Désactiver la vérification SSL pour XAMPP/Localhost
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $result = curl_exec($ch);
    
    if (curl_errno($ch)) {
        echo json_encode(['error' => curl_error($ch)]);
    } else {
        $response = json_decode($result, true);
        if (isset($response['choices'][0]['message']['content'])) {
            $aiContent = $response['choices'][0]['message']['content'];
            
            // Nettoyage de la réponse pour extraire uniquement le JSON
            preg_match('/\{.*\}/s', $aiContent, $matches);
            if (!empty($matches)) {
                echo $matches[0];
            } else {
                echo json_encode(['error' => 'Format de réponse IA invalide', 'raw' => $aiContent]);
            }
        } else {
            echo json_encode(['error' => 'Réponse API invalide', 'raw' => $response]);
        }
    }
    curl_close($ch);
    exit;
}
?>
