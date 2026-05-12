<?php

/**
 * ChatbotIntentClassifier — Classification d'intentions par scoring pondéré.
 * 
 * Analyse le message utilisateur pour déterminer :
 * 1. L'intention (intent) parmi 20+ catégories
 * 2. Les entités clés (dates, aliments, types de repas, préférences)
 * 3. Le sentiment associé
 */
class ChatbotIntentClassifier
{
    /**
     * Dictionnaire d'intentions avec mots-clés pondérés.
     * Chaque intent contient des expressions qui seront recherchées (sous-chaîne) dans le message.
     * Le score d'un intent = somme des longueurs des mots-clés trouvés (les expressions longues pèsent plus).
     */
    private $intentPatterns = [

        // ── Salutations & politesse ──
        'greeting' => [
            'bonjour', 'salut', 'coucou', 'hello', 'hey', 'bonsoir', 'yo',
            'bonne journée', 'bonne soirée', 'wesh', 'hola', 'salam',
            'comment vas-tu', 'comment allez-vous', 'ça va', 'quoi de neuf'
        ],
        'thanks' => [
            'merci', 'super', 'parfait', 'génial', 'excellent', 'cool', 'top',
            'c\'est gentil', 'je te remercie', 'thanks', 'formidable',
            'c\'est super', 'trop bien', 'nickel', 'impeccable'
        ],
        'goodbye' => [
            'au revoir', 'à bientôt', 'à plus', 'bye', 'bonne nuit',
            'à demain', 'salut bye', 'tchao', 'ciao'
        ],
        'who_are_you' => [
            'tu es qui', 'qui es-tu', 'comment tu t\'appelles', 'c\'est quoi ton nom',
            'ton nom', 'présente-toi', 'tu fais quoi', 'quel est ton rôle',
            'qu\'est-ce que tu es', 'qui êtes-vous', 'tu sers à quoi'
        ],
        'joke' => [
            'blague', 'rire', 'drôle', 'humour', 'raconte', 'fait rire',
            'quelque chose de drôle', 'amuse-moi', 'rigol', 'marrant',
            'une blague', 'raconte-moi', 'fais-moi rire'
        ],

        // ── Questions nutritionnelles ──
        'ask_calories' => [
            'combien de calories', 'calories par jour', 'besoin calorique',
            'apport calorique', 'combien je dois manger', 'kcal par jour',
            'calories dois-je', 'nombre de calories', 'déficit calorique',
            'surplus calorique', 'combien manger', 'calories nécessaires',
            'besoins énergétiques', 'dépense calorique'
        ],
        'ask_protein_foods' => [
            'aliments riches en protéines', 'protéines', 'sources de protéines',
            'manger plus de protéines', 'aliments protéinés', 'riche en protéine',
            'protéine végétale', 'protéine animale', 'besoin en protéines'
        ],
        'ask_fiber_foods' => [
            'fibres', 'aliments riches en fibres', 'sources de fibres',
            'manger plus de fibres', 'fibre alimentaire', 'transit',
            'digestion', 'constipation'
        ],
        'ask_vitamins' => [
            'vitamines', 'vitamine', 'quelles vitamines', 'carence',
            'compléments', 'suppléments', 'vitamine c', 'vitamine d',
            'vitamine b12', 'fer', 'magnésium', 'zinc', 'oméga',
            'minéraux', 'nutriment', 'micronutriment'
        ],
        'ask_gluten' => [
            'gluten', 'sans gluten', 'intolérance au gluten', 'coeliaque',
            'gluten mauvais', 'gluten dangereux', 'maladie coeliaque'
        ],
        'ask_hydration' => [
            'eau', 'boire', 'hydratation', 'combien d\'eau', 'litres d\'eau',
            'déshydrat', 'soif', 'boire suffisamment', 'eau par jour'
        ],
        'ask_sugar' => [
            'sucre', 'trop de sucre', 'sucre raffiné', 'index glycémique',
            'diabète', 'glycémie', 'sucres ajoutés', 'édulcorant'
        ],
        'ask_fat' => [
            'gras', 'lipides', 'matières grasses', 'bon gras', 'mauvais gras',
            'oméga 3', 'huile d\'olive', 'graisses saturées', 'cholestérol'
        ],

        // ── Progression & plan ──
        'ask_progression' => [
            'progression', 'mes résultats', 'comment ça avance', 'mon avancement',
            'ma semaine', 'bilan', 'statistiques', 'mes stats', 'comment se passe',
            'est-ce que j\'atteindrai', 'atteindre mon objectif', 'progrès',
            'où j\'en suis', 'mon évolution', 'comment je m\'en sors',
            'est-ce que je progresse', 'suis-je sur la bonne voie'
        ],
        'ask_plan_details' => [
            'mon objectif', 'mon plan', 'plan actuel', 'rappelle-moi mon objectif',
            'quel est mon plan', 'détails du plan', 'résumé du plan', 'info plan',
            'rappelle-moi', 'mon programme', 'objectif actuel', 'quel objectif'
        ],
        'ask_yesterday_meals' => [
            'repas d\'hier', 'mangé hier', 'journée d\'hier',
            'repas de la veille', 'qu\'ai-je mangé hier', 'hier j\'ai mangé'
        ],
        'ask_today_meals' => [
            'repas d\'aujourd\'hui', 'mangé aujourd\'hui',
            'repas du jour', 'ce matin', 'ce midi', 'ce soir',
            'qu\'est-ce que je mange', 'menu du jour', 'aujourd\'hui'
        ],
        'ask_cancelled_meals' => [
            'repas annulés', 'annulé', 'sauté', 'repas sautés', 'pas mangé',
            'repas manqués', 'skip', 'zappé', 'souvent annulé',
            'quels repas j\'ai annulé', 'repas que j\'ai sauté'
        ],

        // ── Recettes & suggestions ──
        'ask_recipe_idea' => [
            'idée de recette', 'recette', 'idée repas', 'idée de petit',
            'idée de déjeuner', 'idée de dîner', 'que manger', 'quoi manger',
            'propose-moi', 'suggère', 'suggestion', 'donne-moi une idée',
            'qu\'est-ce que je peux manger', 'avec des restes',
            'healthy', 'léger', 'qu\'est-ce que je mange',
            'propose un repas', 'une idée', 'que cuisiner',
            'donne-moi un repas', 'menu', 'plat'
        ],

        // ── Motivation & émotions ──
        'ask_motivation' => [
            'abandonner', 'j\'abandonne', 'envie d\'arrêter', 'lâcher',
            'plus la force', 'ça sert à rien', 'pas la peine', 'décourager',
            'découragement', 'foutu', 'nul', 'j\'y arrive pas', 'motivation',
            'démotivé', 'difficile', 'dur', 'envie d\'abandonner',
            'je n\'y arriverai jamais', 'impossible', 'trop dur'
        ],
        'proud' => [
            'fier', 'fière', 'content de moi', 'contente de moi', 'bien fait',
            'réussi', 'j\'ai assuré', 'bravo moi', 'satisfait', 'satisfaite',
            'je suis fier', 'je suis fière', 'fier de moi', 'fière de moi',
            'j\'ai réussi', 'victoire', 'yesss'
        ],
        'ask_strengths' => [
            'points forts', 'mes forces', 'ce que je fais bien', 'qualités',
            'mes réussites', 'meilleur', 'en quoi je suis bon',
            'mes points forts', 'qu\'est-ce que je fais bien'
        ],
        'feeling_fatigue' => [
            'fatigue', 'fatigué', 'fatiguée', 'épuisé', 'épuisée', 'crevé',
            'énergie basse', 'coup de barre', 'lessivé', 'exténué',
            'pas d\'énergie', 'je suis crevé', 'je suis épuisé'
        ],
        'feeling_stress' => [
            'stress', 'stressé', 'stressée', 'anxieux', 'anxieuse', 'angoisse',
            'nerveux', 'tendu', 'pression', 'débordé', 'submergé',
            'je suis stressé', 'trop de pression', 'anxiété'
        ],
        'feeling_guilty' => [
            'coupable', 'culpabilité', 'honte', 'regret', 'trop mangé',
            'craqué', 'craquage', 'excès', 'triche', 'écart', 'dérapé',
            'j\'ai craqué', 'j\'ai trop mangé', 'je me sens coupable'
        ],
        'feeling_hungry' => [
            'faim', 'affamé', 'fringale', 'envie de manger', 'grignoter',
            'grignotage', 'gourmandise', 'tentation', 'envie sucrée',
            'j\'ai faim', 'envie de grignoter', 'creux'
        ],
        'no_time' => [
            'pas le temps', 'manque de temps', 'pressé', 'rush', 'trop occupé',
            'agenda chargé', 'sur le pouce', 'pas cuisiné', 'sauté un repas',
            'je n\'ai pas le temps', 'rapide', 'express', 'vite fait'
        ],
    ];

