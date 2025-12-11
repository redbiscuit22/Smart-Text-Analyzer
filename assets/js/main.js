/**
 * Smart Text Analyzer  - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Smart Text Analyzer  loaded');
    
    // Initialize all components
    initModelSelector();
    initTextArea();
    initSampleTextLoader();
    initFormSubmission();
    initHistoryLoader();
    
    // Load initial history
    loadHistory();
    
    // Update text statistics
    updateTextStats();
});

/**
 * Initialize model selector
 */
function initModelSelector() {
    const modelCards = document.querySelectorAll('.model-card');
    const modelTypeInput = document.getElementById('model_type');
    
    if (!modelCards.length || !modelTypeInput) {
        console.warn('Model selector elements not found');
        return;
    }
    
    modelCards.forEach(card => {
        card.addEventListener('click', function() {
            // Remove active class from all cards
            modelCards.forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked card
            this.classList.add('active');
            
            // Update hidden input
            const modelType = this.dataset.model;
            modelTypeInput.value = modelType;
            
            // Update UI
            updateModelUI(modelType);
        });
    });
}

/**
 * Update UI based on selected model
 */
function updateModelUI(modelType) {
    const textArea = document.getElementById('text_input');
    if (!textArea) return;
    
    const placeholders = {
        'sentiment': 'Enter text to analyze sentiment (e.g., product reviews, feedback)...',
        'keywords': 'Enter text to extract keywords (e.g., articles, documents)...',
        'emotion': 'Enter text to detect emotions (e.g., stories, social media posts)...',
    
    };
    
    textArea.placeholder = placeholders[modelType] || 'Enter your text here... (Minimum 50 characters for best results)';
}

/**
 * Initialize text area functionality
 */
function initTextArea() {
    const textArea = document.getElementById('text_input');
    
    if (!textArea) {
        console.warn('Text area not found');
        return;
    }
    
    // Auto-resize textarea
    textArea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
        
        // Update statistics
        updateTextStats();
    });
    
    // Clear button
    const clearBtn = document.getElementById('clear-btn');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            textArea.value = '';
            textArea.style.height = 'auto';
            updateTextStats();
            clearResults();
        });
    }
}

/**
 * Update word and character count
 */
function updateTextStats() {
    const textArea = document.getElementById('text_input');
    const wordCount = document.getElementById('word-count');
    const charCount = document.getElementById('char-count');
    
    if (!textArea || !wordCount || !charCount) return;
    
    const text = textArea.value;
    const words = text.trim() === '' ? 0 : text.trim().split(/\s+/).length;
    const chars = text.length;
    
    wordCount.textContent = `Words: ${words}`;
    charCount.textContent = `Characters: ${chars}`;
    
    // Update colors
    wordCount.style.color = words < 10 ? '#dc3545' : '#28a745';
    charCount.style.color = chars < 50 ? '#dc3545' : '#28a745';
}

/**
 * Initialize sample text loader
 */
function initSampleTextLoader() {
    const sampleBtn = document.getElementById('sample-btn');
    const textArea = document.getElementById('text_input');
    const modelTypeInput = document.getElementById('model_type');
    
    if (!sampleBtn || !textArea || !modelTypeInput) return;
    
    sampleBtn.addEventListener('click', function() {
        const modelType = modelTypeInput.value || 'sentiment';
        const sample = getSampleText(modelType);
        
        textArea.value = sample.text;
        
        // Trigger input event
        textArea.dispatchEvent(new Event('input'));
        
        // Show success message
        showToast('Sample text loaded!', 'success');
    });
}

/**
 * Get sample text based on model type
 */
function getSampleText(modelType) {
    const samples = {
        'sentiment': {
            text: "This product is absolutely amazing! The quality is exceptional and it works perfectly. I love how easy it is to use and the customer service was wonderful when I had questions. Highly recommended to everyone looking for a reliable solution. Five stars!",
            title: "Positive Product Review"
        },
        'keywords': {
            text: "Machine learning algorithms, particularly deep neural networks, have revolutionized natural language processing. Transformers and attention mechanisms enable models like BERT and GPT to understand context and generate human-like text. These advancements power modern applications from chatbots to automated content generation.",
            title: "Technical Article Excerpt"
        },
        'emotion': {
            text: "I was so happy and excited when I received the news! But then suddenly, fear crept in as I realized the responsibility. Now I feel anxious yet hopeful about the future. It's a rollercoaster of emotions that I never expected to experience.",
            title: "Emotional Story"
        },
        'summarize': {
            text: "Artificial intelligence is transforming industries worldwide. In healthcare, AI helps diagnose diseases and develop treatments. In finance, it detects fraud and manages investments. Education uses AI for personalized learning. While challenges exist regarding ethics and job displacement, the potential benefits are enormous. Governments and organizations must work together to ensure responsible AI development.",
            title: "AI Impact Summary"
        },
        'advanced': {
            text: "The rapid advancement of technology has fundamentally changed how we live and work. Smartphones connect us instantly, social media shapes public opinion, and automation streamlines industries. While these developments offer convenience and efficiency, they also raise important questions about privacy, job security, and mental health. Balancing innovation with ethical considerations remains one of society's greatest challenges in the digital age.",
            title: "Technology Impact Analysis"
        }
    };
    
    return samples[modelType] || samples['sentiment'];
}

