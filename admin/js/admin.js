/**
 * Clein.dev — CMS Admin Dashboard Interactions
 * Pure Vanilla JavaScript • Tab Switching, Modals, & UI Controls
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Tab Switching & Hash Navigation
    const navItems = document.querySelectorAll('.nav-item[data-tab]');
    const tabPanels = document.querySelectorAll('.tab-panel');
    const pageTitle = document.getElementById('activePageTitle');
    const sidebar = document.getElementById('adminSidebar');
    const mobileToggle = document.getElementById('mobileNavToggle');

    const switchTab = (tabId) => {
        // Hide all panels, remove active from all nav items
        tabPanels.forEach(panel => panel.classList.remove('active'));
        navItems.forEach(item => item.classList.remove('active'));

        const targetPanel = document.getElementById(`tab-${tabId}`);
        const targetNav = document.querySelector(`.nav-item[data-tab="${tabId}"]`);

        if (targetPanel && targetNav) {
            targetPanel.classList.add('active');
            targetNav.classList.add('active');

            // Update topbar title
            const labelText = targetNav.querySelector('.nav-label-text')?.textContent || 'Dashboard';
            if (pageTitle) pageTitle.textContent = labelText;

            // Sync URL hash
            history.replaceState(null, null, `#${tabId}`);
        }

        // Close sidebar on mobile after clicking
        if (sidebar && window.innerWidth <= 992) {
            sidebar.classList.remove('open');
        }
    };

    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const tabId = item.getAttribute('data-tab');
            switchTab(tabId);
        });
    });

    // Check initial hash
    const initialHash = window.location.hash.replace('#', '');
    if (initialHash && document.getElementById(`tab-${initialHash}`)) {
        switchTab(initialHash);
    } else {
        switchTab('overview');
    }

    // 2. Mobile Sidebar Toggle
    if (mobileToggle && sidebar) {
        mobileToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });

        // Close when clicking outside
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 992 && !sidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });
    }

    // 3. Modal Dialog Triggers
    const openModalBtns = document.querySelectorAll('[data-modal-open]');
    const closeModalBtns = document.querySelectorAll('[data-modal-close]');
    const modalOverlays = document.querySelectorAll('.modal-overlay');

    const openModal = (modalId) => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('open');
            document.body.style.overflow = 'hidden';
        }
    };

    const closeModal = (modal) => {
        if (modal) {
            modal.classList.remove('open');
            document.body.style.overflow = '';
        }
    };

    openModalBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const modalId = btn.getAttribute('data-modal-open');
            openModal(modalId);
        });
    });

    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = btn.closest('.modal-overlay');
            closeModal(modal);
        });
    });

    modalOverlays.forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                closeModal(overlay);
            }
        });
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            modalOverlays.forEach(overlay => closeModal(overlay));
        }
    });

    // 4. Theme Selector Visual Cards
    const themeCards = document.querySelectorAll('.theme-card');
    themeCards.forEach(card => {
        card.addEventListener('click', () => {
            themeCards.forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            const radio = card.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;

            // Update topbar pill preview
            const themeName = card.getAttribute('data-theme-val') || 'monochrome';
            const themeLabel = document.getElementById('currentThemeName');
            if (themeLabel) themeLabel.textContent = themeName.charAt(0).toUpperCase() + themeName.slice(1);
        });
    });

    // 5. Template Form Feedback (Demonstration of visual save actions)
    const forms = document.querySelectorAll('.admin-form');
    forms.forEach(form => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span>Saved!</span>';
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }, 1500);
            }
        });
    });
});
