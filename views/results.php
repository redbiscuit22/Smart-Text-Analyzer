<?php
/**
 * Results Display View
 * 
 * @var array $result The analysis results
 * @var string $model_type The type of analysis performed
 * @var string $text_preview The original text preview
 */

$result = $result ?? $_SESSION['last_analysis']['result'] ?? [];
$model_type = $model_type ?? $_SESSION['last_analysis']['model_type'] ?? 'sentiment';
$text_preview = $text_preview ?? $_SESSION['last_analysis']['text'] ?? '';

if (empty($result)) {
    echo '<div class="alert alert-info">No analysis results to display.</div>';
    return;
}
?>

<div class="result-container">
    <div class="result-header">
        <h2 class="result-title">
            <i class="fas fa-chart-bar"></i> Analysis Results
        </h2>
        <span class="result-type badge">
            <?php echo ucfirst($model_type); ?> Analysis
        </span>
    </div>
    
    <!-- Model Source Indicator -->
    <?php if (isset($result['source'])): ?>
    <div class="source-indicator">
        <span class="badge <?php echo $result['source'] === 'huggingface' ? 'bg-success' : 'bg-info'; ?>">
            <i class="fas fa-<?php echo $result['source'] === 'huggingface' ? 'cloud' : 'desktop'; ?>"></i>
            Powered by <?php echo $result['source'] === 'huggingface' ? 'Hugging Face API' : 'Local Processing'; ?>
        </span>
        <?php if (isset($result['model'])): ?>
        <small class="text-muted ms-2">
            <i class="fas fa-cogs"></i> Model: <?php echo htmlspecialchars($result['model']); ?>
        </small>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <!-- Text Preview -->
    <div class="text-preview mt-3">
        <h5><i class="fas fa-file-alt"></i> Text Analyzed:</h5>
        <div class="preview-box">
            <?php echo nl2br(htmlspecialchars(substr($text_preview, 0, 300))); ?>
            <?php if (strlen($text_preview) > 300): ?>
            <span class="text-muted">... (truncated)</span>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Main Results -->
    <div class="main-results mt-4">
        <?php
        switch ($model_type) {
            case 'sentiment':
                $this->displaySentimentResults($result);
                break;
            case 'keywords':
                $this->displayKeywordResults($result);
                break;
            case 'emotion':
                $this->displayEmotionResults($result);
                break;
            case 'summarize':
                $this->displaySummaryResults($result);
                break;
            case 'advanced':
                $this->displayAdvancedResults($result);
                break;
            default:
                $this->displayBasicResults($result);
                break;
        }
        ?>
    </div>
    
    <!-- Metadata -->
    <?php if (isset($result['metadata'])): ?>
    <div class="metadata mt-4 pt-3 border-top">
        <h6><i class="fas fa-info-circle"></i> Analysis Details:</h6>
        <div class="row">
            <div class="col-md-6">
                <small class="text-muted">
                    <i class="fas fa-clock"></i> Processed in: 
                    <?php echo $result['metadata']['processing_time_ms'] ?? 'N/A'; ?>ms
                </small>
            </div>
            <div class="col-md-6">
                <small class="text-muted">
                    <i class="fas fa-calendar"></i> Timestamp: 
                    <?php echo $result['metadata']['timestamp'] ?? date('Y-m-d H:i:s'); ?>
                </small>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