/**
 * Initialize form submission
 */
function initFormSubmission() {
    const form = document.getElementById('analysis-form');
    
    if (!form) {
        console.warn('Analysis form not found');
        return;
    }
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const textArea = document.getElementById('text_input');
        const text = textArea.value.trim();
        
        // Validate text
        if (text.length < 10) {
            showToast('Please enter at least 10 characters', 'warning');
            return;
        }
        
        if (text.length > 10000) {
            showToast('Text is too long (max 10,000 characters)', 'warning');
            return;
        }
        
        // Show loading
        showLoading(true);
        
        // Simulate API call (replace with actual fetch)
        setTimeout(() => {
            const modelType = document.getElementById('model_type').value;
            const result = simulateAnalysis(text, modelType);
            
            // Display results
            displayResults(result, modelType, text);
            
            // Add to history
            addToHistory(text.substring(0, 100), modelType, result);
            
            // Hide loading
            showLoading(false);
            
            // Show success message
            showToast('Analysis completed successfully!', 'success');
        }, 1500);
    });
}

/**
 * Simulate analysis (for demo purposes)
 */
function simulateAnalysis(text, modelType) {
    const responses = {
        'sentiment': {
            type: 'sentiment',
            sentiment: text.toLowerCase().includes('amazing') || text.toLowerCase().includes('excellent') || text.toLowerCase().includes('love') ? 'POSITIVE' : 
                      text.toLowerCase().includes('disappointed') || text.toLowerCase().includes('damaged') || text.toLowerCase().includes('refused') ? 'NEGATIVE' : 'NEUTRAL',
            confidence: Math.floor(Math.random() * 30) + 70,
            score: text.toLowerCase().includes('amazing') ? 5 : text.toLowerCase().includes('disappointed') ? -5 : 0
        },
        'keywords': {
            type: 'keywords',
            keywords: ['product', 'quality', 'customer service', 'recommended', 'solution'].filter(word => 
                text.toLowerCase().includes(word.toLowerCase())
            ),
            total_keywords: 5,
            method: 'TF-IDF'
        },
        'emotion': {
            type: 'emotion',
            primary_emotion: text.toLowerCase().includes('happy') ? 'JOY' : 
                            text.toLowerCase().includes('disappointed') ? 'SADNESS' : 'NEUTRAL',
            emotion_score: Math.floor(Math.random() * 30) + 65,
            all_emotions: [
                { label: 'joy', score: text.toLowerCase().includes('happy') ? 0.8 : 0.2 },
                { label: 'sadness', score: text.toLowerCase().includes('disappointed') ? 0.7 : 0.1 },
                { label: 'anger', score: 0.1 },
                { label: 'fear', score: 0.05 },
                { label: 'surprise', score: 0.15 },
                { label: 'love', score: text.toLowerCase().includes('love') ? 0.9 : 0.1 }
            ]
        }
    };
    
    return responses[modelType] || {
        type: 'basic',
        word_count: text.split(/\s+/).length,
        char_count: text.length,
        reading_time: Math.ceil(text.split(/\s+/).length / 200) + ' minutes'
    };
}

/**
 * Display analysis results
 */
function displayResults(result, modelType, originalText) {
    let resultsContainer = document.querySelector('.result-container');
    
    if (!resultsContainer) {
        resultsContainer = document.createElement('div');
        resultsContainer.className = 'result-container';
        const mainContent = document.querySelector('.main-content');
        if (mainContent) {
            mainContent.appendChild(resultsContainer);
        } else {
            document.body.appendChild(resultsContainer);
        }
    }
    
    resultsContainer.innerHTML = generateResultsHTML(result, modelType, originalText);
    
    // Scroll to results
    resultsContainer.scrollIntoView({ behavior: 'smooth' });
}

/**
 * Generate HTML for results
 */
