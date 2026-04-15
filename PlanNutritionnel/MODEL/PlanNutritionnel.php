<?php

class PlanNutritionnel
{
    private $planID;
    private $nom;
    private $calories_journalieres;
    private $utilisateur_id;
    private $date_debut;
    private $date_fin;
    private $statistiques;

    public function __construct($nom, $calories_journalieres, $utilisateur_id, $date_debut, $date_fin, $statistiques = 0.0)
    {
        $this->nom                  = $nom;
        $this->calories_journalieres = $calories_journalieres;
        $this->utilisateur_id       = $utilisateur_id;
        $this->date_debut           = $date_debut;
        $this->date_fin             = $date_fin;
        $this->statistiques         = $statistiques;
    }

    // Getters
    public function getPlanID()               { return $this->planID; }
    public function getNom()                  { return $this->nom; }
    public function getCaloriesJournalieres() { return $this->calories_journalieres; }
    public function getUtilisateurId()        { return $this->utilisateur_id; }
    public function getDateDebut()            { return $this->date_debut; }
    public function getDateFin()              { return $this->date_fin; }
    public function getStatistiques()         { return $this->statistiques; }

    // Setters
    public function setNom($nom)                              { $this->nom = $nom; }
    public function setCaloriesJournalieres($cal)             { $this->calories_journalieres = $cal; }
    public function setDateDebut($date)                       { $this->date_debut = $date; }
    public function setDateFin($date)                         { $this->date_fin = $date; }
    public function setStatistiques($stat)                    { $this->statistiques = $stat; }
}
?>
