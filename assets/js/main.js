/**
 * Smart Text Analyzer Pro - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize components
    initModelSelector();
    initTextArea();
    initSampleTextLoader();
    initFormSubmission();
    initHistoryLoader();
    
    // Load initial history
    loadHistory();
    
    // Update text statistics in real-time
    updateTextStats();
});

/**
 * Model Selection
 */
function initModelSelector() {
    const modelCards = document.querySelectorAll('.model-card');
    const modelTypeInput = document.getElementById('model_type');
    
    modelCards.forEach(card => {
        card.addEventListener('click', function() {
            // Remove active class from all cards
            modelCards.forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked card
            this.classList.add('active');
            
            // Update hidden input
            const modelType = this.dataset.model;
            modelTypeInput.value = modelType;
            
            // Update UI based on selected model
            updateModelUI(modelType);
            
            // Show model description
            showModelDescription(modelType);
        });
    });
}

/**
 * Update UI based on selected model
 */
function updateModelUI(modelType) {
    const textArea = document.getElementById('text_input');
    const placeholder = textArea.placeholder;
    const basePlaceholder = "Enter your text here... (Minimum 50 characters for best results)";
    
    let newPlaceholder = basePlaceholder;
    
    switch(modelType) {
        case 'sentiment':
            newPlaceholder = "Enter text to analyze sentiment (e.g., product reviews, feedback)...";
            break;
        case 'keywords':
            newPlaceholder = "Enter text to extract keywords (e.g., articles, documents)...";
            break;
        case 'emotion':
            newPlaceholder = "Enter text to detect emotions (e.g., stories, social media posts)...";
            break;
        case 'summarize':
            newPlaceholder = "Enter long text to summarize (minimum 100 words recommended)...";
            break;
        case 'advanced':
            newPlaceholder = "Enter text for comprehensive analysis (all features)...";
            break;
    }
    
    textArea.placeholder = newPlaceholder;
    
    // Update button text
    const analyzeBtn = document.querySelector('button[type="submit"]');
    if (analyzeBtn) {
        analyzeBtn.innerHTML = `<i class="fas fa-magic"></i> Analyze ${modelType.charAt(0).toUpperCase() + modelType.slice(1)}`;
    }
}

/**
 * Show model description
 */
function showModelDescription(modelType) {
    const descriptions = {
        'sentiment': 'Detects positive, negative, or neutral sentiment in your text.',
        'keywords': 'Extracts important keywords and phrases from your text.',
        'emotion': 'Identifies emotions like joy, sadness, anger, fear, and more.',
        'summarize': 'Creates a concise summary of longer texts.',
        'advanced': 'Performs comprehensive analysis including all features.'
    };
    
    // Create or update description element
    let descElement = document.getElementById('model-description');
    if (!descElement) {
        descElement = document.createElement('div');
        descElement.id = 'model-description';
        descElement.className = 'alert alert-info mt-3';
        const modelSelector = document.querySelector('.model-selector');
        modelSelector.appendChild(descElement);
    }
    
    descElement.innerHTML = `
        <i class="fas fa-info-circle"></i>
        <strong>${modelType.charAt(0).toUpperCase() + modelType.slice(1)} Analysis:</strong>
        ${descriptions[modelType] || 'Analyzes text based on selected criteria.'}
    `;
}

/**
 * Initialize text area functionality
 */
function initTextArea() {
    const textArea = document.getElementById('text_input');
    
    if (!textArea) return;
    
    // Auto-resize textarea
    textArea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
        
        // Update statistics
        updateTextStats();
        
        // Show/hide validation message
        validateTextLength(this.value);
    });
    
    // Clear button
    document.getElementById('clear-btn').addEventListener('click', function() {
        textArea.value = '';
        textArea.style.height = 'auto';
        updateTextStats();
        hideValidationMessage();
    });
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
    
    // Update colors based on count
    wordCount.style.color = words < 10 ? '#dc3545' : '#28a745';
    charCount.style.color = chars < 50 ? '#dc3545' : '#28a745';
}

/**
 * Validate text length and show messages
 */