    /**
     * Classifie le message en intent + score de confiance.
     * Stratégie : matching par sous-chaîne avec normalisation des accents.
     * Le score = somme des longueurs des mots-clés matchés (favorise les expressions longues/précises).
     */
    public function classifier(string $message): array
    {
        $msg = mb_strtolower(trim($message), 'UTF-8');
        $msgNorm = $this->removeAccents($msg);
        $scores = [];

        foreach ($this->intentPatterns as $intent => $keywords) {
            $score = 0;
            foreach ($keywords as $kw) {
                $kwNorm = $this->removeAccents($kw);
                // Chercher avec ET sans accents pour maximiser la détection
                if (mb_strpos($msg, $kw) !== false || mb_strpos($msgNorm, $kwNorm) !== false) {
                    // Bonus pour les expressions longues (plus spécifiques)
                    $score += mb_strlen($kw);
                }
            }
            if ($score > 0) {
                $scores[$intent] = $score;
            }
        }

        if (empty($scores)) {
            return ['intent' => 'unknown', 'confidence' => 0];
        }

        // Trier par score décroissant → le meilleur match gagne
        arsort($scores);
        $bestIntent = array_key_first($scores);
        $maxScore = $scores[$bestIntent];
        $totalPossible = mb_strlen($msg) * 2;
        $confidence = min(1.0, $maxScore / max($totalPossible, 1));

        return ['intent' => $bestIntent, 'confidence' => round($confidence, 2)];
    }

