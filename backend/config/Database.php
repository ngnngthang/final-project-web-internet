<?php

class Database {
    private $host = 'localhost';
    private $db_name = 'final_project';
    private $db_user = 'root';
    private $db_pass = '';
    private $conn;

    public function connect() {
        $this->conn = new mysqli(
            $this->host,
            $this->db_user,
            $this->db_pass,
            $this->db_name
        );

        if ($this->conn->connect_error) {
            die('Connection Error: ' . $this->conn->connect_error);
        }

        $this->conn->set_charset("utf8");
        return $this->conn;
    }

    public function getConnection() {
        return $this->conn;
    }
}
