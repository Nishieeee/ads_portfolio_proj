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

    // 5. Contact Form Submission (Live REST API Integration)
    const contactForm = document.getElementById('contactForm');
    const formStatus = document.getElementById('formStatus');
    const submitBtn = document.getElementById('contactSubmitBtn');

    if (contactForm && formStatus && submitBtn) {
        // Resolve dynamic API Base URL
        const API_BASE = (() => {
            let path = window.location.pathname;
            if (path.endsWith('.php') || path.endsWith('.html')) {
                path = path.substring(0, path.lastIndexOf('/'));
            }
            return path.replace(/\/+$/, '') + '/api';
        })();

        contactForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const nameInput = document.getElementById('form_name');
            const emailInput = document.getElementById('form_email');
            const subjectInput = document.getElementById('form_subject');
            const messageInput = document.getElementById('form_message');

            const senderName = nameInput ? nameInput.value.trim() : '';
            const senderEmail = emailInput ? emailInput.value.trim() : '';
            const subject = subjectInput ? subjectInput.value.trim() : '';
            const message = messageInput ? messageInput.value.trim() : '';

            // Client-side validation
            if (!senderName || !senderEmail || !subject || !message) {
                formStatus.className = 'form-status error';
                formStatus.textContent = 'Please fill in all required fields.';
                return;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(senderEmail)) {
                formStatus.className = 'form-status error';
                formStatus.textContent = 'Please provide a valid email address.';
                return;
            }

            if (message.length < 10) {
                formStatus.className = 'form-status error';
                formStatus.textContent = 'Message must be at least 10 characters long.';
                return;
            }

            const originalBtnHtml = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span>Sending...</span>';
            formStatus.className = 'form-status';
            formStatus.textContent = '';

            try {
                const response = await fetch(`${API_BASE}/inquiries`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        sender_name: senderName,
                        sender_email: senderEmail,
                        subject: subject,
                        message: message
                    })
                });

                const data = await response.json().catch(() => null);

                if (response.ok && data && data.status === 'success') {
                    contactForm.reset();
                    formStatus.className = 'form-status success';
                    formStatus.textContent = data.message || 'Thank you! Your message has been sent successfully.';

                    setTimeout(() => {
                        formStatus.textContent = '';
                    }, 6000);
                } else {
                    const errorMsg = (data && (data.message || data.error)) || 'Failed to submit inquiry. Please try again.';
                    formStatus.className = 'form-status error';
                    formStatus.textContent = errorMsg;
                }
            } catch (err) {
                console.error('Contact form submission error:', err);
                formStatus.className = 'form-status error';
                formStatus.textContent = 'Network error. Please check your connection and try again.';
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
            }
        });
    }
});