function validateTextLength(text) {
    const minLength = 10;
    const maxLength = 10000;
    const length = text.length;
    
    if (length === 0) {
        hideValidationMessage();
        return true;
    }
    
    if (length < minLength) {
        showValidationMessage(`Text is too short (${length}/${minLength} characters). Please enter at least ${minLength} characters.`, 'warning');
        return false;
    }
    
    if (length > maxLength) {
        showValidationMessage(`Text is too long (${length}/${maxLength} characters). Please reduce to ${maxLength} characters or less.`, 'danger');
        return false;
    }
    
    hideValidationMessage();
    return true;
}

/**
 * Show validation message
 */
function showValidationMessage(message, type = 'warning') {
    let messageElement = document.getElementById('validation-message');
    
    if (!messageElement) {
        messageElement = document.createElement('div');
        messageElement.id = 'validation-message';
        messageElement.className = `alert alert-${type} mt-2`;
        
        const textArea = document.getElementById('text_input');
        textArea.parentNode.insertBefore(messageElement, textArea.nextSibling);
    }
    
    messageElement.className = `alert alert-${type} mt-2`;
    messageElement.innerHTML = `
        <i class="fas fa-exclamation-triangle"></i> ${message}
    `;
    messageElement.style.display = 'block';
}

/**
 * Hide validation message
 */
function hideValidationMessage() {
    const messageElement = document.getElementById('validation-message');
    if (messageElement) {
        messageElement.style.display = 'none';
    }
}

/**
 * Initialize sample text loader
 */
function initSampleTextLoader() {
    const sampleBtn = document.getElementById('sample-btn');
    const textArea = document.getElementById('text_input');
    
    if (!sampleBtn || !textArea) return;
    
    sampleBtn.addEventListener('click', function() {
        const modelType = document.getElementById('model_type').value;
        const samples = getSampleTexts(modelType);
        
        if (samples && samples.length > 0) {
            const randomSample = samples[Math.floor(Math.random() * samples.length)];
            textArea.value = randomSample.text;
            
            // Trigger input event to update stats
            textArea.dispatchEvent(new Event('input'));
            
            // Show success message
            showToast('Sample text loaded!', 'success');
        }
    });
}

/**
 * Get sample texts based on model type
 */
function getSampleTexts(modelType) {
    const samples = {
        'sentiment': [
            {
                text: "This product is absolutely amazing! The quality is exceptional and it works perfectly. I love how easy it is to use and the customer service was wonderful when I had questions. Highly recommended to everyone looking for a reliable solution. Five stars!",
                title: "Positive Product Review"
            },
            {
                text: "Very disappointed with my purchase. The product arrived damaged and doesn't work as advertised. Customer service was unhelpful and refused to provide a refund. I would not recommend this to anyone. Save your money and look elsewhere.",
                title: "Customer Complaint"
            }
        ],
        'keywords': [
            {
                text: "Machine learning algorithms, particularly deep neural networks, have revolutionized natural language processing. Transformers and attention mechanisms enable models like BERT and GPT to understand context and generate human-like text. These advancements power modern applications from chatbots to automated content generation.",
                title: "Technical Article Excerpt"
            }
        ],
        'emotion': [
            {
                text: "I was so happy and excited when I received the news! But then suddenly, fear crept in as I realized the responsibility. Now I feel anxious yet hopeful about the future. It's a rollercoaster of emotions that I never expected to experience.",
                title: "Emotional Story"
            }
        ],
        'summarize': [
            {
                text: "Artificial intelligence is transforming industries worldwide. In healthcare, AI helps diagnose diseases and develop treatments. In finance, it detects fraud and manages investments. Education uses AI for personalized learning. While challenges exist regarding ethics and job displacement, the potential benefits are enormous. Governments and organizations must work together to ensure responsible AI development.",
                title: "AI Impact Summary"
            }
        ],
        'advanced': [
            {
                text: "The rapid advancement of technology has fundamentally changed how we live and work. Smartphones connect us instantly, social media shapes public opinion, and automation streamlines industries. While these developments offer convenience and efficiency, they also raise important questions about privacy, job security, and mental health. Balancing innovation with ethical considerations remains one of society's greatest challenges in the digital age.",
                title: "Technology Impact Analysis"
            }
        ]
    };
    
    return samples[modelType] || samples['sentiment'];
}

/**
 * Initialize form submission with AJAX
 */
