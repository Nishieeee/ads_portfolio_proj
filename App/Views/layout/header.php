<?php
/**
 * Layout: Header & Navigation
 * Receives: $fullName, $activeSections
 */
?>
<header id="header_main">
    <div class="nav-wrapper">
        <a href="#hero" class="logo-link">
            <span class="logo-bracket">&lt;</span>Clein<span class="logo-accent">.dev</span><span class="logo-bracket">/&gt;</span>
        </a>

        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation" aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav id="nav-container" aria-label="Main Navigation">
            <ul id="nav-link-container">
                <?php foreach ($activeSections as $sec): ?>
                    <li>
                        <a href="#<?= htmlspecialchars($sec['section_key']) ?>" class="nav-link">
                            <?= htmlspecialchars($sec['nav_label']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <div class="nav-cta-wrapper">
            <a href="#contact" class="btn btn-sm btn-outline">Let's Connect</a>
        </div>
    </div>
</header>
