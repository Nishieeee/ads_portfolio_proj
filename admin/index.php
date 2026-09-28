<?php
/**
 * Clein.dev — CMS Admin Dashboard Frontend
 * Pure Vanilla PHP, CSS, and JavaScript
 */

// 1. Load Data Store (Live MySQL database with graceful JSON fallback)
require_once dirname(__DIR__) . '/Config/database.php';

$siteSettings   = ['theme' => 'monochrome', 'site_title' => 'Portfolio CMS', 'availability_badge' => 'Available'];
$pageSections   = [];
$basicInfo      = [];
$contacts       = [];
$experiences    = [];
$projects       = [];
$skillsGrouped  = [];
$educations     = [];
$certificates   = [];
$inquiries      = [];

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
            }
        }

        $stmt = $db->query("SELECT * FROM my_contact_info ORDER BY id ASC");
        $contacts = $stmt->fetchAll() ?: [];

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
            $skillsGrouped[] = [
                'id' => (int)$row['id'],
                'skill_category' => $row['skill_category'] ?? 'technical',
                'category_label' => $row['category_label'] ?? '',
                'skills' => $skillsArray,
                'skills_list' => is_array($skillsArray) ? implode(', ', $skillsArray) : $rawList
            ];
        }

        $stmt = $db->query("SELECT * FROM my_education ORDER BY id ASC");
        $educations = $stmt->fetchAll() ?: [];

        $stmt = $db->query("SELECT * FROM my_certificates ORDER BY id ASC");
        $certificates = $stmt->fetchAll() ?: [];

        $stmt = $db->query("SELECT * FROM contact_inquiries ORDER BY created_at DESC");
        $inquiries = $stmt->fetchAll() ?: [];
    }
} catch (Throwable $e) {
    $dataPath = dirname(__DIR__) . '/portfolio_data.json';
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
        $inquiries      = $portfolioData['contact_inquiries'] ?? [];

        foreach ($portfolioData['my_skills'] ?? [] as $k => $grp) {
            $cat = $grp['skill_category'] ?? $grp['category'] ?? 'technical';
            $lbl = $grp['category_label'] ?? $grp['label'] ?? '';
            $rawSkills = $grp['skills'] ?? [];
            if (is_string($rawSkills)) {
                $rawSkills = array_values(array_filter(array_map('trim', explode(',', $rawSkills))));
            }
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

$unreadInquiries = count(array_filter($inquiries, fn($m) => empty($m['is_read'])));
$activeSectionsCount = count(array_filter($pageSections, fn($s) => !empty($s['is_visible'])));
$totalSkillsCount = 0;
foreach ($skillsGrouped as $grp) {
    $totalSkillsCount += count($grp['skills'] ?? []);
}

$activeTheme = $siteSettings['theme'] ?? 'monochrome';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Dashboard — Clein.dev</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar Navigation -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-brand">
                <span class="brand-title">&lt;Clein<span style="color:#71717a">.cms</span>/&gt;</span>
                <span class="brand-badge">Admin</span>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-category">Main</div>
                <button type="button" class="nav-item active" data-tab="overview">
                    <span class="nav-item-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    </span>
                    <span class="nav-label-text">Overview</span>
                </button>

                <button type="button" class="nav-item" data-tab="settings">
                    <span class="nav-item-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    </span>
                    <span class="nav-label-text">Theme & Settings</span>
                </button>

                <button type="button" class="nav-item" data-tab="sections">
                    <span class="nav-item-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                    </span>
                    <span class="nav-label-text">Sections & Layout</span>
                </button>

                <div class="nav-category">Content</div>
                <button type="button" class="nav-item" data-tab="projects">
                    <span class="nav-item-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                    </span>
                    <span class="nav-label-text">Projects</span>
                    <span class="nav-item-count"><?= count($projects) ?></span>
                </button>

                <button type="button" class="nav-item" data-tab="experience">
                    <span class="nav-item-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </span>
                    <span class="nav-label-text">Experience</span>
                </button>

                <button type="button" class="nav-item" data-tab="skills">
                    <span class="nav-item-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    </span>
                    <span class="nav-label-text">Skills</span>
                </button>

                <button type="button" class="nav-item" data-tab="education">
                    <span class="nav-item-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                    </span>
                    <span class="nav-label-text">Education & Certs</span>
                </button>

                <button type="button" class="nav-item" data-tab="profile">
                    <span class="nav-item-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </span>
                    <span class="nav-label-text">Profile & Bio</span>
                </button>

                <div class="nav-category">Inbox</div>
                <button type="button" class="nav-item" data-tab="inquiries">
                    <span class="nav-item-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </span>
                    <span class="nav-label-text">Inquiries</span>
                    <?php if ($unreadInquiries > 0): ?>
                        <span class="nav-item-count" style="background:#ffffff; color:#000000;"><?= $unreadInquiries ?></span>
                    <?php endif; ?>
                </button>
            </nav>

            <div class="sidebar-footer">
                <div class="admin-user">
                    <div class="user-avatar">JC</div>
                    <div class="user-meta">
                        <div class="user-name">Clein Pagarogan</div>
                        <div class="user-role">Administrator</div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Workspace -->
        <div class="admin-main">
            <!-- Topbar -->
            <header class="admin-topbar">
                <div class="topbar-left">
                    <button type="button" class="mobile-nav-toggle" id="mobileNavToggle" aria-label="Toggle Menu">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                    </button>
                    <h1 class="page-title" id="activePageTitle">Overview</h1>
                </div>

                <div class="topbar-right">
                    <div class="theme-indicator-pill">
                        <span class="theme-swatch"></span>
                        <span>Theme: <strong id="currentThemeName"><?= ucfirst($activeTheme) ?></strong></span>
                    </div>

                    <a href="../index.php" target="_blank" class="btn-view-site">
                        <span>View Live Site</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                    </a>
                </div>
            </header>

            <!-- Body Panels -->
            <main class="admin-body">

                <!-- TAB 1: OVERVIEW -->
                <section class="tab-panel active" id="tab-overview">
                    <div class="panel-header">
                        <div>
                            <h2 class="panel-heading">Dashboard Overview</h2>
                            <p class="panel-sub">Real-time summary of portfolio entities, CMS status, and incoming messages.</p>
                        </div>
                    </div>

                    <div class="stats-grid">
                        <div class="stat-card">
                            <span class="stat-title">Featured Projects</span>
                            <span class="stat-number"><?= count($projects) ?></span>
                            <span class="stat-desc">1 Flagship Showcase</span>
                        </div>
                        <div class="stat-card">
                            <span class="stat-title">Work Experience</span>
                            <span class="stat-number"><?= count($experiences) ?></span>
                            <span class="stat-desc">AfterVa & WMSU</span>
                        </div>
                        <div class="stat-card">
                            <span class="stat-title">Active Sections</span>
                            <span class="stat-number"><?= $activeSectionsCount ?> / <?= count($pageSections) ?></span>
                            <span class="stat-desc">Hybrid View Engine</span>
                        </div>
                        <div class="stat-card">
                            <span class="stat-title">Total Skills</span>
                            <span class="stat-number"><?= $totalSkillsCount ?></span>
                            <span class="stat-desc">Across 5 domains</span>
                        </div>
                        <div class="stat-card">
                            <span class="stat-title">Inquiries</span>
                            <span class="stat-number"><?= count($inquiries) ?></span>
                            <span class="stat-desc"><?= $unreadInquiries ?> unread inquiries</span>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h3 class="card-title">Recent Inquiries</h3>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="document.querySelector('.nav-item[data-tab=\'inquiries\']').click()">View All</button>
                        </div>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Sender</th>
                                        <th>Email</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($inquiries as $inq): ?>
                                        <tr>
                                            <td style="font-family:var(--font-mono); font-size:0.8rem;"><?= htmlspecialchars(substr($inq['created_at'] ?? '', 0, 10)) ?></td>
                                            <td class="cell-primary"><?= htmlspecialchars($inq['sender_name'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($inq['sender_email'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($inq['subject'] ?? '') ?></td>
                                            <td>
                                                <span class="badge <?= !empty($inq['is_read']) ? 'badge-accent' : 'badge-success' ?>">
                                                    <?= !empty($inq['is_read']) ? 'Read' : 'New' ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- TAB 2: THEME & SETTINGS -->
                <section class="tab-panel" id="tab-settings">
                    <div class="panel-header">
                        <div>
                            <h2 class="panel-heading">Theme & Site Settings</h2>
                            <p class="panel-sub">Select your global color theme and manage site-wide header metadata.</p>
                        </div>
                        <div class="panel-actions">
                            <button type="submit" form="settingsForm" class="btn btn-primary">Save Settings</button>
                        </div>
                    </div>

                    <form class="admin-form" id="settingsForm">
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h3 class="card-title">Color Theme Presets</h3>
                            </div>
                            <div class="admin-card-body">
                                <div class="theme-grid">
                                    <!-- Monochrome -->
                                    <div class="theme-card <?= $activeTheme === 'monochrome' ? 'selected' : '' ?>" data-theme-val="monochrome">
                                        <input type="radio" name="theme" value="monochrome" class="theme-radio" <?= $activeTheme === 'monochrome' ? 'checked' : '' ?>>
                                        <div class="theme-preview-dots">
                                            <span class="dot" style="background:#09090b"></span>
                                            <span class="dot" style="background:#ffffff"></span>
                                            <span class="dot" style="background:#71717a"></span>
                                        </div>
                                        <div class="theme-name">Monochrome</div>
                                        <div class="theme-desc">Minimalist Studio Black & White</div>
                                    </div>

                                    <!-- Midnight -->
                                    <div class="theme-card <?= $activeTheme === 'midnight' ? 'selected' : '' ?>" data-theme-val="midnight">
                                        <input type="radio" name="theme" value="midnight" class="theme-radio" <?= $activeTheme === 'midnight' ? 'checked' : '' ?>>
                                        <div class="theme-preview-dots">
                                            <span class="dot" style="background:#0b0f19"></span>
                                            <span class="dot" style="background:#818cf8"></span>
                                            <span class="dot" style="background:#334155"></span>
                                        </div>
                                        <div class="theme-name">Midnight Indigo</div>
                                        <div class="theme-desc">Deep Slate & Electric Purple</div>
                                    </div>

                                    <!-- Emerald -->
                                    <div class="theme-card <?= $activeTheme === 'emerald' ? 'selected' : '' ?>" data-theme-val="emerald">
                                        <input type="radio" name="theme" value="emerald" class="theme-radio" <?= $activeTheme === 'emerald' ? 'checked' : '' ?>>
                                        <div class="theme-preview-dots">
                                            <span class="dot" style="background:#05130e"></span>
                                            <span class="dot" style="background:#34d399"></span>
                                            <span class="dot" style="background:#1f5746"></span>
                                        </div>
                                        <div class="theme-name">Emerald Cyber</div>
                                        <div class="theme-desc">Matrix / Forest Terminal Dark</div>
                                    </div>

                                    <!-- Amber -->
                                    <div class="theme-card <?= $activeTheme === 'amber' ? 'selected' : '' ?>" data-theme-val="amber">
                                        <input type="radio" name="theme" value="amber" class="theme-radio" <?= $activeTheme === 'amber' ? 'checked' : '' ?>>
                                        <div class="theme-preview-dots">
                                            <span class="dot" style="background:#12100e"></span>
                                            <span class="dot" style="background:#fbbf24"></span>
                                            <span class="dot" style="background:#4a423a"></span>
                                        </div>
                                        <div class="theme-name">Warm Amber</div>
                                        <div class="theme-desc">Obsidian & Warm Gold</div>
                                    </div>

                                    <!-- Light -->
                                    <div class="theme-card <?= $activeTheme === 'light' ? 'selected' : '' ?>" data-theme-val="light">
                                        <input type="radio" name="theme" value="light" class="theme-radio" <?= $activeTheme === 'light' ? 'checked' : '' ?>>
                                        <div class="theme-preview-dots">
                                            <span class="dot" style="background:#ffffff"></span>
                                            <span class="dot" style="background:#0f172a"></span>
                                            <span class="dot" style="background:#e2e8f0"></span>
                                        </div>
                                        <div class="theme-name">Paper Minimal</div>
                                        <div class="theme-desc">Clean High-Contrast Light</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h3 class="card-title">Site Global Text</h3>
                            </div>
                            <div class="admin-card-body">
                                <div class="form-group">
                                    <label class="form-label" for="site_title">Page Title (Browser Tab)</label>
                                    <input type="text" id="site_title" name="site_title" class="form-input" value="<?= htmlspecialchars($siteSettings['site_title'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="availability_badge">Availability Status Badge</label>
                                    <input type="text" id="availability_badge" name="availability_badge" class="form-input" value="<?= htmlspecialchars($siteSettings['availability_badge'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </form>
                </section>

                <!-- TAB 3: SECTIONS & LAYOUT (HYBRID CMS CONTROLLER) -->
                <section class="tab-panel" id="tab-sections">
                    <div class="panel-header">
                        <div>
                            <h2 class="panel-heading">Sections & Layout (Hybrid Engine)</h2>
                            <p class="panel-sub">Control display order, visibility switches, and headers for every section.</p>
                        </div>
                        <div class="panel-actions">
                            <button type="submit" form="sectionsForm" class="btn btn-primary">Save Section Order</button>
                        </div>
                    </div>

                    <form class="admin-form" id="sectionsForm">
                        <div class="admin-card">
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 80px;">Order</th>
                                            <th>Identifier</th>
                                            <th>Nav Label</th>
                                            <th>Kicker</th>
                                            <th>Section Title</th>
                                            <th style="width: 120px;">Visible</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pageSections as $sec): ?>
                                            <tr class="section-row" data-id="<?= (int)$sec['id'] ?>" data-key="<?= htmlspecialchars($sec['section_key']) ?>">
                                                <td>
                                                    <input type="number" name="order[<?= (int)$sec['id'] ?>]" class="form-input section-order-input" style="width:65px; text-align:center; padding:0.4rem;" value="<?= (int)($sec['order_index'] ?? 1) ?>" min="1" max="10">
                                                </td>
                                                <td>
                                                    <code style="font-family:var(--font-mono); color:#ffffff; background:rgba(255,255,255,0.06); padding:0.2rem 0.5rem; border-radius:4px;">
                                                        <?= htmlspecialchars($sec['section_key']) ?>
                                                    </code>
                                                </td>
                                                <td>
                                                    <input type="text" name="nav_label[<?= (int)$sec['id'] ?>]" class="form-input section-nav-input" value="<?= htmlspecialchars($sec['nav_label']) ?>" style="padding:0.4rem 0.75rem;">
                                                </td>
                                                <td>
                                                    <input type="text" name="kicker[<?= (int)$sec['id'] ?>]" class="form-input section-kicker-input" value="<?= htmlspecialchars($sec['kicker'] ?? '') ?>" style="padding:0.4rem 0.75rem;">
                                                </td>
                                                <td>
                                                    <input type="text" name="title[<?= (int)$sec['id'] ?>]" class="form-input section-title-input" value="<?= htmlspecialchars($sec['title']) ?>" style="padding:0.4rem 0.75rem;">
                                                </td>
                                                <td>
                                                    <label class="switch-container">
                                                        <input type="checkbox" name="visible[<?= (int)$sec['id'] ?>]" class="switch-input section-visibility-toggle" data-id="<?= (int)$sec['id'] ?>" data-name="<?= htmlspecialchars($sec['nav_label']) ?>" <?= !empty($sec['is_visible']) ? 'checked' : '' ?>>
                                                        <span class="switch-slider"></span>
                                                    </label>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </form>
                </section>

                <!-- TAB 4: PROJECTS MANAGER -->
                <section class="tab-panel" id="tab-projects">
                    <div class="panel-header">
                        <div>
                            <h2 class="panel-heading">Projects Directory</h2>
                            <p class="panel-sub">Manage software applications, technical descriptions, and flagship badges.</p>
                        </div>
                        <div class="panel-actions">
                            <button type="button" class="btn btn-primary" id="openAddProjectBtn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                <span>Add New Project</span>
                            </button>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Project</th>
                                        <th>Technologies</th>
                                        <th>Badge / Status</th>
                                        <th>Links</th>
                                        <th style="width: 120px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="projectsTableBody">
                                    <?php foreach ($projects as $proj): ?>
                                        <tr class="project-row" data-id="<?= (int)$proj['id'] ?>">
                                            <td>
                                                <div class="cell-primary"><?= htmlspecialchars($proj['project_name']) ?></div>
                                                <div style="font-size:0.8rem; color:var(--text-muted);"><?= htmlspecialchars($proj['subtitle'] ?? '') ?></div>
                                            </td>
                                            <td style="max-width:280px; font-family:var(--font-mono); font-size:0.78rem;">
                                                <?= htmlspecialchars($proj['technologies'] ?? '') ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($proj['badge'])): ?>
                                                    <span class="badge badge-accent"><?= htmlspecialchars($proj['badge']) ?></span>
                                                <?php else: ?>
                                                    <span style="color:var(--text-muted); font-size:0.8rem;">Standard</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div style="display:flex; gap:0.5rem;">
                                                    <?php if (!empty($proj['github_repo'])): ?>
                                                        <a href="<?= htmlspecialchars($proj['github_repo']) ?>" target="_blank" class="btn-icon" title="GitHub Repo">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/></svg>
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if (!empty($proj['url'])): ?>
                                                        <a href="<?= htmlspecialchars($proj['url']) ?>" target="_blank" class="btn-icon" title="Live URL">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="cell-actions">
                                                    <button type="button" class="btn-icon edit-project-btn" data-id="<?= (int)$proj['id'] ?>" data-project='<?= htmlspecialchars(json_encode($proj, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>' title="Edit">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                                    </button>
                                                    <button type="button" class="btn-icon delete-project-btn" data-id="<?= (int)$proj['id'] ?>" data-name="<?= htmlspecialchars($proj['project_name']) ?>" title="Delete" style="color:var(--danger);">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- TAB 5: EXPERIENCE MANAGER -->
                <section class="tab-panel" id="tab-experience">
                    <div class="panel-header">
                        <div>
                            <h2 class="panel-heading">Work Experience</h2>
                            <p class="panel-sub">Manage client and organizational career roles, dates, and bullet descriptions.</p>
                        </div>
                        <div class="panel-actions">
                            <button type="button" class="btn btn-primary" id="openAddExperienceBtn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                <span>Add Experience</span>
                            </button>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Role & Company</th>
                                        <th>Location</th>
                                        <th>Period</th>
                                        <th>Highlights</th>
                                        <th style="width: 100px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="experienceTableBody">
                                    <?php foreach ($experiences as $exp): ?>
                                        <tr class="experience-row" data-id="<?= (int)$exp['id'] ?>">
                                            <td>
                                                <div class="cell-primary"><?= htmlspecialchars($exp['job_title'] ?? '') ?></div>
                                                <div style="color:var(--text-muted); font-size:0.85rem;"><?= htmlspecialchars($exp['company_name'] ?? '') ?></div>
                                            </td>
                                            <td><?= htmlspecialchars($exp['location'] ?? 'Remote') ?></td>
                                            <td style="font-family:var(--font-mono); font-size:0.8rem;"><?= htmlspecialchars($exp['date_display'] ?? '') ?></td>
                                            <td style="font-size:0.82rem; max-width:320px;">
                                                • <?= htmlspecialchars(substr($exp['description_1'] ?? '', 0, 75)) ?>...
                                            </td>
                                            <td>
                                                <div class="cell-actions">
                                                    <button type="button" class="btn-icon edit-exp-btn" data-id="<?= (int)$exp['id'] ?>" data-exp='<?= htmlspecialchars(json_encode($exp, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>' title="Edit">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                                    </button>
                                                    <button type="button" class="btn-icon delete-exp-btn" data-id="<?= (int)$exp['id'] ?>" data-name="<?= htmlspecialchars($exp['job_title'] ?? '') ?>" title="Delete" style="color:var(--danger);">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- TAB 6: SKILLS MATRIX -->
                <section class="tab-panel" id="tab-skills">
                    <div class="panel-header">
                        <div>
                            <h2 class="panel-heading">Skills Matrix</h2>
                            <p class="panel-sub">Manage categorical technical and soft competencies.</p>
                        </div>
                        <div class="panel-actions" style="display:flex; gap:0.75rem;">
                            <button type="button" class="btn btn-secondary" id="openAddSkillBtn">+ Add Category</button>
                            <button type="button" class="btn btn-primary" id="saveSkillsMatrixBtn">Save Skills Matrix</button>
                        </div>
                    </div>

                    <div id="skillsGrid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
                        <?php foreach ($skillsGrouped as $idx => $grp): ?>
                            <div class="admin-card skill-card" data-id="<?= (int)$grp['id'] ?>">
                                <div class="admin-card-header" style="display:flex; justify-content:space-between; align-items:flex-start;">
                                    <div style="flex:1; margin-right:0.75rem;">
                                        <input type="text" class="form-input skill-label-input" value="<?= htmlspecialchars($grp['category_label']) ?>" style="font-weight:700; font-size:1.05rem; padding:0.35rem 0.6rem; margin-bottom:0.4rem;" placeholder="Category Name">
                                        <select class="form-select skill-category-select" style="font-size:0.75rem; padding:0.25rem 0.5rem; width:auto; display:inline-block;">
                                            <option value="technical" <?= ($grp['skill_category'] ?? '') === 'technical' ? 'selected' : '' ?>>Technical</option>
                                            <option value="soft" <?= ($grp['skill_category'] ?? '') === 'soft' ? 'selected' : '' ?>>Soft</option>
                                        </select>
                                    </div>
                                    <button type="button" class="btn-icon delete-skill-btn" data-id="<?= (int)$grp['id'] ?>" data-name="<?= htmlspecialchars($grp['category_label']) ?>" title="Delete Category" style="color:var(--danger); padding:0.4rem;">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </div>
                                <div class="admin-card-body">
                                    <div class="form-group">
                                        <label class="form-label">Comma-Separated Skills</label>
                                        <textarea class="form-textarea skill-list-input" rows="3"><?= htmlspecialchars(implode(', ', $grp['skills'] ?? [])) ?></textarea>
                                    </div>
                                    <div class="skills-badges-preview" style="display:flex; flex-wrap:wrap; gap:0.4rem; margin-top:0.75rem;">
                                        <?php foreach ($grp['skills'] as $skill): ?>
                                            <span class="badge" style="color:#ffffff;"><?= htmlspecialchars($skill) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- TAB 7: EDUCATION & CERTS -->
                <section class="tab-panel" id="tab-education">
                    <div class="panel-header">
                        <div>
                            <h2 class="panel-heading">Education & Certifications</h2>
                            <p class="panel-sub">Manage academic degrees, hackathon titles, and verified credentials.</p>
                        </div>
                    </div>

                    <!-- Formal Education -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h3 class="card-title">Formal Education</h3>
                            <button type="button" class="btn btn-secondary btn-sm" id="openAddEducationBtn">+ Add Education</button>
                        </div>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Institution</th>
                                        <th>Course / Degree</th>
                                        <th>Period</th>
                                        <th>Focus Areas</th>
                                        <th style="width:90px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="educationTableBody">
                                    <?php foreach ($educations as $edu): ?>
                                        <tr class="education-row" data-id="<?= (int)$edu['id'] ?>">
                                            <td class="cell-primary"><?= htmlspecialchars($edu['school_name'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($edu['course'] ?? '') ?></td>
                                            <td style="font-family:var(--font-mono); font-size:0.8rem;"><?= htmlspecialchars($edu['date_display'] ?? '') ?></td>
                                            <td style="font-size:0.82rem; max-width:260px;"><?= htmlspecialchars($edu['focus_areas'] ?? '') ?></td>
                                            <td>
                                                <div class="cell-actions">
                                                    <button type="button" class="btn-icon edit-edu-btn" data-id="<?= (int)$edu['id'] ?>" data-edu='<?= htmlspecialchars(json_encode($edu, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>' title="Edit">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                                    </button>
                                                    <button type="button" class="btn-icon delete-edu-btn" data-id="<?= (int)$edu['id'] ?>" data-name="<?= htmlspecialchars($edu['school_name'] ?? '') ?>" title="Delete" style="color:var(--danger);">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Honors & Certifications -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h3 class="card-title">Honors & Certifications</h3>
                            <button type="button" class="btn btn-secondary btn-sm" id="openAddCertBtn">+ Add Certificate</button>
                        </div>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Title & Issuer</th>
                                        <th>Date</th>
                                        <th>Credential ID</th>
                                        <th>Verification URL</th>
                                        <th style="width:90px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="certTableBody">
                                    <?php foreach ($certificates as $cert): ?>
                                        <tr class="cert-row" data-id="<?= (int)$cert['id'] ?>">
                                            <td>
                                                <div class="cell-primary"><?= htmlspecialchars($cert['title'] ?? '') ?></div>
                                                <div style="color:var(--text-muted); font-size:0.8rem;"><?= htmlspecialchars($cert['issuer'] ?? '') ?></div>
                                            </td>
                                            <td style="font-family:var(--font-mono); font-size:0.8rem;"><?= htmlspecialchars($cert['date_display'] ?? '') ?></td>
                                            <td style="font-family:var(--font-mono); font-size:0.8rem;"><?= htmlspecialchars($cert['cert_id'] ?? '—') ?></td>
                                            <td>
                                                <?php if (!empty($cert['cert_url'])): ?>
                                                    <a href="<?= htmlspecialchars($cert['cert_url']) ?>" target="_blank" class="badge badge-accent">Verify Link ↗</a>
                                                <?php else: ?>
                                                    <span style="color:var(--text-muted); font-size:0.8rem;">None</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="cell-actions">
                                                    <button type="button" class="btn-icon edit-cert-btn" data-id="<?= (int)$cert['id'] ?>" data-cert='<?= htmlspecialchars(json_encode($cert, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>' title="Edit">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                                    </button>
                                                    <button type="button" class="btn-icon delete-cert-btn" data-id="<?= (int)$cert['id'] ?>" data-name="<?= htmlspecialchars($cert['title'] ?? '') ?>" title="Delete" style="color:var(--danger);">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- TAB 8: PROFILE & CONTACT -->
                <section class="tab-panel" id="tab-profile">
                    <div class="panel-header">
                        <div>
                            <h2 class="panel-heading">Profile & Contact Channels</h2>
                            <p class="panel-sub">Manage your biography narrative, avatar photo, and communication channels.</p>
                        </div>
                        <div class="panel-actions">
                            <button type="submit" form="profileForm" class="btn btn-primary">Save Profile Info</button>
                        </div>
                    </div>

                    <form class="admin-form" id="profileForm">
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h3 class="card-title">Basic Information</h3>
                            </div>
                            <div class="admin-card-body">
                                <div class="form-grid-2" style="grid-template-columns: 1fr 1fr 1fr;">
                                    <div class="form-group">
                                        <label class="form-label" for="first_name">First Name</label>
                                        <input type="text" id="first_name" name="first_name" class="form-input" value="<?= htmlspecialchars($basicInfo['first_name'] ?? '') ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="middle_name">Middle Name</label>
                                        <input type="text" id="middle_name" name="middle_name" class="form-input" value="<?= htmlspecialchars($basicInfo['middle_name'] ?? '') ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="last_name">Last Name</label>
                                        <input type="text" id="last_name" name="last_name" class="form-input" value="<?= htmlspecialchars($basicInfo['last_name'] ?? '') ?>" required>
                                    </div>
                                </div>
                                <div class="form-grid-2">
                                    <div class="form-group">
                                        <label class="form-label" for="role_title">Role Title</label>
                                        <input type="text" id="role_title" name="role_title" class="form-input" value="<?= htmlspecialchars($basicInfo['role_title'] ?? '') ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="birth_date">Birth Date</label>
                                        <input type="date" id="birth_date" name="birth_date" class="form-input" value="<?= htmlspecialchars($basicInfo['birth_date'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="form-grid-2">
                                    <div class="form-group">
                                        <label class="form-label" for="avatar_url">Avatar Image URL / Path</label>
                                        <input type="text" id="avatar_url" name="avatar_url" class="form-input" value="<?= htmlspecialchars($basicInfo['avatar_url'] ?? '') ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="resume_url">Resume Document URL / Path</label>
                                        <input type="text" id="resume_url" name="resume_url" class="form-input" value="<?= htmlspecialchars($basicInfo['resume_url'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="tagline">Hero Tagline</label>
                                    <textarea id="tagline" name="tagline" class="form-textarea" rows="2"><?= htmlspecialchars($basicInfo['tagline'] ?? '') ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="bio_paragraphs">About Bio Narrative</label>
                                    <textarea id="bio_paragraphs" name="bio_paragraphs" class="form-textarea" rows="4"><?= htmlspecialchars(is_array($basicInfo['bio_paragraphs'] ?? null) ? implode("\n\n", $basicInfo['bio_paragraphs']) : ($basicInfo['bio_paragraphs'] ?? '')) ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Channels Directory -->
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h3 class="card-title">Contact Channels Directory</h3>
                                <button type="button" class="btn btn-secondary btn-sm" id="openAddContactBtn">+ Add Channel</button>
                            </div>
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Channel</th>
                                            <th>Type</th>
                                            <th>Value / Handle</th>
                                            <th style="width:90px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="contactTableBody">
                                        <?php foreach ($contacts as $contact): ?>
                                            <tr class="contact-row" data-id="<?= (int)$contact['id'] ?>">
                                                <td class="cell-primary"><?= htmlspecialchars($contact['contact_name']) ?></td>
                                                <td><span class="badge"><?= htmlspecialchars($contact['contact_type']) ?></span></td>
                                                <td style="font-family:var(--font-mono); font-size:0.85rem;"><?= htmlspecialchars($contact['contact_info']) ?></td>
                                                <td>
                                                    <div class="cell-actions">
                                                        <button type="button" class="btn-icon edit-contact-btn" data-id="<?= (int)$contact['id'] ?>" data-name="<?= htmlspecialchars($contact['contact_name']) ?>" data-type="<?= htmlspecialchars($contact['contact_type']) ?>" data-info="<?= htmlspecialchars($contact['contact_info']) ?>" title="Edit">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                                        </button>
                                                        <button type="button" class="btn-icon delete-contact-btn" data-id="<?= (int)$contact['id'] ?>" data-name="<?= htmlspecialchars($contact['contact_name']) ?>" title="Delete" style="color:#ef4444;">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </form>
                </section>

                <!-- TAB 9: INQUIRIES INBOX -->
                <section class="tab-panel" id="tab-inquiries">
                    <div class="panel-header">
                        <div>
                            <h2 class="panel-heading">Inquiries Inbox</h2>
                            <p class="panel-sub">Incoming messages submitted through your portfolio contact form.</p>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Received</th>
                                        <th>Sender Name</th>
                                        <th>Email Address</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                        <th style="width: 100px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="inquiriesTableBody">
                                    <?php foreach ($inquiries as $inq): ?>
                                        <tr class="inquiry-row" data-id="<?= (int)$inq['id'] ?>">
                                            <td style="font-family:var(--font-mono); font-size:0.8rem;"><?= htmlspecialchars($inq['created_at'] ?? '') ?></td>
                                            <td class="cell-primary"><?= htmlspecialchars($inq['sender_name'] ?? '') ?></td>
                                            <td>
                                                <a href="mailto:<?= htmlspecialchars($inq['sender_email'] ?? '') ?>" style="text-decoration:underline; color:#ffffff;">
                                                    <?= htmlspecialchars($inq['sender_email'] ?? '') ?>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($inq['subject'] ?? '') ?></td>
                                            <td>
                                                <span class="badge inquiry-status-badge <?= !empty($inq['is_read']) ? 'badge-accent' : 'badge-success' ?>">
                                                    <?= !empty($inq['is_read']) ? 'Read' : 'New' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="cell-actions" style="display:flex; gap:0.4rem; align-items:center;">
                                                    <button type="button" class="btn btn-secondary btn-sm open-inquiry-btn" data-modal-open="inquiryModal-<?= $inq['id'] ?>" data-id="<?= (int)$inq['id'] ?>" data-read="<?= !empty($inq['is_read']) ? '1' : '0' ?>">Read</button>
                                                    <button type="button" class="btn-icon delete-inquiry-btn" data-id="<?= (int)$inq['id'] ?>" data-name="<?= htmlspecialchars($inq['sender_name'] ?? '') ?>" title="Delete" style="color:var(--danger);">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

            </main>
        </div>
    </div>

    <!-- MODAL: UNIVERSAL DELETE CONFIRMATION -->
    <div class="modal-overlay" id="deleteConfirmModal" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">
        <div class="modal-dialog" style="max-width: 460px;">
            <div class="modal-header">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="width: 36px; height: 36px; border-radius: var(--radius-sm); background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); display: flex; align-items: center; justify-content: center; color: var(--danger);">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            <line x1="10" y1="11" x2="10" y2="17"></line>
                            <line x1="14" y1="11" x2="14" y2="17"></line>
                        </svg>
                    </div>
                    <h3 class="modal-title" id="deleteModalTitle" style="font-size: 1.15rem;">Confirm Deletion</h3>
                </div>
                <button type="button" class="btn-icon" data-modal-close aria-label="Close">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="modal-body" style="padding: 1.5rem 2rem;">
                <p id="deleteModalMessage" style="font-size: 0.95rem; color: var(--text-secondary); line-height: 1.6; margin-bottom: 0.75rem;">
                    Are you sure you want to delete this item?
                </p>
                <div style="padding: 0.75rem 1rem; border-radius: var(--radius-md); background: rgba(239, 68, 68, 0.06); border: 1px solid rgba(239, 68, 68, 0.2); font-size: 0.85rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span><strong style="color: var(--danger);">Warning:</strong> This action cannot be undone.</span>
                </div>
            </div>
            <div class="modal-footer" style="padding: 1.25rem 2rem; background: rgba(0, 0, 0, 0.15);">
                <button type="button" class="btn btn-secondary" data-modal-close id="deleteModalCancelBtn">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteModalBtn" style="background-color: var(--danger); border-color: var(--danger); color: #ffffff; display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    <span>Delete</span>
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL: ADD / EDIT PROJECT -->
    <div class="modal-overlay" id="projectModal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title" id="projectModalTitle">Project Details</h3>
                <button type="button" class="btn-icon" data-modal-close aria-label="Close">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <form class="admin-form" id="projectModalForm">
                <input type="hidden" id="modal_project_id" value="">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="modal_project_name">Project Title</label>
                        <input type="text" id="modal_project_name" class="form-input" placeholder="e.g. NexusCommerce" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="modal_project_sub">Subtitle</label>
                        <input type="text" id="modal_project_sub" class="form-input" placeholder="e.g. High-Concurrency E-Commerce Engine">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="modal_project_desc">Description</label>
                        <textarea id="modal_project_desc" class="form-textarea" rows="4" placeholder="Architectural details, problems solved, and metrics..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="modal_project_tech">Technologies (Comma-separated)</label>
                        <input type="text" id="modal_project_tech" class="form-input" placeholder="Laravel 12, PostgreSQL, Redis, Docker" required>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label" for="modal_project_repo">GitHub Repository URL</label>
                            <input type="url" id="modal_project_repo" class="form-input" placeholder="https://github.com/...">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="modal_project_live">Live Application URL</label>
                            <input type="url" id="modal_project_live" class="form-input" placeholder="https://...">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="modal_project_badge">Spotlight Badge</label>
                        <input type="text" id="modal_project_badge" class="form-input" placeholder="e.g. Flagship Project">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-primary" id="projectModalSubmitBtn">Save Project</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: ADD / EDIT EXPERIENCE -->
    <div class="modal-overlay" id="experienceModal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title" id="experienceModalTitle">Experience Entry</h3>
                <button type="button" class="btn-icon" data-modal-close aria-label="Close">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <form class="admin-form" id="experienceModalForm">
                <input type="hidden" id="modal_exp_id" value="">
                <div class="modal-body">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label" for="modal_job_title">Job Title</label>
                            <input type="text" id="modal_job_title" class="form-input" placeholder="e.g. Full-Stack Developer" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="modal_company">Company / Organization</label>
                            <input type="text" id="modal_company" class="form-input" placeholder="e.g. AfterVa" required>
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label" for="modal_exp_loc">Location</label>
                            <input type="text" id="modal_exp_loc" class="form-input" placeholder="e.g. Remote / Zamboanga City">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="modal_exp_dates">Date Display Range</label>
                            <input type="text" id="modal_exp_dates" class="form-input" placeholder="e.g. May 2026 – Present" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="modal_desc_1">Bullet 1</label>
                        <input type="text" id="modal_desc_1" class="form-input" placeholder="Key achievement or architectural contribution" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="modal_desc_2">Bullet 2</label>
                        <input type="text" id="modal_desc_2" class="form-input" placeholder="Infrastructure, hosting, optimization metrics" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="modal_desc_3">Bullet 3</label>
                        <input type="text" id="modal_desc_3" class="form-input" placeholder="Documentation, testing, collaboration" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-primary" id="experienceModalSubmitBtn">Save Experience</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: ADD / EDIT SKILL CATEGORY -->
    <div class="modal-overlay" id="skillModal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title" id="skillModalTitle">Add Skill Category</h3>
                <button type="button" class="btn-icon" data-modal-close aria-label="Close">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <form class="admin-form" id="skillModalForm">
                <input type="hidden" id="modal_skill_id" value="">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="modal_skill_category">Category Domain</label>
                        <select id="modal_skill_category" class="form-select" required>
                            <option value="technical">Technical Skills</option>
                            <option value="soft">Soft & Professional Skills</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="modal_skill_label">Category Heading</label>
                        <input type="text" id="modal_skill_label" class="form-input" placeholder="e.g. Cloud Infrastructure & DevOps" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="modal_skill_list">Skills List (Comma-separated)</label>
                        <textarea id="modal_skill_list" class="form-textarea" rows="4" placeholder="AWS EC2, Docker, GitHub Actions, NGINX" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-primary" id="skillModalSubmitBtn">Save Category</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: ADD / EDIT EDUCATION -->
    <div class="modal-overlay" id="educationModal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title" id="educationModalTitle">Formal Education Entry</h3>
                <button type="button" class="btn-icon" data-modal-close aria-label="Close">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <form class="admin-form" id="educationModalForm">
                <input type="hidden" id="modal_edu_id" value="">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="modal_edu_school">Institution / University</label>
                        <input type="text" id="modal_edu_school" class="form-input" placeholder="e.g. Ateneo de Zamboanga University" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="modal_edu_course">Course / Degree</label>
                        <input type="text" id="modal_edu_course" class="form-input" placeholder="e.g. BS in Information Technology" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="modal_edu_dates">Date Display Range</label>
                        <input type="text" id="modal_edu_dates" class="form-input" placeholder="e.g. 2022 – 2026" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="modal_edu_focus">Focus Areas / Specialization</label>
                        <textarea id="modal_edu_focus" class="form-textarea" rows="3" placeholder="Software Engineering, Distributed Systems, Database Architecture"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-primary" id="educationModalSubmitBtn">Save Education</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: ADD / EDIT CERTIFICATION -->
    <div class="modal-overlay" id="certificateModal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title" id="certificateModalTitle">Honor & Certification</h3>
                <button type="button" class="btn-icon" data-modal-close aria-label="Close">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <form class="admin-form" id="certificateModalForm">
                <input type="hidden" id="modal_cert_id" value="">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="modal_cert_title">Title</label>
                        <input type="text" id="modal_cert_title" class="form-input" placeholder="e.g. AWS Certified Cloud Practitioner" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="modal_cert_issuer">Issuer / Organization</label>
                        <input type="text" id="modal_cert_issuer" class="form-input" placeholder="e.g. Amazon Web Services" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="modal_cert_date">Date Range / Issued</label>
                        <input type="text" id="modal_cert_date" class="form-input" placeholder="e.g. Nov 2025" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="modal_cert_num">Credential ID</label>
                        <input type="text" id="modal_cert_num" class="form-input" placeholder="e.g. AWS-CCP-10293847">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="modal_cert_url">Verification URL</label>
                        <input type="url" id="modal_cert_url" class="form-input" placeholder="https://aws.amazon.com/verify/...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-primary" id="certificateModalSubmitBtn">Save Certificate</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: ADD / EDIT CONTACT CHANNEL -->
    <div class="modal-overlay" id="contactModal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title" id="contactModalTitle">Add Contact Channel</h3>
                <button type="button" class="btn-icon" data-modal-close aria-label="Close">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <form id="contactModalForm">
                <input type="hidden" id="contact_modal_id" value="">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="contact_modal_name">Channel Name</label>
                        <input type="text" id="contact_modal_name" class="form-input" placeholder="e.g. GitHub, LinkedIn, Email, Phone" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="contact_modal_type">Channel Type</label>
                        <select id="contact_modal_type" class="form-select" required>
                            <option value="url">URL / Web Link</option>
                            <option value="email">Email Address</option>
                            <option value="phone_no">Phone Number</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="contact_modal_info">Contact Value / URL / Address</label>
                        <input type="text" id="contact_modal_info" class="form-input" placeholder="https://... or email@... or +63..." required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-primary" id="contactModalSubmitBtn">Save Channel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODALS: INQUIRY MESSAGE VIEWERS -->
    <?php foreach ($inquiries as $inq): ?>
        <div class="modal-overlay" id="inquiryModal-<?= $inq['id'] ?>">
            <div class="modal-dialog">
                <div class="modal-header">
                    <h3 class="modal-title">Inquiry from <?= htmlspecialchars($inq['sender_name']) ?></h3>
                    <button type="button" class="btn-icon" data-modal-close aria-label="Close">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <div class="modal-body">
                    <div style="margin-bottom:1.25rem; padding-bottom:1rem; border-bottom:1px solid var(--border-subtle);">
                        <div style="font-size:0.8rem; color:var(--text-muted); font-family:var(--font-mono); margin-bottom:0.25rem;">
                            RECEIVED: <?= htmlspecialchars($inq['created_at']) ?>
                        </div>
                        <div style="font-size:1.1rem; font-weight:700; color:#ffffff; margin-bottom:0.25rem;">
                            <?= htmlspecialchars($inq['subject']) ?>
                        </div>
                        <div style="font-size:0.9rem; color:var(--text-secondary);">
                            From: <strong style="color:#ffffff;"><?= htmlspecialchars($inq['sender_name']) ?></strong> &lt;<?= htmlspecialchars($inq['sender_email']) ?>&gt;
                        </div>
                    </div>
                    <div style="font-size:0.95rem; color:#ffffff; line-height:1.7; white-space:pre-wrap;"><?= htmlspecialchars($inq['message']) ?></div>
                </div>
                <div class="modal-footer">
                    <a href="mailto:<?= htmlspecialchars($inq['sender_email']) ?>?subject=Re: <?= urlencode($inq['subject']) ?>" class="btn btn-primary">
                        <span>Reply via Email</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17l9.2-9.2M17 17V8H8"/></svg>
                    </a>
                    <button type="button" class="btn btn-secondary" data-modal-close>Close</button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <script src="js/admin.js"></script>
</body>
</html>
