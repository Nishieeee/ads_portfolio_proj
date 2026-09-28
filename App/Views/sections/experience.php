<?php
/**
 * Section: Work Experience
 * Receives: $sec, $experiences
 */
$kicker = !empty($sec['kicker']) ? $sec['kicker'] : 'Career Path';
$title = !empty($sec['title']) ? $sec['title'] : 'Work Experience';
?>
<section id="experience" class="section section-alt">
    <div class="container">
        <div class="section-header">
            <span class="section-kicker"><?= htmlspecialchars($kicker) ?></span>
            <h2 class="section-title"><?= htmlspecialchars($title) ?></h2>
            <?php if (!empty($sec['subtitle'])): ?>
                <p class="section-subtitle"><?= htmlspecialchars($sec['subtitle']) ?></p>
            <?php endif; ?>
        </div>

        <div class="timeline">
            <?php foreach ($experiences as $exp): ?>
                <div class="timeline-item">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <div>
                                <h3 class="timeline-role"><?= htmlspecialchars($exp['job_title'] ?? '') ?></h3>
                                <div class="timeline-company">
                                    <span class="company-name"><?= htmlspecialchars($exp['company_name'] ?? '') ?></span>
                                    <?php if (!empty($exp['location'])): ?>
                                        <span class="company-loc">• <?= htmlspecialchars($exp['location']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="timeline-date"><?= htmlspecialchars($exp['date_display'] ?? '') ?></span>
                        </div>
                        <ul class="timeline-bullets">
                            <?php if (!empty($exp['description_1'])): ?>
                                <li><?= htmlspecialchars($exp['description_1']) ?></li>
                            <?php endif; ?>
                            <?php if (!empty($exp['description_2'])): ?>
                                <li><?= htmlspecialchars($exp['description_2']) ?></li>
                            <?php endif; ?>
                            <?php if (!empty($exp['description_3'])): ?>
                                <li><?= htmlspecialchars($exp['description_3']) ?></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
