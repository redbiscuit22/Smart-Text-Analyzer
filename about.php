<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - TextSense Analyzer </title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script src="components/navbar.js"></script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <custom-navbar></custom-navbar>
    
    <main class="container mx-auto px-4 py-12">
        <div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8">
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-800 dark:text-white mb-4">
                    About TextSense Analyzer 
                </h1>
                <p class="text-xl text-gray-600 dark:text-gray-300">
                    Advanced AI-powered text analysis at your fingertips
                </p>
            </div>

            <div class="prose dark:prose-invert max-w-none">
                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">Our Technology</h2>
                    <p class="text-gray-600 dark:text-gray-300 mb-4">
                        TextSense Analyzer  leverages cutting-edge machine learning models to provide comprehensive
                        text analysis. Our system uses natural language processing (NLP) to understand and interpret
                        your text with remarkable accuracy.
                    </p>
                    <div class="grid md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-indigo-50 dark:bg-indigo-900/20 p-4 rounded-lg">
                            <i data-feather="cpu" class="w-8 h-8 text-indigo-600 dark:text-indigo-400 mb-2"></i>
                            <h3 class="font-bold">AI Models</h3>
                            <p class="text-sm">Powered by state-of-the-art transformer models</p>
                        </div>
                        <div class="bg-indigo-50 dark:bg-indigo-900/20 p-4 rounded-lg">
                            <i data-feather="shield" class="w-8 h-8 text-indigo-600 dark:text-indigo-400 mb-2"></i>
                            <h3 class="font-bold">Privacy Focused</h3>
                            <p class="text-sm">Your data is processed securely and never stored</p>
                        </div>
                        <div class="bg-indigo-50 dark:bg-indigo-900/20 p-4 rounded-lg">
                            <i data-feather="zap" class="w-8 h-8 text-indigo-600 dark:text-indigo-400 mb-2"></i>
                            <h3 class="font-bold">Lightning Fast</h3>
                            <p class="text-sm">Get results in seconds with our optimized pipeline</p>
                        </div>
                    </div>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">Features</h2>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <i data-feather="check-circle" class="text-green-500 mr-2 mt-1"></i>
                            <span class="text-gray-600 dark:text-gray-300">Sentiment analysis to detect positive, negative or neutral tone</span>
                        </li>
                        <li class="flex items-start">
                            <i data-feather="check-circle" class="text-green-500 mr-2 mt-1"></i>
                            <span class="text-gray-600 dark:text-gray-300">Keyword extraction to identify important concepts</span>
                        </li>
                        <li class="flex items-start">
                            <i data-feather="check-circle" class="text-green-500 mr-2 mt-1"></i>
                            <span class="text-gray-600 dark:text-gray-300">Emotion detection to understand underlying feelings</span>
                        </li>
                        <li class="flex items-start">
                            <i data-feather="check-circle" class="text-green-500 mr-2 mt-1"></i>
                            <span class="text-gray-600 dark:text-gray-300">Text summarization for quick understanding</span>
                        </li>
                    </ul>
                </section>

                <div class="text-center mt-12">
                    <a href="/" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition-colors duration-200">
                        <i data-feather="activity" class="mr-2"></i> Start Analyzing Now
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script src="script.js"></script>
    <script>
        feather.replace();
    </script>
</body>
</html>