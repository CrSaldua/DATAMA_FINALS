<?php
session_start();
if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }

require_once 'config/app.php';
require_once 'config/db.php'; 
require_once 'logs/MongoLogger.php';

require_once 'models/Book.php';
require_once 'models/Member.php';

require_once 'controllers/AuthController.php';
require_once 'controllers/BorrowController.php';
require_once 'controllers/MemberController.php';
require_once 'controllers/BookController.php';

$db = new Database();
$pdo = $db->pdo;
$db_mongo = $db->mongo;
$logger = new MongoLogger($db->mongo);

$authCtrl = new AuthController($db);
$borrowCtrl = new BorrowController($db, $logger);
$memberCtrl = new MemberController($db, $logger);
$bookCtrl = new BookController($db, $logger);

$error_msg = "";
$success_msg = "";
$receipt_data = null; 

// --- HELPER: GET CURRENT ADMIN NAME ---
$admin_name = "Unknown Admin";
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $admin_name = $_SESSION['admin_name'] ?? 'Admin';
}

// --- CONTROLLER LOGIC ---
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $action = $_POST['action'];

        // --- AUTH ---
        if ($action === 'login_admin') {
            $res = $authCtrl->loginAdmin($_POST['employee_code'], $_POST['password']);
        }
        elseif ($action === 'login_member') {
            $res = $authCtrl->loginMember($_POST['username'], $_POST['password']);
        }
        elseif ($action === 'logout') {
            $authCtrl->logout();
        }

        // --- REGISTRATION ---
        elseif ($action === 'register_member' || $action === 'register_self') {
            $res = $memberCtrl->registerMember($_POST['username'], $_POST['fname'], $_POST['lname'], $_POST['phone'], $_POST['email'], $_POST['password']);
        }
        elseif ($action === 'register_admin') {
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                $res = ['error' => "Unauthorized."];
            } else {
                $res = $memberCtrl->registerAdmin($_POST['employee_code'], $_POST['password'], $_POST['fname'], $_POST['lname']);
            }
        }

        // --- CART ---
        elseif ($action === 'add_to_cart') {
            $book_id = $_POST['book_id'];
            if (!in_array($book_id, $_SESSION['cart'])) {
                $_SESSION['cart'][] = $book_id;
                $res = ['success' => "Book added to cart!"];
            } else {
                $res = ['error' => "Book is already in your cart."];
            }
        }
        elseif ($action === 'remove_from_cart') {
            $book_id = $_POST['book_id'];
            if (($key = array_search($book_id, $_SESSION['cart'])) !== false) {
                unset($_SESSION['cart'][$key]);
                $_SESSION['cart'] = array_values($_SESSION['cart']); 
                $res = ['success' => "Item removed from cart."];
            }
        }

        // --- CHECKOUT ---
        elseif ($action === 'checkout') {
             if (empty($_SESSION['cart'])) {
                 $res = ['error' => "Your cart is empty."];
             } else {
                 $res = $borrowCtrl->checkoutCart($_SESSION['member_id'], $_SESSION['cart']);
                 if (isset($res['success'])) { 
                     $success_data = $res; 
                     $receipt_data = $res['receipt']; 
                     $_SESSION['cart'] = []; 
                 }
             }
        }

        // --- ADMIN ACTIONS ---
        elseif ($action === 'inventory_control') {
            if ($_POST['inv_mode'] == 'new') {
                // UPDATED: Now passes $_POST['description'] into registerBook
                $res = $bookCtrl->registerBook(
                    $_POST['title'], 
                    $_POST['author'], 
                    $_POST['category'], 
                    $_POST['isbn'], 
                    $_POST['qty'], 
                    $_POST['cover_image'], 
                    $_POST['description'], // <--- New field passed here
                    $admin_name
                );
            } elseif ($_POST['inv_mode'] == 'update') {
                $qty = ($_POST['update_type'] == 'subtract') ? -$_POST['qty'] : $_POST['qty'];
                $res = $bookCtrl->updateStock($_POST['book_id'], $qty, $admin_name);
            }
        }
        elseif ($action === 'return') {
            $res = $borrowCtrl->returnBook($_POST['loan_id'], $_POST['fine_amount'], $admin_name);
        }
        
        // --- SEND OVERDUE NOTICES ---
        elseif ($action === 'notify_overdue') {
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                $res = ['error' => "Unauthorized."];
            } else {
                $res = $borrowCtrl->notifyOverdueMembers($admin_name);
            }
        }
        
        // RESPONSE HANDLING
        if (isset($res['error'])) $error_msg = $res['error'];
        if (isset($res['success'])) $success_msg = $res['success'];

    } catch (Exception $e) {
        $error_msg = "System Error: " . $e->getMessage();
    }
}

// ROUTER
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $page = $_GET['page'] ?? 'dashboard';
    $search = $_GET['search'] ?? '';
    require 'frontend/admin_dashboard.php';
}
elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'member') {
    $page = $_GET['page'] ?? 'home';
    $search = $_GET['search'] ?? '';
    $_SESSION['name'] = $_SESSION['member_name']; 
    require 'frontend/user_layout.php';
}
else {
    if (isset($_GET['mode']) && $_GET['mode'] === 'admin') {
        require 'frontend/login_admin.php';
    } else {
        require 'frontend/login_member.php';
    }
}
?>