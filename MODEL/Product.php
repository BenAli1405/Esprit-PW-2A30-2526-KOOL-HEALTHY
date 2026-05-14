<?php
/**
 * Product.php - Model
 * Represents a product in the anti-waste inventory.
 */
class Product {

    private string $id;
    private string $nom;
    private string $dateExpiration;
    private int $qte;

    public function __construct(
        string $id,
        string $nom,
        string $dateExpiration,
        int $qte
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->dateExpiration = $dateExpiration;
        $this->qte = $qte;
    }

    public function getId(): string {
        return $this->id;
    }

    public function getNom(): string {
        return $this->nom;
    }

    public function getDateExpiration(): string {
        return $this->dateExpiration;
    }

    public function getQte(): int {
        return $this->qte;
    }

    public function setId(string $id): void {
        $this->id = $id;
    }

    public function setNom(string $nom): void {
        $this->nom = $nom;
    }

    public function setDateExpiration(string $dateExpiration): void {
        $this->dateExpiration = $dateExpiration;
    }

    public function setQte(int $qte): void {
        $this->qte = $qte;
    }

    public function show(): string {
        return "
        <tr>
            <td>{$this->id}</td>
            <td>{$this->nom}</td>
            <td>{$this->dateExpiration}</td>
            <td><strong>{$this->qte}</strong></td>
        </tr>";
    }
}
