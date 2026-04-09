<?php

class ProfilNutritif
{
    private $id;
    private $utilisateur;
    private $age;
    private $allergies;
    private $besoins_caloriques;

    public function __construct($utilisateur, $age, $allergies, $besoins_caloriques)
    {
        $this->utilisateur = $utilisateur;
        $this->age = $age;
        $this->allergies = $allergies;
        $this->besoins_caloriques = $besoins_caloriques;
    }

    public function getId() { return $this->id; }
    public function getUtilisateur() { return $this->utilisateur; }
    public function getAge() { return $this->age; }
    public function getAllergies() { return $this->allergies; }
    public function getBesoinsCaloriques() { return $this->besoins_caloriques; }

    public function setUtilisateur($utilisateur) { $this->utilisateur = $utilisateur; }
    public function setAge($age) { $this->age = $age; }
    public function setAllergies($allergies) { $this->allergies = $allergies; }
    public function setBesoinsCaloriques($besoins) { $this->besoins_caloriques = $besoins; }
}
?>
