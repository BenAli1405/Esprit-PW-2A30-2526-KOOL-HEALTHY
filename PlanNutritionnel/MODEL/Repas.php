<?php

class Repas
{
    private $id;
    private $planID;
    private $recette;
    private $date;
    private $type_repas;   // petit-déjeuner | déjeuner | dîner | collation
    private $statut;       // planifié | consommé | annulé

    public function __construct($planID, $recette, $date, $type_repas, $statut = 'planifié')
    {
        $this->planID     = $planID;
        $this->recette    = $recette;
        $this->date       = $date;
        $this->type_repas = $type_repas;
        $this->statut     = $statut;
    }

    // Getters
    public function getId()       { return $this->id; }
    public function getPlanID()   { return $this->planID; }
    public function getRecette()  { return $this->recette; }
    public function getDate()     { return $this->date; }
    public function getTypeRepas(){ return $this->type_repas; }
    public function getStatut()   { return $this->statut; }

    // Setters
    public function setRecette($recette)     { $this->recette    = $recette; }
    public function setDate($date)           { $this->date       = $date; }
    public function setTypeRepas($type)      { $this->type_repas = $type; }
    public function setStatut($statut)       { $this->statut     = $statut; }
}
?>