    /**
     * Supprime les accents d'une chaîne UTF-8 pour la comparaison robuste.
     */
    private function removeAccents(string $str): string
    {
        $search  = ['à','â','ä','é','è','ê','ë','î','ï','ô','ö','ù','û','ü','ÿ','ç','œ','æ'];
        $replace = ['a','a','a','e','e','e','e','i','i','o','o','u','u','u','y','c','oe','ae'];
        return str_replace($search, $replace, $str);
    }

    /**
     * Extrait les entités clés du message (dates, types de repas, aliments, préférences).
     */
    public function extraireEntites(string $message): array
    {
        $msg = mb_strtolower(trim($message), 'UTF-8');
        $entites = [];

        // ── Type de repas ──
        $types = [
            'petit-déjeuner' => 'petit_dejeuner', 'petit déjeuner' => 'petit_dejeuner',
            'petit-dej' => 'petit_dejeuner', 'petit dej' => 'petit_dejeuner',
            'déjeuner' => 'dejeuner', 'dîner' => 'diner', 'diner' => 'diner',
            'collation' => 'collation', 'goûter' => 'collation', 'snack' => 'collation',
            'encas' => 'collation', 'en-cas' => 'collation',
        ];
        foreach ($types as $label => $code) {
            if (mb_strpos($msg, $label) !== false) {
                $entites['type_repas'] = $code;
                $entites['type_repas_label'] = $label;
                break;
            }
        }

        // ── Préférence alimentaire ──
        $prefs = ['végétarien', 'vegetarien', 'vegan', 'végan', 'sans gluten', 'halal', 'kasher', 'bio', 'sans lactose'];
        foreach ($prefs as $pref) {
            if (mb_strpos($msg, $pref) !== false) {
                $entites['preference'] = $pref;
                break;
            }
        }

        // ── Adjectif de recette ──
        $adjs = ['rapide', 'facile', 'simple', 'léger', 'copieux', 'chaud', 'froid', 'express', 'light'];
        foreach ($adjs as $adj) {
            if (mb_strpos($msg, $adj) !== false) {
                $entites['adjectif'] = $adj;
                break;
            }
        }

        // ── Ingrédient mentionné ──
        $ingredients = [
            'poulet', 'poisson', 'thon', 'saumon', 'œuf', 'oeuf', 'riz',
            'pâtes', 'légumes', 'salade', 'avocat', 'quinoa', 'tomate',
            'fromage', 'yaourt', 'fruits', 'banane', 'pomme', 'épinard',
            'lentille', 'haricot', 'patate', 'brocoli', 'carotte', 'restes',
            'courgette', 'aubergine', 'concombre', 'poivron', 'champignon',
            'pois chiche', 'tofu', 'soja', 'amande', 'noix',
        ];
        foreach ($ingredients as $ing) {
            if (mb_strpos($msg, $ing) !== false) {
                $entites['ingredient'] = $ing;
                break;
            }
        }

        // ── Référence temporelle ──
        if (mb_strpos($msg, 'hier') !== false) {
            $entites['date'] = date('Y-m-d', strtotime('-1 day'));
            $entites['date_label'] = 'hier';
        } elseif (mb_strpos($msg, 'aujourd\'hui') !== false || mb_strpos($msg, 'ce matin') !== false || mb_strpos($msg, 'ce midi') !== false || mb_strpos($msg, 'ce soir') !== false) {
            $entites['date'] = date('Y-m-d');
            $entites['date_label'] = "aujourd'hui";
        } elseif (mb_strpos($msg, 'semaine') !== false) {
            $entites['date_debut'] = date('Y-m-d', strtotime('monday this week'));
            $entites['date_fin'] = date('Y-m-d');
        } elseif (mb_strpos($msg, 'demain') !== false) {
            $entites['date'] = date('Y-m-d', strtotime('+1 day'));
            $entites['date_label'] = 'demain';
        }

        return $entites;
    }

    /**
     * Retourne le sentiment associé à un intent (pour l'analyse émotionnelle).
     */
    public function intentToSentiment(string $intent): string
    {
        $map = [
            'feeling_fatigue' => 'fatigue',
            'feeling_stress'  => 'stress',
            'feeling_guilty'  => 'coupable',
            'feeling_hungry'  => 'faim',
            'no_time'         => 'manque_temps',
            'ask_motivation'  => 'coupable',
            'proud'           => 'motivation',
            'thanks'          => 'motivation',
            'ask_strengths'   => 'motivation',
            'greeting'        => 'neutre',
            'goodbye'         => 'neutre',
        ];
        return $map[$intent] ?? 'neutre';
    }
}
