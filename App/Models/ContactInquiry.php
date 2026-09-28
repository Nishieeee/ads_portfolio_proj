<?php

class ContactInquiry {
    private string $tableName = "contact_inquiries";
    private ?PDO $conn;

    // Entity Properties
    public ?int $id = null;
    public ?string $sender_name = null;
    public ?string $sender_email = null;
    public ?string $subject = null;
    public ?string $message = null;
    public bool $is_read = false;
    public ?string $created_at = null;

    public function __construct(?PDO $db) {
        $this->conn = $db;
    }

    /**
     * Retrieve all inquiries, ordered newest first.
     * Optionally filter only unread messages.
     *
     * @param bool $unreadOnly
     * @return array
     */
    public function getAll(bool $unreadOnly = false): array {
        $query = "SELECT * FROM {$this->tableName}";
        if ($unreadOnly) {
            $query .= " WHERE is_read = 0";
        }
        $query .= " ORDER BY created_at DESC, id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    /**
     * Retrieve a single inquiry by ID.
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
     * Create a new incoming inquiry (public contact form submission).
     *
     * @param array $data
     * @return int|false Returns lastInsertId or false on failure
     */
    public function create(array $data): int|false {
        $query = "INSERT INTO {$this->tableName}
                    (sender_name, sender_email, subject, message, is_read)
                  VALUES
                    (:sender_name, :sender_email, :subject, :message, :is_read)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(':sender_name', trim($data['sender_name'] ?? ''));
        $stmt->bindValue(':sender_email', trim($data['sender_email'] ?? ''));
        $stmt->bindValue(':subject', trim($data['subject'] ?? ''));
        $stmt->bindValue(':message', trim($data['message'] ?? ''));
        $stmt->bindValue(':is_read', !empty($data['is_read']) ? 1 : 0, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return (int) $this->conn->lastInsertId();
        }

        return false;
    }

    /**
     * Toggle or set the read/unread status of an inquiry.
     *
     * @param int $id
     * @param bool $isRead
     * @return bool
     */
    public function setReadStatus(int $id, bool $isRead = true): bool {
        $query = "UPDATE {$this->tableName} SET is_read = :is_read WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':is_read', $isRead ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Delete an inquiry by ID.
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
     * Get the count of unread inquiries for dashboard notification badges.
     *
     * @return int
     */
    public function getUnreadCount(): int {
        $query = "SELECT COUNT(*) AS total FROM {$this->tableName} WHERE is_read = 0";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $result = $stmt->fetch();
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Populate model properties from an associative row array.
     *
     * @param array $row
     * @return void
     */
    public function populate(array $row): void {
        $this->id           = isset($row['id']) ? (int) $row['id'] : null;
        $this->sender_name  = $row['sender_name'] ?? null;
        $this->sender_email = $row['sender_email'] ?? null;
        $this->subject      = $row['subject'] ?? null;
        $this->message      = $row['message'] ?? null;
        $this->is_read      = !empty($row['is_read']);
        $this->created_at   = $row['created_at'] ?? null;
    }
}
