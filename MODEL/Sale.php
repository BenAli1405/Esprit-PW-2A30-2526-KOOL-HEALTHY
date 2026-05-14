<?php
/**
 * Sale.php - Model
 * Represents a sale linked to a product.
 */
class Sale {

    private ?int $idVente;
    private string $idProduit;
    private int $qteAVendre;
    private float $prix;
    private string $statut;
    private ?string $nomProduit;
    private ?string $dateExpiration;
    private ?string $dateCreation;

    public function __construct(
        ?int $idVente,
        string $idProduit,
        int $qteAVendre,
        float $prix,
        string $statut = 'disponible',
        ?string $nomProduit = null,
        ?string $dateExpiration = null,
        ?string $dateCreation = null
    ) {
        $this->idVente = $idVente;
        $this->idProduit = $idProduit;
        $this->qteAVendre = $qteAVendre;
        $this->prix = $prix;
        $this->statut = $statut;
        $this->nomProduit = $nomProduit;
        $this->dateExpiration = $dateExpiration;
        $this->dateCreation = $dateCreation;
    }

    public function getIdVente(): ?int {
        return $this->idVente;
    }

    public function getIdProduit(): string {
        return $this->idProduit;
    }

    public function getQteAVendre(): int {
        return $this->qteAVendre;
    }

    public function getPrix(): float {
        return $this->prix;
    }

    public function getStatut(): string {
        return $this->statut;
    }

    public function getNomProduit(): ?string {
        return $this->nomProduit;
    }

    public function getDateExpiration(): ?string {
        return $this->dateExpiration;
    }

    public function getDateCreation(): ?string {
        return $this->dateCreation;
    }

    public function setIdVente(int $idVente): void {
        $this->idVente = $idVente;
    }

    public function setIdProduit(string $idProduit): void {
        $this->idProduit = $idProduit;
    }

    public function setQteAVendre(int $qteAVendre): void {
        $this->qteAVendre = $qteAVendre;
    }

    public function setPrix(float $prix): void {
        $this->prix = $prix;
    }

    public function setStatut(string $statut): void {
        $this->statut = $statut;
    }

    public function show(): string {
        $priceFormatted = number_format($this->prix, 2) . ' TND';

        return "
        <tr>
            <td>{$this->idVente}</td>
            <td>{$this->idProduit}</td>
            <td>{$this->qteAVendre}</td>
            <td>{$priceFormatted}</td>
            <td>{$this->statut}</td>
        </tr>";
    }
}
