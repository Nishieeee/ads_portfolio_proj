<?php
/**
 * Layout: Footer
 * Receives: $fullName
 */
?>
<footer id="footer_main">
    <div class="container footer-container">
        <div class="footer-left">
            <div class="footer-brand">&lt;<?= htmlspecialchars($fullName) ?>/&gt;</div>
            <p class="footer-sub">Engineered with clean vanilla PHP, CSS, and JavaScript. Powered by modular hybrid CMS architecture.</p>
        </div>
        <div class="footer-right">
            <a href="#hero" class="back-to-top" id="backToTop">
                <span>Back to Top</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 15l-6-6-6 6"/></svg>
            </a>
            <div class="copyright">&copy; <?= date('Y') ?> Jhon Clein Pagarogan. All rights reserved.</div>
        </div>
    </div>
</footer>
