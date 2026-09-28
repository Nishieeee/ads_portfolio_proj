<?php
/**
 * Layout: Hero Section
 * Receives: $fullName, $basicInfo, $siteSettings
 */
?>
<section id="hero" class="hero-section">
    <div class="container hero-container">
        <div class="hero-badge">
            <span class="pulse-dot"></span> <?= htmlspecialchars($siteSettings['availability_badge'] ?? 'Available for Select Projects & Full-Time Roles') ?>
        </div>

        <h1 class="hero-name">
            <span class="hero-greeting">Hi, I'm</span>
            <span class="name-gradient"><?= htmlspecialchars($fullName) ?></span>
        </h1>

        <h2 class="hero-role"><?= htmlspecialchars($basicInfo['role_title'] ?? 'FULL-STACK DEVELOPER') ?></h2>

        <p class="hero-tagline">
            <?= htmlspecialchars($basicInfo['tagline'] ?? 'Crafting premium digital experiences with clean code and high-performance backend architecture.') ?>
        </p>

        <div class="hero-actions">
            <a href="#projects" class="btn btn-primary">
                <span>Explore Projects</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17l9.2-9.2M17 17V8H8"/></svg>
            </a>
            <a href="<?= htmlspecialchars($basicInfo['resume_url'] ?? 'Public/assets/Resume/Resume.md') ?>" target="_blank" class="btn btn-secondary">
                <span>View Resume</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </a>
        </div>
    </div>
</section>
