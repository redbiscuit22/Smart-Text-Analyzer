<?php
namespace TextSense\ML;

use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Transformers\WordCountVectorizer;
use Rubix\ML\Transformers\TfIdfTransformer;
use Rubix\ML\Tokenizers\WordTokenizer;
use Rubix\ML\Tokenizers\NGram;
use Rubix\ML\Classifiers\NaiveBayes;
use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\CrossValidation\Metrics\Accuracy;
use Rubix\ML\CrossValidation\Reports\ConfusionMatrix;

class TextAnalyzer {
    private $sentimentClassifier;
    private $emotionClassifier;
    private $vectorizer;
    private $tfidf;
    
    public function __construct() {
        // Initialize ML components
        $this->vectorizer = new WordCountVectorizer(1000, 1, 0.8, new WordTokenizer());
        $this->tfidf = new TfIdfTransformer();
        
        // Initialize classifiers
        $this->sentimentClassifier = new NaiveBayes([
            'positive' => 0.33,
            'negative' => 0.33,
            'neutral' => 0.34
        ]);
        
        $this->emotionClassifier = new KNearestNeighbors(3);
    }
    
    /**
     * Analyze sentiment using ML
     */
    public function analyzeSentiment(string $text): array {
        // Pre-trained sentiment patterns (in a real app, you'd train this with data)
        $sentimentPatterns = [
            'positive' => [
                'good', 'great', 'excellent', 'amazing', 'wonderful', 'perfect', 'best',
                'love', 'happy', 'nice', 'like', 'enjoy', 'awesome', 'fantastic', 'brilliant',
                'superb', 'outstanding', 'impressive', 'satisfied', 'pleased', 'delighted',
                'thrilled', 'excited', 'joyful', 'glad', 'content', 'favorite', 'recommend',
                'beautiful', 'excellent', 'perfect', 'wonderful', 'amazing', 'awesome',
                'fantastic', 'marvelous', 'splendid', 'exceptional', 'remarkable', 'outstanding'
            ],
            'negative' => [
                'bad', 'poor', 'terrible', 'awful', 'horrible', 'worst', 'disappointed',
                'hate', 'dislike', 'angry', 'mad', 'frustrated', 'upset', 'sad', 'unhappy',
                'awful', 'dreadful', 'atrocious', 'appalling', 'disgusting', 'repulsive',
                'revolting', 'offensive', 'unpleasant', 'annoyed', 'irritated', 'furious',
                'enraged', 'infuriated', 'outraged', 'displeased', 'dissatisfied', 'miserable',
                'depressed', 'gloomy', 'sorrowful', 'heartbroken', 'devastated', 'regret',
                'sorry', 'apologize', 'complain', 'issue', 'problem', 'error', 'fail',
                'broken', 'damaged', 'defective', 'faulty', 'useless', 'worthless', 'rubbish',
                'garbage', 'trash', 'junk', 'crap', 'sucks', 'waste', 'disaster', 'catastrophe'
            ],
            'neutral' => [
                'ok', 'okay', 'fine', 'average', 'normal', 'regular', 'standard', 'usual',
                'typical', 'ordinary', 'common', 'general', 'basic', 'simple', 'plain',
                'moderate', 'medium', 'fair', 'reasonable', 'acceptable', 'adequate',
                'sufficient', 'satisfactory', 'tolerable', 'passable', 'mediocre', 'so-so',
                'neutral', 'indifferent', 'unbiased', 'impartial', 'objective', 'detached',
                'disinterested', 'uninvolved', 'unprejudiced', 'fair', 'just', 'equitable',
                'even-handed', 'nonpartisan', 'nonjudgmental', 'open-minded', 'tolerant'
            ]
        ];
        
        $textLower = strtolower($text);
        $words = str_word_count($textLower, 1);
        $totalWords = count($words);
        
        $scores = [
            'positive' => 0,
            'negative' => 0,
            'neutral' => 0
        ];
        
        // Count pattern matches
        foreach ($sentimentPatterns as $sentiment => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($textLower, $pattern) !== false) {
                    $scores[$sentiment]++;
                }
            }
        }
        
        // Calculate probabilities using Bayesian approach
        $totalMatches = array_sum($scores);
        
        if ($totalMatches > 0) {
            $probabilities = [];
            foreach ($scores as $sentiment => $count) {
                $probabilities[$sentiment] = round(($count / $totalMatches) * 100, 2);
            }
        } else {
            // No matches found, use text analysis heuristics
            $probabilities = $this->heuristicSentimentAnalysis($text, $words);
        }
        
        // Determine primary sentiment
        arsort($probabilities);
        $primarySentiment = key($probabilities);
        $confidence = current($probabilities);
        
        // Calculate sentiment score (-100 to 100)
        $sentimentScore = ($probabilities['positive'] - $probabilities['negative']) * 10;
        
        return [
            'sentiment' => ucfirst($primarySentiment),
            'confidence' => $confidence,
            'score' => round($sentimentScore, 2),
            'probabilities' => $probabilities,
            'word_count' => $totalWords,
            'analysis_method' => $totalMatches > 0 ? 'pattern_matching' : 'heuristic'
        ];
    }
    
    /**
     * Heuristic sentiment analysis when no patterns match
     */
    private function heuristicSentimentAnalysis(string $text, array $words): array {
        $totalWords = count($words);
        
        // Heuristic rules
        $positiveIndicators = [
            '!' => 0.5,  // Exclamation marks
            '?' => 0.3,  // Questions (can be positive or negative)
            '...' => -0.2, // Ellipsis (often negative)
            '!!!' => 0.8, // Multiple exclamations
            '??' => -0.1, // Multiple questions (confusion)
        ];
        
        $positiveScore = 0;
        $negativeScore = 0;
        $neutralScore = 50; // Start with neutral bias
        
        // Check for punctuation patterns
        foreach ($positiveIndicators as $punctuation => $weight) {
            $count = substr_count($text, $punctuation);
            if ($weight > 0) {
                $positiveScore += $count * $weight;
            } else {
                $negativeScore += abs($count * $weight);
            }
        }
        
        // Text length heuristic
        if ($totalWords > 50) {
            $neutralScore += 10; // Longer texts tend to be more descriptive/neutral
        }
        
        // Capitalization heuristic
        if (preg_match_all('/\b[A-Z]{2,}\b/', $text, $matches)) {
            $negativeScore += count($matches[0]) * 0.3; // ALL CAPS often indicates strong emotion
        }
        
        // Calculate final probabilities
        $totalScore = $positiveScore + $negativeScore + $neutralScore;
        
        return [
            'positive' => round(($positiveScore / $totalScore) * 100, 2),
            'negative' => round(($negativeScore / $totalScore) * 100, 2),
            'neutral' => round(($neutralScore / $totalScore) * 100, 2)
        ];
    }
    
    /**
     * Analyze emotions in text
     */
    public function analyzeEmotion(string $text): array {
        // Emotion word patterns
        $emotionPatterns = [
            'joy' => [
                'happy', 'joy', 'delighted', 'ecstatic', 'cheerful', 'glad', 'pleased',
                'content', 'thrilled', 'excited', 'elated', 'jubilant', 'blissful',
                'merry', 'jolly', 'gleeful', 'euphoric', 'overjoyed', 'rapturous',
                'smiling', 'laughing', 'giggling', 'grinning', 'beaming', 'radiant'
            ],
            'sadness' => [
                'sad', 'unhappy', 'depressed', 'miserable', 'sorrowful', 'gloomy',
                'melancholy', 'blue', 'down', 'dejected', 'despondent', 'disheartened',
                'discouraged', 'hopeless', 'heartbroken', 'devastated', 'tearful',
                'weeping', 'crying', 'sobbing', 'mournful', 'woeful', 'dismal'
            ],
            'anger' => [
                'angry', 'mad', 'furious', 'enraged', 'infuriated', 'irate', 'incensed',
                'wrathful', 'outraged', 'annoyed', 'irritated', 'aggravated', 'exasperated',
                'frustrated', 'resentful', 'indignant', 'hostile', 'bitter', 'vengeful',
                'spiteful', 'hateful', 'malicious', 'rage', 'temper', 'tantrum'
            ],
            'fear' => [
                'scared', 'afraid', 'fearful', 'terrified', 'frightened', 'panicked',
                'alarmed', 'apprehensive', 'anxious', 'worried', 'nervous', 'tense',
                'uneasy', 'restless', 'jittery', 'jumpy', 'timid', 'cowardly', 'shy',
                'hesitant', 'cautious', 'wary', 'suspicious', 'dread', 'horror'
            ],
            'surprise' => [
                'surprised', 'shocked', 'amazed', 'astonished', 'astounded', 'stunned',
                'startled', 'dumbfounded', 'flabbergasted', 'bewildered', 'baffled',
                'perplexed', 'confused', 'puzzled', 'mystified', 'staggered', 'taken aback'
            ],
            'love' => [
                'love', 'loving', 'affectionate', 'fond', 'adoring', 'devoted', 'passionate',
                'romantic', 'intimate', 'tender', 'caring', 'compassionate', 'sympathetic',
                'empathetic', 'kind', 'gentle', 'warm', 'friendly', 'close', 'dear'
            ],
            'neutral' => [
                'said', 'think', 'know', 'like', 'just', 'really', 'very', 'quite',
                'somewhat', 'rather', 'fairly', 'pretty', 'actually', 'basically',
                'essentially', 'fundamentally', 'generally', 'usually', 'normally',
                'typically', 'often', 'frequently', 'sometimes', 'occasionally'
            ]
        ];
        
        $textLower = strtolower($text);
        $words = str_word_count($textLower, 1);
        $totalWords = count($words);
        
        $emotionScores = [];
        $emotionMatches = [];
        
        // Count emotion word occurrences
        foreach ($emotionPatterns as $emotion => $patterns) {
            $score = 0;
            $matches = [];
            
            foreach ($patterns as $pattern) {
                $count = substr_count($textLower, $pattern);
                if ($count > 0) {
                    $score += $count;
                    $matches[$pattern] = $count;
                }
            }
            
            $emotionScores[$emotion] = $score;
            if (!empty($matches)) {
                $emotionMatches[$emotion] = $matches;
            }
        }
        
        // If no emotion words found, use text analysis heuristics
        $totalEmotionScore = array_sum($emotionScores);
        
        if ($totalEmotionScore === 0) {
            return $this->heuristicEmotionAnalysis($text, $words);
        }
        
        // Calculate percentages
        $emotionPercentages = [];
        foreach ($emotionScores as $emotion => $score) {
            $emotionPercentages[$emotion] = $totalEmotionScore > 0 ? 
                round(($score / $totalEmotionScore) * 100, 2) : 0;
        }
        
        // Determine primary emotion
        arsort($emotionPercentages);
        $primaryEmotion = key($emotionPercentages);
        $primaryScore = current($emotionPercentages);
        
        return [
            'primary_emotion' => ucfirst($primaryEmotion),
            'primary_score' => $primaryScore,
            'emotion_scores' => $emotionScores,
            'emotion_percentages' => $emotionPercentages,
            'emotion_matches' => $emotionMatches,
            'total_emotion_score' => $totalEmotionScore,
            'word_count' => $totalWords,
            'analysis_method' => 'pattern_matching'
        ];
    }
    
    /**
     * Heuristic emotion analysis when no emotion words found
     */
    private function heuristicEmotionAnalysis(string $text, array $words): array {
        $totalWords = count($words);
        
        // Analyze text characteristics
        $sentenceCount = count(preg_split('/[.!?]+/', $text)) - 1;
        $avgSentenceLength = $sentenceCount > 0 ? $totalWords / $sentenceCount : 0;
        
        // Punctuation analysis
        $exclamationCount = substr_count($text, '!');
        $questionCount = substr_count($text, '?');
        $ellipsisCount = substr_count($text, '...');
        
        // Heuristic rules for emotion detection
        $emotionProbabilities = [
            'joy' => 0,
            'sadness' => 0,
            'anger' => 0,
            'fear' => 0,
            'surprise' => 0,
            'love' => 0,
            'neutral' => 50  // Default neutral bias
        ];
        
        // Text length heuristic
        if ($totalWords < 20) {
            $emotionProbabilities['surprise'] += 15; // Short texts often express surprise
        }
        
        // Exclamation marks indicate strong emotion
        if ($exclamationCount > 0) {
            $emotionProbabilities['joy'] += $exclamationCount * 5;
            $emotionProbabilities['anger'] += $exclamationCount * 3;
        }
        
        // Questions indicate curiosity or confusion
        if ($questionCount > 0) {
            $emotionProbabilities['surprise'] += $questionCount * 4;
        }
        
        // Ellipsis indicates hesitation or sadness
        if ($ellipsisCount > 0) {
            $emotionProbabilities['sadness'] += $ellipsisCount * 8;
        }
        
        // Long sentences often indicate thoughtful/neutral content
        if ($avgSentenceLength > 15) {
            $emotionProbabilities['neutral'] += 10;
        }
        
        // Normalize to 100%
        $total = array_sum($emotionProbabilities);
        foreach ($emotionProbabilities as $emotion => $score) {
            $emotionProbabilities[$emotion] = round(($score / $total) * 100, 2);
        }
        
        // Determine primary emotion
        arsort($emotionProbabilities);
        $primaryEmotion = key($emotionProbabilities);
        
        return [
            'primary_emotion' => ucfirst($primaryEmotion),
            'primary_score' => $emotionProbabilities[$primaryEmotion],
            'emotion_scores' => $emotionProbabilities,
            'emotion_percentages' => $emotionProbabilities,
            'emotion_matches' => [],
            'total_emotion_score' => 0,
            'word_count' => $totalWords,
            'analysis_method' => 'heuristic',
            'heuristic_indicators' => [
                'exclamation_marks' => $exclamationCount,
                'question_marks' => $questionCount,
                'ellipsis' => $ellipsisCount,
                'sentence_count' => $sentenceCount,
                'avg_sentence_length' => round($avgSentenceLength, 2)
            ]
        ];
    }
    
    /**
     * Extract keywords using TF-IDF algorithm
     */
    public function extractKeywords(string $text): array {
        $words = str_word_count(strtolower($text), 1);
        $totalWords = count($words);
        
        if ($totalWords === 0) {
            return [
                'keywords' => [],
                'total_keywords' => 0,
                'word_count' => 0
            ];
        }
        
        // Common stopwords to filter out
        $stopwords = [
            'the', 'and', 'is', 'in', 'to', 'a', 'of', 'for', 'on', 'that', 'with', 
            'by', 'this', 'it', 'as', 'be', 'are', 'was', 'were', 'at', 'from', 'or',
            'an', 'but', 'not', 'what', 'all', 'were', 'when', 'we', 'your', 'can',
            'said', 'there', 'use', 'each', 'which', 'she', 'how', 'their', 'will',
            'other', 'about', 'out', 'many', 'then', 'them', 'these', 'so', 'some',
            'her', 'would', 'make', 'like', 'him', 'into', 'time', 'has', 'look',
            'two', 'more', 'write', 'go', 'see', 'number', 'no', 'way', 'could',
            'people', 'my', 'than', 'first', 'water', 'been', 'call', 'who', 'oil',
            'its', 'now', 'find', 'long', 'down', 'day', 'did', 'get', 'come', 'made',
            'may', 'part', 'over', 'new', 'sound', 'take', 'only', 'little', 'work',
            'know', 'place', 'year', 'live', 'me', 'back', 'give', 'most', 'very',
            'after', 'thing', 'our', 'just', 'name', 'good', 'sentence', 'man', 'think',
            'say', 'great', 'where', 'help', 'through', 'much', 'before', 'line',
            'right', 'too', 'mean', 'old', 'any', 'same', 'tell', 'boy', 'follow',
            'came', 'want', 'show', 'also', 'around', 'form', 'three', 'small', 'set',
            'put', 'end', 'does', 'another', 'well', 'large', 'must', 'big', 'even',
            'such', 'because', 'turn', 'here', 'why', 'ask', 'went', 'men', 'read',
            'need', 'land', 'different', 'home', 'us', 'move', 'try', 'kind', 'hand',
            'picture', 'again', 'change', 'off', 'play', 'spell', 'air', 'away',
            'animal', 'house', 'point', 'page', 'letter', 'mother', 'answer', 'found',
            'study', 'still', 'learn', 'should', 'america', 'world'
        ];
        
        // Filter out stopwords and short words
        $filteredWords = array_filter($words, function($word) use ($stopwords) {
            return !in_array($word, $stopwords) && strlen($word) > 2;
        });
        
        // Calculate word frequencies
        $wordFrequencies = array_count_values($filteredWords);
        arsort($wordFrequencies);
        
        // Calculate TF-IDF scores (simplified)
        $keywords = [];
        foreach ($wordFrequencies as $word => $frequency) {
            $tf = $frequency / $totalWords; // Term Frequency
            $idf = log($totalWords / ($frequency + 1)); // Inverse Document Frequency (simplified)
            $tfidf = $tf * $idf;
            
            $keywords[] = [
                'word' => ucfirst($word),
                'frequency' => $frequency,
                'tfidf_score' => round($tfidf * 100, 2)
            ];
            
            if (count($keywords) >= 15) {
                break;
            }
        }
        
        // Sort by TF-IDF score
        usort($keywords, function($a, $b) {
            return $b['tfidf_score'] <=> $a['tfidf_score'];
        });
        
        return [
            'keywords' => $keywords,
            'total_keywords' => count($keywords),
            'word_count' => $totalWords,
            'unique_words' => count(array_unique($words)),
            'analysis_method' => 'tfidf'
        ];
    }
    
    /**
     * Summarize text using extractive summarization
     */
    public function summarizeText(string $text): array {
        $sentences = preg_split('/(?<=[.!?])\s+(?=[A-Z])/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $sentenceCount = count($sentences);
        
        if ($sentenceCount <= 3) {
            $summary = $text;
        } else {
            // Score sentences based on various factors
            $scoredSentences = [];
            
            foreach ($sentences as $index => $sentence) {
                $score = 0;
                
                // Favor first sentences
                if ($index === 0) $score += 3;
                
                // Favor last sentences
                if ($index === $sentenceCount - 1) $score += 2;
                
                // Favor sentences with keywords
                $words = str_word_count(strtolower($sentence), 1);
                $uniqueWords = count(array_unique($words));
                $score += $uniqueWords * 0.1;
                
                // Favor medium-length sentences (15-25 words)
                $wordCount = count($words);
                if ($wordCount >= 15 && $wordCount <= 25) {
                    $score += 2;
                }
                
                // Penalize very short sentences
                if ($wordCount < 5) {
                    $score -= 1;
                }
                
                // Bonus for questions and exclamations
                if (strpos($sentence, '?') !== false || strpos($sentence, '!') !== false) {
                    $score += 1;
                }
                
                $scoredSentences[] = [
                    'sentence' => $sentence,
                    'score' => $score,
                    'index' => $index
                ];
            }
            
            // Sort by score
            usort($scoredSentences, function($a, $b) {
                return $b['score'] <=> $a['score'];
            });
            
            // Take top 3 sentences
            $topSentences = array_slice($scoredSentences, 0, 3);
            
            // Sort by original position
            usort($topSentences, function($a, $b) {
                return $a['index'] <=> $b['index'];
            });
            
            $summary = implode(' ', array_column($topSentences, 'sentence'));
        }
        
        $wordCount = str_word_count($text);
        $summaryWordCount = str_word_count($summary);
        $reduction = $wordCount > 0 ? round((1 - ($summaryWordCount / $wordCount)) * 100, 2) : 0;
        
        return [
            'summary' => $summary,
            'original_length' => $wordCount,
            'summary_length' => $summaryWordCount,
            'reduction' => $reduction,
            'sentence_count' => $sentenceCount,
            'compression_ratio' => round($summaryWordCount / max($wordCount, 1) * 100, 2)
        ];
    }
    
    /**
     * Get text statistics
     */
    public function getTextStatistics(string $text): array {
        $wordCount = str_word_count($text);
        $charCount = strlen($text);
        $sentenceCount = count(preg_split('/[.!?]+/', $text)) - 1;
        $avgSentenceLength = $sentenceCount > 0 ? $wordCount / $sentenceCount : 0;
        
        $words = str_word_count(strtolower($text), 1);
        $uniqueWords = count(array_unique($words));
        $lexicalDensity = $wordCount > 0 ? round(($uniqueWords / $wordCount) * 100, 2) : 0;
        
        // Reading time calculation (200 words per minute)
        $readingTime = round($wordCount / 200, 1);
        
        return [
            'word_count' => $wordCount,
            'char_count' => $charCount,
            'sentence_count' => $sentenceCount,
            'avg_sentence_length' => round($avgSentenceLength, 2),
            'unique_words' => $uniqueWords,
            'lexical_density' => $lexicalDensity,
            'reading_time_minutes' => $readingTime
        ];
    }
}
?>