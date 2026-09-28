<?php

class Education {
    private string $tableName = "my_education";
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }
}
