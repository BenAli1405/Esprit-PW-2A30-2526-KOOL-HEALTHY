<?php

require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../MODEL/Recette.php";

class RecetteController
{
    private function tableExiste($table)
    {
        $db = config::getConnexion();
        $stmt = $db->prepare('SHOW TABLES LIKE :table_name');
        $stmt->execute(['table_name' => $table]);
        return $stmt->fetchColumn() !== false;
    }

    public function listeRecettes()
    {
        $db = config::getConnexion();
        try {
            $liste = $db->query("SELECT * FROM recettes ORDER BY date_creation DESC");
            return $liste->fetchAll();
        } catch (Exception $e) {
            die("Erreur: " . $e->getMessage());
        }
    }

    public function mesRecettes($auteur = "Moi")
    {
        $db = config::getConnexion();
        try {
            $sql = "SELECT * FROM recettes WHERE auteur = :auteur ORDER BY date_creation DESC";
            $req = $db->prepare($sql);
            $req->execute(['auteur' => $auteur]);
            return $req->fetchAll();
        } catch (Exception $e) {
            die("Erreur: " . $e->getMessage());
        }
    }

    public function recettesFavoris($user_id)
    {
        $db = config::getConnexion();
        try {
            if (!$this->tableExiste('favoris')) {
                return [];
            }

            $sql = "SELECT r.* FROM recettes r 
                    INNER JOIN favoris f ON r.id = f.recette_id 
                    WHERE f.user_id = :user_id 
                    ORDER BY r.date_creation DESC";
            $req = $db->prepare($sql);
            $req->execute(['user_id' => $user_id]);
            return $req->fetchAll();
        } catch (Exception $e) {
            die("Erreur: " . $e->getMessage());
        }
    }

    public function ajouterRecette($recette)
    {
        $db = config::getConnexion();
        $sql = "INSERT INTO recettes (titre, temps_prep, ingredients, etapes, image, auteur, date_creation) 
                VALUES (:titre, :temps_prep, :ingredients, :etapes, :image, :auteur, :date_creation)";
        try {
            $req = $db->prepare($sql);
            $req->execute([
                'titre' => $recette->getTitre(),
                'temps_prep' => $recette->getTempsPrep(),
                'ingredients' => $recette->getIngredients(),
                'etapes' => $recette->getEtapes(),
                'image' => $recette->getImage(),
                'auteur' => $recette->getAuteur(),
                'date_creation' => $recette->getDateCreation()
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function modifierRecette($recette, $id)
    {
        $db = config::getConnexion();
        $sql = "UPDATE recettes SET titre=:titre, temps_prep=:temps_prep, ingredients=:ingredients, 
                etapes=:etapes, image=:image, auteur=:auteur WHERE id=:id";
        try {
            $req = $db->prepare($sql);
            $req->execute([
                'titre' => $recette->getTitre(),
                'temps_prep' => $recette->getTempsPrep(),
                'ingredients' => $recette->getIngredients(),
                'etapes' => $recette->getEtapes(),
                'image' => $recette->getImage(),
                'auteur' => $recette->getAuteur(),
                'id' => $id
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function supprimerRecette($id)
    {
        $db = config::getConnexion();
        $sql = "DELETE FROM recettes WHERE id = :id";
        try {
            $req = $db->prepare($sql);
            $req->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function obtenirRecette($id)
    {
        $db = config::getConnexion();
        try {
            $sql = "SELECT * FROM recettes WHERE id = :id";
            $req = $db->prepare($sql);
            $req->execute(['id' => $id]);
            return $req->fetch();
        } catch (Exception $e) {
            die("Erreur: " . $e->getMessage());
        }
    }

    public function ajouterFavori($user_id, $recette_id)
    {
        $db = config::getConnexion();
        if (!$this->tableExiste('favoris')) {
            return false;
        }

        $sql = "INSERT INTO favoris (user_id, recette_id) VALUES (:user_id, :recette_id)";
        try {
            $req = $db->prepare($sql);
            $req->execute(['user_id' => $user_id, 'recette_id' => $recette_id]);
            return true;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function supprimerFavori($user_id, $recette_id)
    {
        $db = config::getConnexion();
        if (!$this->tableExiste('favoris')) {
            return false;
        }

        $sql = "DELETE FROM favoris WHERE user_id = :user_id AND recette_id = :recette_id";
        try {
            $req = $db->prepare($sql);
            $req->execute(['user_id' => $user_id, 'recette_id' => $recette_id]);
            return true;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }
}
?>
