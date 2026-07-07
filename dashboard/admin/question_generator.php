<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/api_keys.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$unit_id = (int)($_POST['unit_id'] ?? 0);
$count = min((int)($_POST['count'] ?? 5), 10);
$topic = trim($_POST['topic'] ?? '');

if (!$unit_id || $count < 1) {
    http_response_code(400);
    echo json_encode(['error' => 'Unit and count are required.']);
    exit;
}

// Get unit info
$stmt = $pdo->prepare("SELECT u.unit_name, c.course_name FROM units u JOIN courses c ON c.course_id = u.course_id WHERE u.unit_id = ?");
$stmt->execute([$unit_id]);
$unit = $stmt->fetch();

if (!$unit) {
    http_response_code(404);
    echo json_encode(['error' => 'Unit not found.']);
    exit;
}

$topicClause = $topic ? " Topic/theme: {$topic}." : '';

$prompt = "Generate {$count} multiple-choice questions for the unit \"{$unit['unit_name']}\" in the course \"{$unit['course_name']}\".{$topicClause}
Return ONLY a valid JSON array. No markdown, no code blocks, no extra text.
Each object must have exactly these keys: question_text, option_a, option_b, option_c, option_d, correct_option.
correct_option must be one of: \"a\", \"b\", \"c\", \"d\".
Make the options plausible and educational. Ensure the correct answer is accurate.";

$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . GEMINI_KEY;

$payload = [
    'contents' => [[
        'parts' => [['text' => $prompt]]
    ]],
    'generationConfig' => [
        'temperature' => 0.7,
        'maxOutputTokens' => 2048,
    ]
];

function callGeminiApi($url, $payload, &$httpCode, &$curlError) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    return $response;
}

$response = null;
$httpCode = 0;
$curlError = '';
$attempts = 3;

for ($attempt = 1; $attempt <= $attempts; $attempt++) {
    $response = callGeminiApi($url, $payload, $httpCode, $curlError);

    if ($curlError || $httpCode !== 429) {
        break;
    }

    if ($attempt < $attempts) {
        usleep($attempt * 700000);
    }
}

if ($curlError) {
    http_response_code(502);
    echo json_encode(['error' => 'Gemini request failed: ' . $curlError]);
    exit;
}

if ($httpCode !== 200) {
    $errorMessage = 'Gemini API request failed (HTTP ' . $httpCode . ').';

    $errorData = json_decode($response, true);
    if (is_array($errorData) && isset($errorData['error']['message'])) {
        $errorMessage = $errorData['error']['message'];
    }

    http_response_code(502);
    echo json_encode([
        'error' => $errorMessage,
        'http_code' => $httpCode,
        'retryable' => $httpCode === 429,
    ]);
    exit;
}

$data = json_decode($response, true);
$text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

// Strip markdown code fences if present
$text = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $text);
$text = trim($text);

$questions = json_decode($text, true);

if (!is_array($questions) || empty($questions)) {
    http_response_code(502);
    echo json_encode(['error' => 'Failed to parse AI response. Response was: ' . htmlspecialchars(substr($text, 0, 500))]);
    exit;
}

// Validate structure
$valid = [];
foreach ($questions as $q) {
    if (isset($q['question_text'], $q['option_a'], $q['option_b'], $q['option_c'], $q['option_d'], $q['correct_option'])
        && in_array($q['correct_option'], ['a', 'b', 'c', 'd'])) {
        $valid[] = $q;
    }
}

if (empty($valid)) {
    http_response_code(502);
    echo json_encode(['error' => 'AI returned invalid question format.']);
    exit;
}

echo json_encode(['success' => true, 'questions' => $valid, 'unit_id' => $unit_id]);
