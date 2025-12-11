<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Text Analyzer Pro</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-brain"></i> Smart Text Analyzer Pro</h1>
            <p>Advanced Text Analysis with Machine Learning Integration</p>
            <div style="margin-top: 1rem;">
                <span class="badge" style="background: rgba(255,255,255,0.2); padding: 0.3rem 0.8rem; border-radius: 20px;">
                    <i class="fas fa-check-circle"></i> Powered by PHP + Composer + Packagist ML
                </span>
            </div>
        </div>
        
        <div class="dashboard">
            <div class="sidebar">
                <div class="model-selector">
                    <h3><i class="fas fa-microchip"></i> Select ML Model</h3>
                    <div class="model-card active" data-model="sentiment">
                        <div style="display: flex; align-items: center;">
                            <span class="model-icon"><i class="fas fa-smile"></i></span>
                            <div>
                                <h4>Sentiment Analysis</h4>
                                <p style="font-size: 0.9rem; color: #666;">Detect positive/negative tone</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="model-card" data-model="keywords">
                        <div style="display: flex; align-items: center;">
                            <span class="model-icon"><i class="fas fa-key"></i></span>
                            <div>
                                <h4>Keyword Extraction</h4>
                                <p style="font-size: 0.9rem; color: #666;">Extract important keywords</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="model-card" data-model="emotion">
                        <div style="display: flex; align-items: center;">
                            <span class="model-icon"><i class="fas fa-heart"></i></span>
                            <div>
                                <h4>Emotion Detection</h4>
                                <p style="font-size: 0.9rem; color: #666;">Identify emotional content</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="history-section">
                    <h3><i class="fas fa-history"></i> Recent Analyses</h3>
                    <div id="history-list">
                        <!-- Dynamically loaded -->
                        <p style="color: #999; text-align: center; padding: 1rem;">No recent analyses</p>
                    </div>
                </div>
            </div>
            
            <div class="main-content">
                <div class="text-input-container">
                    <h3><i class="fas fa-edit"></i> Enter Text for Analysis</h3>
                    <form action="index.php?page=analyze" method="POST" id="analysis-form">
                        <input type="hidden" name="model_type" id="model_type" value="sentiment">
                        <textarea 
                            class="text-area" 
                            name="text_input" 
                            id="text_input" 
                            placeholder="Enter your text here... (Minimum 50 characters for best results)"
                            required></textarea>
                        
                        <div class="text-stats">
                            <small id="word-count">Words: 0</small>
                            <small id="char-count">Characters: 0</small>
                        </div>
                        
                        <div class="action-buttons">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-magic"></i> Analyze Text
                            </button>
                            <button type="button" class="btn btn-secondary" id="sample-btn">
                                <i class="fas fa-file-alt"></i> Load Sample Text
                            </button>
                            <button type="button" class="btn btn-secondary" id="clear-btn">
                                <i class="fas fa-trash"></i> Clear
                            </button>
                        </div>
                    </form>
                </div>
                
                <?php if(isset($_SESSION['last_result'])): ?>
                <div class="result-container">
                    <?php 
                    $result = $_SESSION['last_result'];
                    include 'views/results.php';
                    ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="footer">
            <p>ITEP 308 Final Project - System Integration & Architecture | LSPU</p>
            <p style="margin-top: 0.5rem; font-size: 0.9rem; color: #666;">
                <i class="fas fa-code-branch"></i> Using: PHP 8.x, Composer, Rubix ML, Hugging Face API
            </p>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
</body>
</html>