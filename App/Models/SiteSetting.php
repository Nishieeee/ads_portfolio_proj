<?php

class SiteSetting {
    private string $tableName = "site_settings";
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }
}