function generateResultsHTML(result, modelType, originalText) {
    let html = `
        <div class="result-header">
            <h2 class="result-title">
                <i class="fas fa-chart-bar"></i> Analysis Results
            </h2>
            <span class="result-type badge bg-primary">
                ${modelType.charAt(0).toUpperCase() + modelType.slice(1)} Analysis
            </span>
        </div>
        
        <div class="result-content mt-3">
            <h5><i class="fas fa-file-alt"></i> Text Analyzed:</h5>
            <div class="preview-box p-3 bg-light rounded mb-3">
                ${escapeHtml(originalText.substring(0, 200))}${originalText.length > 200 ? '...' : ''}
            </div>
    `;
    
    switch(modelType) {
        case 'sentiment':
            html += generateSentimentHTML(result);
            break;
        case 'keywords':
            html += generateKeywordsHTML(result);
            break;
        case 'emotion':
            html += generateEmotionHTML(result);
            break;
        default:
            html += generateBasicHTML(result);
    }
    
    html += `
            <div class="mt-4">
                <button class="btn btn-sm btn-outline-primary" onclick="exportResults()">
                    <i class="fas fa-download"></i> Export Results
                </button>
                <button class="btn btn-sm btn-outline-secondary ms-2" onclick="analyzeAgain()">
                    <i class="fas fa-redo"></i> Analyze Again
                </button>
            </div>
        </div>
    `;
    
    return html;
}

/**
 * Generate sentiment analysis HTML
 */
function generateSentimentHTML(result) {
    let sentimentClass = 'bg-secondary';
    if (result.sentiment === 'POSITIVE') sentimentClass = 'bg-success';
    if (result.sentiment === 'NEGATIVE') sentimentClass = 'bg-danger';
    
    return `
        <h5><i class="fas fa-smile"></i> Sentiment Analysis:</h5>
        <div class="d-flex align-items-center mb-3">
            <span class="badge ${sentimentClass} me-3 p-2" style="font-size: 1rem;">
                ${result.sentiment}
            </span>
            <div>
                <strong>Confidence:</strong> ${result.confidence}%
                <div class="progress" style="width: 200px; height: 10px;">
                    <div class="progress-bar ${result.sentiment === 'POSITIVE' ? 'bg-success' : result.sentiment === 'NEGATIVE' ? 'bg-danger' : 'bg-warning'}" 
                         style="width: ${result.confidence}%">
                    </div>
                </div>
            </div>
        </div>
        <p><strong>Sentiment Score:</strong> ${result.score}</p>
    `;
}

/**
 * Generate keyword extraction HTML
 */
function generateKeywordsHTML(result) {
    let keywordsHTML = '';
    if (result.keywords && result.keywords.length > 0) {
        keywordsHTML = '<div class="d-flex flex-wrap gap-2 mb-3">';
        result.keywords.forEach(keyword => {
            keywordsHTML += `<span class="badge bg-info">${escapeHtml(keyword)}</span>`;
        });
        keywordsHTML += '</div>';
    }
    
    return `
        <h5><i class="fas fa-key"></i> Keyword Extraction:</h5>
        ${keywordsHTML}
        <p><strong>Total Keywords:</strong> ${result.total_keywords || 0}</p>
        <p><strong>Method:</strong> ${result.method || 'TF-IDF'}</p>
    `;
}

/**
 * Generate emotion detection HTML
 */
function generateEmotionHTML(result) {
    let emotionsHTML = '';
    if (result.all_emotions) {
        result.all_emotions.forEach(emotion => {
            const width = Math.round(emotion.score * 100);
            emotionsHTML += `
                <div class="mb-2">
                    <div class="d-flex justify-content-between">
                        <span>${emotion.label.charAt(0).toUpperCase() + emotion.label.slice(1)}</span>
                        <span>${Math.round(emotion.score * 100)}%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar" style="width: ${width}%"></div>
                    </div>
                </div>
            `;
        });
    }
    
    return `
        <h5><i class="fas fa-heart"></i> Emotion Detection:</h5>
        <div class="mb-3">
            <span class="badge bg-danger p-2">
                Primary Emotion: ${result.primary_emotion}
            </span>
            <span class="ms-3">
                <strong>Intensity:</strong> ${result.emotion_score}%
            </span>
        </div>
        ${emotionsHTML}
    `;
}

/**
 * Generate basic analysis HTML
 */
