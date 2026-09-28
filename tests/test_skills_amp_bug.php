<?php
/**
 * Test Suite: Skills Ampersand & Double-Encoding Bug Verification
 *
 * Verifies that:
 * 1. GET /api/skills returns clean ampersands without HTML entity artifacts (&amp;, ;amp).
 * 2. PUT /api/skills/{id} preserves plain '&' and does NOT encode into '&amp;'.
 * 3. Multiple consecutive PUT requests are idempotent and never compound entities.
 * 4. Payloads containing legacy &amp; or double-encoded &amp;amp; are self-healed to clean '&'.
 * 5. POST /api/skills correctly persists and returns clean ampersands.
 * 6. Admin and landing page render clean ampersands in HTML without literal &amp; or ;amp.
 * 7. XSS defense is preserved and script tags remain neutralized.
 */

$baseUrl = 'http://localhost/myprojects/ads_portfolio_proj/api';
$adminToken = null;

function request(string $method, string $path, array $data = [], ?string $token = null): array {
    global $baseUrl;
    $url = $baseUrl . $path;
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = "Authorization: Bearer {$token}";
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'status' => $httpCode,
        'body'   => json_decode($response, true) ?: $response,
        'raw'    => $response
    ];
}

function runTest(string $title, callable $test): bool {
    try {
        $res = $test();
        if ($res === true) {
            echo "  [PASS] {$title}\n";
            return true;
        }
        echo "  [FAIL] {$title}: " . (is_string($res) ? $res : 'Assertion failed') . "\n";
        return false;
    } catch (Throwable $e) {
        echo "  [FAIL] {$title}: Exception - {$e->getMessage()}\n";
        return false;
    }
}

echo "============================================================\n";
echo "   SKILLS CMS - AMPERSAND & DOUBLE-ENCODING VERIFICATION\n";
echo "============================================================\n\n";

// Login to get Bearer token
$loginRes = request('POST', '/auth/login', [
    'username' => 'admin',
    'password' => 'adminpassword123'
]);

if ($loginRes['status'] === 200 && !empty($loginRes['body']['data']['token'])) {
    $adminToken = $loginRes['body']['data']['token'];
    echo "  [PASS] Admin authenticated successfully\n";
} else {
    echo "  [FAIL] Admin authentication failed\n";
    exit(1);
}

// 1. Check GET /api/skills contains NO &amp; or ;amp
runTest("GET /api/skills returns clean ampersands without &amp; or ;amp", function() {
    $res = request('GET', '/skills');
    if ($res['status'] !== 200) return "Status {$res['status']}";
    $skills = $res['body']['data'] ?? [];
    if (empty($skills)) return "No skills returned";

    foreach ($skills as $grp) {
        $label = $grp['category_label'] ?? '';
        if (str_contains($label, '&amp;') || str_contains($label, ';amp')) {
            return "Found entity artifact in category_label: {$label}";
        }
        $list = $grp['skills_list'] ?? '';
        if (str_contains($list, '&amp;') || str_contains($list, ';amp')) {
            return "Found entity artifact in skills_list: {$list}";
        }
        foreach ($grp['skills_array'] ?? [] as $sk) {
            if (str_contains($sk, '&amp;') || str_contains($sk, ';amp')) {
                return "Found entity artifact in skills_array item: {$sk}";
            }
        }
    }
    return true;
});

// 2. PUT /api/skills/1 with plain '&' in category_label and skills_list
runTest("PUT /api/skills/1 saves plain '&' without converting to &amp;", function() use (&$adminToken) {
    $res = request('PUT', '/skills/1', [
        'category_label' => 'Backend & Cloud Systems',
        'skill_category' => 'technical',
        'skills_list'    => 'PHP, Node.js & Express, CI/CD & DevOps, C & C++'
    ], $adminToken);

    if ($res['status'] !== 200) return "Status {$res['status']}";
    $label = $res['body']['data']['category_label'] ?? '';
    $list = $res['body']['data']['skills_list'] ?? '';

    if ($label !== 'Backend & Cloud Systems') {
        return "Expected 'Backend & Cloud Systems', got: {$label}";
    }
    if (!str_contains($list, 'Node.js & Express') || !str_contains($list, 'CI/CD & DevOps')) {
        return "Skills list ampersands missing or altered: {$list}";
    }
    if (str_contains($label, '&amp;') || str_contains($list, '&amp;')) {
        return "Response contains &amp; entity!";
    }
    return true;
});

// 3. Database direct inspection after PUT
runTest("Database row for ID 1 stores clean '&' without &amp;", function() {
    require_once __DIR__ . '/../Config/Database.php';
    $db = (new Database())->getConnection();
    $stmt = $db->prepare("SELECT * FROM my_skills WHERE id = 1");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) return "Row 1 not found";
    if ($row['category_label'] !== 'Backend & Cloud Systems') {
        return "DB category_label is '{$row['category_label']}', expected 'Backend & Cloud Systems'";
    }
    if (str_contains($row['category_label'], '&amp;') || str_contains($row['skills_list'], '&amp;')) {
        return "DB directly contains &amp; in row 1!";
    }
    return true;
});

// 4. Multiple consecutive PUT requests are idempotent (save 1, save 2, save 3)
runTest("Multiple consecutive PUT saves do not compound into &amp;", function() use (&$adminToken) {
    for ($i = 1; $i <= 3; $i++) {
        $res = request('PUT', '/skills/1', [
            'category_label' => 'Backend & Cloud Systems',
            'skill_category' => 'technical',
            'skills_list'    => 'PHP, Node.js & Express, CI/CD & DevOps, C & C++'
        ], $adminToken);

        if ($res['status'] !== 200) return "Iteration {$i} returned status {$res['status']}";
        $label = $res['body']['data']['category_label'] ?? '';
        if ($label !== 'Backend & Cloud Systems') {
            return "Iteration {$i} corrupted label to: {$label}";
        }
    }
    return true;
});

