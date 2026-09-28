<?php
/**
 * Clein.dev — Main Entrypoint & View Orchestrator
 * Hybrid CMS Architecture: Dynamic Sections & Color Themes
 */

// 1. Data Loader (Live MySQL database with graceful JSON fallback)
require_once __DIR__ . '/Config/database.php';

$siteSettings = [
    'theme' => 'monochrome',
    'site_title' => 'Jhon Clein Pagarogan — Full-Stack Developer',
    'availability_badge' => 'Available for Select Projects & Full-Time Roles'
];

$pageSections  = [];
$basicInfo     = [];
$contacts      = [];
$experiences   = [];
$projects      = [];
$skillsGrouped = [];
$educations    = [];
$certificates  = [];

try {
    $database = new Database();
    $db = $database->getConnection();
    if ($db) {
        $stmt = $db->query("SELECT * FROM site_settings WHERE id = 1 LIMIT 1");
        $dbSettings = $stmt->fetch();
        if ($dbSettings) $siteSettings = $dbSettings;

        $stmt = $db->query("SELECT * FROM page_sections ORDER BY order_index ASC");
        $pageSections = $stmt->fetchAll() ?: [];

        $stmt = $db->query("SELECT * FROM my_basic_info WHERE id = 1 LIMIT 1");
        $basicInfo = $stmt->fetch() ?: [];
        if (!empty($basicInfo['bio_paragraphs']) && is_string($basicInfo['bio_paragraphs'])) {
            $decoded = json_decode($basicInfo['bio_paragraphs'], true);
            if (is_array($decoded)) {
                $basicInfo['bio_paragraphs'] = $decoded;
            } else {
                $normalized = str_replace(["\r\n", "\r"], "\n", $basicInfo['bio_paragraphs']);
                $paragraphs = array_values(array_filter(array_map('trim', preg_split('/\n{2,}/', $normalized))));
                $basicInfo['bio_paragraphs'] = !empty($paragraphs) ? $paragraphs : [trim($normalized)];
            }
        }

        $stmt = $db->query("SELECT * FROM my_contact_info ORDER BY id ASC");
        $dbContacts = $stmt->fetchAll() ?: [];
        foreach ($dbContacts as $c) {
            $type = $c['contact_type'] ?? 'url';
            $info = $c['contact_info'] ?? '';
            $href = '#';
            if ($type === 'email') {
                $href = 'mailto:' . $info;
            } elseif ($type === 'phone_no') {
                $cleanPhone = preg_replace('/[^\d+]/', '', $info);
                $href = 'tel:' . $cleanPhone;
            } elseif ($type === 'url') {
                $href = str_starts_with($info, 'http') ? $info : ('https://' . $info);
            }
            $c['href'] = $href;
            $contacts[] = $c;
        }

        $stmt = $db->query("SELECT * FROM my_experience ORDER BY id ASC");
        $experiences = $stmt->fetchAll() ?: [];

        $stmt = $db->query("SELECT * FROM my_projects ORDER BY id ASC");
        $projects = $stmt->fetchAll() ?: [];

        $stmt = $db->query("SELECT * FROM my_skills ORDER BY id ASC");
        $skillsRows = $stmt->fetchAll() ?: [];
        foreach ($skillsRows as $row) {
            $rawList = $row['skills_list'] ?? '';
            $skillsArray = [];
            $dec = json_decode($rawList, true);
            if (is_array($dec)) {
                $skillsArray = $dec;
            } else {
                $skillsArray = array_values(array_filter(array_map('trim', explode(',', $rawList))));
            }
            $catLabel = $row['category_label'] ?? '';
            while (str_contains($catLabel, '&amp;')) {
                $catLabel = str_replace('&amp;', '&', $catLabel);
            }
            $skillsArray = array_map(function($s) {
                while (str_contains($s, '&amp;')) {
                    $s = str_replace('&amp;', '&', $s);
                }
                return $s;
            }, $skillsArray);
            $skillsGrouped[] = [
                'id' => (int)$row['id'],
                'skill_category' => $row['skill_category'] ?? 'technical',
                'category_label' => $catLabel,
                'skills' => $skillsArray,
                'skills_list' => is_array($skillsArray) ? implode(', ', $skillsArray) : $rawList
            ];
        }

        $stmt = $db->query("SELECT * FROM my_education ORDER BY id ASC");
        $educations = $stmt->fetchAll() ?: [];

        $stmt = $db->query("SELECT * FROM my_certificates ORDER BY id ASC");
        $certificates = $stmt->fetchAll() ?: [];
    }
} catch (Throwable $e) {
    // Graceful JSON fallback
    $dataPath = __DIR__ . '/portfolio_data.json';
    if (file_exists($dataPath)) {
        $portfolioData = json_decode(file_get_contents($dataPath), true) ?: [];
        $siteSettings   = $portfolioData['site_settings'] ?? $siteSettings;
        $pageSections   = $portfolioData['page_sections'] ?? [];
        $basicInfo      = $portfolioData['my_basic_info'] ?? [];
        $contacts       = $portfolioData['my_contact_info'] ?? [];
        $experiences    = $portfolioData['my_experience'] ?? [];
        $projects       = $portfolioData['my_projects'] ?? [];
        $educations     = $portfolioData['my_education'] ?? [];
        $certificates   = $portfolioData['my_certificates'] ?? [];

        foreach ($portfolioData['my_skills'] ?? [] as $k => $grp) {
            $cat = $grp['skill_category'] ?? $grp['category'] ?? 'technical';
            $lbl = $grp['category_label'] ?? $grp['label'] ?? '';
            while (str_contains($lbl, '&amp;')) {
                $lbl = str_replace('&amp;', '&', $lbl);
            }
            $rawSkills = $grp['skills'] ?? [];
            if (is_string($rawSkills)) {
                $rawSkills = array_values(array_filter(array_map('trim', explode(',', $rawSkills))));
            }
            $rawSkills = array_map(function($s) {
                while (str_contains($s, '&amp;')) {
                    $s = str_replace('&amp;', '&', $s);
                }
                return $s;
            }, $rawSkills);
            $skillsGrouped[] = [
                'id' => (int)($grp['id'] ?? ($k + 1)),
                'skill_category' => $cat,
                'category_label' => $lbl,
                'skills' => $rawSkills,
                'skills_list' => is_array($rawSkills) ? implode(', ', $rawSkills) : (string)$rawSkills
            ];
        }
    }
}

