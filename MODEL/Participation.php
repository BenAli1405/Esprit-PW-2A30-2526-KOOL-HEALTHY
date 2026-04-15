<?php

class Participation
{
    private $id;
    private $utilisateur_id;
    private $defi_id;
    private $progression;
    private $termine;
    private $points_gagnes;
    private $created_at;

    public function __construct($utilisateur_id, $defi_id, $progression = 0, $termine = false, $points_gagnes = 0)
    {
        $this->utilisateur_id = $utilisateur_id;
        $this->defi_id        = $defi_id;
        $this->progression    = $progression;
        $this->termine        = $termine;
        $this->points_gagnes  = $points_gagnes;
        $this->created_at     = date('Y-m-d H:i:s');
    }

    // Getters
    public function getId()             { return $this->id; }
    public function getUtilisateurId()  { return $this->utilisateur_id; }
    public function getDefiId()         { return $this->defi_id; }
    public function getProgression()    { return $this->progression; }
    public function getTermine()        { return $this->termine; }
    public function getPointsGagnes()   { return $this->points_gagnes; }
    public function getCreatedAt()      { return $this->created_at; }

    // Setters
    public function setProgression($p)   { $this->progression   = $p; }
    public function setTermine($t)       { $this->termine        = $t; }
    public function setPointsGagnes($p)  { $this->points_gagnes  = $p; }
}
?>
