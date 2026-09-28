<?php
/**
 * Section: Contact & Inquiries
 * Receives: $sec, $contacts
 */
$kicker = !empty($sec['kicker']) ? $sec['kicker'] : 'Initiate Collaboration';
$title = !empty($sec['title']) ? $sec['title'] : 'Get In Touch';
$subtitle = !empty($sec['subtitle']) ? $sec['subtitle'] : 'Have a project in mind, interested in high-concurrency systems, or exploring full-time opportunities? Feel free to reach out.';
?>
<section id="contact" class="section section-alt">
    <div class="container">
        <div class="section-header">
            <span class="section-kicker"><?= htmlspecialchars($kicker) ?></span>
            <h2 class="section-title"><?= htmlspecialchars($title) ?></h2>
            <?php if (!empty($subtitle)): ?>
                <p class="section-subtitle"><?= htmlspecialchars($subtitle) ?></p>
            <?php endif; ?>
        </div>

        <!-- 2-Column Split: Directory List + Quick Message Form -->
        <div class="contact-split-grid">
            <!-- Left: Direct Channels Directory -->
            <div class="contact-directory-card">
                <div class="contact-directory-header">
                    <div>
                        <h3 class="directory-title">Direct Channels</h3>
                        <p class="directory-sub">Reach out directly via email, phone, or professional networks.</p>
                    </div>
                </div>

                <div class="contact-list">
                    <?php foreach ($contacts as $contact): 
                        $nameLower = strtolower($contact['contact_name'] ?? '');
                        $type = $contact['contact_type'] ?? '';
                    ?>
                        <div class="contact-row">
                            <div class="contact-row-left">
                                <div class="contact-row-icon">
                                    <?php if ($type === 'email'): ?>
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                    <?php elseif ($type === 'phone_no'): ?>
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    <?php elseif (str_contains($nameLower, 'linkedin')): ?>
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>
                                    <?php elseif (str_contains($nameLower, 'github')): ?>
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/></svg>
                                    <?php else: ?>
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    <?php endif; ?>
                                </div>
                                <div class="contact-row-text">
                                    <div class="contact-row-label"><?= htmlspecialchars($contact['contact_name']) ?></div>
                                    <div class="contact-row-value"><?= htmlspecialchars($contact['contact_info']) ?></div>
                                </div>
                            </div>

                            <div class="contact-row-actions">
                                <?php if ($type === 'email'): ?>
                                    <button type="button" class="btn-action copy-btn" data-copy="<?= htmlspecialchars($contact['contact_info']) ?>" title="Copy email address">
                                        <span>Copy</span>
                                    </button>
                                    <a href="<?= htmlspecialchars($contact['href']) ?>" class="btn-action btn-action-primary">
                                        <span>Write Email</span>
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17l9.2-9.2M17 17V8H8"/></svg>
                                    </a>
                                <?php elseif ($type === 'phone_no'): ?>
                                    <a href="<?= htmlspecialchars($contact['href']) ?>" class="btn-action btn-action-primary">
                                        <span>Call</span>
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17l9.2-9.2M17 17V8H8"/></svg>
                                    </a>
                                <?php elseif ($type === 'url' && !empty($contact['href']) && $contact['href'] !== '#'): ?>
                                    <a href="<?= htmlspecialchars($contact['href']) ?>" target="_blank" rel="noopener noreferrer" class="btn-action btn-action-primary">
                                        <span>Visit</span>
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17l9.2-9.2M17 17V8H8"/></svg>
                                    </a>
                                <?php else: ?>
                                    <span class="contact-row-badge">UTC+8 (PST)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Right: Quick Message Form -->
            <div class="contact-form-card">
                <div class="contact-form-header">
                    <h3 class="directory-title">Send a Direct Message</h3>
                    <p class="directory-sub">Fill out the fields below for project inquiries or software consultations.</p>
                </div>

                <form class="contact-form" id="contactForm">
                    <div class="form-row-2">
                        <div class="form-group">
                            <label for="form_name" class="form-label">Your Name</label>
                            <input type="text" id="form_name" name="name" class="form-input" placeholder="e.g. Alex Rivera" required>
                        </div>
                        <div class="form-group">
                            <label for="form_email" class="form-label">Your Email</label>
                            <input type="email" id="form_email" name="email" class="form-input" placeholder="name@example.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="form_subject" class="form-label">Subject</label>
                        <input type="text" id="form_subject" name="subject" class="form-input" placeholder="Project Inquiry / Job Opportunity" required>
                    </div>

                    <div class="form-group">
                        <label for="form_message" class="form-label">Message</label>
                        <textarea id="form_message" name="message" class="form-textarea" rows="4" placeholder="Briefly describe what you have in mind..." required></textarea>
                    </div>

                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary btn-submit" id="contactSubmitBtn">
                            <span>Send Message</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                        </button>
                        <div class="form-status" id="formStatus" aria-live="polite"></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
