<?php 

class Database {
    private string $host = "localhost";
    private string $db_name = "portfolio_cms";
    private string $username = "root";
    private string $password = "";
    private string $charset = "utf8mb4";
    public ?PDO $conn = null;

    /**
     * Establish and return PDO database connection.
     *
     * @return PDO|null
     */
    public function getConnection(): ?PDO {
        $this->conn = null;

        $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $exception) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Database connection error.",
                "error"   => $exception->getMessage()
            ]);
            exit();
        }

        return $this->conn;
    }
}