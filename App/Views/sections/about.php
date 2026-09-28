<?php
/**
 * Section: About Me
 * Receives: $sec, $basicInfo, $fullName
 */
$kicker = !empty($sec['kicker']) ? $sec['kicker'] : 'Get to Know Me';
$title = !empty($sec['title']) ? $sec['title'] : 'About Me';
?>
<section id="about" class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-kicker"><?= htmlspecialchars($kicker) ?></span>
            <h2 class="section-title"><?= htmlspecialchars($title) ?></h2>
            <?php if (!empty($sec['subtitle'])): ?>
                <p class="section-subtitle"><?= htmlspecialchars($sec['subtitle']) ?></p>
            <?php endif; ?>
        </div>

        <div class="about-grid">
            <div class="about-image-card">
                <div class="image-wrapper">
                    <img src="<?= htmlspecialchars($basicInfo['avatar_url'] ?? 'Public/assets/images/image.png') ?>" alt="<?= htmlspecialchars($fullName) ?>" id="my-pic" class="avatar-img">
                </div>
            </div>

            <div class="about-text-card">
                <h3 class="about-heading"><?= htmlspecialchars($basicInfo['bio_greeting'] ?? "Hi, I'm Clein!") ?></h3>
                <div class="about-paragraphs">
                    <?php 
                    $paragraphs = $basicInfo['bio_paragraphs'] ?? [];
                    if (is_string($paragraphs)) {
                        $normalized = str_replace(["\r\n", "\r"], "\n", $paragraphs);
                        $paragraphs = array_values(array_filter(array_map('trim', preg_split('/\n{2,}/', $normalized))));
                    }
                    if (!empty($paragraphs)): ?>
                        <?php foreach ($paragraphs as $para): ?>
                            <p><?= htmlspecialchars($para) ?></p>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>I am an aspiring Software Developer passionate about building impactful and user-centered web applications.</p>
                    <?php endif; ?>
                </div>

                <div class="about-quick-facts">
                    <div class="fact-box">
                        <span class="fact-number">2026</span>
                        <span class="fact-label">Hackathon Winner</span>
                    </div>
                    <div class="fact-box">
                        <span class="fact-number" id="aboutProjectCounter"><?= count($projects ?? []) ?>+</span>
                        <span class="fact-label">Full-Stack Projects</span>
                    </div>
                    <div class="fact-box">
                        <span class="fact-number">100%</span>
                        <span class="fact-label">Clean Code Focus</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
