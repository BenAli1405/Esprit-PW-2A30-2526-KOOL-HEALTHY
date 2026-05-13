<?php
// Simple DB connection tester. Place in web root tools/ and open in browser
// or run from CLI: php tools/test_db.php
require_once __DIR__ . '/../config.php';

echo "Testing DB connection for project integweb\n";
echo "Using constants:\n";
echo "  DB_HOST=" . DB_HOST . "\n";
echo "  DB_PORT=" . DB_PORT . "\n";
echo "  DB_USER=" . DB_USER . "\n";
echo "  DB_NAME=" . DB_NAME . "\n";

$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "Connection successful (PDO).\n";
    // show server version
    $ver = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
    echo "MySQL server version: " . $ver . "\n";
    exit(0);
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
    echo "Troubleshooting suggestions:\n";
    echo " - Vérifiez que MySQL / MariaDB tourne (XAMPP control panel).\n";
    echo " - Vérifiez le port (par défaut 3306). Si XAMPP utilise 3307, mettez DB_PORT=3307 in config or env.\n";
    echo " - Vérifiez les identifiants DB_USER/DB_PASS dans config.php or env vars.\n";
    echo " - Test réseau local (PowerShell): Test-NetConnection -ComputerName localhost -Port " . DB_PORT . "\n";
    echo " - Regardez le fichier de log MySQL dans XAMPP\n";
    exit(1);
}
