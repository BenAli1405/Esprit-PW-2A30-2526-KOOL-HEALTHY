<?php

class Recette
{
    private $id;
    private $titre;
    private $temps_prep;
    private $ingredients;
    private $etapes;
    private $image;
    private $auteur;
    private $date_creation;

    public function __construct($titre, $temps_prep, $ingredients, $etapes, $image = null, $auteur = "Moi")
    {
        $this->titre = $titre;
        $this->temps_prep = $temps_prep;
        $this->ingredients = $ingredients;
        $this->etapes = $etapes;
        $this->image = $image;
        $this->auteur = $auteur;
        $this->date_creation = date('Y-m-d H:i:s');
    }

    // Getters
    public function getId() { return $this->id; }
    public function getTitre() { return $this->titre; }
    public function getTempsPrep() { return $this->temps_prep; }
    public function getIngredients() { return $this->ingredients; }
    public function getEtapes() { return $this->etapes; }
    public function getImage() { return $this->image; }
    public function getAuteur() { return $this->auteur; }
    public function getDateCreation() { return $this->date_creation; }

    // Setters
    public function setTitre($titre) { $this->titre = $titre; }
    public function setTempsPrep($temps) { $this->temps_prep = $temps; }
    public function setIngredients($ingredients) { $this->ingredients = $ingredients; }
    public function setEtapes($etapes) { $this->etapes = $etapes; }
    public function setImage($image) { $this->image = $image; }
    public function setAuteur($auteur) { $this->auteur = $auteur; }
}
?>
