<?php

class AdminUser {
    private string $tableName = "admin_users";
    private ?PDO $conn;

    // Entity Properties
    public ?int $id = null;
    public ?string $username = null;
    public ?string $password_hash = null;
    public ?string $auth_token = null;
    public ?string $token_expires_at = null;
    public ?string $created_at = null;

    public function __construct(?PDO $db) {
        $this->conn = $db;
    }

    /**
     * Find an admin user by username.
     *
     * @param string $username
     * @return array|null
     */
    public function getByUsername(string $username): ?array {
        $query = "SELECT * FROM {$this->tableName} WHERE username = :username LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':username', trim($username));
        $stmt->execute();

        $row = $stmt->fetch();
        if ($row) {
            $this->populate($row);
            return $row;
        }

        return null;
    }

    /**
     * Find an admin user by valid active token.
     * Checks both token match and expiration timestamp.
     *
     * @param string $token
     * @return array|null
     */
    public function getByToken(string $token): ?array {
        $query = "SELECT id, username, created_at FROM {$this->tableName} 
                  WHERE auth_token = :token 
                    AND (token_expires_at IS NULL OR token_expires_at > NOW()) 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':token', trim($token));
        $stmt->execute();

        $row = $stmt->fetch();
        if ($row) {
            $this->id = (int) $row['id'];
            $this->username = $row['username'];
            return $row;
        }

        return null;
    }

    /**
     * Verify a plaintext password against a stored bcrypt hash.
     *
     * @param string $plainPassword
     * @param string $hash
     * @return bool
     */
    public function verifyPassword(string $plainPassword, string $hash): bool {
        return password_verify($plainPassword, $hash);
    }

    /**
     * Generate and store a cryptographically secure auth token.
     *
     * @param int $userId
     * @param int $daysValid Default 7 days
     * @return string Generated token
     */
    public function generateToken(int $userId, int $daysValid = 7): string {
        $token = bin2hex(random_bytes(32)); // 64-char hex string
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$daysValid} days"));

        $query = "UPDATE {$this->tableName} 
                  SET auth_token = :token, token_expires_at = :expires_at 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':token', $token);
        $stmt->bindValue(':expires_at', $expiresAt);
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $token;
    }

    /**
     * Invalidate/revoke an active token (logout).
     *
     * @param int $userId
     * @return bool
     */
    public function revokeToken(int $userId): bool {
        $query = "UPDATE {$this->tableName} 
                  SET auth_token = NULL, token_expires_at = NULL 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Update an admin user's password.
     *
     * @param int $userId
     * @param string $newPassword
     * @return bool
     */
    public function updatePassword(int $userId, string $newPassword): bool {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);

        $query = "UPDATE {$this->tableName} SET password_hash = :hash WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':hash', $hash);
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Populate model properties from an associative row array.
     *
     * @param array $row
     * @return void
     */
    public function populate(array $row): void {
        $this->id               = isset($row['id']) ? (int) $row['id'] : null;
        $this->username         = $row['username'] ?? null;
        $this->password_hash    = $row['password_hash'] ?? null;
        $this->auth_token       = $row['auth_token'] ?? null;
        $this->token_expires_at = $row['token_expires_at'] ?? null;
        $this->created_at       = $row['created_at'] ?? null;
    }
}
