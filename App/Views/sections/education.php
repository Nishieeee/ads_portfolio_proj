<?php
/**
 * Section: Education & Certifications
 * Receives: $sec, $educations, $certificates
 */
$kicker = !empty($sec['kicker']) ? $sec['kicker'] : 'Academic & Achievements';
$title = !empty($sec['title']) ? $sec['title'] : 'Education & Certifications';
?>
<section id="education" class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-kicker"><?= htmlspecialchars($kicker) ?></span>
            <h2 class="section-title"><?= htmlspecialchars($title) ?></h2>
            <?php if (!empty($sec['subtitle'])): ?>
                <p class="section-subtitle"><?= htmlspecialchars($sec['subtitle']) ?></p>
            <?php endif; ?>
        </div>

        <div class="dual-column-grid">
            <!-- Education Column -->
            <div class="column-block">
                <div class="block-title-row">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                    <h3 class="block-title">Formal Education</h3>
                </div>

                <?php foreach ($educations as $edu): ?>
                    <div class="info-card">
                        <div class="info-card-header">
                            <h4 class="card-headline"><?= htmlspecialchars($edu['school_name'] ?? '') ?></h4>
                            <span class="badge-subtle"><?= htmlspecialchars($edu['date_display'] ?? '') ?></span>
                        </div>
                        <div class="card-sub"><?= htmlspecialchars($edu['course'] ?? '') ?></div>
                        <?php if (!empty($edu['location'])): ?>
                            <div class="card-meta"><?= htmlspecialchars($edu['location']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($edu['focus_areas'])): ?>
                            <div class="card-details">
                                <strong>Focus Areas:</strong> <?= htmlspecialchars($edu['focus_areas']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Certifications Column -->
            <div class="column-block">
                <div class="block-title-row">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                    <h3 class="block-title">Honors & Certifications</h3>
                </div>

                <?php foreach ($certificates as $cert): ?>
                    <div class="info-card cert-card">
                        <div class="info-card-header">
                            <h4 class="card-headline"><?= htmlspecialchars($cert['title'] ?? '') ?></h4>
                            <?php if (!empty($cert['date_display'])): ?>
                                <span class="badge-accent"><?= htmlspecialchars($cert['date_display']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="card-sub"><?= htmlspecialchars($cert['issuer'] ?? '') ?></div>
                        <p class="card-desc"><?= htmlspecialchars($cert['description'] ?? $cert['descripton'] ?? '') ?></p>
                        
                        <div class="cert-footer">
                            <?php if (!empty($cert['cert_id'])): ?>
                                <span class="cert-code">ID: <?= htmlspecialchars($cert['cert_id']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($cert['cert_url'])): ?>
                                <a href="<?= htmlspecialchars($cert['cert_url']) ?>" target="_blank" rel="noopener noreferrer" class="cert-link">
                                    <span>Verify Credential</span>
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17l9.2-9.2M17 17V8H8"/></svg>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
