<?php
require_once 'config/db.php';

class Loan {
    private $db;

    public function __construct() {
        $this->db = (new Database())->conn;
    }

    public function getAllLoans() {
        $stmt = $this->db->query("SELECT loans.*, books.title, members.name 
                                  FROM loans 
                                  JOIN books ON loans.book_id = books.id 
                                  JOIN members ON loans.member_id = members.id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function borrowBook($book_id, $member_id) {
        $stmt = $this->db->prepare("INSERT INTO loans (book_id, member_id, loan_date) VALUES (?, ?, CURDATE())");
        $book = new Book();
        $bookData = $this->db->query("SELECT stock FROM books WHERE id = $book_id")->fetch();
        if ($bookData['stock'] > 0) {
            $book->updateStock($book_id, $bookData['stock'] - 1);
            return $stmt->execute([$book_id, $member_id]);
        }
        return false;
    }

    public function returnBook($loan_id) {
        $stmt = $this->db->prepare("UPDATE loans SET return_date = CURDATE() WHERE id = ?");
        $loan = $this->db->query("SELECT book_id FROM loans WHERE id = $loan_id")->fetch();
        $book = new Book();
        $bookData = $this->db->query("SELECT stock FROM books WHERE id = {$loan['book_id']}")->fetch();
        $book->updateStock($loan['book_id'], $bookData['stock'] + 1);
        return $stmt->execute([$loan_id]);
    }
}
?>