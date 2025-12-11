<?php
namespace SmartTextAnalyzer\API;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Exception;

class HuggingFaceClient {
    private $client;
    private $apiKey;
    private $baseUrl = 'https://api-inference.huggingface.co/models/';
    
    // Available Hugging Face models for different tasks
    private $models = [
        'sentiment' => 'distilbert-base-uncased-finetuned-sst-2-english',
        'keywords' => 'yanekyuk/bert-keyword-extractor',
        'emotion' => 'j-hartmann/emotion-english-distilroberta-base',
        'summarization' => 'facebook/bart-large-cnn',
        'translation' => 'Helsinki-NLP/opus-mt-en-es'
    ];
    
    public function __construct() {
        $this->client = new Client([
            'timeout' => 30.0,
            'verify' => true
        ]);
        $this->apiKey = getenv('HUGGINGFACE_API_KEY') ?: '';
    }
    
    /**
     * Main method to analyze text using Hugging Face models
     */
    public function analyzeWithHuggingFace(string $text, string $task = 'sentiment'): array {
        try {
            if (empty($this->apiKey)) {
                throw new Exception('Hugging Face API key not configured');
            }
            
            switch ($task) {
                case 'sentiment':
                    return $this->analyzeSentiment($text);
                case 'keywords':
                    return $this->extractKeywords($text);
                case 'emotion':
                    return $this->detectEmotions($text);
                case 'summarize':
                    return $this->summarizeText($text);
                default:
                    return $this->genericAnalysis($text, $task);
            }
            
        } catch (Exception $e) {
            error_log("Hugging Face API Error: " . $e->getMessage());
            return $this->fallbackAnalysis($text, $task);
        }
    }
    
    /**
     * Sentiment Analysis using DistilBERT
     */
    private function analyzeSentiment(string $text): array {
        $response = $this->client->post($this->baseUrl . $this->models['sentiment'], [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'inputs' => $text,
                'parameters' => [
                    'return_all_scores' => true
                ]
            ]
        ]);
        
        $data = json_decode($response->getBody(), true);
        
        if (is_array($data) && isset($data[0])) {
            $scores = $data[0];
            $primary = array_reduce($scores, function($carry, $item) {
                return $item['score'] > $carry['score'] ? $item : $carry;
            }, ['label' => 'NEUTRAL', 'score' => 0]);
            
            return [
                'success' => true,
                'model' => 'DistilBERT Sentiment Analysis',
                'primary_sentiment' => strtoupper($primary['label']),
                'confidence' => round($primary['score'] * 100, 2),
                'all_scores' => $scores,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
        
        throw new Exception('Invalid response format from Hugging Face');
    }
    
    /**
     * Keyword Extraction using BERT
     */
    private function extractKeywords(string $text): array {
        // First try the keyword extraction model
        try {
            $response = $this->client->post($this->baseUrl . $this->models['keywords'], [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'inputs' => $text
                ]
            ]);
            
            $keywords = json_decode($response->getBody(), true);
            
            if (is_array($keywords) && !empty($keywords)) {
                return [
                    'success' => true,
                    'model' => 'BERT Keyword Extractor',
                    'keywords' => array_slice($keywords, 0, 10),
                    'total_keywords' => count($keywords),
                    'method' => 'huggingface'
                ];
            }
        } catch (Exception $e) {
            // Fall back to zero-shot classification for topic extraction
            return $this->extractTopicsZeroShot($text);
        }
        
        return $this->fallbackKeywordExtraction($text);
    }
    
    /**
     * Emotion Detection using RoBERTa
     */
    private function detectEmotions(string $text): array {
        $response = $this->client->post($this->baseUrl . $this->models['emotion'], [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'inputs' => $text,
                'parameters' => [
                    'return_all_scores' => true
                ]
            ]
        ]);
        
        $data = json_decode($response->getBody(), true);
        
        if (is_array($data) && isset($data[0])) {
            $emotions = $data[0];
            usort($emotions, function($a, $b) {
                return $b['score'] <=> $a['score'];
            });
            
            return [
                'success' => true,
                'model' => 'RoBERTa Emotion Detection',
                'primary_emotion' => $emotions[0]['label'],
                'emotion_score' => round($emotions[0]['score'] * 100, 2),
                'all_emotions' => $emotions,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
        
        return $this->fallbackEmotionDetection($text);
    }
    
    /**
     * Text Summarization using BART
     */
    private function summarizeText(string $text): array {
        if (str_word_count($text) < 50) {
            return [
                'success' => false,
                'error' => 'Text too short for summarization',
                'summary' => $text
            ];
        }
        
        $response = $this->client->post($this->baseUrl . $this->models['summarization'], [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'inputs' => $text,
                'parameters' => [
                    'max_length' => 150,
                    'min_length' => 30,
                    'do_sample' => false
                ]
            ]
        ]);
        
        $data = json_decode($response->getBody(), true);
        
        return [
            'success' => true,
            'model' => 'BART Summarization',
            'summary' => $data[0]['summary_text'] ?? substr($text, 0, 150) . '...',
            'original_length' => str_word_count($text),
            'summary_length' => str_word_count($data[0]['summary_text'] ?? ''),
            'compression_ratio' => round((1 - (str_word_count($data[0]['summary_text'] ?? '') / str_word_count($text))) * 100, 2)
        ];
    }
    
