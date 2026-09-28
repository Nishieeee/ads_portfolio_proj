<?php

class Experience {
    private string $tableName = "my_experience";
    private ?PDO $conn;

    // Entity Properties
    public ?int $id = null;
    public ?string $job_title = null;
    public ?string $company_name = null;
    public ?string $location = null;
    public ?string $description_1 = null;
    public ?string $description_2 = null;
    public ?string $description_3 = null;
    public ?string $date_start = null;
    public ?string $date_end = null;
    public ?string $date_display = null;

    public function __construct(?PDO $db) {
        $this->conn = $db;
    }

    /**
     * Get all experience entries, ordered chronologically newest first.
     *
     * @return array
     */
    public function getAll(): array {
        $query = "SELECT * FROM {$this->tableName} ORDER BY date_start DESC, id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    /**
     * Get an experience record by ID.
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
     * Create a new work experience record.
     *
     * @param array $data
     * @return int|false Returns lastInsertId or false on failure
     */
    public function create(array $data): int|false {
        $query = "INSERT INTO {$this->tableName}
                    (job_title, company_name, location, description_1, description_2, description_3, date_start, date_end, date_display)
                  VALUES
                    (:job_title, :company_name, :location, :description_1, :description_2, :description_3, :date_start, :date_end, :date_display)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(':job_title', trim($data['job_title'] ?? ''));
        $stmt->bindValue(':company_name', trim($data['company_name'] ?? ''));
        $stmt->bindValue(':location', !empty($data['location']) ? trim($data['location']) : null);
        $stmt->bindValue(':description_1', trim($data['description_1'] ?? ''));
        $stmt->bindValue(':description_2', trim($data['description_2'] ?? ''));
        $stmt->bindValue(':description_3', trim($data['description_3'] ?? ''));
        $stmt->bindValue(':date_start', $data['date_start'] ?? null);
        $stmt->bindValue(':date_end', !empty($data['date_end']) ? $data['date_end'] : null);
        $stmt->bindValue(':date_display', trim($data['date_display'] ?? ''));

        if ($stmt->execute()) {
            return (int) $this->conn->lastInsertId();
        }

        return false;
    }

    /**
     * Update an experience record by ID.
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
                    job_title     = :job_title,
                    company_name  = :company_name,
                    location      = :location,
                    description_1 = :description_1,
                    description_2 = :description_2,
                    description_3 = :description_3,
                    date_start    = :date_start,
                    date_end      = :date_end,
                    date_display  = :date_display
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(':job_title', trim($data['job_title'] ?? ''));
        $stmt->bindValue(':company_name', trim($data['company_name'] ?? ''));
        $stmt->bindValue(':location', !empty($data['location']) ? trim($data['location']) : null);
        $stmt->bindValue(':description_1', trim($data['description_1'] ?? ''));
        $stmt->bindValue(':description_2', trim($data['description_2'] ?? ''));
        $stmt->bindValue(':description_3', trim($data['description_3'] ?? ''));
        $stmt->bindValue(':date_start', $data['date_start'] ?? null);
        $stmt->bindValue(':date_end', !empty($data['date_end']) ? $data['date_end'] : null);
        $stmt->bindValue(':date_display', trim($data['date_display'] ?? ''));
        $stmt->bindValue(':id', (int) $targetId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Delete an experience record by ID.
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
     * Populate model properties from an associative row array.
     *
     * @param array $row
     * @return void
     */
    public function populate(array $row): void {
        $this->id            = isset($row['id']) ? (int) $row['id'] : null;
        $this->job_title     = $row['job_title'] ?? null;
        $this->company_name  = $row['company_name'] ?? null;
        $this->location      = $row['location'] ?? null;
        $this->description_1 = $row['description_1'] ?? null;
        $this->description_2 = $row['description_2'] ?? null;
        $this->description_3 = $row['description_3'] ?? null;
        $this->date_start    = $row['date_start'] ?? null;
        $this->date_end      = $row['date_end'] ?? null;
        $this->date_display  = $row['date_display'] ?? null;
    }
}
