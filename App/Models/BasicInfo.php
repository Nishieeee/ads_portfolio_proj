<?php

class BasicInfo {
    private string $tableName = "my_basic_info";
    private ?PDO $conn = null;

    // Entity Properties
    public ?int $id = null;
    public ?string $first_name = null;
    public ?string $last_name = null;
    public ?string $middle_name = null;
    public ?string $birth_date = null;
    public ?string $role_title = null;
    public ?string $tagline = null;
    public ?string $avatar_url = null;
    public ?string $resume_url = null;
    public ?string $bio_greeting = null;
    public ?string $bio_paragraphs = null;

    /**
     * Constructor accepting PDO database instance
     *
     * @param PDO|null $db
     */
    public function __construct(?PDO $db) {
        $this->conn = $db;
    }

    /**
     * Fetch the primary basic info profile record.
     *
     * @return array|null
     */
    public function get(): ?array {
        $query = "SELECT * FROM {$this->tableName} ORDER BY id ASC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $row = $stmt->fetch();
        if ($row) {
            $this->populate($row);
            return $row;
        }

        return null;
    }

    /**
     * Fetch a basic info record by its ID.
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
     * Create a new basic info record.
     *
     * @param array $data
     * @return int|false Returns lastInsertId or false on failure
     */
    public function create(array $data): int|false {
        $bioGreeting = $data['bio_greeting'] ?? "Hi, I'm Clein!";
        $bioParagraphs = $data['bio_paragraphs'] ?? '';
        if (is_array($bioParagraphs)) {
            $bioParagraphs = json_encode($bioParagraphs);
        }

        $query = "INSERT INTO {$this->tableName} 
                    (first_name, last_name, middle_name, birth_date, role_title, tagline, avatar_url, resume_url, bio_greeting, bio_paragraphs)
                  VALUES 
                    (:first_name, :last_name, :middle_name, :birth_date, :role_title, :tagline, :avatar_url, :resume_url, :bio_greeting, :bio_paragraphs)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(':first_name', trim($data['first_name'] ?? ''));
        $stmt->bindValue(':last_name', trim($data['last_name'] ?? ''));
        $stmt->bindValue(':middle_name', !empty($data['middle_name']) ? trim($data['middle_name']) : null);
        $stmt->bindValue(':birth_date', $data['birth_date'] ?? null);
        $stmt->bindValue(':role_title', trim($data['role_title'] ?? ''));
        $stmt->bindValue(':tagline', trim($data['tagline'] ?? ''));
        $stmt->bindValue(':avatar_url', $data['avatar_url'] ?? null);
        $stmt->bindValue(':resume_url', $data['resume_url'] ?? null);
        $stmt->bindValue(':bio_greeting', $bioGreeting);
        $stmt->bindValue(':bio_paragraphs', $bioParagraphs);

        if ($stmt->execute()) {
            return (int) $this->conn->lastInsertId();
        }

        return false;
    }

    /**
     * Update an existing basic info record.
     *
     * @param array $data
     * @param int|null $id Optional ID; if omitted, targets first existing row or $this->id
     * @return bool
     */
    public function update(array $data, ?int $id = null): bool {
        $targetId = $id ?? $this->id;

        // If no ID provided, try to find the primary record ID
        if (!$targetId) {
            $current = $this->get();
            if ($current) {
                $targetId = (int) $current['id'];
            }
        }

        if (!$targetId) {
            return false;
        }

        $bioGreeting = $data['bio_greeting'] ?? "Hi, I'm Clein!";
        $bioParagraphs = $data['bio_paragraphs'] ?? '';
        if (is_array($bioParagraphs)) {
            $bioParagraphs = json_encode($bioParagraphs);
        }

        $query = "UPDATE {$this->tableName} SET 
                    first_name     = :first_name,
                    last_name      = :last_name,
                    middle_name    = :middle_name,
                    birth_date     = :birth_date,
                    role_title     = :role_title,
                    tagline        = :tagline,
                    avatar_url     = :avatar_url,
                    resume_url     = :resume_url,
                    bio_greeting   = :bio_greeting,
                    bio_paragraphs = :bio_paragraphs
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(':first_name', trim($data['first_name'] ?? ''));
        $stmt->bindValue(':last_name', trim($data['last_name'] ?? ''));
        $stmt->bindValue(':middle_name', !empty($data['middle_name']) ? trim($data['middle_name']) : null);
        $stmt->bindValue(':birth_date', $data['birth_date'] ?? null);
        $stmt->bindValue(':role_title', trim($data['role_title'] ?? ''));
        $stmt->bindValue(':tagline', trim($data['tagline'] ?? ''));
        $stmt->bindValue(':avatar_url', $data['avatar_url'] ?? null);
        $stmt->bindValue(':resume_url', $data['resume_url'] ?? null);
        $stmt->bindValue(':bio_greeting', $bioGreeting);
        $stmt->bindValue(':bio_paragraphs', $bioParagraphs);
        $stmt->bindValue(':id', $targetId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Upsert: Saves data by updating if a record exists, or creating one if empty.
     *
     * @param array $data
     * @return bool
     */
    public function save(array $data): bool {
        $existing = $this->get();
        if ($existing) {
            return $this->update($data, (int) $existing['id']);
        }
        return (bool) $this->create($data);
    }

    /**
     * Populate model properties from an array.
     *
     * @param array $row
     * @return void
     */
    private function populate(array $row): void {
        $this->id             = isset($row['id']) ? (int) $row['id'] : null;
        $this->first_name     = $row['first_name'] ?? null;
        $this->last_name      = $row['last_name'] ?? null;
        $this->middle_name    = $row['middle_name'] ?? null;
        $this->birth_date     = $row['birth_date'] ?? null;
        $this->role_title     = $row['role_title'] ?? null;
        $this->tagline        = $row['tagline'] ?? null;
        $this->avatar_url     = $row['avatar_url'] ?? null;
        $this->resume_url     = $row['resume_url'] ?? null;
        $this->bio_greeting   = $row['bio_greeting'] ?? null;
        $this->bio_paragraphs = $row['bio_paragraphs'] ?? null;
    }

    /**
     * Helper to compute full formatted name.
     *
     * @param array|null $row Optional row data; uses loaded properties if null
     * @return string
     */
    public function getFullName(?array $row = null): string {
        $first  = $row['first_name'] ?? $this->first_name ?? '';
        $middle = $row['middle_name'] ?? $this->middle_name ?? '';
        $last   = $row['last_name'] ?? $this->last_name ?? '';

        $parts = array_filter([$first, $middle, $last], fn($p) => !empty(trim($p)));
        return implode(' ', $parts);
    }

    /**
     * Helper to calculate age in years from birth_date.
     *
     * @param string|null $birthDate Optional YYYY-MM-DD string
     * @return int|null
     */
    public function getAge(?string $birthDate = null): ?int {
        $dateStr = $birthDate ?? $this->birth_date;
        if (!$dateStr) {
            return null;
        }

        try {
            $birth = new DateTime($dateStr);
            $today = new DateTime('today');
            return $birth->diff($today)->y;
        } catch (Exception $e) {
            return null;
        }
    }
}