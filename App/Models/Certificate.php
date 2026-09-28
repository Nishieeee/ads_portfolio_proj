<?php

class Certificate {
    private string $tableName = "my_certificates";
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }
}
