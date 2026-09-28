<?php

class Education {
    private string $tableName = "my_education";
    private ?PDO $conn;

    // Entity Properties
    public ?int $id = null;
    public ?string $school_name = null;
    public ?string $course = null;
    public ?string $date_start = null;
    public ?string $date_end = null;
    public ?string $date_display = null;
    public ?string $location = null;
    public ?string $focus_areas = null;

    public function __construct(?PDO $db) {
        $this->conn = $db;
    }

    /**
     * Get all education entries, sorted chronologically (newest first).
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
     * Get an education record by ID.
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
     * Create a new education record.
     *
     * @param array $data
     * @return int|false Returns lastInsertId or false on failure
     */
    public function create(array $data): int|false {
        $query = "INSERT INTO {$this->tableName}
                    (school_name, course, date_start, date_end, date_display, location, focus_areas)
                  VALUES
                    (:school_name, :course, :date_start, :date_end, :date_display, :location, :focus_areas)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(':school_name', trim($data['school_name'] ?? ''));
        $stmt->bindValue(':course', trim($data['course'] ?? ''));
        $stmt->bindValue(':date_start', $data['date_start'] ?? null);
        $stmt->bindValue(':date_end', !empty($data['date_end']) ? $data['date_end'] : null);
        $stmt->bindValue(':date_display', trim($data['date_display'] ?? ''));
        $stmt->bindValue(':location', !empty($data['location']) ? trim($data['location']) : null);
        $stmt->bindValue(':focus_areas', !empty($data['focus_areas']) ? trim($data['focus_areas']) : null);

        if ($stmt->execute()) {
            return (int) $this->conn->lastInsertId();
        }

        return false;
    }

    /**
     * Update an existing education record by ID.
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
                    school_name  = :school_name,
                    course       = :course,
                    date_start   = :date_start,
                    date_end     = :date_end,
                    date_display = :date_display,
                    location     = :location,
                    focus_areas  = :focus_areas
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(':school_name', trim($data['school_name'] ?? ''));
        $stmt->bindValue(':course', trim($data['course'] ?? ''));
        $stmt->bindValue(':date_start', $data['date_start'] ?? null);
        $stmt->bindValue(':date_end', !empty($data['date_end']) ? $data['date_end'] : null);
        $stmt->bindValue(':date_display', trim($data['date_display'] ?? ''));
        $stmt->bindValue(':location', !empty($data['location']) ? trim($data['location']) : null);
        $stmt->bindValue(':focus_areas', !empty($data['focus_areas']) ? trim($data['focus_areas']) : null);
        $stmt->bindValue(':id', (int) $targetId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Delete an education record by ID.
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
        $this->id           = isset($row['id']) ? (int) $row['id'] : null;
        $this->school_name  = $row['school_name'] ?? null;
        $this->course       = $row['course'] ?? null;
        $this->date_start   = $row['date_start'] ?? null;
        $this->date_end     = $row['date_end'] ?? null;
        $this->date_display = $row['date_display'] ?? null;
        $this->location     = $row['location'] ?? null;
        $this->focus_areas  = $row['focus_areas'] ?? null;
    }
}
