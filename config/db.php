<?php
require_once __DIR__ . '/../vendor/autoload.php';

class Database {
    public $pdo;
    public $mongo;

    public function __construct() {
        // MySQL
        $mysql_host = 'localhost';
        $mysql_db   = 'library_db'; 
        $mysql_user = 'root';
        $mysql_pass = 'datama'; 

        // MongoDB
        $mongo_db_name = 'Library_Management_Logs'; 

        try {
            $this->pdo = new PDO("mysql:host=$mysql_host;dbname=$mysql_db;charset=utf8mb4", $mysql_user, $mysql_pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->exec("SET time_zone = '+08:00';");

            $mongoClient = new MongoDB\Client("mongodb://localhost:27017");
            $this->mongo = $mongoClient->$mongo_db_name;

        } catch (Exception $e) {
            die("❌ Database Connection Failed: " . $e->getMessage());
        }
    }
}
?>