function initFormSubmission() {
    const form = document.getElementById('analysis-form');
    
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate text length
        const textArea = document.getElementById('text_input');
        if (!validateTextLength(textArea.value)) {
            return;
        }
        
        // Show loading state
        showLoading(true);
        
        // Collect form data
        const formData = new FormData(this);
        
        // Send AJAX request
        fetch('index.php?page=analyze', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update results section
                updateResults(data);
                // Add to history
                addToHistory(data);
                // Show success message
                showToast('Analysis completed successfully!', 'success');
            } else {
                // Show error message
                showToast(data.errors?.join(', ') || 'Analysis failed', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred. Please try again.', 'danger');
        })
        .finally(() => {
            // Hide loading state
            showLoading(false);
        });
    });
}

/**
 * Show loading state
 */
function showLoading(show) {
    const analyzeBtn = document.querySelector('button[type="submit"]');
    const originalContent = analyzeBtn.innerHTML;
    
    if (show) {
        analyzeBtn.innerHTML = `
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Analyzing...
        `;
        analyzeBtn.disabled = true;
    } else {
        analyzeBtn.innerHTML = originalContent;
        analyzeBtn.disabled = false;
    }
}

/**
 * Update results section with new data
 */
function updateResults(data) {
    // Create results container if it doesn't exist
    let resultsContainer = document.querySelector('.result-container');
    if (!resultsContainer) {
        resultsContainer = document.createElement('div');
        resultsContainer.className = 'result-container';
        const mainContent = document.querySelector('.main-content');
        mainContent.appendChild(resultsContainer);
    }
    
    // Generate HTML for results
    resultsContainer.innerHTML = generateResultsHTML(data);
    
    // Initialize tabs if advanced analysis
    if (data.result?.metadata?.analysis_type === 'advanced') {
        initTabs();
    }
    
    // Scroll to results
    resultsContainer.scrollIntoView({ behavior: 'smooth' });
}

/**
 * Generate HTML for results display
 */
function generateResultsHTML(data) {
    const result = data.result;
    const modelType = data.model_type;
    
    // This is a simplified version. In production, you'd want a more robust template
    return `
        <div class="result-header">
            <h2 class="result-title">
                <i class="fas fa-chart-bar"></i> Analysis Results
            </h2>
            <span class="result-type badge">
                ${modelType.charAt(0).toUpperCase() + modelType.slice(1)} Analysis
            </span>
        </div>
        
        <div class="result-content">
            <pre>${JSON.stringify(result, null, 2)}</pre>
        </div>
        
        <div class="mt-3">
            <button class="btn btn-sm btn-outline-primary" onclick="exportResults()">
                <i class="fas fa-download"></i> Export Results
            </button>
            <button class="btn btn-sm btn-outline-secondary ms-2" onclick="shareResults()">
                <i class="fas fa-share"></i> Share
            </button>
        </div>
    `;
}

/**
 * Initialize Bootstrap tabs
 */
function initTabs() {
    const tabElements = document.querySelectorAll('[data-bs-toggle="tab"]');
    tabElements.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('data-bs-target'));
            
            // Hide all tab panes
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('show', 'active');
            });
            
            // Deactivate all tabs
            tabElements.forEach(t => {
                t.classList.remove('active');
            });
            
            // Activate current tab and pane
            this.classList.add('active');
            target.classList.add('show', 'active');
        });
    });
}

/**
 * Load and display analysis history
 */
function loadHistory() {
    // This would typically make an AJAX request to get history
    const historyList = document.getElementById('history-list');
    if (!historyList) return;
    
    // For now, show a placeholder
    historyList.innerHTML = `
        <div class="history-item">
            <small class="text-muted">No recent analyses</small>
        </div>
    `;
}

/**
 * Add analysis to history display
 */
function addToHistory(data) {
    const historyList = document.getElementById('history-list');
    if (!historyList) return;
    
    // Remove "no history" message if present
    if (historyList.querySelector('.text-muted')) {
        historyList.innerHTML = '';
    }
    
    // Create history item
    const historyItem = document.createElement('div');
    historyItem.className = 'history-item border-bottom pb-2 mb-2';
    historyItem.innerHTML = `
        <div class="d-flex justify-content-between">
            <strong class="text-truncate" style="max-width: 70%;">
                ${data.text_preview}
            </strong>
            <small class="text-muted">Just now</small>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-1">
            <span class="badge bg-info">${data.model_type}</span>
            <button class="btn btn-sm btn-link p-0" onclick="viewHistoryItem('${data.text_preview}')">
                <i class="fas fa-eye"></i>
            </button>
        </div>
    `;
    
    // Add to beginning of list
    historyList.insertBefore(historyItem, historyList.firstChild);
    
    // Limit to 5 items
    const items = historyList.querySelectorAll('.history-item');
    if (items.length > 5) {
        items[items.length - 1].remove();
    }
}

