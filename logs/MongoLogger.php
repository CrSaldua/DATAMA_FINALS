<?php
require_once __DIR__ . '/../vendor/autoload.php';

class MongoLogger {
    private $db;
    public $loan_histories;
    public $book_logs;

    public function __construct($mongo_db) {
        $this->db = $mongo_db;
        $this->loan_histories = $this->db->loan_histories;
        $this->book_logs = $this->db->book_logs;
    }

    /**
     * 1. GENERIC LOG FUNCTION (Restored)
     * This fixes the error in MemberController.
     */
    public function log($collection, $data) {
        $col = $this->db->$collection;
        if (!isset($data['timestamp'])) {
            $data['timestamp'] = date('Y-m-d H:i:s');
        }
        $col->insertOne($data);
    }

    /**
     * 2. Log Loan Actions (Borrow/Return)
     */
    public function logLoan($loan_id, $action, $details) {
        $this->loan_histories->insertOne([
            'loan_id' => $loan_id,
            'action' => $action,
            'details' => $details,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * 3. Log Inventory Actions (New Book/Stock Update)
     */
    public function logBook($book_id, $action, $details) {
        $this->book_logs->insertOne([
            'book_id' => $book_id,
            'action' => $action,
            'details' => $details,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}
?>