function generateBasicHTML(result) {
    return `
        <h5><i class="fas fa-chart-pie"></i> Text Statistics:</h5>
        <div class="row">
            <div class="col-md-6">
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Word Count</span>
                        <strong>${result.word_count || 0}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Character Count</span>
                        <strong>${result.char_count || 0}</strong>
                    </li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Reading Time</span>
                        <strong>${result.reading_time || 'N/A'}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Analysis Type</span>
                        <strong>${result.type || 'Basic'}</strong>
                    </li>
                </ul>
            </div>
        </div>
    `;
}

/**
 * Initialize history loader
 */
function initHistoryLoader() {
    // Load history from localStorage
    loadHistory();
}

/**
 * Load analysis history
 */
function loadHistory() {
    const historyList = document.getElementById('history-list');
    if (!historyList) return;
    
    const history = JSON.parse(localStorage.getItem('textAnalysisHistory') || '[]');
    
    if (history.length === 0) {
        historyList.innerHTML = `
            <div class="text-center text-muted py-3">
                <i class="fas fa-history fa-2x mb-2"></i>
                <p>No recent analyses</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    history.slice(0, 5).forEach((item, index) => {
        html += `
            <div class="history-item border-bottom pb-2 mb-2">
                <div class="d-flex justify-content-between">
                    <strong class="text-truncate" style="max-width: 70%;">
                        ${escapeHtml(item.text)}
                    </strong>
                    <small class="text-muted">${index === 0 ? 'Just now' : `${index + 1}h ago`}</small>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-1">
                    <span class="badge bg-info">${item.type}</span>
                    <button class="btn btn-sm btn-link p-0" onclick="loadHistoryItem(${index})">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
        `;
    });
    
    historyList.innerHTML = html;
}

/**
 * Add analysis to history
 */
function addToHistory(text, type, result) {
    const history = JSON.parse(localStorage.getItem('textAnalysisHistory') || '[]');
    
    history.unshift({
        text: text + (text.length > 100 ? '...' : ''),
        type: type,
        result: result,
        timestamp: new Date().toISOString()
    });
    
    // Keep only last 10 items
    if (history.length > 10) {
        history.pop();
    }
    
    localStorage.setItem('textAnalysisHistory', JSON.stringify(history));
    loadHistory();
}

/**
 * Load history item
 */
function loadHistoryItem(index) {
    const history = JSON.parse(localStorage.getItem('textAnalysisHistory') || '[]');
    if (history[index]) {
        const item = history[index];
        document.getElementById('text_input').value = item.text;
        document.getElementById('text_input').dispatchEvent(new Event('input'));
        showToast('Text loaded from history', 'info');
    }
}

/**
 * Show loading state
 */
function showLoading(show) {
    const analyzeBtn = document.querySelector('button[type="submit"]');
    if (!analyzeBtn) return;
    
    if (show) {
        analyzeBtn.innerHTML = `
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Analyzing...
        `;
        analyzeBtn.disabled = true;
    } else {
        analyzeBtn.innerHTML = `
            <i class="fas fa-magic"></i> Analyze Text
        `;
        analyzeBtn.disabled = false;
    }
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    // Remove existing toasts
    document.querySelectorAll('.toast-container').forEach(el => el.remove());
    
    const icons = {
        'success': 'check-circle',
        'error': 'exclamation-circle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    };
    
    const toast = document.createElement('div');
    toast.className = `toast-container position-fixed top-0 end-0 p-3`;
    toast.style.zIndex = '9999';
    
    toast.innerHTML = `
        <div class="toast align-items-center text-white bg-${type} border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${icons[type] || 'info-circle'} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

/**
 * Clear results
 */
function clearResults() {
    const resultsContainer = document.querySelector('.result-container');
    if (resultsContainer) {
        resultsContainer.remove();
    }
}

/**
 * Analyze again
 */
function analyzeAgain() {
    const textArea = document.getElementById('text_input');
    if (textArea && textArea.value.trim()) {
        document.getElementById('analysis-form').dispatchEvent(new Event('submit'));
    }
}

/**
 * Export results
 */
function exportResults() {
    const resultsContainer = document.querySelector('.result-container');
    if (!resultsContainer) return;
    
    const data = {
        title: 'Text Analysis Results',
        timestamp: new Date().toISOString(),
        content: resultsContainer.textContent
    };
    
    const dataStr = JSON.stringify(data, null, 2);
    const dataUri = 'data:application/json;charset=utf-8,' + encodeURIComponent(dataStr);
    
    const link = document.createElement('a');
    link.setAttribute('href', dataUri);
    link.setAttribute('download', `analysis-${Date.now()}.json`);
    link.click();
    
    showToast('Results exported successfully!', 'success');
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Make functions available globally
window.loadHistoryItem = loadHistoryItem;
window.analyzeAgain = analyzeAgain;
window.exportResults = exportResults;