/**
 * View a history item
 */
function viewHistoryItem(text) {
    const textArea = document.getElementById('text_input');
    textArea.value = text;
    textArea.dispatchEvent(new Event('input'));
    showToast('Text loaded from history', 'info');
}

/**
 * Initialize history loader
 */
function initHistoryLoader() {
    const historyBtn = document.getElementById('history-btn');
    if (historyBtn) {
        historyBtn.addEventListener('click', loadHistory);
    }
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        `;
        document.body.appendChild(toastContainer);
    }
    
    // Create toast
    const toastId = 'toast-' + Date.now();
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-${getToastIcon(type)} me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    // Initialize and show toast
    const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
    bsToast.show();
    
    // Remove toast after it's hidden
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}

/**
 * Get appropriate icon for toast type
 */
function getToastIcon(type) {
    const icons = {
        'success': 'check-circle',
        'danger': 'exclamation-circle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    };
    return icons[type] || 'info-circle';
}

/**
 * Export results as JSON
 */
function exportResults() {
    const resultElement = document.querySelector('.result-content pre');
    if (!resultElement) return;
    
    try {
        const data = JSON.parse(resultElement.textContent);
        const dataStr = JSON.stringify(data, null, 2);
        const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
        
        const exportFileDefaultName = `text-analysis-${Date.now()}.json`;
        
        const linkElement = document.createElement('a');
        linkElement.setAttribute('href', dataUri);
        linkElement.setAttribute('download', exportFileDefaultName);
        linkElement.click();
        
        showToast('Results exported successfully!', 'success');
    } catch (error) {
        showToast('Failed to export results', 'danger');
    }
}

/**
 * Share results
 */
function shareResults() {
    if (navigator.share) {
        const resultElement = document.querySelector('.result-content pre');
        if (resultElement) {
            navigator.share({
                title: 'Text Analysis Results',
                text: resultElement.textContent.substring(0, 100) + '...',
                url: window.location.href
            })
            .then(() => showToast('Results shared successfully!', 'success'))
            .catch(error => console.log('Error sharing:', error));
        }
    } else {
        // Fallback: copy to clipboard
        const resultElement = document.querySelector('.result-content pre');
        if (resultElement) {
            navigator.clipboard.writeText(resultElement.textContent)
                .then(() => showToast('Results copied to clipboard!', 'success'))
                .catch(error => showToast('Failed to copy results', 'danger'));
        }
    }
}

/**
 * System test function
 */
function runSystemTests() {
    showLoading(true);
    
    fetch('index.php?page=analyze', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=test&text_input=Test+text+for+system+testing'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(`System tests completed: ${data.results.successful_tests}/${data.results.total_tests} passed`, 'success');
            console.log('Test results:', data.results);
        } else {
            showToast('System tests failed', 'danger');
        }
    })
    .catch(error => {
        showToast('Error running system tests', 'danger');
        console.error('Test error:', error);
    })
    .finally(() => {
        showLoading(false);
    });
}

/**
 * Keyboard shortcuts
 */
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + Enter to submit
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        const form = document.getElementById('analysis-form');
        if (form) {
            form.dispatchEvent(new Event('submit'));
        }
    }
    
    // Ctrl/Cmd + L to clear
    if ((e.ctrlKey || e.metaKey) && e.key === 'l') {
        document.getElementById('clear-btn').click();
    }
    
    // Ctrl/Cmd + 1-5 for model selection
    if ((e.ctrlKey || e.metaKey) && e.key >= '1' && e.key <= '5') {
        const models = ['sentiment', 'keywords', 'emotion', 'summarize', 'advanced'];
        const index = parseInt(e.key) - 1;
        if (models[index]) {
            const modelCard = document.querySelector(`.model-card[data-model="${models[index]}"]`);
            if (modelCard) {
                modelCard.click();
                showToast(`Selected: ${models[index]} analysis`, 'info');
            }
        }
    }
});