<?php
/**
 * SaleController.php - Controller
 * Handles all sale-related logic.
 */

require_once __DIR__ . '/../MODEL/Sale.php';
require_once __DIR__ . '/../MODEL/KoolDatabase.php';

class SaleController {

    public function getAllSales(): array {
        $pdo = KoolDatabase::getConnection();
        $stmt = $pdo->prepare(
            'SELECT
                v.id_vente,
                v.id_produit,
                v.qte_a_vendre,
                v.prix,
                v.statut,
                v.date_creation,
                p.nom AS nom_produit,
                p.date_expiration
            FROM vente v
            INNER JOIN produits p ON p.id = v.id_produit
            ORDER BY v.date_creation DESC, v.id_vente DESC'
        );
        $stmt->execute();
        $rows = $stmt->fetchAll();

        return array_map(fn($row) => new Sale(
            (int) $row['id_vente'],
            $row['id_produit'],
            (int) $row['qte_a_vendre'],
            (float) $row['prix'],
            $row['statut'],
            $row['nom_produit'],
            $row['date_expiration'],
            $row['date_creation']
        ), $rows);
    }

    public function getSaleById(int $idVente): ?Sale {
        $pdo = KoolDatabase::getConnection();
        $stmt = $pdo->prepare(
            'SELECT
                v.id_vente,
                v.id_produit,
                v.qte_a_vendre,
                v.prix,
                v.statut,
                v.date_creation,
                p.nom AS nom_produit,
                p.date_expiration
            FROM vente v
            INNER JOIN produits p ON p.id = v.id_produit
            WHERE v.id_vente = :id_vente'
        );
        $stmt->execute([':id_vente' => $idVente]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return new Sale(
            (int) $row['id_vente'],
            $row['id_produit'],
            (int) $row['qte_a_vendre'],
            (float) $row['prix'],
            $row['statut'],
            $row['nom_produit'],
            $row['date_expiration'],
            $row['date_creation']
        );
    }

    public function addSale(Sale $sale): bool {
        $pdo = KoolDatabase::getConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO vente (id_produit, qte_a_vendre, prix, statut)
             VALUES (:id_produit, :qte_a_vendre, :prix, :statut)'
        );

        return $stmt->execute([
            ':id_produit' => $sale->getIdProduit(),
            ':qte_a_vendre' => $sale->getQteAVendre(),
            ':prix' => $sale->getPrix(),
            ':statut' => $sale->getStatut(),
        ]);
    }

    public function updateSale(Sale $sale): bool {
        $pdo = KoolDatabase::getConnection();
        $stmt = $pdo->prepare(
            'UPDATE vente
             SET id_produit = :id_produit, qte_a_vendre = :qte_a_vendre, prix = :prix, statut = :statut
             WHERE id_vente = :id_vente'
        );

        return $stmt->execute([
            ':id_produit' => $sale->getIdProduit(),
            ':qte_a_vendre' => $sale->getQteAVendre(),
            ':prix' => $sale->getPrix(),
            ':statut' => $sale->getStatut(),
            ':id_vente' => $sale->getIdVente(),
        ]);
    }

    public function deleteSale(int $idVente): bool {
        $pdo = KoolDatabase::getConnection();
        $stmt = $pdo->prepare('DELETE FROM vente WHERE id_vente = :id_vente');

        return $stmt->execute([':id_vente' => $idVente]);
    }

    /**
     * Reservation pour retrait / paiement sur place (passe en reservee si disponible).
     */
    public function reserveSaleSurPlace(int $idVente): bool {
        $pdo = KoolDatabase::getConnection();
        $stmt = $pdo->prepare(
            "UPDATE vente SET statut = 'reservee' WHERE id_vente = :id AND statut = 'disponible'"
        );

        $stmt->execute([':id' => $idVente]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Apres paiement Stripe : marque la vente comme vendue et diminue le stock produit (transaction).
     */
    public function completeSaleAfterPayment(int $idVente): bool {
        return $this->completeCartAfterPayment([['id_vente' => $idVente, 'qty' => 1]]);
    }

    /**
     * Finalise un panier : quantites achetees par annonce, prix = prix unitaire TND.
     * Accepte une liste de ['id_vente' => int, 'qty' => int] ou legacy liste d entiers (qty 1).
     *
     * @param array<int, mixed> $input
     */
    public function completeCartAfterPayment(array $input): bool {
        $lines = [];
        foreach ($input as $row) {
            if (is_array($row) && isset($row['id_vente'])) {
                $id = (int) $row['id_vente'];
                $qty = (int) ($row['qty'] ?? 1);
                if ($id > 0 && $qty > 0) {
                    $lines[] = ['id_vente' => $id, 'qty' => $qty];
                }
            } elseif (is_int($row) || (is_string($row) && ctype_digit((string) $row))) {
                $id = (int) $row;
                if ($id > 0) {
                    $lines[] = ['id_vente' => $id, 'qty' => 1];
                }
            }
        }

        $merged = [];
        foreach ($lines as $row) {
            $id = $row['id_vente'];
            $merged[$id] = ($merged[$id] ?? 0) + $row['qty'];
        }
        $lines = [];
        foreach ($merged as $idVente => $qty) {
            $lines[] = ['id_vente' => $idVente, 'qty' => $qty];
        }

        if ($lines === []) {
            return false;
        }

        foreach ($lines as $row) {
            $sale = $this->getSaleById($row['id_vente']);
            if ($sale === null) {
                return false;
            }
            if ($sale->getQteAVendre() < $row['qty']) {
                return false;
            }
            if (!in_array($sale->getStatut(), ['disponible', 'reservee'], true)) {
                return false;
            }
        }

        $pdo = KoolDatabase::getConnection();
        $pdo->beginTransaction();

        try {
            foreach ($lines as $row) {
                $idVente = $row['id_vente'];
                $qty = $row['qty'];
                $sale = $this->getSaleById($idVente);
                if ($sale === null || $sale->getQteAVendre() < $qty) {
                    $pdo->rollBack();
                    return false;
                }

                $stmt = $pdo->prepare(
                    'UPDATE vente SET qte_a_vendre = qte_a_vendre - :q
                     WHERE id_vente = :id AND qte_a_vendre >= :q2 AND statut IN (\'disponible\',\'reservee\')'
                );
                $stmt->execute([':q' => $qty, ':q2' => $qty, ':id' => $idVente]);

                if ($stmt->rowCount() === 0) {
                    $pdo->rollBack();
                    return false;
                }

                $stmtStock = $pdo->prepare(
                    'UPDATE produits SET qte = qte - :qte WHERE id = :pid AND qte >= :qte2'
                );
                $stmtStock->execute([
                    ':qte' => $qty,
                    ':pid' => $sale->getIdProduit(),
                    ':qte2' => $qty,
                ]);

                if ($stmtStock->rowCount() === 0) {
                    $pdo->rollBack();
                    return false;
                }

                $stmtStat = $pdo->prepare(
                    "UPDATE vente SET statut = CASE
                        WHEN qte_a_vendre <= 0 THEN 'vendue'
                        WHEN statut = 'reservee' THEN 'reservee'
                        ELSE 'disponible'
                    END
                    WHERE id_vente = :id"
                );
                $stmtStat->execute([':id' => $idVente]);
            }

            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function showSale(Sale $sale): void {
        echo "<table border='1' cellpadding='8' style='border-collapse:collapse; font-family:Outfit,sans-serif;'>";
        echo "<tr><th>ID Vente</th><th>ID Produit</th><th>Quantite</th><th>Prix</th><th>Statut</th></tr>";
        echo $sale->show();
        echo "</table>";
    }
}
