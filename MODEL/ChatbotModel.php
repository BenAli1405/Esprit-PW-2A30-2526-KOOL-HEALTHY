<?php

include_once __DIR__ . '/Database.php';

class ChatbotModel
{
    private $pdo;

    /**
     * Dictionnaires de mots-clés par catégorie de sentiment.
     * Chaque catégorie contient des mots/expressions à détecter dans les notes ou messages.
     */
    private $sentimentKeywords = [
        'fatigue' => [
            'fatigue', 'fatigué', 'fatiguée', 'épuisé', 'épuisée', 'crevé', 'crevée',
            'manque énergie', 'pas d\'énergie', 'sans énergie', 'exténué', 'exténuée',
            'énergie basse', 'coup de barre', 'sommeil', 'dormir', 'insomnie',
            'somnolent', 'somnolente', 'lessivé', 'lessivée'
        ],
        'stress' => [
            'stress', 'stressé', 'stressée', 'anxieux', 'anxieuse', 'angoisse',
            'nerveux', 'nerveuse', 'tendu', 'tendue', 'pression', 'surcharge',
            'débordé', 'débordée', 'inquiet', 'inquiète', 'paniqué', 'paniquée',
            'burnout', 'craqué', 'craquée', 'submergé', 'submergée'
        ],
        'coupable' => [
            'coupable', 'culpabilité', 'honte', 'regret', 'regrette', 'trop mangé',
            'craqué', 'craquage', 'excès', 'excessif', 'triche', 'triché',
            'pas respecté', 'écart', 'dérapé', 'dérapage', 'rechute',
            'mauvaise conscience', 'me sens mal', 'pas fier', 'pas fière'
        ],
        'manque_temps' => [
            'pas le temps', 'manque de temps', 'pressé', 'pressée', 'rush',
            'débordé', 'trop occupé', 'trop occupée', 'agenda chargé', 'rapide',
            'en retard', 'pas eu le temps', 'bâclé', 'vite fait', 'sur le pouce',
            'pas cuisiné', 'pas préparé', 'sauté un repas', 'skip', 'zappé'
        ],
        'motivation' => [
            'motivé', 'motivée', 'motivation', 'content', 'contente', 'fier', 'fière',
            'satisfait', 'satisfaite', 'progrès', 'réussi', 'bravo', 'bien mangé',
            'respecté', 'objectif atteint', 'en forme', 'super', 'génial', 'top',
            'heureux', 'heureuse', 'confiant', 'confiante', 'encouragé', 'encouragée'
        ],
        'faim' => [
            'faim', 'affamé', 'affamée', 'fringale', 'envie de manger', 'grignoter',
            'grignotage', 'snack', 'gourmandise', 'tentant', 'tentation', 'irrésistible',
            'craquer', 'envie sucrée', 'envie salée', 'estomac qui gargouille'
        ]
    ];

