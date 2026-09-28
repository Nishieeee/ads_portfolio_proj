<?php

class PageSection {
    private string $tableName = "page_sections";
    private ?PDO $conn;

    // Entity Properties
    public ?int $id = null;
    public ?string $section_key = null;
    public ?string $nav_label = null;
    public ?string $kicker = null;
    public ?string $title = null;
    public ?string $subtitle = null;
    public bool $is_visible = true;
    public int $order_index = 0;

    public function __construct(?PDO $db) {
        $this->conn = $db;
    }

    /**
     * Get all page sections, ordered by their display order index.
     * Optionally filter only visible sections.
     *
     * @param bool $visibleOnly
     * @return array
     */
    public function getAll(bool $visibleOnly = false): array {
        $query = "SELECT * FROM {$this->tableName}";
        if ($visibleOnly) {
            $query .= " WHERE is_visible = 1";
        }
        $query .= " ORDER BY order_index ASC, id ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    /**
     * Get a page section by ID.
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
     * Get a page section by its unique key (e.g. 'about', 'experience').
     *
     * @param string $key
     * @return array|null
     */
    public function getByKey(string $key): ?array {
        $query = "SELECT * FROM {$this->tableName} WHERE section_key = :key LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':key', $key);
        $stmt->execute();

        $row = $stmt->fetch();
        if ($row) {
            $this->populate($row);
            return $row;
        }

        return null;
    }

    /**
     * Create a new page section entry.
     *
     * @param array $data
     * @return int|false
     */
    public function create(array $data): int|false {
        $query = "INSERT INTO {$this->tableName}
                    (section_key, nav_label, kicker, title, subtitle, is_visible, order_index)
                  VALUES
                    (:section_key, :nav_label, :kicker, :title, :subtitle, :is_visible, :order_index)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(':section_key', trim($data['section_key'] ?? ''));
        $stmt->bindValue(':nav_label', trim($data['nav_label'] ?? ''));
        $stmt->bindValue(':kicker', !empty($data['kicker']) ? trim($data['kicker']) : null);
        $stmt->bindValue(':title', trim($data['title'] ?? ''));
        $stmt->bindValue(':subtitle', !empty($data['subtitle']) ? trim($data['subtitle']) : null);
        $stmt->bindValue(':is_visible', isset($data['is_visible']) && !$data['is_visible'] ? 0 : 1, PDO::PARAM_INT);
        $stmt->bindValue(':order_index', (int) ($data['order_index'] ?? 0), PDO::PARAM_INT);

        if ($stmt->execute()) {
            return (int) $this->conn->lastInsertId();
        }

        return false;
    }

    /**
     * Update a page section by ID.
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
                    nav_label   = :nav_label,
                    kicker      = :kicker,
                    title       = :title,
                    subtitle    = :subtitle,
                    is_visible  = :is_visible,
                    order_index = :order_index
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(':nav_label', trim($data['nav_label'] ?? ''));
        $stmt->bindValue(':kicker', !empty($data['kicker']) ? trim($data['kicker']) : null);
        $stmt->bindValue(':title', trim($data['title'] ?? ''));
        $stmt->bindValue(':subtitle', !empty($data['subtitle']) ? trim($data['subtitle']) : null);
        $stmt->bindValue(':is_visible', isset($data['is_visible']) && !$data['is_visible'] ? 0 : 1, PDO::PARAM_INT);
        $stmt->bindValue(':order_index', (int) ($data['order_index'] ?? 0), PDO::PARAM_INT);
        $stmt->bindValue(':id', (int) $targetId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Batch update section display orders in a single transaction.
     * Accepts an array of items: [ ['id' => 1, 'order_index' => 1], ... ]
     * or a flat list of IDs in ordered sequence: [ 3, 1, 2, ... ]
     *
     * @param array $orderList
     * @return bool
     */
    public function reorder(array $orderList): bool {
        try {
            $this->conn->beginTransaction();

            $query = "UPDATE {$this->tableName} SET order_index = :order_index WHERE id = :id";
            $stmt = $this->conn->prepare($query);

            foreach ($orderList as $index => $item) {
                if (is_array($item)) {
                    $id = (int) ($item['id'] ?? 0);
                    $order = (int) ($item['order_index'] ?? ($index + 1));
                } else {
                    $id = (int) $item;
                    $order = $index + 1;
                }

                if ($id > 0) {
                    $stmt->bindValue(':order_index', $order, PDO::PARAM_INT);
                    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                    $stmt->execute();
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
    }

    /**
     * Toggle or set the visibility of a section.
     *
     * @param int $id
     * @param bool|null $isVisible If null, toggles the current state
     * @return bool
     */
    public function toggleVisibility(int $id, ?bool $isVisible = null): bool {
        if ($isVisible === null) {
            $query = "UPDATE {$this->tableName} SET is_visible = CASE WHEN is_visible = 1 THEN 0 ELSE 1 END WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        }

        $query = "UPDATE {$this->tableName} SET is_visible = :is_visible WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':is_visible', $isVisible ? 1 : 0, PDO::PARAM_INT);
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
        $this->id          = isset($row['id']) ? (int) $row['id'] : null;
        $this->section_key = $row['section_key'] ?? null;
        $this->nav_label   = $row['nav_label'] ?? null;
        $this->kicker      = $row['kicker'] ?? null;
        $this->title       = $row['title'] ?? null;
        $this->subtitle    = $row['subtitle'] ?? null;
        $this->is_visible  = !empty($row['is_visible']);
        $this->order_index = (int) ($row['order_index'] ?? 0);
    }
}