// 2. Normalize Full Name
$nameParts = array_filter([
    $basicInfo['first_name'] ?? 'Jhon Clein',
    $basicInfo['middle_name'] ?? '',
    $basicInfo['last_name'] ?? 'Pagarogan'
], fn($part) => trim($part) !== '');
$fullName = implode(' ', $nameParts);

// 3. Sort and Filter Active Page Sections (Hybrid CMS engine)
$activeSections = array_filter($pageSections, fn($s) => !empty($s['is_visible']));
usort($activeSections, fn($a, $b) => ($a['order_index'] ?? 0) <=> ($b['order_index'] ?? 0));

$activeTheme = $_GET['theme'] ?? ($siteSettings['theme'] ?? 'monochrome');
$pageTitle   = $siteSettings['site_title'] ?? ($fullName . ' — Full-Stack Developer');
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= htmlspecialchars($activeTheme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($basicInfo['tagline'] ?? 'Full-Stack Developer Portfolio') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="Public/css/main.css" rel="stylesheet">
</head>
<body>
    <!-- Sticky Navigation Bar -->
    <?php include __DIR__ . '/App/Views/layout/header.php'; ?>

    <main>
        <!-- Hero Section -->
        <?php include __DIR__ . '/App/Views/layout/hero.php'; ?>

        <!-- Dynamic Page Sections (Order & Visibility controlled by CMS) -->
        <?php 
        foreach ($activeSections as $sec): 
            $sectionKey = $sec['section_key'] ?? '';
            $sectionPath = __DIR__ . "/App/Views/sections/{$sectionKey}.php";
            if (file_exists($sectionPath)):
                include $sectionPath;
            endif;
        endforeach; 
        ?>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/App/Views/layout/footer.php'; ?>

    <script src="Public/js/main.js"></script>
</body>
</html>
