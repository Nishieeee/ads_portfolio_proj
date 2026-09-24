/**
 * Clein.dev — Vanilla JavaScript Interactions
 * Header scroll state, mobile menu toggle, and active navigation spy
 */

document.addEventListener('DOMContentLoaded', () => {
    const header = document.getElementById('header_main');
    const navToggle = document.getElementById('navToggle');
    const navContainer = document.getElementById('nav-container');
    const navLinks = document.querySelectorAll('.nav-link');
    const sections = document.querySelectorAll('section[id]');

    // 1. Sticky Header Scroll Effect
    const handleScroll = () => {
        if (window.scrollY > 30) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    };

    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll(); // Initial check

    // 2. Mobile Navigation Toggle
    if (navToggle && navContainer) {
        navToggle.addEventListener('click', () => {
            const isOpen = navContainer.classList.toggle('open');
            navToggle.setAttribute('aria-expanded', isOpen);
        });

        // Close mobile nav when clicking a link
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                navContainer.classList.remove('open');
                navToggle.setAttribute('aria-expanded', 'false');
            });
        });

        // Close mobile nav when clicking outside
        document.addEventListener('click', (e) => {
            if (!navContainer.contains(e.target) && !navToggle.contains(e.target)) {
                navContainer.classList.remove('open');
                navToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // 3. Scroll Spy (Active Nav Link)
    const observerOptions = {
        root: null,
        rootMargin: '-20% 0px -65% 0px',
        threshold: 0
    };

    const sectionObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const activeId = entry.target.getAttribute('id');
                navLinks.forEach(link => {
                    if (link.getAttribute('href') === `#${activeId}`) {
                        link.classList.add('active');
                    } else {
                        link.classList.remove('active');
                    }
                });
            }
        });
    }, observerOptions);

    sections.forEach(section => sectionObserver.observe(section));

    // 4. One-Click Copy to Clipboard
    const copyButtons = document.querySelectorAll('.copy-btn');
    copyButtons.forEach(btn => {
        btn.addEventListener('click', async () => {
            const textToCopy = btn.getAttribute('data-copy');
            if (!textToCopy) return;

            try {
                await navigator.clipboard.writeText(textToCopy);
                const originalText = btn.innerHTML;
                btn.innerHTML = '<span>Copied!</span>';
                btn.classList.add('copied');

                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.classList.remove('copied');
                }, 1800);
            } catch (err) {
                // Fallback for older browsers / insecure origins
                const textarea = document.createElement('textarea');
                textarea.value = textToCopy;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.select();
                try {
                    document.execCommand('copy');
                    btn.innerHTML = '<span>Copied!</span>';
                    setTimeout(() => {
                        btn.innerHTML = '<span>Copy</span>';
                    }, 1800);
                } catch (e) {
                    console.error('Copy failed:', e);
                }
                document.body.removeChild(textarea);
            }
        });
    });

    // 5. Contact Form Submission (Template Interaction)
    const contactForm = document.getElementById('contactForm');
    const formStatus = document.getElementById('formStatus');
    const submitBtn = document.getElementById('contactSubmitBtn');

    if (contactForm && formStatus && submitBtn) {
        contactForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const originalBtnHtml = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span>Sending...</span>';
            formStatus.className = 'form-status';
            formStatus.textContent = '';

            setTimeout(() => {
                contactForm.reset();
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
                formStatus.className = 'form-status success';
                formStatus.textContent = 'Message sent! (Ready for CMS integration)';

                setTimeout(() => {
                    formStatus.textContent = '';
                }, 4000);
            }, 600);
        });
    }
});

