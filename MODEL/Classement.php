<?php

class Classement
{
    private $id;
    private $utilisateur_id;
    private $rang;
    private $points_total;
    private $defis_completes;
    private $updated_at;

    public function __construct($utilisateur_id, $rang = null, $points_total = 0, $defis_completes = 0)
    {
        $this->utilisateur_id  = $utilisateur_id;
        $this->rang            = $rang;
        $this->points_total    = $points_total;
        $this->defis_completes = $defis_completes;
        $this->updated_at      = date('Y-m-d H:i:s');
    }

    // Getters
    public function getId()            { return $this->id; }
    public function getUtilisateurId() { return $this->utilisateur_id; }
    public function getRang()          { return $this->rang; }
    public function getPointsTotal()   { return $this->points_total; }
    public function getDefisCompletes(){ return $this->defis_completes; }
    public function getUpdatedAt()     { return $this->updated_at; }

    // Setters
    public function setRang($rang)                    { $this->rang            = $rang; }
    public function setPointsTotal($points)           { $this->points_total    = $points; }
    public function setDefisCompletes($completes)     { $this->defis_completes = $completes; }
}
?>
