<?php

class Skill {
    private string $tableName = "my_skills";
    private ?PDO $conn;

    // Entity Properties
    public ?int $id = null;
    public ?string $skill_category = null; // 'technical' | 'soft'
    public ?string $category_label = null;
    public ?string $skills_list = null;

    public function __construct(?PDO $db) {
        $this->conn = $db;
    }

    /**
     * Get all skill categories with their skills list.
     * Optionally filter by category ('technical' or 'soft').
     *
     * @param string|null $category
     * @return array
     */
    public function getAll(?string $category = null): array {
        $query = "SELECT * FROM {$this->tableName}";
        if ($category) {
            $query .= " WHERE skill_category = :category";
        }
        $query .= " ORDER BY id ASC";

        $stmt = $this->conn->prepare($query);
        if ($category) {
            $stmt->bindValue(':category', $category);
        }
        $stmt->execute();

        $rows = $stmt->fetchAll() ?: [];
        return array_map([$this, 'cleanRow'], $rows);
    }

    /**
     * Get a skill group by ID.
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
            $row = $this->cleanRow($row);
            $this->populate($row);
            return $row;
        }

        return null;
    }

    /**
     * Create a new skill category group.
     *
     * @param array $data
     * @return int|false Returns lastInsertId or false on failure
     */
    public function create(array $data): int|false {
        $query = "INSERT INTO {$this->tableName}
                    (skill_category, category_label, skills_list)
                  VALUES
                    (:skill_category, :category_label, :skills_list)";

        $stmt = $this->conn->prepare($query);

        $categoryLabel = $this->cleanAmpersands(trim($data['category_label'] ?? ''));
        $skillsList = $this->cleanAmpersands($this->formatSkillsList($data['skills_list'] ?? $data['skills'] ?? ''));

        $stmt->bindValue(':skill_category', trim($data['skill_category'] ?? 'technical'));
        $stmt->bindValue(':category_label', $categoryLabel);
        $stmt->bindValue(':skills_list', $skillsList);

        if ($stmt->execute()) {
            return (int) $this->conn->lastInsertId();
        }

        return false;
    }

    /**
     * Update an existing skill group by ID.
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
                    skill_category = :skill_category,
                    category_label = :category_label,
                    skills_list    = :skills_list
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $categoryLabel = $this->cleanAmpersands(trim($data['category_label'] ?? ''));
        $skillsList = $this->cleanAmpersands($this->formatSkillsList($data['skills_list'] ?? $data['skills'] ?? ''));

        $stmt->bindValue(':skill_category', trim($data['skill_category'] ?? 'technical'));
        $stmt->bindValue(':category_label', $categoryLabel);
        $stmt->bindValue(':skills_list', $skillsList);
        $stmt->bindValue(':id', (int) $targetId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Delete a skill group by ID.
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
     * Helper to parse skills_list into an array of individual trimmed strings.
     *
     * @param string|null $list
     * @return array
     */
    public function getSkillsArray(?string $list = null): array {
        $raw = $list ?? $this->skills_list ?? '';
        if (empty(trim($raw))) {
            return [];
        }

        // Try JSON decode first in case it's stored as JSON
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return array_map('trim', $decoded);
        }

        // Fallback: comma-separated
        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    /**
     * Format array or string of skills into comma-separated text.
     *
     * @param mixed $input
     * @return string
     */
    private function formatSkillsList($input): string {
        if (is_array($input)) {
            return implode(', ', array_map('trim', $input));
        }
        return trim((string) $input);
    }

    /**
     * Clean ampersands from a skill row array.
     *
     * @param array $row
     * @return array
     */
    public function cleanRow(array $row): array {
        if (isset($row['category_label']) && is_string($row['category_label'])) {
            $row['category_label'] = $this->cleanAmpersands($row['category_label']);
        }
        if (isset($row['skills_list']) && is_string($row['skills_list'])) {
            $row['skills_list'] = $this->cleanAmpersands($row['skills_list']);
        }
        return $row;
    }

    /**
     * Normalize ampersands by decoding any stored &amp; or &amp;amp; entities into plain '&'.
     *
     * @param string $str
     * @return string
     */
    public function cleanAmpersands(string $str): string {
        while (str_contains($str, '&amp;')) {
            $str = str_replace('&amp;', '&', $str);
        }
        return $str;
    }

    /**
     * Populate model properties from an associative row array.
     *
     * @param array $row
     * @return void
     */
    public function populate(array $row): void {
        $this->id             = isset($row['id']) ? (int) $row['id'] : null;
        $this->skill_category = $row['skill_category'] ?? null;
        $this->category_label = isset($row['category_label']) ? $this->cleanAmpersands((string)$row['category_label']) : null;
        $this->skills_list    = isset($row['skills_list']) ? $this->cleanAmpersands((string)$row['skills_list']) : null;
    }
}
