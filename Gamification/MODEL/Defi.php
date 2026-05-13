<?php

class Defi
{
    private $id;
    private $titre;
    private $type;
    private $points;
    private $date_debut;
    private $date_fin;
    private $status;
    private $proposant_id;
    private $restrictions;
    private $created_at;

    public function __construct($titre, $type, $points, $date_debut = null, $date_fin = null, $status = 'approuve', $proposant_id = null, $restrictions = null)
    {
        $this->titre        = $titre;
        $this->type         = $type;
        $this->points       = $points;
        $this->date_debut   = $date_debut;
        $this->date_fin     = $date_fin;
        $this->status       = $status;
        $this->proposant_id = $proposant_id;
        $this->restrictions = $restrictions;
        $this->created_at   = date('Y-m-d H:i:s');
    }

    // Getters
    public function getId()           { return $this->id; }
    public function getTitre()        { return $this->titre; }
    public function getType()         { return $this->type; }
    public function getPoints()       { return $this->points; }
    public function getDateDebut()    { return $this->date_debut; }
    public function getDateFin()      { return $this->date_fin; }
    public function getStatus()       { return $this->status; }
    public function getProposantId()  { return $this->proposant_id; }
    public function getRestrictions() { return $this->restrictions; }
    public function getCreatedAt()    { return $this->created_at; }

    // Setters
    public function setTitre($titre)                { $this->titre        = $titre; }
    public function setType($type)                  { $this->type         = $type; }
    public function setPoints($points)              { $this->points       = $points; }
    public function setDateDebut($date_debut)       { $this->date_debut   = $date_debut; }
    public function setDateFin($date_fin)           { $this->date_fin     = $date_fin; }
    public function setStatus($status)              { $this->status       = $status; }
    public function setProposantId($proposant_id)   { $this->proposant_id = $proposant_id; }
    public function setRestrictions($restrictions)  { $this->restrictions = $restrictions; }
}
?>
