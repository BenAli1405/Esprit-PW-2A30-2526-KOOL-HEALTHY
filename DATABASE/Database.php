<?php
// ========== DATABASE CONNECTION CLASS ==========
class Database {
    private $host;
    private $db_name;
    private $user;
    private $pass;
    private $conn;

    public function __construct() {
        $this->host = DB_HOST;
        $this->db_name = DB_NAME;
        $this->user = DB_USER;
        $this->pass = DB_PASS;
    }

    // Connect to database
    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                'mysql:host=' . $this->host . ';dbname=' . $this->db_name . ';charset=utf8mb4',
                $this->user,
                $this->pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $this->conn->exec("SET NAMES utf8mb4");
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }

        return $this->conn;
    }

    // Get connection
    public function getConnection() {
        if ($this->conn === null) {
            return $this->connect();
        }
        return $this->conn;
    }

    // Execute query
    public function query($sql) {
        return $this->getConnection()->prepare($sql);
    }

    // Execute and fetch one
    public function fetchOne($sql, $params = []) {
        try {
            $stmt = $this->query($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database Query Error: " . $e->getMessage() . " | SQL: " . $sql);
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }

    // Execute and fetch all
    public function fetchAll($sql, $params = []) {
        try {
            $stmt = $this->query($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database Query Error: " . $e->getMessage() . " | SQL: " . $sql);
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }

    // Execute insert/update/delete
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->query($sql);
            $result = $stmt->execute($params);
            $rowCount = $stmt->rowCount();
            error_log("Execute: " . substr($sql, 0, 60) . "... params: " . json_encode($params) . " | affected rows: $rowCount | result: " . ($result ? 'true' : 'false'));
            return $result;
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage() . " | SQL: " . $sql . " | Params: " . json_encode($params));
            return false;
        }
    }

    // Get last insert ID
    public function lastInsertId() {
        return $this->getConnection()->lastInsertId();
    }
}
?>