    /**
     * Zero-shot topic extraction as fallback
     */
    private function extractTopicsZeroShot(string $text): array {
        $candidateLabels = [
            'technology', 'business', 'health', 'education', 
            'entertainment', 'sports', 'politics', 'science'
        ];
        
        try {
            $response = $this->client->post('https://api-inference.huggingface.co/models/facebook/bart-large-mnli', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'inputs' => $text,
                    'parameters' => [
                        'candidate_labels' => $candidateLabels,
                        'multi_label' => true
                    ]
                ]
            ]);
            
            $data = json_decode($response->getBody(), true);
            
            if (isset($data['labels']) && isset($data['scores'])) {
                $topics = [];
                foreach ($data['labels'] as $index => $label) {
                    if ($data['scores'][$index] > 0.3) { // Threshold of 30%
                        $topics[] = [
                            'topic' => $label,
                            'confidence' => round($data['scores'][$index] * 100, 2)
                        ];
                    }
                }
                
                return [
                    'success' => true,
                    'model' => 'BART Zero-shot Classification',
                    'topics' => $topics,
                    'method' => 'zero-shot'
                ];
            }
        } catch (Exception $e) {
            // Continue to fallback
        }
        
        return $this->fallbackKeywordExtraction($text);
    }
    
    /**
     * Fallback keyword extraction using TF-IDF algorithm
     */
    private function fallbackKeywordExtraction(string $text): array {
        // Remove common stopwords
        $stopwords = ['the', 'and', 'is', 'in', 'to', 'a', 'of', 'for', 'on', 'that', 'with', 'by', 'this', 'it', 'as', 'be', 'are', 'was', 'were', 'at', 'from'];
        
        $words = str_word_count(strtolower($text), 1);
        $filteredWords = array_diff($words, $stopwords);
        
        // Calculate word frequencies
        $wordFrequency = array_count_values($filteredWords);
        
        // Calculate TF-IDF (simplified)
        $totalWords = count($filteredWords);
        $keywords = [];
        
        foreach ($wordFrequency as $word => $count) {
            if ($count > 1 && strlen($word) > 3) { // Filter short words and single occurrences
                $tf = $count / $totalWords;
                $keywords[$word] = round($tf * 100, 2);
            }
        }
        
        arsort($keywords);
        
        return [
            'success' => true,
            'model' => 'TF-IDF Fallback',
            'keywords' => array_slice(array_keys($keywords), 0, 15),
            'keyword_scores' => array_slice($keywords, 0, 15),
            'total_keywords' => count($keywords),
            'method' => 'fallback'
        ];
    }
    
    /**
     * Fallback emotion detection using lexicon-based approach
     */
    private function fallbackEmotionDetection(string $text): array {
        $emotionLexicons = [
            'joy' => ['happy', 'joy', 'excited', 'great', 'wonderful', 'love', 'amazing', 'fantastic'],
            'sadness' => ['sad', 'unhappy', 'depressed', 'miserable', 'terrible', 'awful', 'hurt'],
            'anger' => ['angry', 'mad', 'furious', 'annoyed', 'frustrated', 'rage'],
            'fear' => ['scared', 'afraid', 'fear', 'terrified', 'anxious', 'worried'],
            'surprise' => ['surprised', 'shocked', 'amazed', 'astonished']
        ];
        
        $words = str_word_count(strtolower($text), 1);
        $emotionScores = [];
        
        foreach ($emotionLexicons as $emotion => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                $score += substr_count(strtolower($text), $keyword);
            }
            if ($score > 0) {
                $emotionScores[] = [
                    'label' => $emotion,
                    'score' => $score / count($words) * 100
                ];
            }
        }
        
        if (empty($emotionScores)) {
            $emotionScores[] = [
                'label' => 'neutral',
                'score' => 100
            ];
        }
        
        usort($emotionScores, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        return [
            'success' => true,
            'model' => 'Lexicon-based Fallback',
            'primary_emotion' => $emotionScores[0]['label'],
            'emotion_score' => round($emotionScores[0]['score'], 2),
            'all_emotions' => $emotionScores,
            'method' => 'fallback'
        ];
    }
    
    /**
     * Generic analysis fallback
     */
    private function fallbackAnalysis(string $text, string $task): array {
        return [
            'success' => false,
            'error' => 'Hugging Face API unavailable. Using fallback method.',
            'task' => $task,
            'text_sample' => substr($text, 0, 100),
            'local_analysis' => [
                'word_count' => str_word_count($text),
                'char_count' => strlen($text),
                'reading_time' => ceil(str_word_count($text) / 200) . ' minutes'
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Test connection to Hugging Face API
     */
    public function testConnection(): array {
        try {
            $response = $this->client->get('https://huggingface.co/api/whoami', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey
                ]
            ]);
            
            $userInfo = json_decode($response->getBody(), true);
            
            return [
                'connected' => true,
                'user' => $userInfo['name'] ?? 'Unknown',
                'type' => $userInfo['type'] ?? 'Unknown',
                'message' => 'Successfully connected to Hugging Face API'
            ];
            
        } catch (Exception $e) {
            return [
                'connected' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to connect to Hugging Face API'
            ];
        }
    }
}
?>