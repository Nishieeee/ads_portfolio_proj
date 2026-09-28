<?php

class SiteSetting {
    private string $tableName = "site_settings";
    private ?PDO $conn;

    public function __construct(?PDO $db) {
        $this->conn = $db;
    }

    /**
     * Retrieve all settings formatted as a key => value associative dictionary.
     *
     * @return array
     */
    public function getAll(): array {
        $query = "SELECT setting_key, setting_value FROM {$this->tableName}";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        return $settings;
    }

    /**
     * Get a specific setting value by key.
     *
     * @param string $key
     * @param string|null $default
     * @return string|null
     */
    public function get(string $key, ?string $default = null): ?string {
        $query = "SELECT setting_value FROM {$this->tableName} WHERE setting_key = :key LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':key', $key);
        $stmt->execute();

        $row = $stmt->fetch();
        return $row ? $row['setting_value'] : $default;
    }

    /**
     * Set/update a setting value (upsert using ON DUPLICATE KEY UPDATE).
     *
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function set(string $key, string $value): bool {
        $query = "INSERT INTO {$this->tableName} (setting_key, setting_value)
                  VALUES (:key, :value)
                  ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':key', trim($key));
        $stmt->bindValue(':value', trim($value));

        return $stmt->execute();
    }

    /**
     * Batch update multiple settings in a single transaction.
     *
     * @param array $settings Associative array [key => value]
     * @return bool
     */
    public function updateMultiple(array $settings): bool {
        try {
            $this->conn->beginTransaction();

            $query = "INSERT INTO {$this->tableName} (setting_key, setting_value)
                      VALUES (:key, :value)
                      ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
            $stmt = $this->conn->prepare($query);

            foreach ($settings as $key => $value) {
                $stmt->bindValue(':key', trim($key));
                $stmt->bindValue(':value', trim((string) $value));
                $stmt->execute();
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
}
