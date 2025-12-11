 <?php
/**
 * Configuration File for Smart Text Analyzer Pro
 * 
 * Important: Create a .env file in the root directory with your actual API keys
 * For security, never commit .env file to version control
 */

// Load environment variables from .env file
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}

// Application Configuration
define('APP_NAME', 'Smart Text Analyzer Pro');
define('APP_VERSION', '1.0.0');
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('APP_DEBUG', getenv('APP_DEBUG') ?: true);

// Hugging Face API Configuration
define('HUGGINGFACE_API_KEY', getenv('HUGGINGFACE_API_KEY') ?: '');
define('HUGGINGFACE_API_URL', 'https://api-inference.huggingface.co/models/');

// Database Configuration (Optional)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'text_analyzer_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Application Paths
define('BASE_PATH', dirname(__DIR__));
define('VIEWS_PATH', BASE_PATH . '/views');
define('ASSETS_PATH', BASE_PATH . '/assets');
define('SRC_PATH', BASE_PATH . '/src');

// Session Configuration
define('SESSION_LIFETIME', 86400); // 24 hours
define('SESSION_NAME', 'smart_text_analyzer');

// Analysis Limits
define('MAX_TEXT_LENGTH', 10000);
define('MIN_TEXT_LENGTH', 10);
define('MAX_HISTORY_ITEMS', 20);

// Cache Configuration
define('CACHE_ENABLED', getenv('CACHE_ENABLED') ?: false);
define('CACHE_TTL', 3600); // 1 hour

// Initialize session with custom settings
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'secure' => APP_ENV === 'production',
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

// Error Reporting based on environment
if (APP_ENV === 'development' && APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('Asia/Manila');

// Autoloader function for non-Composer classes
spl_autoload_register(function ($className) {
    $prefix = 'SmartTextAnalyzer\\';
    $baseDir = SRC_PATH . '/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $className, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($className, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Helper Functions
function config($key, $default = null) {
    static $config = null;
    
    if ($config === null) {
        $config = [
            'app_name' => APP_NAME,
            'app_version' => APP_VERSION,
            'app_env' => APP_ENV,
            'huggingface_key' => HUGGINGFACE_API_KEY,
            'max_text_length' => MAX_TEXT_LENGTH,
            'min_text_length' => MIN_TEXT_LENGTH
        ];
    }
    
    return $config[$key] ?? $default;
}

function asset($path) {
    return '/' . ltrim($path, '/');
}

function view($viewName, $data = []) {
    $viewFile = VIEWS_PATH . '/' . $viewName . '.php';
    
    if (file_exists($viewFile)) {
        extract($data);
        require $viewFile;
    } else {
        throw new Exception("View not found: $viewName");
    }
}

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// API Response Helper
function apiSuccess($data = [], $message = 'Success') {
    return [
        'success' => true,
        'message' => $message,
        'data' => $data,
        'timestamp' => time()
    ];
}

function apiError($message = 'Error', $errors = [], $code = 400) {
    return [
        'success' => false,
        'message' => $message,
        'errors' => $errors,
        'code' => $code,
        'timestamp' => time()
    ];
}
?>