// Helper methods for displaying different result types
function displaySentimentResults($result): void {
    ?>
    <div class="sentiment-results">
        <h4><i class="fas fa-smile"></i> Sentiment Analysis</h4>
        
        <?php if (isset($result['primary_sentiment'])): ?>
        <div class="sentiment-display mb-4">
            <div class="d-flex align-items-center mb-3">
                <div class="sentiment-indicator <?php 
                    echo strtolower($result['primary_sentiment']) === 'positive' ? 'sentiment-positive' : 
                         (strtolower($result['primary_sentiment']) === 'negative' ? 'sentiment-negative' : 'sentiment-neutral'); 
                ?>">
                    <?php echo htmlspecialchars($result['primary_sentiment']); ?>
                </div>
                
                <?php if (isset($result['confidence'])): ?>
                <div class="ms-3">
                    <strong>Confidence:</strong> <?php echo $result['confidence']; ?>%
                    <div class="progress" style="width: 200px; height: 10px;">
                        <div class="progress-bar" 
                             role="progressbar" 
                             style="width: <?php echo $result['confidence']; ?>%"
                             aria-valuenow="<?php echo $result['confidence']; ?>" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (isset($result['score'])): ?>
            <p><strong>Sentiment Score:</strong> <?php echo $result['score']; ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($result['all_scores'])): ?>
        <div class="detailed-scores">
            <h5>Detailed Scores:</h5>
            <div class="row">
                <?php foreach ($result['all_scores'] as $score): ?>
                <div class="col-md-4 mb-2">
                    <div class="card">
                        <div class="card-body p-2">
                            <small>
                                <strong><?php echo ucfirst($score['label']); ?>:</strong>
                                <?php echo round($score['score'] * 100, 2); ?>%
                            </small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php
}

function displayKeywordResults($result): void {
    ?>
    <div class="keyword-results">
        <h4><i class="fas fa-key"></i> Keyword Extraction</h4>
        
        <?php if (isset($result['keywords']) && is_array($result['keywords'])): ?>
        <div class="keyword-cloud mb-4">
            <h5>Extracted Keywords:</h5>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($result['keywords'] as $keyword): ?>
                <?php
                $size = is_array($keyword) && isset($keyword['score']) ? 
                        min(18, 12 + ($keyword['score'] * 10)) : 14;
                ?>
                <span class="keyword-badge" style="font-size: <?php echo $size; ?>px;">
                    <?php echo is_array($keyword) ? htmlspecialchars($keyword['word'] ?? $keyword) : htmlspecialchars($keyword); ?>
                    <?php if (is_array($keyword) && isset($keyword['score'])): ?>
                    <small class="text-muted">(<?php echo round($keyword['score'] * 100, 0); ?>%)</small>
                    <?php endif; ?>
                </span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (isset($result['total_keywords'])): ?>
        <p><strong>Total Keywords Found:</strong> <?php echo $result['total_keywords']; ?></p>
        <?php endif; ?>
        
        <?php if (isset($result['method'])): ?>
        <p><strong>Extraction Method:</strong> <?php echo ucfirst($result['method']); ?></p>
        <?php endif; ?>
    </div>
    <?php
}

function displayEmotionResults($result): void {
    ?>
    <div class="emotion-results">
        <h4><i class="fas fa-heart"></i> Emotion Detection</h4>
        
        <?php if (isset($result['primary_emotion'])): ?>
        <div class="primary-emotion mb-4">
            <div class="d-flex align-items-center">
                <div class="emotion-icon me-3" style="font-size: 3rem;">
                    <?php
                    $emotionIcons = [
                        'joy' => 'fas fa-laugh-beam',
                        'sadness' => 'fas fa-sad-cry',
                        'anger' => 'fas fa-angry',
                        'fear' => 'fas fa-surprise',
                        'love' => 'fas fa-heart',
                        'surprise' => 'fas fa-surprise'
                    ];
                    $icon = $emotionIcons[strtolower($result['primary_emotion'])] ?? 'fas fa-meh';
                    ?>
                    <i class="<?php echo $icon; ?>"></i>
                </div>
                <div>
                    <h3><?php echo ucfirst($result['primary_emotion']); ?></h3>
                    <?php if (isset($result['emotion_score'])): ?>
                    <p class="mb-0">
                        <strong>Intensity:</strong> <?php echo $result['emotion_score']; ?>%
                        <div class="progress" style="width: 200px; height: 8px;">
                            <div class="progress-bar bg-danger" 
                                 style="width: <?php echo $result['emotion_score']; ?>%">
                            </div>
                        </div>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (isset($result['all_emotions'])): ?>
        <div class="all-emotions">
            <h5>All Emotion Scores:</h5>
            <div class="row">
                <?php foreach ($result['all_emotions'] as $emotion): ?>
                <div class="col-md-6 mb-2">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="fas fa-feather-alt me-2"></i>
                                    <?php echo ucfirst($emotion['label'] ?? $emotion); ?>
                                </span>
                                <strong>
                                    <?php echo round(($emotion['score'] ?? 0) * 100, 1); ?>%
                                </strong>
                            </div>
                            <div class="progress mt-2" style="height: 5px;">
                                <div class="progress-bar" 
                                     style="width: <?php echo ($emotion['score'] ?? 0) * 100; ?>%">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php
}

