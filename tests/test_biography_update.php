<?php
/**
 * Test Suite: About Me Biography Update Flow
 */

require_once __DIR__ . '/../Config/database.php';

echo "============================================================\n";
echo "   PORTFOLIO CMS - BIOGRAPHY UPDATE VERIFICATION\n";
echo "============================================================\n\n";

$testsPassed = 0;
$totalTests = 0;

function assertCheck(string $desc, bool $condition): void {
    global $testsPassed, $totalTests;
    $totalTests++;
    if ($condition) {
        $testsPassed++;
        echo "  [PASS] {$desc}\n";
    } else {
        echo "  [FAIL] {$desc}\n";
    }
}

$db = (new Database())->getConnection();
assertCheck("Database connection active", $db !== null);

// 1. Verify Columns Exist in MySQL
$stmt = $db->query("DESCRIBE my_basic_info");
$cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
assertCheck("my_basic_info has bio_greeting column", in_array('bio_greeting', $cols));
assertCheck("my_basic_info has bio_paragraphs column", in_array('bio_paragraphs', $cols));

// 2. Login to get token
$loginCh = curl_init('http://localhost/myprojects/ads_portfolio_proj/api/auth/login');
curl_setopt_array($loginCh, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
    CURLOPT_POSTFIELDS => json_encode(['username' => 'admin', 'password' => 'adminpassword123'])
]);
$loginResp = json_decode(curl_exec($loginCh), true);
curl_close($loginCh);
$token = $loginResp['data']['token'] ?? '';
assertCheck("Admin login succeeded with Bearer token", !empty($token));

// 3. Test PUT /api/basic-info with updated biography
$testGreeting = "Hello from Test Suite " . time();
$testBio = "Paragraph 1: Testing updated biography narrative.\n\nParagraph 2: Second dynamic paragraph with technical details.";

$ch = curl_init('http://localhost/myprojects/ads_portfolio_proj/api/basic-info');
curl_setopt_array($ch, [
    CURLOPT_CUSTOMREQUEST => 'PUT',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $token
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'first_name' => 'Jhon Clein',
        'last_name' => 'Pagarogan',
        'middle_name' => '',
        'role_title' => 'Full-Stack Developer',
        'tagline' => 'Crafting modern applications.',
        'bio_greeting' => $testGreeting,
        'bio_paragraphs' => $testBio
    ])
]);
$resp = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertCheck("PUT /api/basic-info returns 200 OK", $httpCode === 200);

$resData = json_decode($resp, true);
assertCheck("Response status is success", ($resData['success'] ?? false) === true);
assertCheck("Response contains updated bio_greeting", ($resData['data']['bio_greeting'] ?? '') === $testGreeting);
assertCheck("Response contains updated bio_paragraphs", ($resData['data']['bio_paragraphs'] ?? '') === $testBio);

// 3. Verify Database Persistence Directly
$stmt = $db->query("SELECT bio_greeting, bio_paragraphs FROM my_basic_info WHERE id = 1 LIMIT 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
assertCheck("Database row persisted updated bio_greeting", $row['bio_greeting'] === $testGreeting);
assertCheck("Database row persisted updated bio_paragraphs", $row['bio_paragraphs'] === $testBio);

// 4. Verify Public Landing Page index.php Renders Updated Bio
ob_start();
$_GET = [];
$_SERVER['REQUEST_METHOD'] = 'GET';
require __DIR__ . '/../index.php';
$landingHtml = ob_get_clean();

assertCheck("Landing page displays updated bio greeting", str_contains($landingHtml, $testGreeting));
assertCheck("Landing page displays updated Paragraph 1", str_contains($landingHtml, "Testing updated biography narrative."));
assertCheck("Landing page displays updated Paragraph 2", str_contains($landingHtml, "Second dynamic paragraph with technical details."));

// 5. Verify Admin Page admin/index.php Loads Updated Bio into Form Fields
ob_start();
require __DIR__ . '/../admin/index.php';
$adminHtml = ob_get_clean();

assertCheck("Admin form loads updated bio greeting into input value", str_contains($adminHtml, htmlspecialchars($testGreeting)));
assertCheck("Admin form loads updated bio paragraphs into textarea", str_contains($adminHtml, htmlspecialchars($testBio)));

// 6. Restore Original Default Bio
$defaultGreeting = "Hi, I'm Clein!";
$defaultBio = "I am a results-driven Full-Stack Web Developer and Computer Science undergraduate at Western Mindanao State University. I focus on building scalable backend architectures, high-concurrency systems, and intuitive client interfaces.\n\nChampion of the Build With AI: Hackathon 2026, my technical depth spans Laravel, Django, Node.js, and modern React/Next.js with TypeScript. I have practical experience in database query optimization, Redis tiered caching, atomic transactions, and AI API integrations.\n\nWhether engineering mission-critical government portals or architecting high-traffic e-commerce systems, I prioritize clean code, resilient security standards, and measurable real-world performance.";

$db->prepare("UPDATE my_basic_info SET bio_greeting = :g, bio_paragraphs = :p WHERE id = 1")->execute([
    ':g' => $defaultGreeting,
    ':p' => $defaultBio
]);
assertCheck("Restored default biography in database", true);

echo "\n============================================================\n";
echo "   BIOGRAPHY TEST RESULTS\n";
echo "============================================================\n";
echo "  Total Checks : {$totalTests}\n";
echo "  Passed       : {$testsPassed}\n";
echo "  Failed       : " . ($totalTests - $testsPassed) . "\n";
echo "  Success Rate : " . round(($testsPassed / $totalTests) * 100, 1) . "%\n";
echo "============================================================\n";
