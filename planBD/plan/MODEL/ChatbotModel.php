<?php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../config.php';

class ChatbotModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Point d'entrée principal : analyse le message et retourne la réponse via API OpenRouter
     */
    public function traiterMessage(int $planId, string $message): array
    {
        // 1. Récupérer le contexte utilisateur
        $context = $this->recupererContexteUtilisateur($planId);
        
        // 2. Récupérer l'historique récent des conversations (pour la mémoire du LLM)
        $historique = $this->recupererHistorique($planId, 6); // 6 derniers messages (3 allers-retours)
        
        // 3. Construire le prompt système
        $systemPrompt = $this->construireSystemPrompt($context);
        
        // 4. Appeler l'API OpenRouter
        $apiResponse = $this->appelerOpenRouterAPI($systemPrompt, $historique, $message);
        
        // 5. Analyser la réponse (on s'attend à du JSON avec 'reponse', 'intent', 'sentiment')
        $parsed = $this->parseApiResponse($apiResponse, $message);

        // 6. Sauvegarder en BDD
        $this->sauvegarderConversation($planId, $message, $parsed['reponse'], $parsed['sentiment']);

        return [
            'reponse' => $parsed['reponse'],
            'intent' => $parsed['intent'],
            'sentiment' => $parsed['sentiment'],
            'entites' => []
        ];
    }

    private function recupererContexteUtilisateur(int $planId): array
    {
        $stmt = $this->pdo->prepare('SELECT nom, objectif, duree, preference, allergies, poids, taille, age, sexe, niveau_activite FROM plan WHERE id = ?');
        $stmt->execute([$planId]);
        $plan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$plan) {
            $plan = [];
        }

        // Stats des 7 derniers jours
        $stmt2 = $this->pdo->prepare("SELECT statut, type_repas, nom_recette, calories_consommees, date, notes FROM repas WHERE plan_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) ORDER BY date DESC, heure_prevue ASC");
        $stmt2->execute([$planId]);
        $repas = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        return [
            'plan' => $plan,
            'repas_recents' => $repas
        ];
    }

    private function construireSystemPrompt(array $context): string
    {
        $plan = $context['plan'];
        $repas = $context['repas_recents'];

        $prompt = "Tu es 'Kool Healthy', un Coach Nutritionnel expert, bienveillant et conversationnel.
Ton rôle est d'accompagner l'utilisateur dans son parcours de nutrition, de répondre à ses questions, de lui proposer des menus, de l'encourager, d'analyser ses étiquettes, etc.
Tu dois toujours répondre en français, de manière naturelle, motivante et empathique. Ne sois pas trop robotique. Utilise des emojis.

### CONTEXTE DE L'UTILISATEUR
";
        if (!empty($plan)) {
            $prompt .= "- Objectif : " . ($plan['objectif'] ?? 'Non défini') . "\n";
            $prompt .= "- Préférences : " . ($plan['preference'] ?? 'Aucune') . "\n";
            $prompt .= "- Allergies : " . ($plan['allergies'] ?? 'Aucune') . "\n";
            if (isset($plan['poids']) && !empty($plan['poids'])) {
                $prompt .= "- Profil : " . ($plan['age'] ?? '?') . " ans, " . ($plan['sexe'] ?? '?') . ", " . $plan['poids'] . " kg, " . ($plan['taille'] ?? '?') . " cm, activité : " . ($plan['niveau_activite'] ?? '?') . "\n";
            }
        } else {
            $prompt .= "Aucun plan actif trouvé.\n";
        }

        $prompt .= "\n### HISTORIQUE DES REPAS (7 derniers jours)\n";
        if (!empty($repas)) {
            $count = 0;
            foreach ($repas as $r) {
                if ($count > 10) break; // Ne pas surcharger le prompt
                $prompt .= "- " . $r['date'] . " [" . $r['statut'] . "] : " . $r['nom_recette'] . " (" . $r['type_repas'] . ")";
                if (!empty($r['calories_consommees'])) $prompt .= " - " . $r['calories_consommees'] . " kcal";
                if (!empty($r['notes'])) $prompt .= " | Note: " . $r['notes'];
                $prompt .= "\n";
                $count++;
            }
        } else {
            $prompt .= "Aucun repas enregistré récemment.\n";
        }

        $prompt .= "
### COMPÉTENCES SPÉCIALES
Tu dois être capable de :
1. GÉNÉRER DES MENUS PERSONNALISÉS adaptés à l'objectif et aux calories.
2. ANALYSER DES ÉTIQUETTES PRODUITS (calories, macros) et donner un avis.
3. CRÉER DES RECETTES à partir d'ingrédients donnés.
4. CRÉER DES DÉFIS personnalisés basés sur l'historique.
5. FAIRE UN BILAN HEBDOMADAIRE.
6. COMPARER DES ALIMENTS.

### FORMAT DE RÉPONSE EXIGÉ
Tu DOIS IMPÉRATIVEMENT répondre UNIQUEMENT avec un objet JSON valide, sans bloc markdown autour. Ne mets jamais de code markdown comme ```json au début.
Format attendu strict :
{
  \"reponse\": \"Ta réponse détaillée en texte (supporte la mise en forme basique avec **gras** et les sauts de ligne \\n).\",
  \"intent\": \"catégorie de l'intention (ex: generate_menu, ask_calories, feeling_hungry, motivation, create_recipe, compare_foods, etc.)\",
  \"sentiment\": \"sentiment_detecte (choisir STRICTEMENT parmi: fatigue, stress, coupable, manque_temps, motivation, faim, neutre)\"
}
NE RIEN AJOUTER AVANT OU APRÈS LE JSON.
";
        return $prompt;
    }

    private function appelerOpenRouterAPI(string $systemPrompt, array $historique, string $userMessage): string
    {
        $apiKey = config::getOpenRouterApiKey();
        if (empty($apiKey) || $apiKey === 'sk-or-v1-votre-cle-api-openrouter-ici') {
            return json_encode([
                'reponse' => "Désolé, la clé API OpenRouter n'est pas configurée. Veuillez l'ajouter dans `config.php`.",
                'intent' => "error",
                'sentiment' => "neutre"
            ]);
        }

        $messages = [];
        $messages[] = [
            "role" => "system",
            "content" => $systemPrompt
        ];

        // Ajouter l'historique dans le bon ordre chronologique (plus vieux au plus récent)
        foreach ($historique as $msg) {
            $messages[] = [
                "role" => "user",
                "content" => $msg['message_utilisateur']
            ];
            $messages[] = [
                "role" => "assistant",
                "content" => $msg['reponse_chatbot']
            ];
        }

        $messages[] = [
            "role" => "user",
            "content" => $userMessage
        ];

        $ch = curl_init("https://openrouter.ai/api/v1/chat/completions");
        
        // Utilisation de google/gemini-2.5-flash via OpenRouter car très performant et peu coûteux (souvent gratuit)
        $payload = json_encode([
            "model" => "google/gemini-2.5-flash",
            "messages" => $messages,
            "response_format" => ["type" => "json_object"],
            "max_tokens" => 1500 // Limite explicite pour éviter l'erreur de tokens sur les comptes gratuits
        ]);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $apiKey,
            "HTTP-Referer: http://localhost", 
            "X-Title: Kool Healthy",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            error_log("CURL Error: " . $err);
            return json_encode([
                'reponse' => "Désolé, je n'ai pas pu joindre mon cerveau IA (Erreur réseau).",
                'intent' => "error",
                'sentiment' => "neutre"
            ]);
        }

        $data = json_decode($response, true);
        if (isset($data['choices'][0]['message']['content'])) {
            return $data['choices'][0]['message']['content'];
        }

        error_log("OpenRouter API Error: " . $response);
        
        // Pour le débogage, on renvoie l'erreur de l'API
        $errorMsg = "Je suis désolé, l'IA a renvoyé une erreur : " . $response;
        return json_encode([
            'reponse' => $errorMsg,
            'intent' => "error",
            'sentiment' => "neutre"
        ]);
    }

    private function parseApiResponse(string $apiResponse, string $userMessage): array
    {
        $apiResponse = preg_replace('/```json/i', '', $apiResponse);
        $apiResponse = preg_replace('/```/i', '', $apiResponse);
        $apiResponse = trim($apiResponse);

        $parsed = json_decode($apiResponse, true);

        if ($parsed && isset($parsed['reponse'])) {
            return [
                'reponse' => $parsed['reponse'],
                'intent' => $parsed['intent'] ?? 'unknown',
                'sentiment' => $parsed['sentiment'] ?? 'neutre'
            ];
        }

        // Fallback si ce n'est pas du JSON valide
        return [
            'reponse' => $apiResponse,
            'intent' => 'unknown',
            'sentiment' => 'neutre'
        ];
    }

    private function sauvegarderConversation(int $planId, string $message, string $reponse, string $sentiment): void
    {
        try {
            $stmt = $this->pdo->prepare('INSERT INTO conversations (plan_id, message_utilisateur, reponse_chatbot, sentiment_detecte) VALUES (?, ?, ?, ?)');
            $stmt->execute([$planId, $message, $reponse, $sentiment]);
        } catch (PDOException $e) {
            error_log("Erreur sauvegarde conversation : " . $e->getMessage());
        }
    }

    public function recupererHistorique(int $planId, int $limit = 20): array
    {
        try {
            $stmt = $this->pdo->prepare('SELECT message_utilisateur, reponse_chatbot, sentiment_detecte, date_creation FROM conversations WHERE plan_id = ? ORDER BY date_creation DESC LIMIT ?');
            $stmt->bindValue(1, $planId, PDO::PARAM_INT);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            // L'API a besoin de l'ordre inversé (plus vieux en premier), et l'interface aussi
            // On inverse le résultat pour avoir l'ordre chronologique (ASC)
            return array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            return [];
        }
    }

    public function analyserNotesPlan(int $planId): array
    {
        $context = $this->recupererContexteUtilisateur($planId);
        $systemPrompt = "Tu es Kool Healthy. Analyse ces notes de repas et fais un bilan court et motivant de l'humeur de l'utilisateur. Retourne un JSON: {\"reponse\": \"...\", \"intent\":\"analyze\", \"sentiment\":\"neutre\"}";
        
        $notes = [];
        foreach ($context['repas_recents'] as $r) {
            if (!empty($r['notes'])) {
                $notes[] = $r['date'] . " : " . $r['notes'];
            }
        }
        
        if (empty($notes)) {
            return ['resume' => "Aucune note récente à analyser. Pense à ajouter des notes sur tes repas !"];
        }

        $userMessage = "Voici mes notes récentes :\n" . implode("\n", $notes) . "\nFais-moi un bilan émotionnel court.";
        $apiResponse = $this->appelerOpenRouterAPI($systemPrompt, [], $userMessage);
        $parsed = $this->parseApiResponse($apiResponse, $userMessage);
        
        return ['resume' => $parsed['reponse']];
    }
}
