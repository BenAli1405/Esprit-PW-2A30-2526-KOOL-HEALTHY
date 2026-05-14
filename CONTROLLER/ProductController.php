<?php
/**
 * ProductController.php - Controller
 * Handles all product-related logic.
 */

require_once __DIR__ . '/../MODEL/Product.php';
require_once __DIR__ . '/../MODEL/KoolDatabase.php';

class ProductController {

    public function getAllProducts(): array {
        $pdo = KoolDatabase::getConnection();
        $stmt = $pdo->prepare('SELECT id, nom, date_expiration, qte FROM produits ORDER BY date_expiration ASC, nom ASC');
        $stmt->execute();
        $rows = $stmt->fetchAll();

        return array_map(fn($row) => new Product(
            $row['id'],
            $row['nom'],
            $row['date_expiration'],
            (int) $row['qte']
        ), $rows);
    }

    public function getProductById(string $id): ?Product {
        $pdo = KoolDatabase::getConnection();
        $stmt = $pdo->prepare('SELECT id, nom, date_expiration, qte FROM produits WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return new Product(
            $row['id'],
            $row['nom'],
            $row['date_expiration'],
            (int) $row['qte']
        );
    }

    public function addProduct(Product $product): bool {
        $pdo = KoolDatabase::getConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO produits (id, nom, date_expiration, qte)
             VALUES (:id, :nom, :date_expiration, :qte)'
        );

        return $stmt->execute([
            ':id' => $product->getId(),
            ':nom' => $product->getNom(),
            ':date_expiration' => $product->getDateExpiration(),
            ':qte' => $product->getQte(),
        ]);
    }

    public function updateProduct(string $originalId, Product $product): bool {
        $pdo = KoolDatabase::getConnection();
        $stmt = $pdo->prepare(
            'UPDATE produits
             SET id = :new_id, nom = :nom, date_expiration = :date_expiration, qte = :qte
             WHERE id = :original_id'
        );

        return $stmt->execute([
            ':new_id' => $product->getId(),
            ':nom' => $product->getNom(),
            ':date_expiration' => $product->getDateExpiration(),
            ':qte' => $product->getQte(),
            ':original_id' => $originalId,
        ]);
    }

    public function deleteProduct(string $id): bool {
        $pdo = KoolDatabase::getConnection();
        $stmt = $pdo->prepare('DELETE FROM produits WHERE id = :id');

        return $stmt->execute([':id' => $id]);
    }

    public function showProduct(Product $product): void {
        echo "<table border='1' cellpadding='8' style='border-collapse:collapse; font-family:Outfit,sans-serif;'>";
        echo "<tr><th>ID Produit</th><th>Nom</th><th>Expiration</th><th>Stock</th></tr>";
        echo $product->show();
        echo "</table>";
    }
}
