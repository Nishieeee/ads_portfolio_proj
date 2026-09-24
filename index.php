<?php
// Load data from portfolio_data.json (strictly mirrors portfolio_cms.sql tables)
$dataPath = __DIR__ . '/portfolio_data.json';
$portfolioData = [];
if (file_exists($dataPath)) {
    $portfolioData = json_decode(file_get_contents($dataPath), true) ?: [];
}

$basicInfo = $portfolioData['my_basic_info'] ?? [];
$contacts = $portfolioData['my_contact_info'] ?? [];
$experiences = $portfolioData['my_experience'] ?? [];
$projects = $portfolioData['my_projects'] ?? [];
$skillsGrouped = $portfolioData['my_skills'] ?? [];
$educations = $portfolioData['my_education'] ?? [];
$certificates = $portfolioData['my_certificates'] ?? [];

$nameParts = array_filter([
    $basicInfo['first_name'] ?? 'Jhon Clein',
    $basicInfo['middle_name'] ?? '',
    $basicInfo['last_name'] ?? 'Pagarogan'
], fn($part) => trim($part) !== '');
$fullName = implode(' ', $nameParts);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($fullName) ?> — Full-Stack Developer</title>
    <meta name="description" content="<?= htmlspecialchars($basicInfo['tagline'] ?? 'Full-Stack Developer Portfolio') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="Public/css/main.css" rel="stylesheet">
