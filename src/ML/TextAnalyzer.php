<?php
namespace SmartTextAnalyzer\ML;

use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\Transformers\TfIdfTransformer;
use Rubix\ML\Clusterers\KMeans;
use SmartTextAnalyzer\API\HuggingFaceClient;

class TextAnalyzer {
    private $huggingFaceClient;
    private $localModels = [];
    
    public function __construct() {
        $this->huggingFaceClient = new HuggingFaceClient();
        $this->initializeLocalModels();
    }
    
    private function initializeLocalModels(): void {
        // Initialize Rubix ML models for local processing
        $this->localModels['knn'] = new KNearestNeighbors(3);
        $this->localModels['tfidf'] = new TfIdfTransformer();
        $this->localModels['kmeans'] = new KMeans(5);
    }
    
    /**
     * Main analysis method
     */
    public function analyze(string $text, string $modelType = 'sentiment'): array {
        $startTime = microtime(true);
        
        // Pre-process text
        $cleanedText = $this->preprocessText($text);
        
        // Perform analysis based on model type
        $result = [];
        
        switch($modelType) {
            case 'sentiment':
                $result = $this->analyzeSentiment($cleanedText);
                break;
            case 'keywords':
                $result = $this->extractKeywords($cleanedText);
                break;
            case 'emotion':
                $result = $this->detectEmotion($cleanedText);
                break;
            case 'summarize':
                $result = $this->summarizeText($cleanedText);
                break;
            case 'advanced':
                $result = $this->advancedAnalysis($cleanedText);
                break;
            default:
                $result = $this->basicAnalysis($cleanedText);
                break;
        }
        
        // Add metadata
        $processingTime = round((microtime(true) - $startTime) * 1000, 2); // in ms
        
        $result['metadata'] = [
            'processing_time_ms' => $processingTime,
            'text_length' => strlen($cleanedText),
            'word_count' => str_word_count($cleanedText),
            'analysis_type' => $modelType,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        return $result;
    }
    
    /**
     * Text preprocessing
     */
    private function preprocessText(string $text): string {
        // Convert to lowercase
        $text = strtolower($text);
        
        // Remove special characters but keep basic punctuation
        $text = preg_replace('/[^\w\s.,!?]/', '', $text);
        
        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Trim
        $text = trim($text);
        
        return $text;
    }
    
    /**
     * Hybrid sentiment analysis - Hugging Face + local
     */
    private function analyzeSentiment(string $text): array {
        // First try Hugging Face API
        $hfResult = $this->huggingFaceClient->analyzeWithHuggingFace($text, 'sentiment');
        
        if ($hfResult['success']) {
            $hfResult['source'] = 'huggingface';
            return $hfResult;
        }
        
        // Fallback to local sentiment analysis
        return $this->localSentimentAnalysis($text);
    }
    
    /**
     * Local sentiment analysis using Rubix ML
     */
    private function localSentimentAnalysis(string $text): array {
        // Simple sentiment scoring algorithm
        $positiveWords = [
            'good', 'great', 'excellent', 'awesome', 'fantastic', 'amazing',
            'wonderful', 'perfect', 'love', 'happy', 'joy', 'positive',
            'best', 'better', 'recommend', 'excited', 'pleased', 'satisfied'
        ];
        
        $negativeWords = [
            'bad', 'poor', 'terrible', 'awful', 'horrible', 'worst',
            'hate', 'angry', 'sad', 'disappointed', 'negative',
            'problem', 'issue', 'error', 'fail', 'broken', 'wrong'
        ];
        
        $words = str_word_count($text, 1);
        $score = 0;
        $positiveCount = 0;
        $negativeCount = 0;
        
        foreach($words as $word) {
            if(in_array($word, $positiveWords)) {
                $score++;
                $positiveCount++;
            }
            if(in_array($word, $negativeWords)) {
                $score--;
                $negativeCount++;
            }
        }
        
        $totalWords = count($words);
        $sentiment = 'NEUTRAL';
        
        if ($score > 0) {
            $sentiment = 'POSITIVE';
            $confidence = ($positiveCount / $totalWords) * 100;
        } elseif ($score < 0) {
            $sentiment = 'NEGATIVE';
            $confidence = ($negativeCount / $totalWords) * 100;
        } else {
            $confidence = 50;
        }
        
        return [
            'success' => true,
            'source' => 'local',
            'model' => 'Lexicon-based Sentiment Analysis',
            'primary_sentiment' => $sentiment,
            'confidence' => round($confidence, 2),
            'score' => $score,
            'positive_words' => $positiveCount,
            'negative_words' => $negativeCount,
            'total_words' => $totalWords
        ];
    }
    
    /**
     * Keyword extraction
     */
    private function extractKeywords(string $text): array {
        $hfResult = $this->huggingFaceClient->analyzeWithHuggingFace($text, 'keywords');
        
        if ($hfResult['success']) {
            $hfResult['source'] = 'huggingface';
            return $hfResult;
        }
        
        // Local keyword extraction
        return $this->huggingFaceClient->fallbackKeywordExtraction($text);
    }
    
    /**
     * Emotion detection
     */
    private function detectEmotion(string $text): array {
        $hfResult = $this->huggingFaceClient->analyzeWithHuggingFace($text, 'emotion');
        
        if ($hfResult['success']) {
            $hfResult['source'] = 'huggingface';
            return $hfResult;
        }
        
        return $this->huggingFaceClient->fallbackEmotionDetection($text);
    }
    
    /**
     * Text summarization
     */
    private function summarizeText(string $text): array {
        $hfResult = $this->huggingFaceClient->analyzeWithHuggingFace($text, 'summarize');
        
        if ($hfResult['success']) {
            $hfResult['source'] = 'huggingface';
            return $hfResult;
        }
        
        // Simple extractive summarization
        $sentences = preg_split('/[.!?]+/', $text);
        $sentences = array_filter($sentences);
        
        if (count($sentences) <= 3) {
            $summary = $text;
        } else {
            // Take first, middle, and last sentences
            $summarySentences = [
                $sentences[0],
                $sentences[floor(count($sentences) / 2)],
                end($sentences)
            ];
            $summary = implode('. ', $summarySentences) . '.';
        }
        
        return [
            'success' => true,
            'source' => 'local',
            'model' => 'Extractive Summarization',
            'summary' => $summary,
            'original_length' => str_word_count($text),
            'summary_length' => str_word_count($summary),
            'compression_ratio' => round((1 - (str_word_count($summary) / str_word_count($text))) * 100, 2)
        ];
    }
    
    /**
     * Advanced analysis combining multiple techniques
     */
    private function advancedAnalysis(string $text): array {
        $results = [];
        
        // Run all analyses
        $results['sentiment'] = $this->analyzeSentiment($text);
        $results['keywords'] = $this->extractKeywords($text);
        $results['emotion'] = $this->detectEmotion($text);
        $results['summarization'] = $this->summarizeText($text);
        
        // Basic text statistics
        $results['statistics'] = [
            'word_count' => str_word_count($text),
            'char_count' => strlen($text),
            'sentence_count' => count(preg_split('/[.!?]+/', $text)) - 1,
            'avg_word_length' => round(strlen(str_replace(' ', '', $text)) / str_word_count($text), 2),
            'reading_time_minutes' => round(str_word_count($text) / 200, 2)
        ];
        
        // Complexity score
        $uniqueWords = array_unique(str_word_count($text, 1));
        $lexicalDiversity = count($uniqueWords) / max(str_word_count($text), 1);
        
        $results['complexity'] = [
            'lexical_diversity' => round($lexicalDiversity * 100, 2),
            'unique_words' => count($uniqueWords),
            'unique_ratio' => round((count($uniqueWords) / str_word_count($text)) * 100, 2)
        ];
        
        return [
            'success' => true,
            'source' => 'combined',
            'analyses' => $results,
            'overall_score' => $this->calculateOverallScore($results)
        ];
    }
    
    /**
     * Basic text analysis
     */
    private function basicAnalysis(string $text): array {
        $words = str_word_count($text, 1);
        $uniqueWords = array_unique($words);
        
        return [
            'success' => true,
            'source' => 'basic',
            'word_count' => count($words),
            'unique_words' => count($uniqueWords),
            'char_count' => strlen($text),
            'sentence_count' => count(preg_split('/[.!?]+/', $text)) - 1,
            'most_common_words' => $this->getMostCommonWords($text, 5),
            'reading_time' => round(count($words) / 200, 2) . ' minutes'
        ];
    }
    
    /**
     * Get most common words
     */
    private function getMostCommonWords(string $text, int $limit = 5): array {
        $words = str_word_count(strtolower($text), 1);
        $stopwords = ['the', 'and', 'is', 'in', 'to', 'a', 'of', 'for', 'on', 'that', 'with'];
        $filtered = array_diff($words, $stopwords);
        
        $frequency = array_count_values($filtered);
        arsort($frequency);
        
        return array_slice($frequency, 0, $limit, true);
    }
    
    /**
     * Calculate overall text quality score
     */
    private function calculateOverallScore(array $results): array {
        $score = 50; // Start with neutral score
        
        // Adjust based on sentiment
        if (isset($results['sentiment']['primary_sentiment'])) {
            if ($results['sentiment']['primary_sentiment'] === 'POSITIVE') {
                $score += 20;
            } elseif ($results['sentiment']['primary_sentiment'] === 'NEGATIVE') {
                $score -= 20;
            }
        }
        
        // Adjust based on lexical diversity
        if (isset($results['complexity']['lexical_diversity'])) {
            $diversity = $results['complexity']['lexical_diversity'];
            if ($diversity > 60) $score += 15;
            elseif ($diversity > 40) $score += 5;
            elseif ($diversity < 20) $score -= 10;
        }
        
        // Clamp score between 0-100
        $score = max(0, min(100, $score));
        
        return [
            'score' => $score,
            'rating' => $this->getRating($score),
            'interpretation' => $this->getInterpretation($score)
        ];
    }
    
    private function getRating(int $score): string {
        if ($score >= 80) return 'Excellent';
        if ($score >= 60) return 'Good';
        if ($score >= 40) return 'Average';
        if ($score >= 20) return 'Poor';
        return 'Very Poor';
    }
    
    private function getInterpretation(int $score): string {
        if ($score >= 80) return 'High quality text with positive sentiment and good vocabulary';
        if ($score >= 60) return 'Good quality text with generally positive content';
        if ($score >= 40) return 'Average quality text, could be improved';
        if ($score >= 20) return 'Below average text quality or negative sentiment';
        return 'Poor quality text requiring significant improvement';
    }
    
    /**
     * Test all analysis methods
     */
    public function runAllTests(string $sampleText = null): array {
        if (!$sampleText) {
            $sampleText = "This is an amazing product! I absolutely love how easy it is to use. 
                          The quality is excellent and the customer service was wonderful. 
                          Highly recommended for everyone looking for a great solution.";
        }
        
        $tests = [
            'sentiment' => $this->analyzeSentiment($sampleText),
            'keywords' => $this->extractKeywords($sampleText),
            'emotion' => $this->detectEmotion($sampleText),
            'summarization' => $this->summarizeText($sampleText),
            'huggingface_connection' => $this->huggingFaceClient->testConnection()
        ];
        
        $successCount = 0;
        foreach ($tests as $test) {
            if (isset($test['success']) && $test['success']) {
                $successCount++;
            }
        }
        
        return [
            'total_tests' => count($tests),
            'successful_tests' => $successCount,
            'success_rate' => round(($successCount / count($tests)) * 100, 2),
            'test_results' => $tests,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}
?>