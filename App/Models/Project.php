<?php

class Project {
    private string $tableName = "my_projects";
    private ?PDO $conn;

    // Entity Properties
    public ?int $id = null;
    public ?string $project_name = null;
    public ?string $subtitle = null;
    public ?string $description = null;
    public ?string $technologies = null;
    public ?string $url = null;
    public ?string $github_repo = null;
    public ?string $date_start = null;
    public ?string $date_end = null;
    public bool $is_featured = false;
    public ?string $badge = null;

    public function __construct(?PDO $db) {
        $this->conn = $db;
    }

    /**
     * Get all projects, optionally filtered by featured status.
     * Ordered by featured first, then newest start date.
     *
     * @param bool $featuredOnly
     * @return array
     */
    public function getAll(bool $featuredOnly = false): array {
        $query = "SELECT * FROM {$this->tableName}";
        if ($featuredOnly) {
            $query .= " WHERE is_featured = 1";
        }
        $query .= " ORDER BY is_featured DESC, date_start DESC, id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    /**
     * Get a project by ID.
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
     * Create a new project.
     *
     * @param array $data
     * @return int|false Returns lastInsertId or false on failure
     */
    public function create(array $data): int|false {
        $query = "INSERT INTO {$this->tableName}
                    (project_name, subtitle, description, technologies, url, github_repo, date_start, date_end, is_featured, badge)
                  VALUES
                    (:project_name, :subtitle, :description, :technologies, :url, :github_repo, :date_start, :date_end, :is_featured, :badge)";

        $stmt = $this->conn->prepare($query);

        $techStr = is_array($data['technologies'] ?? null)
            ? implode(', ', $data['technologies'])
            : trim($data['technologies'] ?? '');

        $stmt->bindValue(':project_name', trim($data['project_name'] ?? ''));
        $stmt->bindValue(':subtitle', !empty($data['subtitle']) ? trim($data['subtitle']) : null);
        $stmt->bindValue(':description', trim($data['description'] ?? ''));
        $stmt->bindValue(':technologies', $techStr);
        $stmt->bindValue(':url', !empty($data['url']) ? trim($data['url']) : null);
        $stmt->bindValue(':github_repo', !empty($data['github_repo']) ? trim($data['github_repo']) : null);
        $stmt->bindValue(':date_start', $data['date_start'] ?? null);
        $stmt->bindValue(':date_end', !empty($data['date_end']) ? $data['date_end'] : null);
        $stmt->bindValue(':is_featured', !empty($data['is_featured']) ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':badge', !empty($data['badge']) ? trim($data['badge']) : null);

        if ($stmt->execute()) {
            return (int) $this->conn->lastInsertId();
        }

        return false;
    }

    /**
     * Update an existing project by ID.
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
                    project_name = :project_name,
                    subtitle     = :subtitle,
                    description  = :description,
                    technologies = :technologies,
                    url          = :url,
                    github_repo  = :github_repo,
                    date_start   = :date_start,
                    date_end     = :date_end,
                    is_featured  = :is_featured,
                    badge        = :badge
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $techStr = is_array($data['technologies'] ?? null)
            ? implode(', ', $data['technologies'])
            : trim($data['technologies'] ?? '');

        $stmt->bindValue(':project_name', trim($data['project_name'] ?? ''));
        $stmt->bindValue(':subtitle', !empty($data['subtitle']) ? trim($data['subtitle']) : null);
        $stmt->bindValue(':description', trim($data['description'] ?? ''));
        $stmt->bindValue(':technologies', $techStr);
        $stmt->bindValue(':url', !empty($data['url']) ? trim($data['url']) : null);
        $stmt->bindValue(':github_repo', !empty($data['github_repo']) ? trim($data['github_repo']) : null);
        $stmt->bindValue(':date_start', $data['date_start'] ?? null);
        $stmt->bindValue(':date_end', !empty($data['date_end']) ? $data['date_end'] : null);
        $stmt->bindValue(':is_featured', !empty($data['is_featured']) ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':badge', !empty($data['badge']) ? trim($data['badge']) : null);
        $stmt->bindValue(':id', (int) $targetId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Delete a project by ID.
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
     * Helper to retrieve technologies as an array of tags.
     *
     * @param string|null $technologies
     * @return array
     */
    public function getTechnologiesArray(?string $technologies = null): array {
        $raw = $technologies ?? $this->technologies ?? '';
        if (empty(trim($raw))) {
            return [];
        }
        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    /**
     * Populate model properties from an associative row array.
     *
     * @param array $row
     * @return void
     */
    public function populate(array $row): void {
        $this->id           = isset($row['id']) ? (int) $row['id'] : null;
        $this->project_name = $row['project_name'] ?? null;
        $this->subtitle     = $row['subtitle'] ?? null;
        $this->description  = $row['description'] ?? null;
        $this->technologies = $row['technologies'] ?? null;
        $this->url          = $row['url'] ?? null;
        $this->github_repo  = $row['github_repo'] ?? null;
        $this->date_start   = $row['date_start'] ?? null;
        $this->date_end     = $row['date_end'] ?? null;
        $this->is_featured  = !empty($row['is_featured']);
        $this->badge        = $row['badge'] ?? null;
    }
}
