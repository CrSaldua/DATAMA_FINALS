<?php
class AuthController {
    private $pdo;

    public function __construct($db) {
        $this->pdo = $db->pdo;
    }

    // --- 1. ADMIN LOGIN (Updated to save Employee Code in Session) ---
    public function loginAdmin($employee_code, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM admins WHERE employee_code = ?");
        $stmt->execute([$employee_code]);
        $admin = $stmt->fetch();

        if ($admin && $password === $admin['password']) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['employee_code'] = $admin['employee_code']; // SAVE THIS for logs!
            $_SESSION['admin_name'] = $admin['firstname'] . ' ' . $admin['lastname'];
            $_SESSION['role'] = 'admin';
            return ['success' => 'Welcome, ' . $admin['firstname']];
        }

        return ['error' => 'Invalid Employee Code or Password.'];
    }

    // --- 2. MEMBER LOGIN (Updated with @member strip & Regex check) ---
    public function loginMember($username, $password) {
        
        // 1. Clean the input (remove spaces and the @member suffix if it was passed)
        $clean_username = str_replace('@member', '', trim($username));
        
        // 2. STRICT VALIDATION: Must be 4-20 chars, letters/numbers only, must contain a letter
        if (!preg_match('/^(?=.*[a-zA-Z])[a-zA-Z0-9_]{4,20}$/', $clean_username)) {
            return ['error' => 'Invalid username format. Letters and numbers only.'];
        }

        // 3. SECURE QUERY: Search using the cleaned username
        $stmt = $this->pdo->prepare("SELECT * FROM members WHERE username = ?");
        $stmt->execute([$clean_username]);
        $member = $stmt->fetch();

        if ($member && $password === $member['password']) {
            $_SESSION['member_id'] = $member['member_id'];
            $_SESSION['member_name'] = $member['members_firstname'];
            $_SESSION['role'] = 'member';
            $_SESSION['cart'] = []; 
            return ['success' => 'Welcome back!'];
        }

        return ['error' => 'Invalid Member credentials.'];
    }

    public function logout() {
        session_destroy();
        header("Location: index.php");
        exit;
    }
}
?>