<?php
class BookController {
    private $pdo;
    private $logger;

    public function __construct($db, $logger) {
        $this->pdo = $db->pdo;
        $this->logger = $logger;
    }

    /**
     * 1. REGISTER NEW BOOK (Accepts Admin Name)
     */
    public function registerBook($title, $author_name, $category, $isbn, $qty, $cover_image, $admin_name) {
        try {
            // A. Handle Author
            $nameParts = explode(' ', trim($author_name), 2);
            $fname = $nameParts[0];
            $lname = $nameParts[1] ?? ''; 

            $stmtAuth = $this->pdo->prepare("SELECT author_id FROM authors WHERE author_firstname = ? AND author_lastname = ?");
            $stmtAuth->execute([$fname, $lname]);
            $author = $stmtAuth->fetch();

            if ($author) {
                $author_id = $author['author_id'];
            } else {
                $stmtNewAuth = $this->pdo->prepare("INSERT INTO authors (author_firstname, author_lastname) VALUES (?, ?)");
                $stmtNewAuth->execute([$fname, $lname]);
                $author_id = $this->pdo->lastInsertId();
            }

            // B. Insert Book
            if (empty($cover_image)) $cover_image = 'https://placehold.co/200x300?text=No+Cover';

            $sql = "INSERT INTO books (title, author_id, isbn, category, total_copies, available_copies, cover_image) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$title, $author_id, $isbn, $category, $qty, $qty, $cover_image]);
            $book_id = $this->pdo->lastInsertId();

            // C. Log (Already has title from arguments)
            $this->logger->logBook($book_id, "NEW_BOOK", "Registered '$title' ($qty copies). By: $admin_name");

            return ['success' => "Book '$title' added successfully!"];

        } catch (Exception $e) {
            return ['error' => "Error adding book: " . $e->getMessage()];
        }
    }

    /**
     * 2. UPDATE STOCK (Accepts Admin Name & Logs Book Title)
     */
    public function updateStock($book_id, $qty_change, $admin_name) {
        try {
            // Get Book Title first so we can log it
            $stmtGet = $this->pdo->prepare("SELECT title, total_copies FROM books WHERE book_id = ?");
            $stmtGet->execute([$book_id]);
            $book = $stmtGet->fetch();

            if (!$book) return ['error' => "Book not found."];

            // Update Database
            $sql = "UPDATE books SET total_copies = total_copies + ?, available_copies = available_copies + ? WHERE book_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$qty_change, $qty_change, $book_id]);

            // Log with Title and Admin Name
            $action = ($qty_change > 0) ? "STOCK_ADD" : "STOCK_REMOVE";
            $msg = "Stock changed for '{$book['title']}' by $qty_change. (Old Total: {$book['total_copies']}). By: $admin_name";
            
            $this->logger->logBook($book_id, $action, $msg);

            return ['success' => "Stock updated for '{$book['title']}'."];

        } catch (Exception $e) {
            return ['error' => "Error updating stock: " . $e->getMessage()];
        }
    }
}
?>