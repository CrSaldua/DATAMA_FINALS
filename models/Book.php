<?php
class Book {
    private $pdo;

    public function __construct($pdo) { $this->pdo = $pdo; }

    public function find($id) {
        // UPDATED: Added description to the SELECT statement
        $stmt = $this->pdo->prepare("SELECT title, total_copies, description FROM books WHERE book_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateStock($id, $qty) {
        return $this->pdo->prepare("UPDATE books SET total_copies = total_copies + ? WHERE book_id = ?")->execute([$qty, $id]);
    }

    // UPDATED: Now accepts $description as a parameter
    public function create($title, $author, $cat, $isbn, $qty, $cover_image, $description) {
        // Author Logic
        $stmtA = $this->pdo->prepare("SELECT author_id FROM authors WHERE CONCAT(author_firstname,' ',author_lastname) LIKE ?");
        $stmtA->execute(["%$author%"]);
        $authRow = $stmtA->fetch();
        
        if ($authRow) {
            $auth_id = $authRow['author_id'];
        } else {
            $this->pdo->prepare("INSERT INTO authors (author_firstname, author_lastname) VALUES (?, '')")->execute([$author]);
            $auth_id = $this->pdo->lastInsertId();
        }

        // Insert Book with Cover Image AND Description
        // We calculate available_copies same as total_copies initially
        return $this->pdo->prepare("INSERT INTO books (title, author_id, category, isbn, total_copies, available_copies, cover_image, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([$title, $auth_id, $cat, $isbn, $qty, $qty, $cover_image, $description]);
    }
}
?>