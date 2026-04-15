<?php

class Utilisateur
{
    private $id;
    private $nom;
    private $email;
    private $mot_de_passe;
    private $role;
    private $poids;
    private $taille;
    private $imc;
    private $objectif;
    private $created_at;

    public function __construct(
        $nom,
        $email,
        $mot_de_passe,
        $role,
        $poids,
        $taille,
        $objectif
    )
    {
        $this->nom = $nom;
        $this->email = $email;
        $this->mot_de_passe = $mot_de_passe;
        $this->role = $role;
        $this->poids = $poids;
        $this->taille = $taille;
        $this->imc = $this->calculerImc($poids, $taille);
        $this->objectif = $objectif;
        $this->created_at = date('Y-m-d H:i:s');
    }

    private function calculerImc($poids, $taille)
    {
        $tailleM = (float) $taille;
        if ($tailleM > 3) {
            $tailleM = $tailleM / 100;
        }

        if ($tailleM <= 0) {
            return null;
        }

        return round(((float) $poids) / ($tailleM * $tailleM), 2);
    }

    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getEmail() { return $this->email; }
    public function getMotDePasse() { return $this->mot_de_passe; }
    public function getRole() { return $this->role; }
    public function getPoids() { return $this->poids; }
    public function getTaille() { return $this->taille; }
    public function getImc() { return $this->imc; }
    public function getObjectif() { return $this->objectif; }
    public function getCreatedAt() { return $this->created_at; }

    public function setNom($nom) { $this->nom = $nom; }
    public function setEmail($email) { $this->email = $email; }
    public function setMotDePasse($mot_de_passe) { $this->mot_de_passe = $mot_de_passe; }
    public function setRole($role) { $this->role = $role; }
    public function setPoids($poids) { $this->poids = $poids; }
    public function setTaille($taille) { $this->taille = $taille; }
    public function setObjectif($objectif) { $this->objectif = $objectif; }
}
?>
