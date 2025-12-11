<?php
require_once 'process.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['text']) || empty($data['text'])) {
        echo json_encode(['error' => 'No text provided']);
        exit;
    }
    
    $ml = new MLProcessor();
    $results = [];
    
    if (isset($data['analyses'])) {
        foreach ($data['analyses'] as $analysis) {
            switch ($analysis) {
                case 'sentiment':
                    $results['sentiment'] = $ml->analyzeSentiment($data['text']);
                    break;
                case 'keywords':
                    $results['keywords'] = $ml->extractKeywords($data['text']);
                    break;
                case 'emotion':
                    $results['emotion'] = $ml->detectEmotion($data['text']);
                    break;
                case 'summary':
                    $results['summary'] = $ml->summarizeText($data['text']);
                    break;
            }
        }
    }
    
    echo json_encode($results);
}