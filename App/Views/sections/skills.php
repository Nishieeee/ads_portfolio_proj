<?php
/**
 * Section: Skills & Technologies
 * Receives: $sec, $skillsGrouped
 */
$kicker = !empty($sec['kicker']) ? $sec['kicker'] : 'Technical Stack';
$title = !empty($sec['title']) ? $sec['title'] : 'Skills & Technologies';
$subtitle = !empty($sec['subtitle']) ? $sec['subtitle'] : 'Structured by domains, from backend systems and database engines to client interfaces.';
?>
<section id="skills" class="section section-alt">
    <div class="container">
        <div class="section-header">
            <span class="section-kicker"><?= htmlspecialchars($kicker) ?></span>
            <h2 class="section-title"><?= htmlspecialchars($title) ?></h2>
            <?php if (!empty($subtitle)): ?>
                <p class="section-subtitle"><?= htmlspecialchars($subtitle) ?></p>
            <?php endif; ?>
        </div>

        <div class="skills-grid">
            <?php foreach ($skillsGrouped as $group): ?>
                <div class="skill-category-card">
                    <div class="category-header">
                        <h3 class="category-title"><?= htmlspecialchars($group['category_label'] ?? 'Skills') ?></h3>
                        <span class="category-pill"><?= htmlspecialchars(ucfirst($group['skill_category'] ?? 'technical')) ?></span>
                    </div>
                    <div class="skill-chips">
                        <?php foreach ($group['skills'] as $skill): ?>
                            <span class="skill-chip"><?= htmlspecialchars($skill) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
