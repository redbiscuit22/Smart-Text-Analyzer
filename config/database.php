// config/database.php
<?php
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        $host = getenv('DB_HOST');
        $dbname = getenv('DB_NAME');
        $username = getenv('DB_USER');
        $password = getenv('DB_PASS');
        
        try {
            $this->connection = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $username,
                $password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if(self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->connection;
    }
    
    public function saveAnalysis($text, $modelType, $result) {
        $db = self::getInstance();
        $stmt = $db->prepare("INSERT INTO analyses (text_input, model_type, result, created_at) 
                             VALUES (?, ?, ?, NOW())");
        return $stmt->execute([$text, $modelType, json_encode($result)]);
    }
    
    public function getAnalysisHistory($limit = 10) {
        $db = self::getInstance();
        $stmt = $db->prepare("SELECT * FROM analyses ORDER BY created_at DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>