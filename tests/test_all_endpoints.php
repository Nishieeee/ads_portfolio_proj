<?php

$baseUrl = "http://localhost/myprojects/ads_portfolio_proj/api";
$passCount = 0;
$failCount = 0;
$testsRun = 0;

function runTest(string $name, callable $testFn) {
    global $passCount, $failCount, $testsRun;
    $testsRun++;
    try {
        $result = $testFn();
        if ($result === true) {
            echo "  [PASS] {$name}\n";
            $passCount++;
        } else {
            echo "  [FAIL] {$name} - {$result}\n";
            $failCount++;
        }
    } catch (Exception $e) {
        echo "  [FAIL] {$name} - Exception: " . $e->getMessage() . "\n";
        $failCount++;
    }
}

function request(string $method, string $path, $payload = null, ?string $token = null): array {
    global $baseUrl;
    $url = $baseUrl . $path;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    $headers = [
        'Accept: application/json',
        'Content-Type: application/json'
    ];

    if ($token) {
        $headers[] = "Authorization: Bearer {$token}";
    }

    if ($payload !== null) {
        $json = is_string($payload) ? $payload : json_encode($payload);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decoded = json_decode($response, true);
    return [
        'status' => $statusCode,
        'body'   => $decoded,
        'raw'    => $response
    ];
}

echo "============================================================\n";
echo "   PORTFOLIO CMS - REST API & EDGE CASE TEST SUITE\n";
echo "============================================================\n\n";

$adminToken = null;

// ==========================================
// 1. ROUTER & ROOT ENDPOINT
// ==========================================
echo "1. Router Base Checks:\n";
runTest("GET /api/ should return 200 with router ready message", function() {
    $res = request('GET', '/');
    return $res['status'] === 200 && ($res['body']['success'] ?? false) === true ? true : "Got status {$res['status']}";
});

// ==========================================
// 2. AUTHENTICATION & EDGE CASES
// ==========================================
echo "\n2. Authentication Endpoints & Edge Cases:\n";

runTest("POST /api/auth/login with empty body -> 400 Bad Request", function() {
    $res = request('POST', '/auth/login', []);
    return $res['status'] === 400 ? true : "Got status {$res['status']}";
});

runTest("POST /api/auth/login with invalid password -> 401 Unauthorized", function() {
    $res = request('POST', '/auth/login', ['username' => 'admin', 'password' => 'wrongpassword']);
    return $res['status'] === 401 ? true : "Got status {$res['status']}";
});

runTest("POST /api/auth/login with valid credentials -> 200 & returns Bearer token", function() use (&$adminToken) {
    $res = request('POST', '/auth/login', ['username' => 'admin', 'password' => 'adminpassword123']);
    if ($res['status'] === 200 && !empty($res['body']['data']['token'])) {
        $adminToken = $res['body']['data']['token'];
        return true;
    }
    return "Failed to get token, status: {$res['status']}, body: {$res['raw']}";
});

runTest("GET /api/auth/me without token -> 401 Unauthorized", function() {
    $res = request('GET', '/auth/me');
    return $res['status'] === 401 ? true : "Got status {$res['status']}";
});

runTest("GET /api/auth/me with forged token -> 401 Unauthorized", function() {
    $res = request('GET', '/auth/me', null, 'fake-forged-token-123456');
    return $res['status'] === 401 ? true : "Got status {$res['status']}";
});

runTest("GET /api/auth/me with valid Bearer token -> 200 & returns admin username", function() use (&$adminToken) {
    $res = request('GET', '/auth/me', null, $adminToken);
    return ($res['status'] === 200 && ($res['body']['data']['username'] ?? '') === 'admin') ? true : "Status: {$res['status']}";
});

// ==========================================
// 3. SETTINGS & EDGE CASES
// ==========================================
echo "\n3. Site Settings Endpoints & Edge Cases:\n";

runTest("GET /api/settings -> 200 returns theme and title", function() {
    $res = request('GET', '/settings');
    return ($res['status'] === 200 && isset($res['body']['data']['theme'])) ? true : "Status {$res['status']}";
});

runTest("PUT /api/settings without auth -> 401 Unauthorized", function() {
    $res = request('PUT', '/settings', ['theme' => 'midnight']);
    return $res['status'] === 401 ? true : "Got status {$res['status']}";
});

runTest("PUT /api/settings with invalid theme -> 400 Bad Request", function() use (&$adminToken) {
    $res = request('PUT', '/settings', ['theme' => 'neon-pink-invalid'], $adminToken);
    return $res['status'] === 400 ? true : "Got status {$res['status']}";
});

runTest("PUT /api/settings with valid theme 'midnight' -> 200 OK", function() use (&$adminToken) {
    $res = request('PUT', '/settings', ['theme' => 'midnight'], $adminToken);
    return ($res['status'] === 200 && ($res['body']['data']['theme'] ?? '') === 'midnight') ? true : "Status {$res['status']}";
});

// Restore theme to monochrome
request('PUT', '/settings', ['theme' => 'monochrome'], $adminToken);

// ==========================================
// 4. PAGE SECTIONS & EDGE CASES
// ==========================================
echo "\n4. Page Sections Endpoints & Edge Cases:\n";

runTest("GET /api/sections -> 200 returns 6 sections", function() {
    $res = request('GET', '/sections');
    return ($res['status'] === 200 && count($res['body']['data'] ?? []) >= 6) ? true : "Count: " . count($res['body']['data'] ?? []);
});

runTest("GET /api/sections/999 (nonexistent ID) -> 404 Not Found", function() {
    $res = request('GET', '/sections/999');
    return $res['status'] === 404 ? true : "Got status {$res['status']}";
});

runTest("POST /api/sections/reorder without auth -> 401 Unauthorized", function() {
    $res = request('POST', '/sections/reorder', ['sections' => [1, 2, 3]]);
    return $res['status'] === 401 ? true : "Got status {$res['status']}";
});

runTest("POST /api/sections/reorder with auth -> 200 OK", function() use (&$adminToken) {
    $res = request('POST', '/sections/reorder', ['sections' => [1, 2, 3, 4, 5, 6]], $adminToken);
    return $res['status'] === 200 ? true : "Got status {$res['status']}";
});

runTest("PATCH /api/sections/1/toggle-visibility with auth -> 200 OK", function() use (&$adminToken) {
    $res = request('PATCH', '/sections/1/toggle-visibility', null, $adminToken);
    // Toggle back
    request('PATCH', '/sections/1/toggle-visibility', ['is_visible' => 1], $adminToken);
    return $res['status'] === 200 ? true : "Got status {$res['status']}";
});

// ==========================================
// 5. BASIC INFO & XSS PROTECTION TEST
// ==========================================
echo "\n5. Basic Info Endpoints & XSS Defense:\n";

runTest("GET /api/basic-info -> 200 returns profile with full_name and age", function() {
    $res = request('GET', '/basic-info');
    return ($res['status'] === 200 && !empty($res['body']['data']['first_name']) && isset($res['body']['data']['age'])) ? true : "Status {$res['status']}";
});

runTest("PUT /api/basic-info without auth -> 401 Unauthorized", function() {
    $res = request('PUT', '/basic-info', ['first_name' => 'Hacker']);
    return $res['status'] === 401 ? true : "Got status {$res['status']}";
});

runTest("PUT /api/basic-info with invalid birth_date format -> 400 Bad Request", function() use (&$adminToken) {
    $res = request('PUT', '/basic-info', [
        'first_name' => 'Jhon Clein',
        'last_name'  => 'Pagarogan',
        'role_title' => 'Developer',
        'tagline'    => 'Tagline text',
        'birth_date' => '01-01-2004-invalid'
    ], $adminToken);
    return $res['status'] === 400 ? true : "Got status {$res['status']}";
});

runTest("PUT /api/basic-info with XSS script injection -> 200 and script is neutralized", function() use (&$adminToken) {
    $res = request('PUT', '/basic-info', [
        'first_name' => 'Jhon Clein',
        'last_name'  => 'Pagarogan',
        'role_title' => 'Full-Stack Developer <script>alert("xss")</script>',
        'tagline'    => 'Crafting high-performance web applications.'
    ], $adminToken);

    if ($res['status'] !== 200) {
        return "Status {$res['status']}";
    }

    $title = $res['body']['data']['role_title'] ?? '';
    // Must contain &lt;script&gt; instead of unescaped raw <script>
    if (strpos($title, '<script>') === false && strpos($title, '&lt;script&gt;') !== false) {
        // Restore clean title
        request('PUT', '/basic-info', [
            'first_name' => 'Jhon Clein',
            'last_name'  => 'Pagarogan',
            'role_title' => 'Full-Stack Web Developer',
            'tagline'    => 'Crafting high-performance web applications, resilient backend architectures, and thoughtful minimalist user experiences.'
        ], $adminToken);
        return true;
    }
    return "XSS was not neutralized! Received: {$title}";
});

// ==========================================
// 6. CONTACT CHANNELS & VALIDATION
// ==========================================
echo "\n6. Contact Channels Endpoints & Validation:\n";

runTest("GET /api/contact-info -> 200 returns list", function() {
    $res = request('GET', '/contact-info');
    return ($res['status'] === 200 && is_array($res['body']['data'])) ? true : "Status {$res['status']}";
});

runTest("POST /api/contact-info with invalid contact_type -> 400 Bad Request", function() use (&$adminToken) {
    $res = request('POST', '/contact-info', [
        'contact_name' => 'Discord',
        'contact_type' => 'carrier_pigeon', // Invalid ENUM
        'contact_info' => 'Clein#1234'
    ], $adminToken);
    return $res['status'] === 400 ? true : "Got status {$res['status']}";
});

runTest("POST /api/contact-info with invalid email format -> 400 Bad Request", function() use (&$adminToken) {
    $res = request('POST', '/contact-info', [
        'contact_name' => 'Test Email',
        'contact_type' => 'email',
        'contact_info' => 'not-an-email-address'
    ], $adminToken);
    return $res['status'] === 400 ? true : "Got status {$res['status']}";
});

$testContactId = null;
runTest("POST /api/contact-info with valid channel -> 201 Created", function() use (&$adminToken, &$testContactId) {
    $res = request('POST', '/contact-info', [
        'contact_name' => 'Temp Discord',
        'contact_type' => 'url',
        'contact_info' => 'https://discord.com/users/clein'
    ], $adminToken);
    if ($res['status'] === 201 && !empty($res['body']['data']['id'])) {
        $testContactId = (int) $res['body']['data']['id'];
        return true;
    }
    return "Status {$res['status']}";
});

runTest("DELETE /api/contact-info/{id} without auth -> 401 Unauthorized", function() use (&$testContactId) {
    $res = request('DELETE', "/contact-info/{$testContactId}");
    return $res['status'] === 401 ? true : "Got status {$res['status']}";
});

runTest("DELETE /api/contact-info/{id} with auth -> 200 OK", function() use (&$adminToken, &$testContactId) {
    $res = request('DELETE', "/contact-info/{$testContactId}", null, $adminToken);
    return $res['status'] === 200 ? true : "Got status {$res['status']}";
});

// ==========================================
// 7. WORK EXPERIENCE
// ==========================================
echo "\n7. Experience Endpoints & Validation:\n";

runTest("GET /api/experience -> 200 returns chronological entries", function() {
    $res = request('GET', '/experience');
    return ($res['status'] === 200 && is_array($res['body']['data'])) ? true : "Status {$res['status']}";
});

runTest("POST /api/experience with missing required fields -> 400 Bad Request", function() use (&$adminToken) {
    $res = request('POST', '/experience', ['job_title' => 'Dev'], $adminToken);
    return $res['status'] === 400 ? true : "Got status {$res['status']}";
});

$testExpId = null;
runTest("POST /api/experience with valid payload -> 201 Created", function() use (&$adminToken, &$testExpId) {
    $res = request('POST', '/experience', [
        'job_title'     => 'Temporary QA Automation Engineer',
        'company_name'  => 'Test Labs',
        'location'      => 'Remote',
        'description_1' => 'Built test automation suite.',
        'description_2' => 'Maintained 99.8% test coverage.',
        'description_3' => 'Integrated with GitHub Actions.',
        'date_start'    => '2026-01-01',
        'date_end'      => '2026-06-01',
        'date_display'  => 'Jan 2026 – Jun 2026'
    ], $adminToken);

    if ($res['status'] === 201 && !empty($res['body']['data']['id'])) {
        $testExpId = (int) $res['body']['data']['id'];
        return true;
    }
    return "Status {$res['status']}";
});

runTest("DELETE /api/experience/{id} with auth -> 200 OK", function() use (&$adminToken, &$testExpId) {
    $res = request('DELETE', "/experience/{$testExpId}", null, $adminToken);
    return $res['status'] === 200 ? true : "Got status {$res['status']}";
});

// ==========================================
// 8. SKILLS MATRIX
// ==========================================
echo "\n8. Skills Matrix Endpoints & Category Filtering:\n";

runTest("GET /api/skills -> 200 returns groups with skills_array", function() {
    $res = request('GET', '/skills');
    return ($res['status'] === 200 && isset($res['body']['data'][0]['skills_array'])) ? true : "Status {$res['status']}";
});

runTest("GET /api/skills?category=technical -> 200 returns filtered technical groups", function() {
    $res = request('GET', '/skills?category=technical');
    if ($res['status'] !== 200) return "Status {$res['status']}";
    foreach ($res['body']['data'] as $group) {
        if ($group['skill_category'] !== 'technical') return "Non-technical group returned: {$group['skill_category']}";
    }
    return true;
});

runTest("POST /api/skills with invalid category -> 400 Bad Request", function() use (&$adminToken) {
    $res = request('POST', '/skills', [
        'skill_category' => 'quantum_computing',
        'category_label' => 'Quantum Skills'
    ], $adminToken);
    return $res['status'] === 400 ? true : "Got status {$res['status']}";
});

$testSkillId = null;
runTest("POST /api/skills with valid group -> 201 Created", function() use (&$adminToken, &$testSkillId) {
    $res = request('POST', '/skills', [
        'skill_category' => 'technical',
        'category_label' => 'Cloud & DevOps Temp',
        'skills_list'    => 'AWS, Terraform, Kubernetes'
    ], $adminToken);

    if ($res['status'] === 201 && !empty($res['body']['data']['id'])) {
        $testSkillId = (int) $res['body']['data']['id'];
        return true;
    }
    return "Status {$res['status']}";
});

runTest("DELETE /api/skills/{id} with auth -> 200 OK", function() use (&$adminToken, &$testSkillId) {
    $res = request('DELETE', "/skills/{$testSkillId}", null, $adminToken);
    return $res['status'] === 200 ? true : "Got status {$res['status']}";
});

// ==========================================
// 9. EDUCATION
// ==========================================
echo "\n9. Education Endpoints:\n";

runTest("GET /api/education -> 200 returns academic records", function() {
    $res = request('GET', '/education');
    return ($res['status'] === 200 && is_array($res['body']['data'])) ? true : "Status {$res['status']}";
});

$testEduId = null;
runTest("POST /api/education with valid data -> 201 Created", function() use (&$adminToken, &$testEduId) {
    $res = request('POST', '/education', [
        'school_name'  => 'Test Academy of Technology',
        'course'       => 'Certificate in Cloud Engineering',
        'date_start'   => '2025-01-01',
        'date_end'     => '2025-06-01',
        'date_display' => '2025',
        'location'     => 'Online',
        'focus_areas'  => 'Cloud Architecture, Microservices'
    ], $adminToken);

    if ($res['status'] === 201 && !empty($res['body']['data']['id'])) {
        $testEduId = (int) $res['body']['data']['id'];
        return true;
    }
    return "Status {$res['status']}";
});

runTest("DELETE /api/education/{id} with auth -> 200 OK", function() use (&$adminToken, &$testEduId) {
    $res = request('DELETE', "/education/{$testEduId}", null, $adminToken);
    return $res['status'] === 200 ? true : "Got status {$res['status']}";
});

// ==========================================
// 10. PROJECTS & URL VALIDATION
// ==========================================
echo "\n10. Projects Endpoints & URL Validation:\n";

runTest("GET /api/projects -> 200 returns projects with technologies_array", function() {
    $res = request('GET', '/projects');
    return ($res['status'] === 200 && isset($res['body']['data'][0]['technologies_array'])) ? true : "Status {$res['status']}";
});

runTest("GET /api/projects?featured=1 -> 200 returns featured projects only", function() {
    $res = request('GET', '/projects?featured=1');
    if ($res['status'] !== 200) return "Status {$res['status']}";
    foreach ($res['body']['data'] as $p) {
        if (empty($p['is_featured'])) return "Non-featured project returned";
    }
    return true;
});

runTest("POST /api/projects with invalid URL format -> 400 Bad Request", function() use (&$adminToken) {
    $res = request('POST', '/projects', [
        'project_name' => 'Broken URL App',
        'description'  => 'Testing invalid url input.',
        'technologies' => 'PHP, MySQL',
        'date_start'   => '2026-01-01',
        'url'          => 'htt://broken..url' // Invalid URL
    ], $adminToken);
    return $res['status'] === 400 ? true : "Got status {$res['status']}";
});

$testProjId = null;
runTest("POST /api/projects with valid project -> 201 Created", function() use (&$adminToken, &$testProjId) {
    $res = request('POST', '/projects', [
        'project_name' => 'Temp Cloud Tracker',
        'subtitle'     => 'Real-time telemetry monitor',
        'description'  => 'Engineered real-time telemetry monitor using WebSockets and Redis.',
        'technologies' => 'Node.js, Redis, React, WebSocket',
        'url'          => 'https://demo.example.com',
        'github_repo'  => 'https://github.com/example/tracker',
        'date_start'   => '2026-02-01',
        'is_featured'  => 1,
        'badge'        => 'Telemetry'
    ], $adminToken);

    if ($res['status'] === 201 && !empty($res['body']['data']['id'])) {
        $testProjId = (int) $res['body']['data']['id'];
        return true;
    }
    return "Status {$res['status']}";
});

runTest("DELETE /api/projects/{id} with auth -> 200 OK", function() use (&$adminToken, &$testProjId) {
    $res = request('DELETE', "/projects/{$testProjId}", null, $adminToken);
    return $res['status'] === 200 ? true : "Got status {$res['status']}";
});

// ==========================================
// 11. CERTIFICATES
// ==========================================
echo "\n11. Certificates Endpoints:\n";

runTest("GET /api/certificates -> 200 returns certificates list", function() {
    $res = request('GET', '/certificates');
    return ($res['status'] === 200 && is_array($res['body']['data'])) ? true : "Status {$res['status']}";
});

$testCertId = null;
runTest("POST /api/certificates with valid certificate -> 201 Created", function() use (&$adminToken, &$testCertId) {
    $res = request('POST', '/certificates', [
        'title'        => 'AWS Certified Cloud Practitioner Temp',
        'issuer'       => 'Amazon Web Services',
        'date_display' => '2026',
        'description'  => 'Validation of overall understanding of AWS Cloud platform.',
        'cert_id'      => 'AWS-12345-TEMP',
        'cert_url'     => 'https://aws.amazon.com/verification'
    ], $adminToken);

    if ($res['status'] === 201 && !empty($res['body']['data']['id'])) {
        $testCertId = (int) $res['body']['data']['id'];
        return true;
    }
    return "Status {$res['status']}";
});

runTest("DELETE /api/certificates/{id} with auth -> 200 OK", function() use (&$adminToken, &$testCertId) {
    $res = request('DELETE', "/certificates/{$testCertId}", null, $adminToken);
    return $res['status'] === 200 ? true : "Got status {$res['status']}";
});

// ==========================================
// 12. CONTACT INQUIRIES & INBOX
// ==========================================
echo "\n12. Inquiries Form & Admin Inbox:\n";

runTest("POST /api/inquiries (Public) with invalid email -> 400 Bad Request", function() {
    $res = request('POST', '/inquiries', [
        'sender_name'  => 'Recruiter',
        'sender_email' => 'invalid-email',
        'subject'      => 'Job Offer',
        'message'      => 'We would love to discuss a developer position with you.'
    ]);
    return $res['status'] === 400 ? true : "Got status {$res['status']}";
});

runTest("POST /api/inquiries (Public) with message < 10 characters -> 400 Bad Request", function() {
    $res = request('POST', '/inquiries', [
        'sender_name'  => 'Recruiter',
        'sender_email' => 'recruiter@tech.com',
        'subject'      => 'Hi',
        'message'      => 'Short' // Only 5 chars
    ]);
    return $res['status'] === 400 ? true : "Got status {$res['status']}";
});

$testInquiryId = null;
runTest("POST /api/inquiries (Public) with valid message -> 201 Created", function() use (&$testInquiryId) {
    $res = request('POST', '/inquiries', [
        'sender_name'  => 'Alex Rivera',
        'sender_email' => 'alex.rivera@innovate.co',
        'subject'      => 'Contract Architecture Consultation',
        'message'      => 'Hi Clein, I saw your high concurrency projects and would like to consult on a distributed database architecture.'
    ]);
    if ($res['status'] === 201 && !empty($res['body']['data']['id'])) {
        $testInquiryId = (int) $res['body']['data']['id'];
        return true;
    }
    return "Status {$res['status']}";
});

runTest("GET /api/inquiries without auth -> 401 Unauthorized", function() {
    $res = request('GET', '/inquiries');
    return $res['status'] === 401 ? true : "Got status {$res['status']}";
});

runTest("GET /api/inquiries with auth -> 200 returns inbox & unread_count", function() use (&$adminToken) {
    $res = request('GET', '/inquiries', null, $adminToken);
    return ($res['status'] === 200 && isset($res['body']['data']['unread_count'])) ? true : "Status {$res['status']}";
});

runTest("PATCH /api/inquiries/{id}/read with auth -> 200 marks as read", function() use (&$adminToken, &$testInquiryId) {
    $res = request('PATCH', "/inquiries/{$testInquiryId}/read", ['is_read' => true], $adminToken);
    return ($res['status'] === 200 && !empty($res['body']['data']['is_read'])) ? true : "Status {$res['status']}";
});

runTest("DELETE /api/inquiries/{id} with auth -> 200 OK", function() use (&$adminToken, &$testInquiryId) {
    $res = request('DELETE', "/inquiries/{$testInquiryId}", null, $adminToken);
    return $res['status'] === 200 ? true : "Got status {$res['status']}";
});

// ==========================================
// 13. LOGOUT & TOKEN REVOCATION
// ==========================================
echo "\n13. Token Revocation & Logout:\n";

runTest("POST /api/auth/logout with Bearer token -> 200 OK", function() use (&$adminToken) {
    $res = request('POST', '/auth/logout', null, $adminToken);
    return $res['status'] === 200 ? true : "Got status {$res['status']}";
});

runTest("GET /api/auth/me after logout -> 401 Unauthorized (token is revoked)", function() use (&$adminToken) {
    $res = request('GET', '/auth/me', null, $adminToken);
    return $res['status'] === 401 ? true : "Got status {$res['status']}";
});

// ==========================================
// SUMMARY
// ==========================================
echo "\n============================================================\n";
echo "   TEST RESULTS SUMMARY\n";
echo "============================================================\n";
echo "  Total Tests Run : {$testsRun}\n";
echo "  Passed          : {$passCount}\n";
echo "  Failed          : {$failCount}\n";
echo "  Success Rate    : " . round(($passCount / $testsRun) * 100, 1) . "%\n";
echo "============================================================\n";

if ($failCount > 0) {
    exit(1);
} else {
    exit(0);
}
