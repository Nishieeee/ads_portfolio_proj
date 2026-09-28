<?php

class ContactInfo {
    private string $tableName = "my_contact_info";
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }
}
