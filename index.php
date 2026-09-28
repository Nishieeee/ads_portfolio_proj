<?php
/**
 * Clein.dev — Main Entrypoint & View Orchestrator
 * Hybrid CMS Architecture: Dynamic Sections & Color Themes
 */

// 1. Data Loader (Currently reads from portfolio_data.json, strictly mapped to portfolio_cms database)
$dataPath = __DIR__ . '/portfolio_data.json';
$portfolioData = [];
if (file_exists($dataPath)) {
    $portfolioData = json_decode(file_get_contents($dataPath), true) ?: [];
}

// 2. Extract Data Entities
$siteSettings = $portfolioData['site_settings'] ?? [
    'theme' => 'monochrome',
    'site_title' => 'Jhon Clein Pagarogan — Full-Stack Developer',
    'availability_badge' => 'Available for Select Projects & Full-Time Roles'
];

$pageSections = $portfolioData['page_sections'] ?? [];
$basicInfo    = $portfolioData['my_basic_info'] ?? [];
$contacts     = $portfolioData['my_contact_info'] ?? [];
$experiences  = $portfolioData['my_experience'] ?? [];
$projects     = $portfolioData['my_projects'] ?? [];
$skillsGrouped= $portfolioData['my_skills'] ?? [];
$educations   = $portfolioData['my_education'] ?? [];
$certificates = $portfolioData['my_certificates'] ?? [];

// 3. Normalize Full Name
$nameParts = array_filter([
    $basicInfo['first_name'] ?? 'Jhon Clein',
    $basicInfo['middle_name'] ?? '',
    $basicInfo['last_name'] ?? 'Pagarogan'
], fn($part) => trim($part) !== '');
$fullName = implode(' ', $nameParts);

// 4. Sort and Filter Active Page Sections (Hybrid CMS engine)
$activeSections = array_filter($pageSections, fn($s) => !empty($s['is_visible']));
usort($activeSections, fn($a, $b) => ($a['order_index'] ?? 0) <=> ($b['order_index'] ?? 0));

$activeTheme = $siteSettings['theme'] ?? 'monochrome';
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
