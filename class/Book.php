<?php
require_once 'config/db.php';

class Book {
    private $db;

    public function __construct() {
        $this->db = (new Database())->conn;
    }

    public function getAllBooks() {
        $stmt = $this->db->query("SELECT * FROM books");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStock($id, $stock) {
        $stmt = $this->db->prepare("UPDATE books SET stock = ? WHERE id = ?");
        return $stmt->execute([$stock, $id]);
    }
}
?>