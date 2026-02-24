<?php
class Member {
    private $pdo;

    public function __construct($pdo) { $this->pdo = $pdo; }

    // Check if Username OR Email exists
    public function exists($username, $email) {
        $stmt = $this->pdo->prepare("SELECT member_id FROM members WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        return $stmt->rowCount() > 0;
    }

    // Register with Username, Email, Password
    public function register($username, $fname, $lname, $phone, $email, $password) {
        $stmt = $this->pdo->prepare("INSERT INTO members (username, members_firstname, members_lastname, mobile_number, email, password, role) VALUES (?, ?, ?, ?, ?, ?, 'member')");
        return $stmt->execute([$username, $fname, $lname, $phone, $email, $password]);
    }
}
?>