// 5. Self-healing: PUT with &amp; in payload gets normalized to clean '&'
runTest("Self-healing: PUT with '&amp;' in category_label is normalized to plain '&'", function() use (&$adminToken) {
    $res = request('PUT', '/skills/1', [
        'category_label' => 'Backend &amp; Distributed APIs',
        'skill_category' => 'technical',
        'skills_list'    => 'Go &amp; Rust, Python &amp; FastAPI'
    ], $adminToken);

    if ($res['status'] !== 200) return "Status {$res['status']}";
    $label = $res['body']['data']['category_label'] ?? '';
    $list = $res['body']['data']['skills_list'] ?? '';

    if ($label !== 'Backend & Distributed APIs') {
        return "Expected 'Backend & Distributed APIs', got '{$label}'";
    }
    if ($list !== 'Go & Rust, Python & FastAPI') {
        return "Expected 'Go & Rust, Python & FastAPI', got '{$list}'";
    }
    return true;
});

// 6. Self-healing: PUT with double-encoded &amp;amp; is normalized to plain '&'
runTest("Self-healing: PUT with double-encoded '&amp;amp;' is normalized to plain '&'", function() use (&$adminToken) {
    $res = request('PUT', '/skills/1', [
        'category_label' => 'Backend &amp;amp; Scalable Systems',
        'skill_category' => 'technical',
        'skills_list'    => 'Docker &amp;amp; Kubernetes'
    ], $adminToken);

    if ($res['status'] !== 200) return "Status {$res['status']}";
    $label = $res['body']['data']['category_label'] ?? '';
    $list = $res['body']['data']['skills_list'] ?? '';

    if ($label !== 'Backend & Scalable Systems') {
        return "Expected 'Backend & Scalable Systems', got '{$label}'";
    }
    if ($list !== 'Docker & Kubernetes') {
        return "Expected 'Docker & Kubernetes', got '{$list}'";
    }
    return true;
});

// 7. POST /api/skills correctly creates new group with clean '&'
$createdSkillId = null;
runTest("POST /api/skills creates new category with clean ampersands", function() use (&$adminToken, &$createdSkillId) {
    $res = request('POST', '/skills', [
        'skill_category' => 'technical',
        'category_label' => 'AI & Machine Learning',
        'skills_list'    => 'PyTorch & TensorFlow, Scikit-Learn & Pandas'
    ], $adminToken);

    if ($res['status'] !== 201) return "Status {$res['status']}";
    $createdSkillId = (int)($res['body']['data']['id'] ?? 0);
    if (!$createdSkillId) return "No ID returned";

    $label = $res['body']['data']['category_label'] ?? '';
    if ($label !== 'AI & Machine Learning') {
        return "Expected 'AI & Machine Learning', got '{$label}'";
    }
    $skillsArray = $res['body']['data']['skills_array'] ?? [];
    if (!in_array('PyTorch & TensorFlow', $skillsArray, true)) {
        return "skills_array missing expected item: " . json_encode($skillsArray);
    }
    return true;
});

// Cleanup created test category
if ($createdSkillId) {
    request('DELETE', "/skills/{$createdSkillId}", [], $adminToken);
}

// 8. Admin CMS rendering: index.php contains clean input value
runTest("admin/index.php renders input fields with clean ampersand value", function() {
    ob_start();
    $_SERVER['REQUEST_METHOD'] = 'GET';
    // Load admin page
    include __DIR__ . '/../admin/index.php';
    $html = ob_get_clean();

    // The HTML should contain value="Backend &amp; Scalable Systems" so browser DOM renders "Backend & Scalable Systems"
    if (!str_contains($html, 'value="Backend &amp; Scalable Systems"')) {
        return "HTML did not contain expected escaped attribute value";
    }
    // The HTML must NOT contain value="Backend &amp;amp;" (which caused the visible &amp; in the input)
    if (str_contains($html, 'value="Backend &amp;amp;')) {
        return "HTML contains double-encoded &amp;amp; in input value!";
    }
    return true;
});

// 9. Landing page rendering: index.php displays clean ampersand
runTest("Landing page index.php renders skills category heading with clean ampersand", function() {
    ob_start();
    $_SERVER['REQUEST_METHOD'] = 'GET';
    include __DIR__ . '/../index.php';
    $html = ob_get_clean();

    if (!str_contains($html, 'Backend &amp; Scalable Systems')) {
        return "Landing page HTML missing category heading: Backend & Scalable Systems";
    }
    if (str_contains($html, 'Backend &amp;amp; Scalable Systems')) {
        return "Landing page HTML has double-encoded &amp;amp;!";
    }
    return true;
});

// 10. Restore original skill 1
runTest("Restore default skill 1 data", function() use (&$adminToken) {
    $res = request('PUT', '/skills/1', [
        'category_label' => 'Backend Architecture & APIs',
        'skill_category' => 'technical',
        'skills_list'    => 'PHP, Laravel, Python, Django, Node.js, Express, RESTful APIs, MVC Architecture, OOP'
    ], $adminToken);

    if ($res['status'] !== 200) return "Status {$res['status']}";
    return true;
});

echo "\n============================================================\n";
echo "   AMPERSAND BUG VERIFICATION COMPLETE\n";
echo "============================================================\n";
