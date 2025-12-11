<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once 'config/config.php';

use SmartTextAnalyzer\API\HuggingFaceClient;

$client = new HuggingFaceClient();
$testResult = $client->testConnection();

echo "<pre>";
print_r($testResult);
echo "</pre>";

// Test with a simple analysis
if (!empty($_POST['text'])) {
    $text = $_POST['text'];
    $result = $client->analyzeWithHuggingFace($text, 'sentiment');
    echo "<h2>Analysis Result:</h2>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
}
?>

<form method="POST">
    <textarea name="text" rows="5" cols="50">This is a test text for analysis.</textarea><br>
    <input type="submit" value="Test Analysis">
</form>