</head>
<body>
    <!-- Sticky Glass Navigation -->
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
                    <li><a href="#about" class="nav-link">About</a></li>
                    <li><a href="#experience" class="nav-link">Experience</a></li>
                    <li><a href="#projects" class="nav-link">Projects</a></li>
                    <li><a href="#skills" class="nav-link">Skills</a></li>
                    <li><a href="#education" class="nav-link">Education</a></li>
                    <li><a href="#contact" class="nav-link">Contact</a></li>
                </ul>
            </nav>

            <div class="nav-cta-wrapper">
                <a href="#contact" class="btn btn-sm btn-outline">Let's Connect</a>
            </div>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section id="hero" class="hero-section">
            <div class="container hero-container">
                <div class="hero-badge">
                    <span class="pulse-dot"></span> Available for Select Projects & Full-Time Roles
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

        <!-- 1. About Section -->
        <section id="about" class="section">
            <div class="container">
                <div class="section-header">
                    <span class="section-kicker">Get to Know Me</span>
                    <h2 class="section-title">About Me</h2>
                </div>

                <div class="about-grid">
                    <div class="about-image-card">
                        <div class="image-wrapper">
                            <img src="<?= htmlspecialchars($basicInfo['avatar_url'] ?? 'Public/assets/images/image.png') ?>" alt="<?= htmlspecialchars($fullName) ?>" id="my-pic" class="avatar-img">
                            <div class="image-caption-card">
                                <span class="status-indicator"></span>
                                <div>
                                    <div class="status-title">Computer Science</div>
                                    <div class="status-sub">Western Mindanao State Univ.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="about-text-card">
                        <h3 class="about-heading"><?= htmlspecialchars($basicInfo['bio_greeting'] ?? "Hi, I'm Clein!") ?></h3>
                        <div class="about-paragraphs">
                            <?php if (!empty($basicInfo['bio_paragraphs'])): ?>
                                <?php foreach ($basicInfo['bio_paragraphs'] as $para): ?>
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
                                <span class="fact-number">5+</span>
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

        <!-- 2. Experience Section -->
        <section id="experience" class="section section-alt">
            <div class="container">
                <div class="section-header">
                    <span class="section-kicker">Career Path</span>
                    <h2 class="section-title">Work Experience</h2>
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

        <!-- 3. Key Projects Section -->
        <section id="projects" class="section">
            <div class="container">
                <div class="section-header">
                    <span class="section-kicker">Featured Engineering</span>
                    <h2 class="section-title">Key Projects</h2>
                    <p class="section-subtitle">Real-world applications demonstrating high concurrency, robust architecture, and modern interfaces.</p>
                </div>

                <div class="projects-grid">
                    <?php foreach ($projects as $proj): 
                        $isFeatured = !empty($proj['featured']);
                    ?>
                        <article class="project-card <?= $isFeatured ? 'project-card-featured' : '' ?>">
                            <div class="project-card-inner">
                                <div class="project-top-meta">
                                    <?php if (!empty($proj['badge'])): ?>
                                        <span class="project-badge"><?= htmlspecialchars($proj['badge']) ?></span>
                                    <?php endif; ?>
                                    <div class="project-links">
                                        <?php if (!empty($proj['github_repo'])): ?>
                                            <a href="<?= htmlspecialchars($proj['github_repo']) ?>" target="_blank" rel="noopener noreferrer" class="icon-link" aria-label="GitHub Repository">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path></svg>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($proj['url'])): ?>
                                            <a href="<?= htmlspecialchars($proj['url']) ?>" target="_blank" rel="noopener noreferrer" class="icon-link" aria-label="Live Application">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                            </a>
                                        <?php endif; ?>
                                    </div>
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

        <!-- 4. Skills Section -->
        <section id="skills" class="section section-alt">
            <div class="container">
                <div class="section-header">
                    <span class="section-kicker">Technical Stack</span>
                    <h2 class="section-title">Skills & Technologies</h2>
                    <p class="section-subtitle">Structured by domains, from backend systems and database engines to client interfaces.</p>
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

        <!-- 5. Education & Certifications Section -->
        <section id="education" class="section">
            <div class="container">
                <div class="section-header">
                    <span class="section-kicker">Academic & Achievements</span>
                    <h2 class="section-title">Education & Certifications</h2>
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
                                <p class="card-desc"><?= htmlspecialchars($cert['descripton'] ?? '') ?></p>
                                
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

        <!-- 6. Contact Section -->
        <section id="contact" class="section section-alt">
            <div class="container">
                <div class="section-header">
                    <span class="section-kicker">Initiate Collaboration</span>
                    <h2 class="section-title">Get In Touch</h2>
                    <p class="section-subtitle">Have a project in mind, interested in high-concurrency systems, or exploring full-time opportunities? Feel free to reach out.</p>
                </div>

                <div class="contact-card-grid">
                    <?php foreach ($contacts as $contact): ?>
                        <div class="contact-box">
                            <div class="contact-type-label"><?= htmlspecialchars($contact['contact_name']) ?></div>
                            <div class="contact-main-info">
                                <?php if ($contact['contact_type'] === 'email'): ?>
                                    <a href="<?= htmlspecialchars($contact['href']) ?>" class="contact-link"><?= htmlspecialchars($contact['label']) ?></a>
                                <?php elseif ($contact['contact_type'] === 'phone_no'): ?>
                                    <a href="<?= htmlspecialchars($contact['href']) ?>" class="contact-link"><?= htmlspecialchars($contact['label']) ?></a>
                                <?php elseif ($contact['contact_type'] === 'url' && !empty($contact['href']) && $contact['href'] !== '#'): ?>
                                    <a href="<?= htmlspecialchars($contact['href']) ?>" target="_blank" rel="noopener noreferrer" class="contact-link inline-flex-link">
                                        <span><?= htmlspecialchars($contact['label']) ?></span>
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17l9.2-9.2M17 17V8H8"/></svg>
                                    </a>
                                <?php else: ?>
                                    <span class="contact-text"><?= htmlspecialchars($contact['label']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="contact-cta-panel">
                    <h3>Ready to build something impactful?</h3>
                    <p>Send an email directly or connect on LinkedIn to discuss architecture, development, or collaborations.</p>
                    <a href="mailto:<?= htmlspecialchars($basicInfo['my_contact_info'][0]['contact_info'] ?? 'pagaroganjhonclein@gmail.com') ?>" class="btn btn-primary btn-lg">
                        <span>Send an Email</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer id="footer_main">
        <div class="container footer-container">
            <div class="footer-left">
                <div class="footer-brand">&lt;<?= htmlspecialchars($fullName) ?>/&gt;</div>
                <p class="footer-sub">Designed & built with clean vanilla PHP, CSS, and JavaScript. Prepared for custom CMS integration.</p>
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

    <script src="Public/js/main.js"></script>
</body>
</html>
