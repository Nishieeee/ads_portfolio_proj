<?php

class PageSection {
    private string $tableName = "page_sections";
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }
}
