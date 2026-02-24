<?php
// Include the Email Service
require_once __DIR__ . '/../services/EmailService.php';

class BorrowController {
    private $pdo;
    private $logger;
    private $emailer;

    public function __construct($db, $logger) {
        $this->pdo = $db->pdo;
        $this->logger = $logger;
        $this->emailer = new EmailService(); 
    }

    /**
     * CHECKOUT CART (With Strict 5-Book Limit)
     */
    public function checkoutCart($member_id, $book_ids) {
        if (empty($book_ids)) return ['error' => 'Cart is empty.'];
        
        try {
            // 1. Get Member Name
            $stmtMem = $this->pdo->prepare("SELECT members_firstname, members_lastname FROM members WHERE member_id = ?");
            $stmtMem->execute([$member_id]);
            $member = $stmtMem->fetch();
            $memberName = $member ? ($member['members_firstname'].' '.$member['members_lastname']) : "Member #$member_id";

            $this->pdo->beginTransaction();

            // 2. CRITICAL: Check Current Loan Count
            $stmtLimit = $this->pdo->prepare("SELECT COUNT(*) FROM loans WHERE member_id = ? AND status = 'borrowed' FOR UPDATE");
            $stmtLimit->execute([$member_id]);
            $current_loans = $stmtLimit->fetchColumn();

            $new_total = $current_loans + count($book_ids);

            // --- STRICT LIMIT CHECK ---
            if ($new_total > 5) {
                $this->pdo->rollBack();
                return ['error' => "LIMIT EXCEEDED: You can only have 5 active loans. You currently have $current_loans and are trying to borrow " . count($book_ids) . " more."];
            }

            $receipt_items = [];
            foreach ($book_ids as $book_id) {
                // Check Stock
                $stmtStock = $this->pdo->prepare("SELECT title, available_copies FROM books WHERE book_id = ? FOR UPDATE");
                $stmtStock->execute([$book_id]);
                $book = $stmtStock->fetch();

                if (!$book || $book['available_copies'] < 1) {
                    $this->pdo->rollBack();
                    return ['error' => "The book '{$book['title']}' is out of stock."];
                }

                // Process Loan
                $due_date = date('Y-m-d', strtotime('+7 days'));
                $this->pdo->prepare("INSERT INTO loans (member_id, book_id, borrow_date, due_date, status) VALUES (?, ?, CURDATE(), ?, 'borrowed')")->execute([$member_id, $book_id, $due_date]);
                $loan_id = $this->pdo->lastInsertId();

                // Decrease Stock
                $this->pdo->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE book_id = ?")->execute([$book_id]);

                // Log
                $this->logger->logLoan($loan_id, "BORROW", "$memberName borrowed '{$book['title']}'");
                $receipt_items[] = ['title' => $book['title'], 'due' => $due_date];
            }
            
            $this->pdo->commit();
            return ['success' => 'Checkout successful!', 'receipt' => $receipt_items];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * PROCESS RETURN
     */
    public function returnBook($loan_id, $fine, $admin_name) {
        try {
            $sql = "SELECT l.book_id, b.title, m.email, m.members_firstname, m.members_lastname FROM loans l JOIN books b ON l.book_id = b.book_id JOIN members m ON l.member_id = m.member_id WHERE l.loan_id = ?";
            $stmtGet = $this->pdo->prepare($sql);
            $stmtGet->execute([$loan_id]);
            $info = $stmtGet->fetch();
            
            if (!$info) return ['error' => 'Loan record not found.'];
            
            $book_id = $info['book_id'];
            $borrowerName = $info['members_firstname'] . ' ' . $info['members_lastname'];
            $borrowerEmail = $info['email'];

            $this->pdo->beginTransaction();

            $stmt1 = $this->pdo->prepare("UPDATE loans SET status='returned', return_date=CURDATE(), fine_amount=? WHERE loan_id=?");
            $stmt1->execute([$fine, $loan_id]);

            $stmt2 = $this->pdo->prepare("UPDATE books SET available_copies = available_copies + 1 WHERE book_id = ?");
            $stmt2->execute([$book_id]);

            $this->pdo->commit();

            $msg = "$borrowerName returned '{$info['title']}' (Fine: ₱$fine). Processed by: $admin_name";
            $this->logger->logLoan($loan_id, "RETURN", $msg);
            
            // Send Email Receipt
            $this->emailer->sendReturnReceipt($borrowerEmail, $borrowerName, $info['title'], $fine, $fine);

            return ['success' => 'Book returned. Receipt emailed.'];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * NOTIFY OVERDUE MEMBERS (UPDATED: Uses Consolidated Emails)
     */
    public function notifyOverdueMembers($admin_name) {
        try {
            // 1. Get all overdue loans
            $sql = "SELECT l.loan_id, b.title, l.due_date, m.member_id, m.email, m.members_firstname, m.members_lastname,
                    DATEDIFF(CURDATE(), l.due_date) as days_late 
                    FROM loans l
                    JOIN books b ON l.book_id = b.book_id
                    JOIN members m ON l.member_id = m.member_id
                    WHERE l.status = 'borrowed' AND l.due_date < CURDATE()";
            
            $stmt = $this->pdo->query($sql);
            $overdueItems = $stmt->fetchAll();

            if (empty($overdueItems)) {
                return ['error' => "No overdue members found."];
            }

            // 2. Group items by Member
            $groupedData = [];

            foreach ($overdueItems as $item) {
                $mid = $item['member_id'];
                
                // Initialize array for this member if not exists
                if (!isset($groupedData[$mid])) {
                    $groupedData[$mid] = [
                        'name' => $item['members_firstname'] . ' ' . $item['members_lastname'],
                        'email' => $item['email'],
                        'books' => [],
                        'grand_total' => 0
                    ];
                }

                // Calculate fine for this specific book
                $fine = $item['days_late'] * 20; // 20 pesos per day

                // Add book details
                $groupedData[$mid]['books'][] = [
                    'title' => $item['title'],
                    'due_date' => $item['due_date'],
                    'days_late' => $item['days_late'],
                    'fine' => number_format($fine, 2)
                ];

                // Add to user's grand total
                $groupedData[$mid]['grand_total'] += $fine;
            }

            // 3. Send ONE email per member using the NEW method
            $emailCount = 0;
            foreach ($groupedData as $member) {
                // FIXED: Calls sendConsolidatedOverdueNotice instead of sendOverdueNotice
                $this->emailer->sendConsolidatedOverdueNotice(
                    $member['email'],
                    $member['name'],
                    $member['books'],
                    number_format($member['grand_total'], 2)
                );
                $emailCount++;
            }

            // 4. Log
            $this->logger->logLoan(0, "NOTIFY", "Sent $emailCount consolidated overdue alerts. Processed by: $admin_name");
            
            return ['success' => "Successfully sent $emailCount overdue email alerts."];

        } catch (Exception $e) {
            return ['error' => "Email Error: " . $e->getMessage()];
        }
    }
}
?>