function displaySummaryResults($result): void {
    ?>
    <div class="summary-results">
        <h4><i class="fas fa-file-contract"></i> Text Summary</h4>
        
        <?php if (isset($result['summary'])): ?>
        <div class="summary-box p-3 mb-4 bg-light rounded">
            <p class="mb-0"><?php echo nl2br(htmlspecialchars($result['summary'])); ?></p>
        </div>
        <?php endif; ?>
        
        <?php if (isset($result['original_length']) && isset($result['summary_length'])): ?>
        <div class="summary-stats">
            <div class="row">
                <div class="col-md-4">
                    <div class="stat-card text-center p-3 border rounded">
                        <h5><?php echo $result['original_length']; ?></h5>
                        <small class="text-muted">Original Words</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card text-center p-3 border rounded">
                        <h5><?php echo $result['summary_length']; ?></h5>
                        <small class="text-muted">Summary Words</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card text-center p-3 border rounded">
                        <h5><?php echo $result['compression_ratio'] ?? 'N/A'; ?>%</h5>
                        <small class="text-muted">Compression</small>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php
}

function displayAdvancedResults($result): void {
    if (!isset($result['analyses'])) return;
    
    $analyses = $result['analyses'];
    ?>
    <div class="advanced-results">
        <h4><i class="fas fa-chart-line"></i> Comprehensive Analysis</h4>
        
        <!-- Overall Score -->
        <?php if (isset($result['overall_score'])): ?>
        <div class="overall-score text-center mb-4 p-4 bg-gradient rounded text-white" 
             style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <h2><?php echo $result['overall_score']['score']; ?>/100</h2>
            <h4><?php echo $result['overall_score']['rating']; ?></h4>
            <p class="mb-0"><?php echo $result['overall_score']['interpretation']; ?></p>
        </div>
        <?php endif; ?>
        
        <!-- Tabs for different analyses -->
        <ul class="nav nav-tabs mb-4" id="analysisTabs" role="tablist">
            <?php
            $tabs = ['sentiment', 'keywords', 'emotion', 'statistics', 'complexity'];
            foreach ($tabs as $index => $tab):
                if (isset($analyses[$tab])):
            ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $index === 0 ? 'active' : ''; ?>" 
                        id="<?php echo $tab; ?>-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#<?php echo $tab; ?>" 
                        type="button" 
                        role="tab">
                    <?php echo ucfirst($tab); ?>
                </button>
            </li>
            <?php
                endif;
            endforeach;
            ?>
        </ul>
        
        <!-- Tab Content -->
        <div class="tab-content" id="analysisTabsContent">
            <?php foreach ($tabs as $index => $tab): ?>
            <?php if (isset($analyses[$tab])): ?>
            <div class="tab-pane fade <?php echo $index === 0 ? 'show active' : ''; ?>" 
                 id="<?php echo $tab; ?>" 
                 role="tabpanel">
                <?php
                switch ($tab) {
                    case 'sentiment':
                        displaySentimentResults($analyses[$tab]);
                        break;
                    case 'keywords':
                        displayKeywordResults($analyses[$tab]);
                        break;
                    case 'emotion':
                        displayEmotionResults($analyses[$tab]);
                        break;
                    case 'statistics':
                        displayStatistics($analyses[$tab]);
                        break;
                    case 'complexity':
                        displayComplexity($analyses[$tab]);
                        break;
                }
                ?>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

function displayStatistics($stats): void {
    ?>
    <div class="statistics">
        <h5><i class="fas fa-chart-pie"></i> Text Statistics</h5>
        <div class="row">
            <div class="col-md-6">
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Word Count</span>
                        <strong><?php echo $stats['word_count'] ?? 'N/A'; ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Character Count</span>
                        <strong><?php echo $stats['char_count'] ?? 'N/A'; ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Sentence Count</span>
                        <strong><?php echo $stats['sentence_count'] ?? 'N/A'; ?></strong>
                    </li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Average Word Length</span>
                        <strong><?php echo $stats['avg_word_length'] ?? 'N/A'; ?> chars</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Reading Time</span>
                        <strong><?php echo $stats['reading_time_minutes'] ?? 'N/A'; ?> min</strong>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <?php
}

function displayComplexity($complexity): void {
    ?>
    <div class="complexity">
        <h5><i class="fas fa-brain"></i> Text Complexity</h5>
        
        <?php if (isset($complexity['lexical_diversity'])): ?>
        <div class="mb-4">
            <h6>Lexical Diversity: <?php echo $complexity['lexical_diversity']; ?>%</h6>
            <div class="progress" style="height: 20px;">
                <div class="progress-bar" 
                     role="progressbar" 
                     style="width: <?php echo $complexity['lexical_diversity']; ?>%"
                     aria-valuenow="<?php echo $complexity['lexical_diversity']; ?>" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                </div>
            </div>
            <small class="text-muted">
                Percentage of unique words in the text. Higher values indicate richer vocabulary.
            </small>
        </div>
        <?php endif; ?>
        
        <?php if (isset($complexity['unique_words'])): ?>
        <p><strong>Unique Words:</strong> <?php echo $complexity['unique_words']; ?></p>
        <p><strong>Unique Ratio:</strong> <?php echo $complexity['unique_ratio'] ?? 'N/A'; ?>%</p>
        <?php endif; ?>
    </div>
    <?php
}

function displayBasicResults($result): void {
    ?>
    <div class="basic-results">
        <h4><i class="fas fa-file-alt"></i> Basic Text Analysis</h4>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="card-title"><i class="fas fa-font"></i> Word Statistics</h6>
                        <ul class="list-unstyled">
                            <li><strong>Total Words:</strong> <?php echo $result['word_count'] ?? 'N/A'; ?></li>
                            <li><strong>Unique Words:</strong> <?php echo $result['unique_words'] ?? 'N/A'; ?></li>
                            <li><strong>Characters:</strong> <?php echo $result['char_count'] ?? 'N/A'; ?></li>
                            <li><strong>Sentences:</strong> <?php echo $result['sentence_count'] ?? 'N/A'; ?></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="card-title"><i class="fas fa-history"></i> Reading Information</h6>
                        <ul class="list-unstyled">
                            <li><strong>Reading Time:</strong> <?php echo $result['reading_time'] ?? 'N/A'; ?></li>
                            <?php if (isset($result['most_common_words'])): ?>
                            <li class="mt-2">
                                <strong>Most Common Words:</strong>
                                <div class="mt-1">
                                    <?php
                                    $count = 0;
                                    foreach ($result['most_common_words'] as $word => $frequency):
                                        if ($count++ < 5):
                                    ?>
                                    <span class="badge bg-secondary me-1 mb-1">
                                        <?php echo htmlspecialchars($word); ?> (<?php echo $frequency; ?>)
                                    </span>
                                    <?php
                                        endif;
                                    endforeach;
                                    ?>
                                </div>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>