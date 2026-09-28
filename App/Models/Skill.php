<?php

class Skill {
    private string $tableName = "my_skills";
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }
}
