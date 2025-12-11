<?php
/**
 * Smart Text Analyzer Pro - Main Entry Point
 * ITEP 308 Final Project: Web Application with Machine Learning Integration
 */

// Load configuration
require_once 'config/config.php';

// Use Composer autoload
require_once 'vendor/autoload.php';

use SmartTextAnalyzer\UI\FormHandler;
use SmartTextAnalyzer\ML\TextAnalyzer;

// Handle AJAX requests
if (isAjaxRequest()) {
    header('Content-Type: application/json');
    
    $handler = new FormHandler();
    $result = $handler->processRequest($_POST);
    
    echo json_encode($result);
    exit;
}

// Handle regular requests
$page = $_GET['page'] ?? 'dashboard';
$handler = new FormHandler();

switch($page) {
    case 'analyze':
        $result = $handler->processRequest($_POST);
        
        if ($result['success']) {
            // Store in session for display
            $_SESSION['last_result'] = $result;
            header('Location: index.php');
            exit;
        } else {
            // Show errors
            $_SESSION['errors'] = $result['errors'];
            header('Location: index.php');
            exit;
        }
        break;
        
    case 'test':
        $result = $handler->processRequest(['action' => 'test']);
        $_SESSION['test_results'] = $result;
        header('Location: index.php');
        exit;
        
    case 'history':
        $result = $handler->processRequest(['action' => 'history']);
        $_SESSION['history_data'] = $result;
        header('Location: index.php');
        exit;
        
    case 'api':
        // API endpoint for external use
        $text = $_GET['text'] ?? $_POST['text'] ?? '';
        $type = $_GET['type'] ?? $_POST['type'] ?? 'sentiment';
        
        if (empty($text)) {
            jsonResponse(apiError('Text parameter is required'), 400);
        }
        
        $analyzer = new TextAnalyzer();
        $result = $analyzer->analyze($text, $type);
        
        jsonResponse(apiSuccess($result, 'Analysis completed'));
        break;
        
    case 'about':
        include 'views/about.php';
        break;
        
    case 'documentation':
        include 'views/documentation.php';
        break;
        
    default:
        // Main dashboard
        $lastResult = $handler->getLastAnalysis();
        $history = $handler->getFormattedHistory(5);
        $sampleTexts = $handler->getSampleTexts();
        $errors = $_SESSION['errors'] ?? [];
        
        // Clear session errors
        unset($_SESSION['errors']);
        
        include 'views/dashboard.php';
        break;
}
?>