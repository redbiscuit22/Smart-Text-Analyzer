<?php
require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Dotenv\Dotenv;

class MLProcessor {
    private $client;
    private $apiToken;
    
    public function __construct() {
        $dotenv = Dotenv::createImmutable(__DIR__);
        $dotenv->load();
        
        $this->apiToken = $_ENV['HUGGINGFACE_API_TOKEN'] ?? '';
        $this->client = new Client([
            'base_uri' => 'https://api-inference.huggingface.co/models/',
            'timeout'  => 30.0,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
            ]
        ]);
    }
    
    public function analyzeSentiment($text) {
        try {
            $response = $this->client->post('distilbert-base-uncased-finetuned-sst-2-english', [
                'json' => ['inputs' => $text]
            ]);
            
            $result = json_decode($response->getBody(), true);
            
            if (isset($result[0])) {
                $scores = $result[0];
                $positive = $negative = 0;
                
                foreach ($scores as $item) {
                    if ($item['label'] == 'POSITIVE') {
                        $positive = $item['score'];
                    } elseif ($item['label'] == 'NEGATIVE') {
                        $negative = $item['score'];
                    }
                }
                
                return [
                    'sentiment' => $positive >= $negative ? 'positive' : 'negative',
                    'confidence' => max($positive, $negative),
                    'positive_score' => $positive,
                    'negative_score' => $negative
                ];
            }
        } catch (Exception $e) {
            error_log('Sentiment analysis error: ' . $e->getMessage());
        }
        
        // Fallback to simulation if API fails
        return $this->simulateSentiment($text);
    }
    
    public function extractKeywords($text) {
        try {
            $response = $this->client->post('yanekyuk/bert-keyword-extractor', [
                'json' => ['inputs' => $text]
            ]);
            
            $result = json_decode($response->getBody(), true);
            
            if (is_array($result)) {
                return array_slice($result, 0, 10);
            }
        } catch (Exception $e) {
            error_log('Keyword extraction error: ' . $e->getMessage());
        }
        
        // Fallback simulation
        return $this->simulateKeywords($text);
    }
    
    public function detectEmotion($text) {
        try {
            $response = $this->client->post('j-hartmann/emotion-english-distilroberta-base', [
                'json' => ['inputs' => $text]
            ]);
            
            $result = json_decode($response->getBody(), true);
            
            if (isset($result[0])) {
                $emotions = [];
                foreach ($result[0] as $emotion) {
                    $emotions[$emotion['label']] = $emotion['score'];
                }
                arsort($emotions);
                return $emotions;
            }
        } catch (Exception $e) {
            error_log('Emotion detection error: ' . $e->getMessage());
        }
        
        // Fallback simulation
        return $this->simulateEmotions();
    }
    
    public function summarizeText($text) {
        try {
            $response = $this->client->post('facebook/bart-large-cnn', [
                'json' => [
                    'inputs' => $text,
                    'parameters' => [
                        'max_length' => 130,
                        'min_length' => 30
                    ]
                ]
            ]);
            
            $result = json_decode($response->getBody(), true);
            
            if (isset($result[0]['summary_text'])) {
                return $result[0]['summary_text'];
            }
        } catch (Exception $e) {
            error_log('Summarization error: ' . $e->getMessage());
        }
        
        // Fallback simulation
        return $this->simulateSummary($text);
    }
    
    private function simulateSentiment($text) {
        $words = str_word_count($text, 1);
        $positiveWords = ['good', 'great', 'excellent', 'happy', 'love', 'amazing', 'best'];
        $negativeWords = ['bad', 'terrible', 'awful', 'hate', 'worst', 'sad', 'angry'];
        
        $positiveCount = 0;
        $negativeCount = 0;
        
        foreach ($words as $word) {
            if (in_array(strtolower($word), $positiveWords)) $positiveCount++;
            if (in_array(strtolower($word), $negativeWords)) $negativeCount++;
        }
        
        $total = $positiveCount + $negativeCount;
        if ($total > 0) {
            $positiveScore = $positiveCount / $total;
            $negativeScore = $negativeCount / $total;
        } else {
            $positiveScore = 0.5;
            $negativeScore = 0.5;
        }
        
        return [
            'sentiment' => $positiveScore >= $negativeScore ? 'positive' : 'negative',
            'confidence' => abs($positiveScore - $negativeScore),
            'positive_score' => $positiveScore,
            'negative_score' => $negativeScore
        ];
    }
    
    private function simulateKeywords($text) {
        $words = str_word_count($text, 1);
        $commonWords = ['the', 'and', 'is', 'in', 'to', 'of', 'a', 'that', 'it', 'with'];
        $keywords = array_diff($words, $commonWords);
        $keywords = array_slice(array_unique($keywords), 0, 8);
        return array_map('ucfirst', $keywords);
    }
    
    private function simulateEmotions() {
        $emotions = ['Joy', 'Anger', 'Sadness', 'Fear', 'Surprise', 'Love', 'Confidence'];
        $result = [];
        $total = 0;
        
        foreach ($emotions as $emotion) {
            $score = rand(1, 100) / 100;
            $result[$emotion] = $score;
            $total += $score;
        }
        
        // Normalize
        foreach ($result as &$score) {
            $score = $score / $total;
        }
        
        arsort($result);
        return $result;
    }
    
    private function simulateSummary($text) {
        $sentences = explode('.', $text);
        $sentences = array_filter($sentences);
        $summary = array_slice($sentences, 0, min(3, count($sentences)));
        return implode('. ', $summary) . (count($sentences) > 3 ? '...' : '');
    }
}