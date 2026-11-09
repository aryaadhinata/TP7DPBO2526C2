<?php
require_once 'config/db.php';

class Member {
    private $db;

    public function __construct() {
        $this->db = (new Database())->conn;
    }

    public function getAllMembers() {
        $stmt = $this->db->query("SELECT * FROM members");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>