    /**
     * Réponses personnalisées par sentiment détecté.
     * Chaque sentiment possède un ensemble de réponses variées.
     */
    private $responses = [
        'fatigue' => [
            "😴 Je remarque que vous ressentez de la fatigue. C'est peut-être un signal que votre corps a besoin de plus de nutriments énergétiques. Pensez à intégrer des glucides complexes (patates douces, riz complet) et des aliments riches en fer (épinards, lentilles).",
            "💤 La fatigue peut être liée à votre alimentation. Assurez-vous de bien vous hydrater (1,5L minimum) et d'avoir un apport suffisant en vitamines B et en magnésium. Un en-cas avec des amandes peut aider !",
            "🌿 Quand on se sent épuisé, c'est souvent le corps qui demande du repos ET une meilleure nutrition. Essayez d'ajouter des fruits secs et des graines à vos collations pour un boost d'énergie naturel."
        ],
        'stress' => [
            "🧘 Le stress peut fortement impacter vos habitudes alimentaires. Prenez un moment pour respirer profondément. Côté nutrition, les aliments riches en magnésium (chocolat noir 70%, bananes, noix) aident à réguler le stress.",
            "💆 Je sens que vous traversez une période de stress. C'est normal que cela affecte votre plan. N'hésitez pas à adapter temporairement vos repas : des plats simples et réconfortants mais sains, comme une soupe maison.",
            "🍵 Le stress peut pousser au grignotage. Préparez-vous une tisane apaisante (camomille, verveine) et gardez des encas sains à portée de main : fruits frais, yaourt nature, bâtonnets de légumes."
        ],
        'coupable' => [
            "💚 Ne soyez pas trop dur(e) avec vous-même ! Un écart ne ruine pas tout votre travail. L'important, c'est la tendance générale, pas un repas isolé. Reprenez simplement au prochain repas, sans culpabilité.",
            "🌱 La culpabilité est contre-productive. Chaque repas est une nouvelle opportunité de faire un bon choix. Concentrez-vous sur le positif : vous êtes ici, vous suivez votre plan, et c'est déjà formidable !",
            "🤗 Un craquage, ça arrive à tout le monde ! L'essentiel est de ne pas abandonner. Regardez tout le chemin parcouru jusqu'ici. Un petit écart ne change rien à votre progression globale."
        ],
        'manque_temps' => [
            "⏰ Je comprends que le temps est un défi. Astuce : préparez vos repas le dimanche (meal prep) pour avoir des portions prêtes toute la semaine. Ça change la vie !",
            "🚀 Quand on manque de temps, la clé c'est l'anticipation. Gardez toujours des bases saines au frigo : œufs durs, légumes lavés, riz cuit. Un repas équilibré en 10 minutes, c'est possible !",
            "📦 Pas de temps ? Pas de panique ! Optez pour des repas express mais nutritifs : salade de thon, wrap aux légumes, bol de quinoa pré-cuit. L'important est de ne pas sauter de repas."
        ],
        'motivation' => [
            "🎉 Bravo ! Votre motivation est votre plus grand atout. Continuez sur cette lancée, chaque bon choix alimentaire vous rapproche de votre objectif. Vous êtes sur la bonne voie !",
            "⭐ C'est super de vous sentir motivé(e) ! Profitez de cette énergie pour essayer de nouvelles recettes saines. La variété est la clé pour maintenir cette motivation sur le long terme.",
            "🏆 Excellente attitude ! Votre engagement envers votre plan nutritionnel porte ses fruits. N'oubliez pas de célébrer ces petites victoires, elles sont le carburant de votre réussite !"
        ],
        'faim' => [
            "🍎 Les fringales sont souvent un signe que vous n'avez pas assez de protéines ou de fibres dans vos repas. Essayez d'ajouter un œuf dur, du fromage blanc ou des noix en collation.",
            "🥤 Parfois, la faim est en réalité de la soif ! Buvez un grand verre d'eau et attendez 15 minutes. Si la faim persiste, optez pour un encas sain : pomme + beurre de cacahuète.",
            "🥕 Pour éviter les fringales, assurez-vous que chaque repas contient des protéines, des fibres et des bonnes graisses. Ces trois éléments garantissent une satiété durable."
        ],
        'neutre' => [
            "👋 Bonjour ! Comment se passe votre plan nutritionnel aujourd'hui ? Je suis là pour vous accompagner. N'hésitez pas à me parler de vos repas, vos ressentis ou vos difficultés.",
            "🥗 Je suis votre coach nutritionnel ! Dites-moi comment vous vous sentez, parlez-moi de vos repas ou posez-moi vos questions sur la nutrition. Je suis là pour vous aider !",
            "💬 Comment puis-je vous aider ? Je peux analyser vos habitudes alimentaires, vous donner des conseils personnalisés ou simplement discuter de votre plan nutritionnel."
        ]
    ];

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Récupère les repas récents d'un plan, ordonnés par date décroissante.
     *
     * @param int $plan_id  Identifiant du plan
     * @param int $limit    Nombre maximum de repas à retourner
     * @return array
     */
    public function getRepasRecents(int $plan_id, int $limit = 5): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, plan_id, nom_recette, date, type_repas, statut, 
                    calories_consommees, heure_prevue, heure_reelle, notes
             FROM repas
             WHERE plan_id = ?
             ORDER BY date DESC, heure_prevue DESC
             LIMIT ?'
        );
        $stmt->execute([$plan_id, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Analyse le sentiment d'un texte (notes ou message) en détectant des mots-clés.
     * Retourne le sentiment dominant ou 'neutre' si aucun mot-clé n'est détecté.
     *
     * @param string $texte  Texte à analyser
     * @return string        Sentiment détecté (fatigue, stress, coupable, manque_temps, motivation, faim, neutre)
     */
    public function analyserSentiment(string $texte): string
    {
        $texte = mb_strtolower(trim($texte), 'UTF-8');
        $scores = [];

        foreach ($this->sentimentKeywords as $sentiment => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                if (mb_strpos($texte, $keyword) !== false) {
                    $score++;
                }
            }
            if ($score > 0) {
                $scores[$sentiment] = $score;
            }
        }

        if (empty($scores)) {
            return 'neutre';
        }

        // Retourner le sentiment avec le score le plus élevé
        arsort($scores);
        return array_key_first($scores);
    }

    /**
     * Génère une réponse personnalisée en fonction du sentiment détecté
     * et des repas récents du plan.
     *
     * @param string $sentiment     Sentiment détecté
     * @param array  $repasRecents  Repas récents du plan
     * @return string               Message de réponse du coach
     */
    public function genererReponse(string $sentiment, array $repasRecents): string
    {
        // Choisir une réponse aléatoire pour le sentiment
        $pool = $this->responses[$sentiment] ?? $this->responses['neutre'];
        $reponse = $pool[array_rand($pool)];

        // Enrichir la réponse avec les données des repas récents
        if (!empty($repasRecents)) {
            $totalCalories = 0;
            $nbConsommes = 0;
            $nbAnnules = 0;
            $nbPrevus = 0;

            foreach ($repasRecents as $repas) {
                $totalCalories += (int)($repas['calories_consommees'] ?? 0);
                switch ($repas['statut']) {
                    case 'consomme':
                        $nbConsommes++;
                        break;
                    case 'annule':
                        $nbAnnules++;
                        break;
                    case 'prevu':
                        $nbPrevus++;
                        break;
                }
            }

            $reponse .= "\n\n📊 **Résumé de vos derniers repas :**";
            $reponse .= "\n• " . count($repasRecents) . " repas récents analysés";
            $reponse .= "\n• ✅ " . $nbConsommes . " consommé(s)";

            if ($nbAnnules > 0) {
                $reponse .= "\n• ❌ " . $nbAnnules . " annulé(s)";
            }
            if ($nbPrevus > 0) {
                $reponse .= "\n• ⏳ " . $nbPrevus . " prévu(s)";
            }
            if ($totalCalories > 0) {
                $reponse .= "\n• 🔥 " . $totalCalories . " kcal au total";
            }

            // Conseils contextuels supplémentaires
            if ($nbAnnules > 2) {
                $reponse .= "\n\n⚠️ Vous avez annulé plusieurs repas récemment. Essayez de maintenir un rythme régulier, même avec des repas légers.";
            }

            $moyenneCalories = $nbConsommes > 0 ? round($totalCalories / $nbConsommes) : 0;
            if ($moyenneCalories > 0 && $moyenneCalories < 300) {
                $reponse .= "\n\n💡 Vos repas semblent légers en calories. Assurez-vous d'atteindre vos objectifs caloriques quotidiens.";
            }
        }

        return $reponse;
    }

    /**
     * Sauvegarde un échange (message + réponse) dans la table conversations.
     *
     * @param int    $plan_id    Identifiant du plan
     * @param string $message    Message de l'utilisateur
     * @param string $reponse    Réponse du chatbot
     * @param string $sentiment  Sentiment détecté
     * @return bool
     */
    public function sauvegarderConversation(int $plan_id, string $message, string $reponse, string $sentiment): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO conversations (plan_id, message_utilisateur, reponse_chatbot, sentiment_detecte, date_creation)
                 VALUES (?, ?, ?, ?, NOW())'
            );
            return $stmt->execute([$plan_id, $message, $reponse, $sentiment]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Récupère l'historique des conversations d'un plan.
     *
     * @param int $plan_id  Identifiant du plan
     * @param int $limit    Nombre maximum de conversations
     * @return array
     */
    public function getHistorique(int $plan_id, int $limit = 20): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, plan_id, message_utilisateur, reponse_chatbot, sentiment_detecte, date_creation
             FROM conversations
             WHERE plan_id = ?
             ORDER BY date_creation ASC
             LIMIT ?'
        );
        $stmt->execute([$plan_id, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Analyse les notes de tous les repas d'un plan pour détecter
     * les sentiments dominants et retourner un bilan.
     *
     * @param int $plan_id  Identifiant du plan
     * @return array        Bilan des sentiments détectés dans les notes
     */
    public function analyserNotesPlan(int $plan_id): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT notes FROM repas WHERE plan_id = ? AND notes IS NOT NULL AND notes != ""'
        );
        $stmt->execute([$plan_id]);
        $rows = $stmt->fetchAll();

        $bilanSentiments = [];

        foreach ($rows as $row) {
            $sentiment = $this->analyserSentiment($row['notes']);
            if (!isset($bilanSentiments[$sentiment])) {
                $bilanSentiments[$sentiment] = 0;
            }
            $bilanSentiments[$sentiment]++;
        }

        arsort($bilanSentiments);

        // Générer un résumé textuel
        $resume = "📋 **Analyse de vos notes de repas :**\n";
        $sentimentLabels = [
            'fatigue'      => '😴 Fatigue',
            'stress'       => '😰 Stress',
            'coupable'     => '😔 Culpabilité',
            'manque_temps' => '⏰ Manque de temps',
            'motivation'   => '💪 Motivation',
            'faim'         => '🍽️ Faim/Fringales',
            'neutre'       => '😊 Neutre',
        ];

        if (empty($bilanSentiments)) {
            $resume .= "Aucune note à analyser pour le moment.";
        } else {
            foreach ($bilanSentiments as $sent => $count) {
                $label = $sentimentLabels[$sent] ?? $sent;
                $resume .= "\n• " . $label . " : " . $count . " mention(s)";
            }

            // Conseil basé sur le sentiment dominant
            $dominant = array_key_first($bilanSentiments);
            $resume .= "\n\n";
            switch ($dominant) {
                case 'fatigue':
                    $resume .= "💡 Tendance à la fatigue détectée. Revoyez votre apport en fer et en vitamines B.";
                    break;
                case 'stress':
                    $resume .= "💡 Beaucoup de stress noté. Pensez à intégrer des aliments anti-stress (magnésium, oméga-3).";
                    break;
                case 'coupable':
                    $resume .= "💡 Sentiment de culpabilité fréquent. Rappelez-vous : la perfection n'existe pas, la régularité oui !";
                    break;
                case 'manque_temps':
                    $resume .= "💡 Le temps semble un obstacle. Essayez le meal prep du dimanche pour gagner du temps en semaine.";
                    break;
                case 'motivation':
                    $resume .= "💡 Belle motivation ! Continuez à varier vos recettes pour maintenir cet élan.";
                    break;
                case 'faim':
                    $resume .= "💡 Fringales fréquentes. Augmentez les protéines et fibres dans vos repas principaux.";
                    break;
                default:
                    $resume .= "💡 Vos notes sont plutôt neutres. N'hésitez pas à détailler vos ressentis pour des conseils plus précis.";
                    break;
            }
        }

        return [
            'sentiments' => $bilanSentiments,
            'resume'     => $resume,
        ];
    }
}
