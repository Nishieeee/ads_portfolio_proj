<?php

class Project {
    private string $tableName = "my_projects";
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }
}
