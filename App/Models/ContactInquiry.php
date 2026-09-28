<?php

class ContactInquiry {
    private string $tableName = "contact_inquiries";
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }
}
