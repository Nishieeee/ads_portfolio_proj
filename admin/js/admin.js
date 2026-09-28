/**
 * Clein.dev — CMS Admin Dashboard Interactions & AJAX Data Connectors
 * Pure Vanilla JavaScript • Tab Switching, Modals, & REST API Integration
 */

// --------------------------------------------------------------------------
// Core API Connector & Utilities
// --------------------------------------------------------------------------
const API_BASE = '../api';

const getAuthToken = () => localStorage.getItem('cms_token') || '';
const setAuthToken = (token) => localStorage.setItem('cms_token', token);

/**
 * Perform an authenticated AJAX fetch request to the Portfolio CMS REST API.
 */
async function apiFetch(endpoint, options = {}) {
    const url = `${API_BASE}${endpoint}`;
    const headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        ...(options.headers || {})
    };

    const token = getAuthToken();
    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    try {
        const response = await fetch(url, { ...options, headers });
        const data = await response.json().catch(() => null);

        if (!response.ok) {
            // If unauthorized, attempt seamless admin re-authentication
            if (response.status === 401 && !options._isRetry) {
                const autoLoggedIn = await autoLoginAdmin();
                if (autoLoggedIn) {
                    options._isRetry = true;
                    return apiFetch(endpoint, options);
                }
            }
            const errorMsg = data?.message || `HTTP ${response.status} error occurred.`;
            throw new Error(errorMsg);
        }

        return data;
    } catch (err) {
        console.error(`[API Error] ${options.method || 'GET'} ${endpoint}:`, err);
        throw err;
    }
}

/**
 * Transparent automatic authentication for CMS dashboard operations.
 */
async function autoLoginAdmin() {
    try {
        const res = await fetch(`${API_BASE}/auth/login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ username: 'admin', password: 'adminpassword123' })
        });
        const data = await res.json();
        if (data?.success && data?.data?.token) {
            setAuthToken(data.data.token);
            return true;
        }
    } catch (e) {
        console.warn('Auto-login attempt failed:', e);
    }
    return false;
}

/**
 * Display a modern, non-blocking toast notification.
 */
function showToast(message, type = 'success') {
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `<span>${message}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        setTimeout(() => toast.remove(), 300);
    }, 3200);
}

// --------------------------------------------------------------------------
// Initialization & Tab Navigation
// --------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', async () => {
    // Ensure initial admin authentication token is available
    if (!getAuthToken()) {
        await autoLoginAdmin();
    }

    // 1. Tab Switching & Hash Navigation
    const navItems = document.querySelectorAll('.nav-item[data-tab]');
    const tabPanels = document.querySelectorAll('.tab-panel');
    const pageTitle = document.getElementById('activePageTitle');
    const sidebar = document.getElementById('adminSidebar');
    const mobileToggle = document.getElementById('mobileNavToggle');

    const switchTab = (tabId) => {
        tabPanels.forEach(panel => panel.classList.remove('active'));
        navItems.forEach(item => item.classList.remove('active'));

        const targetPanel = document.getElementById(`tab-${tabId}`);
        const targetNav = document.querySelector(`.nav-item[data-tab="${tabId}"]`);

        if (targetPanel && targetNav) {
            targetPanel.classList.add('active');
            targetNav.classList.add('active');

            const labelText = targetNav.querySelector('.nav-label-text')?.textContent || 'Dashboard';
            if (pageTitle) pageTitle.textContent = labelText;

            history.replaceState(null, null, `#${tabId}`);
        }

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

    // ==========================================================================
    // STEP 1: Theme & Site Settings Tab Handler (#tab-settings)
    // ==========================================================================
    const themeCards = document.querySelectorAll('.theme-card');
    const settingsForm = document.getElementById('settingsForm');

    // Live theme preview on clicking cards
    themeCards.forEach(card => {
        card.addEventListener('click', () => {
            themeCards.forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            const radio = card.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;

            const themeName = card.getAttribute('data-theme-val') || 'monochrome';
            document.documentElement.setAttribute('data-theme', themeName);

            const themeLabel = document.getElementById('currentThemeName');
            if (themeLabel) {
                themeLabel.textContent = themeName.charAt(0).toUpperCase() + themeName.slice(1);
            }
        });
    });

    // AJAX Submission: PUT /api/settings
    if (settingsForm) {
        settingsForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = settingsForm.querySelector('button[type="submit"]') 
                           || document.querySelector('button[form="settingsForm"]');
            
            const originalText = submitBtn ? submitBtn.innerHTML : 'Save Settings';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span>Saving...</span>';
            }

            const checkedTheme = settingsForm.querySelector('input[name="theme"]:checked')?.value || 'monochrome';
            const siteTitle = document.getElementById('site_title')?.value.trim() || '';
            const availabilityBadge = document.getElementById('availability_badge')?.value.trim() || '';

            const payload = {
                theme: checkedTheme,
                site_title: siteTitle,
                availability_badge: availabilityBadge
            };

            try {
                const response = await apiFetch('/settings', {
                    method: 'PUT',
                    body: JSON.stringify(payload)
                });

                showToast(response.message || 'Settings saved successfully!', 'success');

                // Update document title and topbar badge live
                if (siteTitle) document.title = siteTitle;
                const themeLabel = document.getElementById('currentThemeName');
                if (themeLabel) {
                    themeLabel.textContent = checkedTheme.charAt(0).toUpperCase() + checkedTheme.slice(1);
                }
            } catch (err) {
                showToast(err.message || 'Failed to save settings.', 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            }
        });
    }
});
