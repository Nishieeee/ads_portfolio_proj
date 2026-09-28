<?php

class Experience {
    private string $tableName = "my_experience";
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }
}
