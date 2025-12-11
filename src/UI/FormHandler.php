<?php
namespace SmartTextAnalyzer\UI;

use SmartTextAnalyzer\ML\TextAnalyzer;

class FormHandler {
    private $analyzer;
    private $errors = [];
    private $sessionKey = 'text_analysis_history';
    
    public function __construct() {
        $this->analyzer = new TextAnalyzer();
        $this->startSession();
    }
    
    private function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Process form submission
     */
    public function processRequest(array $postData): array {
        $text = trim($postData['text_input'] ?? '');
        $modelType = $postData['model_type'] ?? 'sentiment';
        $action = $postData['action'] ?? 'analyze';
        
        // Validate input
        if (!$this->validateInput($text, $modelType)) {
            return [
                'success' => false,
                'errors' => $this->errors
            ];
        }
        
        // Process based on action
        switch ($action) {
            case 'analyze':
                return $this->processAnalysis($text, $modelType);
            case 'test':
                return $this->runTests($text);
            case 'history':
                return $this->getHistory();
            case 'clear':
                return $this->clearHistory();
            default:
                return $this->processAnalysis($text, $modelType);
        }
    }
    
    /**
     * Validate user input
     */
    private function validateInput(string $text, string $modelType): bool {
        $this->errors = [];
        
        // Validate text
        if (empty($text)) {
            $this->errors[] = 'Please enter text to analyze';
        } elseif (strlen($text) < 10) {
            $this->errors[] = 'Text must be at least 10 characters long';
        } elseif (strlen($text) > 10000) {
            $this->errors[] = 'Text cannot exceed 10,000 characters';
        }
        
        // Validate model type
        $validModels = ['sentiment', 'keywords', 'emotion', 'summarize', 'advanced', 'basic'];
        if (!in_array($modelType, $validModels)) {
            $this->errors[] = 'Invalid analysis type selected';
        }
        
        return empty($this->errors);
    }
    
    /**
     * Process text analysis
     */
    private function processAnalysis(string $text, string $modelType): array {
        try {
            // Perform analysis
            $result = $this->analyzer->analyze($text, $modelType);
            
            // Add to session history
            $this->addToHistory([
                'text' => substr($text, 0, 200) . (strlen($text) > 200 ? '...' : ''),
                'model_type' => $modelType,
                'result' => $result,
                'timestamp' => date('Y-m-d H:i:s'),
                'id' => uniqid('analysis_')
            ]);
            
            // Store in session for display
            $_SESSION['last_analysis'] = [
                'text' => $text,
                'model_type' => $modelType,
                'result' => $result,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            return [
                'success' => true,
                'result' => $result,
                'model_type' => $modelType,
                'text_preview' => substr($text, 0, 100) . (strlen($text) > 100 ? '...' : ''),
                'message' => 'Analysis completed successfully'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['Analysis failed: ' . $e->getMessage()],
                'model_type' => $modelType
            ];
        }
    }
    
    /**
     * Run system tests
     */
    private function runTests(string $sampleText = null): array {
        $testResults = $this->analyzer->runAllTests($sampleText);
        
        return [
            'success' => true,
            'action' => 'test',
            'results' => $testResults,
            'message' => 'System tests completed'
        ];
    }
    
    /**
     * Manage analysis history
     */
    private function addToHistory(array $analysis): void {
        if (!isset($_SESSION[$this->sessionKey])) {
            $_SESSION[$this->sessionKey] = [];
        }
        
        // Add to beginning of array (newest first)
        array_unshift($_SESSION[$this->sessionKey], $analysis);
        
        // Keep only last 20 analyses
        $_SESSION[$this->sessionKey] = array_slice($_SESSION[$this->sessionKey], 0, 20);
    }
    
    private function getHistory(): array {
        $history = $_SESSION[$this->sessionKey] ?? [];
        
        return [
            'success' => true,
            'action' => 'history',
            'history' => $history,
            'count' => count($history)
        ];
    }
    
    private function clearHistory(): array {
        $_SESSION[$this->sessionKey] = [];
        $_SESSION['last_analysis'] = null;
        
        return [
            'success' => true,
            'action' => 'clear',
            'message' => 'Analysis history cleared'
        ];
    }
    
    /**
     * Get formatted analysis history for display
     */
    public function getFormattedHistory(int $limit = 5): array {
        $history = $_SESSION[$this->sessionKey] ?? [];
        $formatted = [];
        
        foreach (array_slice($history, 0, $limit) as $item) {
            $formatted[] = [
                'id' => $item['id'] ?? uniqid(),
                'preview' => $item['text'],
                'type' => $item['model_type'],
                'time' => $item['timestamp'],
                'summary' => $this->getResultSummary($item['result'])
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Create a summary of analysis results
     */
    private function getResultSummary(array $result): string {
        if (isset($result['primary_sentiment'])) {
            return "Sentiment: " . $result['primary_sentiment'] . " (" . ($result['confidence'] ?? 'N/A') . "%)";
        } elseif (isset($result['keywords'])) {
            $keywordCount = is_array($result['keywords']) ? count($result['keywords']) : 0;
            return "Keywords extracted: " . $keywordCount;
        } elseif (isset($result['primary_emotion'])) {
            return "Emotion: " . $result['primary_emotion'];
        } elseif (isset($result['summary'])) {
            return "Summary: " . substr($result['summary'], 0, 50) . "...";
        }
        
        return "Analysis completed";
    }
    
    /**
     * Get the last analysis result
     */
    public function getLastAnalysis(): ?array {
        return $_SESSION['last_analysis'] ?? null;
    }
    
    /**
     * Get sample texts for demonstration
     */
    public function getSampleTexts(): array {
        return [
            'positive_review' => [
                'title' => 'Positive Product Review',
                'text' => "This product is absolutely amazing! The quality is exceptional and it works perfectly. 
                         I love how easy it is to use and the customer service was wonderful when I had questions. 
                         Highly recommended to everyone looking for a reliable solution. Five stars!",
                'type' => 'sentiment'
            ],
            'negative_feedback' => [
                'title' => 'Customer Complaint',
                'text' => "Very disappointed with my purchase. The product arrived damaged and doesn't work as advertised. 
                         Customer service was unhelpful and refused to provide a refund. I would not recommend this 
                         to anyone. Save your money and look elsewhere.",
                'type' => 'sentiment'
            ],
            'technical_article' => [
                'title' => 'Technical Article Excerpt',
                'text' => "Machine learning algorithms, particularly deep neural networks, have revolutionized 
                         natural language processing. Transformers and attention mechanisms enable models like 
                         BERT and GPT to understand context and generate human-like text. These advancements 
                         power modern applications from chatbots to automated content generation.",
                'type' => 'keywords'
            ],
            'emotional_story' => [
                'title' => 'Emotional Story',
                'text' => "I was so happy and excited when I received the news! But then suddenly, fear crept in 
                         as I realized the responsibility. Now I feel anxious yet hopeful about the future. 
                         It's a rollercoaster of emotions that I never expected to experience.",
                'type' => 'emotion'
            ]
        ];
    }
}
?>