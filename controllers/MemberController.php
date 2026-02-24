<?php
class MemberController {
    private $pdo;
    private $logger;

    public function __construct($db, $logger) {
        $this->pdo = $db->pdo;
        $this->logger = $logger;
    }

    // MEMBER REGISTRATION
    public function registerMember($username, $fname, $lname, $phone, $email, $password) {
        try {
            $stmt = $this->pdo->prepare("SELECT member_id FROM members WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) return ['error' => "Username '$username' is already taken."];

            $sql = "INSERT INTO members (username, members_firstname, members_lastname, mobile_number, email, password) VALUES (?, ?, ?, ?, ?, ?)";
            $stmtInsert = $this->pdo->prepare($sql);
            
            if ($stmtInsert->execute([$username, $fname, $lname, $phone, $email, $password])) {
                $this->logger->log('loan_histories', ['action' => 'NEW_MEMBER', 'details' => "Member '$username' registered."]);
                return ['success' => "Welcome, $fname! Registration successful."];
            }
            return ['error' => "Database error."];

        } catch (Exception $e) {
            return ['error' => "System Error: " . $e->getMessage()];
        }
    }

    // --- NEW: REGISTER ADMIN (With Employee Code) ---
    public function registerAdmin($employee_code, $password, $fname, $lname) {
        try {
            // Validate 6-digit code
            if (!preg_match('/^\d{6}$/', $employee_code)) {
                return ['error' => "Employee Code must be exactly 6 digits."];
            }

            // Check duplicate
            $stmt = $this->pdo->prepare("SELECT admin_id FROM admins WHERE employee_code = ?");
            $stmt->execute([$employee_code]);
            if ($stmt->fetch()) return ['error' => "Employee Code '$employee_code' is already assigned."];

            // Insert
            $stmtInsert = $this->pdo->prepare("INSERT INTO admins (employee_code, password, firstname, lastname) VALUES (?, ?, ?, ?)");
            
            if ($stmtInsert->execute([$employee_code, $password, $fname, $lname])) {
                $this->logger->log('loan_histories', ['action' => 'NEW_ADMIN', 'details' => "Admin #$employee_code created."]);
                return ['success' => "Administrator $fname $lname (ID: $employee_code) registered!"];
            }
            return ['error' => "Database error."];

        } catch (Exception $e) {
            return ['error' => "System Error: " . $e->getMessage()];
        }
    }
}
?>