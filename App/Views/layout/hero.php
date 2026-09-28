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

        <!-- Credibility Highlights Strip -->
        <div class="hero-proof-strip">
            <div class="proof-item">
                <span class="proof-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.45 1-1 1H8v4h8v-4h-1c-.55 0-1-.45-1-1v-2.34"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2z"/></svg>
                </span>
                <div class="proof-text">
                    <strong>Hackathon Champion</strong>
                    <small>Build With AI 2026</small>
                </div>
            </div>
            <div class="proof-divider"></div>
            <div class="proof-item">
                <span class="proof-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                </span>
                <div class="proof-text">
                    <strong>High-Concurrency</strong>
                    <small>Pessimistic Locks & Redis</small>
                </div>
            </div>
            <div class="proof-divider"></div>
            <div class="proof-item">
                <span class="proof-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                </span>
                <div class="proof-text">
                    <strong>C1 Advanced English</strong>
                    <small>EF SET Verified (70/100)</small>
                </div>
            </div>
        </div>
    </div>
</section>
