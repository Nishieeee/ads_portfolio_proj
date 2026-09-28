<?php
/**
 * Section: Key Projects
 * Receives: $sec, $projects
 */
$kicker = !empty($sec['kicker']) ? $sec['kicker'] : 'Featured Engineering';
$title = !empty($sec['title']) ? $sec['title'] : 'Key Projects';
$subtitle = !empty($sec['subtitle']) ? $sec['subtitle'] : 'Real-world applications demonstrating high concurrency, robust architecture, and modern interfaces.';
?>
<section id="projects" class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-kicker"><?= htmlspecialchars($kicker) ?></span>
            <h2 class="section-title"><?= htmlspecialchars($title) ?></h2>
            <?php if (!empty($subtitle)): ?>
                <p class="section-subtitle"><?= htmlspecialchars($subtitle) ?></p>
            <?php endif; ?>
        </div>

        <div class="projects-grid">
            <?php foreach ($projects as $proj): 
                $isFeatured = !empty($proj['featured']);
            ?>
                <article class="project-card <?= $isFeatured ? 'project-card-featured' : '' ?>">
                    <div class="project-card-inner">
                        <?php if (!empty($proj['badge'])): ?>
                            <div class="project-badge-wrapper">
                                <span class="project-badge"><?= htmlspecialchars($proj['badge']) ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="project-links">
                            <?php if (!empty($proj['github_repo'])): ?>
                                <a href="<?= htmlspecialchars($proj['github_repo']) ?>" target="_blank" rel="noopener noreferrer" class="icon-link" aria-label="GitHub Repository">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path></svg>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($proj['url'])): ?>
                                <a href="<?= htmlspecialchars($proj['url']) ?>" target="_blank" rel="noopener noreferrer" class="icon-link" aria-label="Live Application">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                </a>
                            <?php endif; ?>
                        </div>

                        <h3 class="project-title"><?= htmlspecialchars($proj['project_name']) ?></h3>
                        <?php if (!empty($proj['subtitle'])): ?>
                            <div class="project-subtitle"><?= htmlspecialchars($proj['subtitle']) ?></div>
                        <?php endif; ?>

                        <p class="project-desc"><?= htmlspecialchars($proj['description']) ?></p>

                        <div class="project-tech-tags">
                            <?php 
                            $tags = array_map('trim', explode(',', $proj['technologies'] ?? ''));
                            foreach ($tags as $tag): 
                                if ($tag !== ''): ?>
                                    <span class="tech-tag"><?= htmlspecialchars($tag) ?></span>
                            <?php endif; endforeach; ?>
                        </div>

                        <div class="project-footer">
                            <?php if (!empty($proj['url'])): ?>
                                <a href="<?= htmlspecialchars($proj['url']) ?>" target="_blank" rel="noopener noreferrer" class="project-action-btn">
                                    <span>Visit Live</span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17l9.2-9.2M17 17V8H8"/></svg>
                                </a>
                            <?php elseif (!empty($proj['github_repo'])): ?>
                                <a href="<?= htmlspecialchars($proj['github_repo']) ?>" target="_blank" rel="noopener noreferrer" class="project-action-btn">
                                    <span>View Repository</span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17l9.2-9.2M17 17V8H8"/></svg>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
