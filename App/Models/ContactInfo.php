<?php

class ContactInfo {
    private string $tableName = "my_contact_info";
    private ?PDO $conn;

    // Entity Properties
    public ?int $id = null;
    public ?string $contact_name = null;
    public ?string $contact_type = null; // 'email' | 'phone_no' | 'url'
    public ?string $contact_info = null;

    public function __construct(?PDO $db) {
        $this->conn = $db;
    }

    /**
     * Get all available contact channels
     * 
     * @return array
     */
    public function getAll(): array {
        $query = "SELECT * FROM {$this->tableName} ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    /**
     * Get contact info by ID
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
     * Create a new contact channel
     * 
     * @param array $data
     * @return int|false
     */
    public function create(array $data): int|false {
        $query = "INSERT INTO {$this->tableName}
                    (contact_name, contact_type, contact_info)
                  VALUES
                    (:contact_name, :contact_type, :contact_info)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(':contact_name', trim($data['contact_name'] ?? ''));
        $stmt->bindValue(':contact_type', trim($data['contact_type'] ?? 'email'));
        $stmt->bindValue(':contact_info', trim($data['contact_info'] ?? ''));

        if ($stmt->execute()) {
            return (int) $this->conn->lastInsertId();
        }

        return false;
    }

    /**
     * Update contact info by ID
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
                    contact_name = :contact_name,
                    contact_type = :contact_type,
                    contact_info = :contact_info
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':contact_name', trim($data['contact_name'] ?? ''));
        $stmt->bindValue(':contact_type', trim($data['contact_type'] ?? 'email'));
        $stmt->bindValue(':contact_info', trim($data['contact_info'] ?? ''));
        $stmt->bindValue(':id', (int) $targetId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Delete contact info using ID
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
        $this->contact_name = $row['contact_name'] ?? null;
        $this->contact_type = $row['contact_type'] ?? null;
        $this->contact_info = $row['contact_info'] ?? null;
    }
}
