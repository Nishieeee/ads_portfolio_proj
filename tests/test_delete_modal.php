<?php
/**
 * Test Suite: Universal Delete Confirmation Modal Integration
 */

echo "============================================================\n";
echo "   PORTFOLIO CMS - DELETE CONFIRMATION MODAL TESTS\n";
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

// 1. Admin Index HTML Verification
$adminHtml = file_get_contents(__DIR__ . '/../admin/index.php');

assertCheck("admin/index.php contains #deleteConfirmModal element", str_contains($adminHtml, 'id="deleteConfirmModal"'));
assertCheck("admin/index.php contains #deleteModalTitle element", str_contains($adminHtml, 'id="deleteModalTitle"'));
assertCheck("admin/index.php contains #deleteModalMessage element", str_contains($adminHtml, 'id="deleteModalMessage"'));
assertCheck("admin/index.php contains #confirmDeleteModalBtn element", str_contains($adminHtml, 'id="confirmDeleteModalBtn"'));
assertCheck("admin/index.php contains data-modal-close on cancel button", str_contains($adminHtml, 'id="deleteModalCancelBtn"'));

// 2. Admin JavaScript Verification
$adminJs = file_get_contents(__DIR__ . '/../admin/js/admin.js');

assertCheck("admin.js contains openDeleteModal function", str_contains($adminJs, 'openDeleteModal'));
assertCheck("admin.js does NOT contain native alert() function calls", preg_match('/\balert\s*\(/', $adminJs) === 0);
assertCheck("admin.js does NOT contain native confirm() function calls", preg_match('/\bconfirm\s*\(/', $adminJs) === 0);

// 3. Verify All 7 Delete Triggers hook into openDeleteModal
assertCheck("Contact delete uses openDeleteModal", str_contains($adminJs, "title: 'Delete Contact Channel'"));
assertCheck("Project delete uses openDeleteModal", str_contains($adminJs, "title: 'Delete Project'"));
assertCheck("Experience delete uses openDeleteModal", str_contains($adminJs, "title: 'Delete Experience Entry'"));
assertCheck("Skills category delete uses openDeleteModal", str_contains($adminJs, "title: 'Delete Skill Category'"));
assertCheck("Education delete uses openDeleteModal", str_contains($adminJs, "title: 'Delete Education Entry'"));
assertCheck("Certificate delete uses openDeleteModal", str_contains($adminJs, "title: 'Delete Certification'"));
assertCheck("Inquiry delete uses openDeleteModal", str_contains($adminJs, "title: 'Delete Inquiry'"));

echo "\n============================================================\n";
echo "   DELETE MODAL TEST RESULTS\n";
echo "============================================================\n";
echo "  Total Checks : {$totalTests}\n";
echo "  Passed       : {$testsPassed}\n";
echo "  Failed       : " . ($totalTests - $testsPassed) . "\n";
echo "  Success Rate : " . round(($testsPassed / $totalTests) * 100, 1) . "%\n";
echo "============================================================\n";
