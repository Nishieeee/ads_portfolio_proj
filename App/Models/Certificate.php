<?php

class Certificate {
    private string $tableName = "my_certificates";
    private ?PDO $conn;

    // Entity Properties
    public ?int $id = null;
    public ?string $title = null;
    public ?string $issuer = null;
    public ?string $date_display = null;
    public ?string $description = null;
    public ?string $cert_id = null;
    public ?string $cert_url = null;

    public function __construct(?PDO $db) {
        $this->conn = $db;
    }

    /**
     * Get all certificates
     *  
     * @return array
     */
    public function getAll(): array {
        $query = "SELECT * FROM {$this->tableName} ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    /**
     * Get certificate by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array {
        $query = "SELECT * FROM {$this->tableName} WHERE id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();
        if ($row) {
            $this->populate($row);
            return $row;
        }
        return null;
    }

    /**
     * Create new certificate
     * 
     * @param array $data
     * @return int|false
     */
    public function create(array $data): int|false {
        $query = "INSERT INTO {$this->tableName}
                    (title, issuer, date_display, description, cert_id, cert_url)
                  VALUES
                    (:title, :issuer, :date_display, :description, :cert_id, :cert_url)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(':title', trim($data['title'] ?? ''));
        $stmt->bindValue(':issuer', trim($data['issuer'] ?? ''));
        $stmt->bindValue(':date_display', !empty($data['date_display']) ? trim($data['date_display']) : null);
        $stmt->bindValue(':description', !empty($data['description']) ? trim($data['description']) : null);
        $stmt->bindValue(':cert_id', !empty($data['cert_id']) ? trim($data['cert_id']) : null);
        $stmt->bindValue(':cert_url', !empty($data['cert_url']) ? trim($data['cert_url']) : null);

        if ($stmt->execute()) {
            return (int) $this->conn->lastInsertId();
        }

        return false;
    }

    /**
     * Update an existing certificate
     * 
     * @param array $data
     * @param int|null $id
     * @return bool
     */
    public function update(array $data, ?int $id = null): bool {
        $targetId = $id ?? $data['id'] ?? $this->id;

        if (!$targetId) {
            return false;
        }

        $query = "UPDATE {$this->tableName} SET
                    title        = :title,
                    issuer       = :issuer,
                    date_display = :date_display,
                    description  = :description,
                    cert_id      = :cert_id,
                    cert_url     = :cert_url
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(':title', trim($data['title'] ?? ''));
        $stmt->bindValue(':issuer', trim($data['issuer'] ?? ''));
        $stmt->bindValue(':date_display', !empty($data['date_display']) ? trim($data['date_display']) : null);
        $stmt->bindValue(':description', !empty($data['description']) ? trim($data['description']) : null);
        $stmt->bindValue(':cert_id', !empty($data['cert_id']) ? trim($data['cert_id']) : null);
        $stmt->bindValue(':cert_url', !empty($data['cert_url']) ? trim($data['cert_url']) : null);
        $stmt->bindValue(':id', (int) $targetId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Delete certificate via ID
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        $query = "DELETE FROM {$this->tableName} WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Populate model properties from an associative row array
     *
     * @param array $row
     * @return void
     */
    public function populate(array $row): void {
        $this->id           = isset($row['id']) ? (int) $row['id'] : null;
        $this->title        = $row['title'] ?? null;
        $this->issuer       = $row['issuer'] ?? null;
        $this->date_display = $row['date_display'] ?? null;
        $this->description  = $row['description'] ?? null;
        $this->cert_id      = $row['cert_id'] ?? null;
        $this->cert_url     = $row['cert_url'] ?? null;
    }
}
