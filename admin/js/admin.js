/**
 * Clein.dev — CMS Admin Dashboard Interactions & AJAX Data Connectors
 * Pure Vanilla JavaScript • Tab Switching, Modals, & REST API Integration
 */

// --------------------------------------------------------------------------
// Core API Connector & Utilities
// --------------------------------------------------------------------------
const API_BASE = (() => {
    const loc = window.location.pathname;
    const adminIdx = loc.indexOf('/admin');
    if (adminIdx !== -1) {
        return loc.substring(0, adminIdx) + '/api';
    }
    return '../api';
})();

const getAuthToken = () => localStorage.getItem('cms_token') || '';
const setAuthToken = (token) => localStorage.setItem('cms_token', token);

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
            return data.data.token;
        }
    } catch (e) {
        console.warn('Auto-login attempt failed:', e);
    }
    return null;
}

/**
 * Perform an authenticated AJAX fetch request to the Portfolio CMS REST API.
 */
async function apiFetch(endpoint, options = {}) {
    const url = `${API_BASE}${endpoint}`;
    
    let token = getAuthToken();
    if (!token) {
        token = await autoLoginAdmin();
    }

    const headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        ...(options.headers || {})
    };

    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    try {
        const response = await fetch(url, { ...options, headers });
        const data = await response.json().catch(() => null);

        if (!response.ok) {
            // If unauthorized, clear cached token, attempt re-login, and retry once
            if (response.status === 401 && !options._isRetry) {
                localStorage.removeItem('cms_token');
                const newToken = await autoLoginAdmin();
                if (newToken) {
                    const retryOptions = {
                        ...options,
                        _isRetry: true,
                        headers: {
                            ...(options.headers || {}),
                            'Authorization': `Bearer ${newToken}`
                        }
                    };
                    return apiFetch(endpoint, retryOptions);
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

    const escapeHtml = (str) => {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    };

    /**
     * Universal Delete Confirmation Modal Handler
     * Opens #deleteConfirmModal, displays dynamic title/message, and executes async onConfirm
     *
     * @param {Object} options
     * @param {string} options.title - Modal title (e.g. "Delete Project")
     * @param {string} options.message - Confirmation HTML message
     * @param {Function} options.onConfirm - Async function to execute on confirmation
     */
    const openDeleteModal = ({ title = 'Confirm Deletion', message = 'Are you sure you want to delete this item?', onConfirm }) => {
        const modal = document.getElementById('deleteConfirmModal');
        const titleEl = document.getElementById('deleteModalTitle');
        const messageEl = document.getElementById('deleteModalMessage');
        const confirmBtn = document.getElementById('confirmDeleteModalBtn');

        if (!modal || !confirmBtn) return;

        if (titleEl) titleEl.textContent = title;
        if (messageEl) messageEl.innerHTML = message;

        const defaultBtnHtml = `
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            <span>Delete</span>
        `;
        confirmBtn.innerHTML = defaultBtnHtml;
        confirmBtn.disabled = false;

        // Replace button with clean clone to clear previous listeners
        const cleanConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(cleanConfirmBtn, confirmBtn);

        cleanConfirmBtn.addEventListener('click', async () => {
            cleanConfirmBtn.disabled = true;
            cleanConfirmBtn.innerHTML = '<span>Deleting...</span>';

            try {
                if (typeof onConfirm === 'function') {
                    await onConfirm();
                }
                closeModal(modal);
            } catch (err) {
                cleanConfirmBtn.disabled = false;
                cleanConfirmBtn.innerHTML = defaultBtnHtml;
                showToast(err.message || 'Failed to complete deletion.', 'error');
            }
        });

        openModal('deleteConfirmModal');
    };

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

    // ==========================================================================
    // STEP 2: Sections & Layout Tab Handler (#tab-sections)
    // ==========================================================================
    const sectionsForm = document.getElementById('sectionsForm');
    const visibilityToggles = document.querySelectorAll('.section-visibility-toggle');

    // 1. Instant Section Visibility Toggle: PATCH /api/sections/{id}/toggle-visibility
    visibilityToggles.forEach(toggle => {
        toggle.addEventListener('change', async () => {
            const secId = toggle.getAttribute('data-id');
            const secName = toggle.getAttribute('data-name') || 'Section';
            const isVisible = toggle.checked;

            try {
                await apiFetch(`/sections/${secId}/toggle-visibility`, {
                    method: 'PATCH',
                    body: JSON.stringify({ is_visible: isVisible })
                });
                const statusText = isVisible ? 'visible' : 'hidden';
                showToast(`"${secName}" section is now ${statusText}.`, 'success');
            } catch (err) {
                toggle.checked = !isVisible;
                showToast(err.message || 'Failed to toggle section visibility.', 'error');
            }
        });
    });

    // 2. Sections Order & Content Save: POST /api/sections/reorder & PUT /api/sections/{id}
    if (sectionsForm) {
        sectionsForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = sectionsForm.querySelector('button[type="submit"]')
                           || document.querySelector('button[form="sectionsForm"]');

            const originalText = submitBtn ? submitBtn.innerHTML : 'Save Section Order';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span>Saving Layout...</span>';
            }

            const rows = sectionsForm.querySelectorAll('.section-row');
            const reorderList = [];
            const updatePromises = [];

            rows.forEach(row => {
                const id = parseInt(row.getAttribute('data-id'), 10);
                const orderIndex = parseInt(row.querySelector('.section-order-input')?.value || '1', 10);
                const navLabel = row.querySelector('.section-nav-input')?.value.trim() || '';
                const kicker = row.querySelector('.section-kicker-input')?.value.trim() || '';
                const title = row.querySelector('.section-title-input')?.value.trim() || '';
                const isVisible = row.querySelector('.section-visibility-toggle')?.checked ? 1 : 0;

                if (id) {
                    reorderList.push({ id, order_index: orderIndex });
                    updatePromises.push(
                        apiFetch(`/sections/${id}`, {
                            method: 'PUT',
                            body: JSON.stringify({
                                nav_label: navLabel,
                                kicker: kicker,
                                title: title,
                                order_index: orderIndex,
                                is_visible: isVisible
                            })
                        })
                    );
                }
            });

            try {
                // Update section content details
                await Promise.all(updatePromises);

                // Update section order sequence
                if (reorderList.length > 0) {
                    await apiFetch('/sections/reorder', {
                        method: 'POST',
                        body: JSON.stringify({ sections: reorderList })
                    });
                }

                showToast('Section order and layout updated successfully!', 'success');
            } catch (err) {
                showToast(err.message || 'Failed to update section layout.', 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            }
        });
    }

    // ==========================================================================
    // STEP 3: Profile & Bio + Contact Channels Tab Handler (#tab-profile)
    // ==========================================================================
    const profileForm = document.getElementById('profileForm');

    // 1. Profile & Bio Save: PUT /api/basic-info
    if (profileForm) {
        profileForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = profileForm.querySelector('button[type="submit"]')
                           || document.querySelector('button[form="profileForm"]');

            const originalText = submitBtn ? submitBtn.innerHTML : 'Save Profile Info';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span>Saving Profile...</span>';
            }

            const payload = {
                first_name: document.getElementById('first_name')?.value.trim() || '',
                middle_name: document.getElementById('middle_name')?.value.trim() || '',
                last_name: document.getElementById('last_name')?.value.trim() || '',
                role_title: document.getElementById('role_title')?.value.trim() || '',
                birth_date: document.getElementById('birth_date')?.value || null,
                avatar_url: document.getElementById('avatar_url')?.value.trim() || '',
                resume_url: document.getElementById('resume_url')?.value.trim() || '',
                tagline: document.getElementById('tagline')?.value.trim() || '',
                bio_paragraphs: document.getElementById('bio_paragraphs')?.value.trim() || ''
            };

            try {
                const res = await apiFetch('/basic-info', {
                    method: 'PUT',
                    body: JSON.stringify(payload)
                });

                showToast(res.message || 'Profile information updated successfully!', 'success');

                const sidebarName = document.querySelector('.admin-user .user-name');
                if (sidebarName && res.data?.full_name) {
                    sidebarName.textContent = res.data.full_name;
                }
            } catch (err) {
                showToast(err.message || 'Failed to update profile info.', 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            }
        });
    }

    // 2. Contact Channels CRUD Handlers
    const contactModal = document.getElementById('contactModal');
    const contactModalForm = document.getElementById('contactModalForm');
    const openAddContactBtn = document.getElementById('openAddContactBtn');
    const contactTableBody = document.getElementById('contactTableBody');

    if (openAddContactBtn && contactModal) {
        openAddContactBtn.addEventListener('click', () => {
            contactModalForm.reset();
            document.getElementById('contact_modal_id').value = '';
            document.getElementById('contactModalTitle').textContent = 'Add Contact Channel';
            openModal('contactModal');
        });
    }

    // Delegate edit and delete on contact table
    if (contactTableBody) {
        contactTableBody.addEventListener('click', async (e) => {
            const editBtn = e.target.closest('.edit-contact-btn');
            const deleteBtn = e.target.closest('.delete-contact-btn');

            if (editBtn) {
                const id = editBtn.getAttribute('data-id');
                const name = editBtn.getAttribute('data-name');
                const type = editBtn.getAttribute('data-type');
                const info = editBtn.getAttribute('data-info');

                document.getElementById('contact_modal_id').value = id;
                document.getElementById('contact_modal_name').value = name;
                document.getElementById('contact_modal_type').value = type;
                document.getElementById('contact_modal_info').value = info;
                document.getElementById('contactModalTitle').textContent = 'Edit Contact Channel';
                openModal('contactModal');
            }

            if (deleteBtn) {
                const id = deleteBtn.getAttribute('data-id');
                const name = deleteBtn.getAttribute('data-name') || 'contact channel';

                openDeleteModal({
                    title: 'Delete Contact Channel',
                    message: `Are you sure you want to delete the <strong>${escapeHtml(name)}</strong> channel?`,
                    onConfirm: async () => {
                        await apiFetch(`/contact-info/${id}`, { method: 'DELETE' });
                        showToast(`Contact channel "${name}" deleted successfully!`, 'success');
                        const row = deleteBtn.closest('.contact-row');
                        if (row) {
                            row.style.opacity = '0';
                            setTimeout(() => row.remove(), 250);
                        }
                    }
                });
            }
        });
    }

    // Save contact channel modal submit: POST or PUT /api/contact-info
    if (contactModalForm) {
        contactModalForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = document.getElementById('contactModalSubmitBtn');
            const originalText = submitBtn ? submitBtn.innerHTML : 'Save Channel';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span>Saving...</span>';
            }

            const id = document.getElementById('contact_modal_id')?.value;
            const name = document.getElementById('contact_modal_name')?.value.trim();
            const type = document.getElementById('contact_modal_type')?.value;
            const info = document.getElementById('contact_modal_info')?.value.trim();

            const payload = { contact_name: name, contact_type: type, contact_info: info };

            try {
                let res;
                if (id) {
                    res = await apiFetch(`/contact-info/${id}`, {
                        method: 'PUT',
                        body: JSON.stringify(payload)
                    });
                    showToast('Contact channel updated successfully!', 'success');
                } else {
                    res = await apiFetch('/contact-info', {
                        method: 'POST',
                        body: JSON.stringify(payload)
                    });
                    showToast('Contact channel added successfully!', 'success');
                }

                // Dynamically refresh or insert row into table
                if (res?.data && contactTableBody) {
                    const rowData = res.data;
                    const existingRow = contactTableBody.querySelector(`.contact-row[data-id="${rowData.id}"]`);
                    const rowHtml = `
                        <td class="cell-primary">${rowData.contact_name}</td>
                        <td><span class="badge">${rowData.contact_type}</span></td>
                        <td style="font-family:var(--font-mono); font-size:0.85rem;">${rowData.contact_info}</td>
                        <td>
                            <div class="cell-actions">
                                <button type="button" class="btn-icon edit-contact-btn" data-id="${rowData.id}" data-name="${rowData.contact_name}" data-type="${rowData.contact_type}" data-info="${rowData.contact_info}" title="Edit">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                </button>
                                <button type="button" class="btn-icon delete-contact-btn" data-id="${rowData.id}" data-name="${rowData.contact_name}" title="Delete" style="color:#ef4444;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </td>
                    `;

                    if (existingRow) {
                        existingRow.innerHTML = rowHtml;
                    } else {
                        const newTr = document.createElement('tr');
                        newTr.className = 'contact-row';
                        newTr.setAttribute('data-id', rowData.id);
                        newTr.innerHTML = rowHtml;
                        contactTableBody.appendChild(newTr);
                    }
                }

                closeModal(contactModal);
            } catch (err) {
                showToast(err.message || 'Failed to save contact channel.', 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            }
        });
    }

    // ==========================================================================
    // STEP 4: Projects Directory Tab Handler (#tab-projects)
    // ==========================================================================
    const projectModal = document.getElementById('projectModal');
    const projectModalForm = document.getElementById('projectModalForm');
    const openAddProjectBtn = document.getElementById('openAddProjectBtn');
    const projectsTableBody = document.getElementById('projectsTableBody');

    // 1. Open Add Project Modal
    if (openAddProjectBtn && projectModal) {
        openAddProjectBtn.addEventListener('click', () => {
            projectModalForm.reset();
            document.getElementById('modal_project_id').value = '';
            document.getElementById('projectModalTitle').textContent = 'Add New Project';
            openModal('projectModal');
        });
    }

    // 2. Delegate Edit and Delete on Projects Table
    if (projectsTableBody) {
        projectsTableBody.addEventListener('click', async (e) => {
            const editBtn = e.target.closest('.edit-project-btn');
            const deleteBtn = e.target.closest('.delete-project-btn');

            if (editBtn) {
                const id = editBtn.getAttribute('data-id');
                let proj = null;
                const rawData = editBtn.getAttribute('data-project');
                if (rawData) {
                    try {
                        proj = JSON.parse(rawData);
                    } catch (err) {
                        console.warn('Failed to parse data-project JSON:', err);
                    }
                }

                if (!proj && id) {
                    try {
                        const res = await apiFetch(`/projects/${id}`);
                        proj = res.data;
                    } catch (err) {
                        showToast('Failed to load project details.', 'error');
                        return;
                    }
                }

                if (proj) {
                    document.getElementById('modal_project_id').value = id || proj.id || '';
                    document.getElementById('modal_project_name').value = proj.project_name || '';
                    document.getElementById('modal_project_sub').value = proj.subtitle || '';
                    document.getElementById('modal_project_desc').value = proj.description || '';
                    document.getElementById('modal_project_tech').value = proj.technologies || '';
                    document.getElementById('modal_project_repo').value = proj.github_repo || '';
                    document.getElementById('modal_project_live').value = proj.url || '';
                    document.getElementById('modal_project_badge').value = proj.badge || '';
                    document.getElementById('projectModalTitle').textContent = 'Edit Project Details';
                    openModal('projectModal');
                }
            }

            if (deleteBtn) {
                const id = deleteBtn.getAttribute('data-id');
                const name = deleteBtn.getAttribute('data-name') || 'project';

                openDeleteModal({
                    title: 'Delete Project',
                    message: `Are you sure you want to delete <strong>${escapeHtml(name)}</strong>?`,
                    onConfirm: async () => {
                        await apiFetch(`/projects/${id}`, { method: 'DELETE' });
                        showToast(`Project "${name}" deleted successfully!`, 'success');
                        const row = deleteBtn.closest('.project-row');
                        if (row) {
                            row.style.opacity = '0';
                            setTimeout(() => row.remove(), 250);
                        }
                    }
                });
            }
        });
    }

    // 3. Save Project Modal Submit: POST or PUT /api/projects
    if (projectModalForm) {
        projectModalForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = document.getElementById('projectModalSubmitBtn');
            const originalText = submitBtn ? submitBtn.innerHTML : 'Save Project';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span>Saving...</span>';
            }

            const id = document.getElementById('modal_project_id')?.value;
            const payload = {
                project_name: document.getElementById('modal_project_name')?.value.trim(),
                subtitle: document.getElementById('modal_project_sub')?.value.trim(),
                description: document.getElementById('modal_project_desc')?.value.trim(),
                technologies: document.getElementById('modal_project_tech')?.value.trim(),
                github_repo: document.getElementById('modal_project_repo')?.value.trim(),
                url: document.getElementById('modal_project_live')?.value.trim(),
                badge: document.getElementById('modal_project_badge')?.value.trim()
            };

            try {
                let res;
                if (id) {
                    res = await apiFetch(`/projects/${id}`, {
                        method: 'PUT',
                        body: JSON.stringify(payload)
                    });
                    showToast('Project updated successfully!', 'success');
                } else {
                    res = await apiFetch('/projects', {
                        method: 'POST',
                        body: JSON.stringify(payload)
                    });
                    showToast('Project created successfully!', 'success');
                }

                if (res?.data && projectsTableBody) {
                    const rowData = res.data;
                    const jsonSafe = JSON.stringify(rowData).replace(/"/g, '&quot;');
                    const existingRow = projectsTableBody.querySelector(`.project-row[data-id="${rowData.id}"]`);
                    const rowHtml = `
                        <td>
                            <div class="cell-primary">${rowData.project_name || ''}</div>
                            <div style="font-size:0.8rem; color:var(--text-muted);">${rowData.subtitle || ''}</div>
                        </td>
                        <td style="max-width:280px; font-family:var(--font-mono); font-size:0.78rem;">
                            ${rowData.technologies || ''}
                        </td>
                        <td>
                            ${rowData.badge ? `<span class="badge badge-accent">${rowData.badge}</span>` : '<span style="color:var(--text-muted); font-size:0.8rem;">Standard</span>'}
                        </td>
                        <td>
                            <div style="display:flex; gap:0.5rem;">
                                ${rowData.github_repo ? `
                                    <a href="${rowData.github_repo}" target="_blank" class="btn-icon" title="GitHub Repo">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/></svg>
                                    </a>
                                ` : ''}
                                ${rowData.url ? `
                                    <a href="${rowData.url}" target="_blank" class="btn-icon" title="Live URL">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                    </a>
                                ` : ''}
                            </div>
                        </td>
                        <td>
                            <div class="cell-actions">
                                <button type="button" class="btn-icon edit-project-btn" data-id="${rowData.id}" data-project="${jsonSafe}" title="Edit">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                </button>
                                <button type="button" class="btn-icon delete-project-btn" data-id="${rowData.id}" data-name="${rowData.project_name || ''}" title="Delete" style="color:var(--danger);">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </td>
                    `;

                    if (existingRow) {
                        existingRow.innerHTML = rowHtml;
                    } else {
                        const newTr = document.createElement('tr');
                        newTr.className = 'project-row';
                        newTr.setAttribute('data-id', rowData.id);
                        newTr.innerHTML = rowHtml;
                        projectsTableBody.appendChild(newTr);
                    }
                }

                closeModal(projectModal);
            } catch (err) {
                showToast(err.message || 'Failed to save project.', 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            }
        });
    }

    // ==========================================================================
    // STEP 5: Work Experience Tab Handler (#tab-experience)
    // ==========================================================================
    const experienceModal = document.getElementById('experienceModal');
    const experienceModalForm = document.getElementById('experienceModalForm');
    const openAddExperienceBtn = document.getElementById('openAddExperienceBtn');
    const experienceTableBody = document.getElementById('experienceTableBody');

    // 1. Open Add Experience Modal
    if (openAddExperienceBtn && experienceModal) {
        openAddExperienceBtn.addEventListener('click', () => {
            experienceModalForm.reset();
            document.getElementById('modal_exp_id').value = '';
            document.getElementById('experienceModalTitle').textContent = 'Add Experience Entry';
            openModal('experienceModal');
        });
    }

    // 2. Delegate Edit and Delete on Experience Table
    if (experienceTableBody) {
        experienceTableBody.addEventListener('click', async (e) => {
            const editBtn = e.target.closest('.edit-exp-btn');
            const deleteBtn = e.target.closest('.delete-exp-btn');

            if (editBtn) {
                const id = editBtn.getAttribute('data-id');
                let exp = null;
                const rawData = editBtn.getAttribute('data-exp');
                if (rawData) {
                    try {
                        exp = JSON.parse(rawData);
                    } catch (err) {
                        console.warn('Failed to parse experience data:', err);
                    }
                }

                if (!exp && id) {
                    try {
                        const res = await apiFetch(`/experience/${id}`);
                        exp = res.data;
                    } catch (err) {
                        showToast('Failed to load experience details.', 'error');
                        return;
                    }
                }

                if (exp) {
                    document.getElementById('modal_exp_id').value = id || exp.id || '';
                    document.getElementById('modal_job_title').value = exp.job_title || '';
                    document.getElementById('modal_company').value = exp.company_name || '';
                    document.getElementById('modal_exp_loc').value = exp.location || '';
                    document.getElementById('modal_exp_dates').value = exp.date_display || '';
                    document.getElementById('modal_desc_1').value = exp.description_1 || '';
                    document.getElementById('modal_desc_2').value = exp.description_2 || '';
                    document.getElementById('modal_desc_3').value = exp.description_3 || '';
                    document.getElementById('experienceModalTitle').textContent = 'Edit Experience Entry';
                    openModal('experienceModal');
                }
            }

            if (deleteBtn) {
                const id = deleteBtn.getAttribute('data-id');
                const name = deleteBtn.getAttribute('data-name') || 'role';

                openDeleteModal({
                    title: 'Delete Experience Entry',
                    message: `Are you sure you want to delete <strong>${escapeHtml(name)}</strong>?`,
                    onConfirm: async () => {
                        await apiFetch(`/experience/${id}`, { method: 'DELETE' });
                        showToast(`Experience "${name}" deleted successfully!`, 'success');
                        const row = deleteBtn.closest('.experience-row');
                        if (row) {
                            row.style.opacity = '0';
                            setTimeout(() => row.remove(), 250);
                        }
                    }
                });
            }
        });
    }

    // 3. Save Experience Modal Submit: POST or PUT /api/experience
    if (experienceModalForm) {
        experienceModalForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = document.getElementById('experienceModalSubmitBtn');
            const originalText = submitBtn ? submitBtn.innerHTML : 'Save Experience';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span>Saving...</span>';
            }

            const id = document.getElementById('modal_exp_id')?.value;
            const payload = {
                job_title: document.getElementById('modal_job_title')?.value.trim(),
                company_name: document.getElementById('modal_company')?.value.trim(),
                location: document.getElementById('modal_exp_loc')?.value.trim(),
                date_display: document.getElementById('modal_exp_dates')?.value.trim(),
                description_1: document.getElementById('modal_desc_1')?.value.trim(),
                description_2: document.getElementById('modal_desc_2')?.value.trim(),
                description_3: document.getElementById('modal_desc_3')?.value.trim()
            };

            try {
                let res;
                if (id) {
                    res = await apiFetch(`/experience/${id}`, {
                        method: 'PUT',
                        body: JSON.stringify(payload)
                    });
                    showToast('Experience updated successfully!', 'success');
                } else {
                    res = await apiFetch('/experience', {
                        method: 'POST',
                        body: JSON.stringify(payload)
                    });
                    showToast('Experience added successfully!', 'success');
                }

                if (res?.data && experienceTableBody) {
                    const rowData = res.data;
                    const jsonSafe = JSON.stringify(rowData).replace(/"/g, '&quot;');
                    const existingRow = experienceTableBody.querySelector(`.experience-row[data-id="${rowData.id}"]`);
                    const snippet = rowData.description_1 ? (rowData.description_1.substring(0, 75) + '...') : '';
                    const rowHtml = `
                        <td>
                            <div class="cell-primary">${rowData.job_title || ''}</div>
                            <div style="color:var(--text-muted); font-size:0.85rem;">${rowData.company_name || ''}</div>
                        </td>
                        <td>${rowData.location || 'Remote'}</td>
                        <td style="font-family:var(--font-mono); font-size:0.8rem;">${rowData.date_display || ''}</td>
                        <td style="font-size:0.82rem; max-width:320px;">
                            • ${snippet}
                        </td>
                        <td>
                            <div class="cell-actions">
                                <button type="button" class="btn-icon edit-exp-btn" data-id="${rowData.id}" data-exp="${jsonSafe}" title="Edit">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                </button>
                                <button type="button" class="btn-icon delete-exp-btn" data-id="${rowData.id}" data-name="${rowData.job_title || ''}" title="Delete" style="color:var(--danger);">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </td>
                    `;

                    if (existingRow) {
                        existingRow.innerHTML = rowHtml;
                    } else {
                        const newTr = document.createElement('tr');
                        newTr.className = 'experience-row';
                        newTr.setAttribute('data-id', rowData.id);
                        newTr.innerHTML = rowHtml;
                        experienceTableBody.appendChild(newTr);
                    }
                }

                closeModal(experienceModal);
            } catch (err) {
                showToast(err.message || 'Failed to save experience.', 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            }
        });
    }

    // ==========================================================================
    // STEP 6: Skills Matrix Tab Handler (#tab-skills)
    // ==========================================================================
    const skillsGrid = document.getElementById('skillsGrid');
    const saveSkillsMatrixBtn = document.getElementById('saveSkillsMatrixBtn');
    const openAddSkillBtn = document.getElementById('openAddSkillBtn');
    const skillModal = document.getElementById('skillModal');
    const skillModalForm = document.getElementById('skillModalForm');

    // 1. Batch Save All Skill Cards: PUT /api/skills/{id}
    if (saveSkillsMatrixBtn && skillsGrid) {
        saveSkillsMatrixBtn.addEventListener('click', async () => {
            const originalText = saveSkillsMatrixBtn.innerHTML;
            saveSkillsMatrixBtn.disabled = true;
            saveSkillsMatrixBtn.innerHTML = '<span>Saving Matrix...</span>';

            const cards = skillsGrid.querySelectorAll('.skill-card');
            const savePromises = [];

            cards.forEach(card => {
                const id = card.getAttribute('data-id');
                const label = card.querySelector('.skill-label-input')?.value.trim() || '';
                const category = card.querySelector('.skill-category-select')?.value || 'technical';
                const skillsText = card.querySelector('.skill-list-input')?.value.trim() || '';

                if (id) {
                    savePromises.push(
                        apiFetch(`/skills/${id}`, {
                            method: 'PUT',
                            body: JSON.stringify({
                                category_label: label,
                                skill_category: category,
                                skills_list: skillsText
                            })
                        }).then(res => {
                            // Update badge preview dynamically
                            const preview = card.querySelector('.skills-badges-preview');
                            if (preview && res?.data?.skills_array) {
                                preview.innerHTML = res.data.skills_array
                                    .map(s => `<span class="badge" style="color:#ffffff;">${s}</span>`)
                                    .join(' ');
                            }
                        })
                    );
                }
            });

            try {
                await Promise.all(savePromises);
                showToast('Skills matrix saved successfully!', 'success');
            } catch (err) {
                showToast(err.message || 'Failed to save skills matrix.', 'error');
            } finally {
                saveSkillsMatrixBtn.disabled = false;
                saveSkillsMatrixBtn.innerHTML = originalText;
            }
        });
    }

    // 2. Open Add Skill Category Modal
    if (openAddSkillBtn && skillModal) {
        openAddSkillBtn.addEventListener('click', () => {
            skillModalForm.reset();
            document.getElementById('modal_skill_id').value = '';
            document.getElementById('skillModalTitle').textContent = 'Add Skill Category';
            openModal('skillModal');
        });
    }

    // 3. Delegate Delete Category on Skills Grid
    if (skillsGrid) {
        skillsGrid.addEventListener('click', async (e) => {
            const deleteBtn = e.target.closest('.delete-skill-btn');
            if (deleteBtn) {
                const id = deleteBtn.getAttribute('data-id');
                const name = deleteBtn.getAttribute('data-name') || 'category';

                openDeleteModal({
                    title: 'Delete Skill Category',
                    message: `Are you sure you want to delete <strong>${escapeHtml(name)}</strong> and all its associated skills?`,
                    onConfirm: async () => {
                        await apiFetch(`/skills/${id}`, { method: 'DELETE' });
                        showToast(`Skill category "${name}" deleted successfully!`, 'success');
                        const card = deleteBtn.closest('.skill-card');
                        if (card) {
                            card.style.opacity = '0';
                            setTimeout(() => card.remove(), 250);
                        }
                    }
                });
            }
        });
    }

    // 4. Submit Add Skill Category Modal: POST /api/skills
    if (skillModalForm) {
        skillModalForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = document.getElementById('skillModalSubmitBtn');
            const originalText = submitBtn ? submitBtn.innerHTML : 'Save Category';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span>Saving...</span>';
            }

            const category = document.getElementById('modal_skill_category')?.value;
            const label = document.getElementById('modal_skill_label')?.value.trim();
            const list = document.getElementById('modal_skill_list')?.value.trim();

            const payload = {
                skill_category: category,
                category_label: label,
                skills_list: list
            };

            try {
                const res = await apiFetch('/skills', {
                    method: 'POST',
                    body: JSON.stringify(payload)
                });
                showToast('Skill category created successfully!', 'success');

                if (res?.data && skillsGrid) {
                    const rowData = res.data;
                    const skillsArray = rowData.skills_array || [];
                    const badgesHtml = skillsArray.map(s => `<span class="badge" style="color:#ffffff;">${s}</span>`).join(' ');

                    const newCard = document.createElement('div');
                    newCard.className = 'admin-card skill-card';
                    newCard.setAttribute('data-id', rowData.id);
                    newCard.innerHTML = `
                        <div class="admin-card-header" style="display:flex; justify-content:space-between; align-items:flex-start;">
                            <div style="flex:1; margin-right:0.75rem;">
                                <input type="text" class="form-input skill-label-input" value="${rowData.category_label || ''}" style="font-weight:700; font-size:1.05rem; padding:0.35rem 0.6rem; margin-bottom:0.4rem;" placeholder="Category Name">
                                <select class="form-select skill-category-select" style="font-size:0.75rem; padding:0.25rem 0.5rem; width:auto; display:inline-block;">
                                    <option value="technical" ${rowData.skill_category === 'technical' ? 'selected' : ''}>Technical</option>
                                    <option value="soft" ${rowData.skill_category === 'soft' ? 'selected' : ''}>Soft</option>
                                </select>
                            </div>
                            <button type="button" class="btn-icon delete-skill-btn" data-id="${rowData.id}" data-name="${rowData.category_label || ''}" title="Delete Category" style="color:var(--danger); padding:0.4rem;">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                        <div class="admin-card-body">
                            <div class="form-group">
                                <label class="form-label">Comma-Separated Skills</label>
                                <textarea class="form-textarea skill-list-input" rows="3">${rowData.skills_list || ''}</textarea>
                            </div>
                            <div class="skills-badges-preview" style="display:flex; flex-wrap:wrap; gap:0.4rem; margin-top:0.75rem;">
                                ${badgesHtml}
                            </div>
                        </div>
                    `;
                    skillsGrid.appendChild(newCard);
                }

                closeModal(skillModal);
            } catch (err) {
                showToast(err.message || 'Failed to create skill category.', 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            }
        });
    }

    // ==========================================================================
    // STEP 7: Education, Certifications & Inquiries Inbox Handlers
    // ==========================================================================
    
    // --------------------------------------------------------------------------
    // 7A: Formal Education CRUD
    // --------------------------------------------------------------------------
    const educationModal = document.getElementById('educationModal');
    const educationModalForm = document.getElementById('educationModalForm');
    const openAddEducationBtn = document.getElementById('openAddEducationBtn');
    const educationTableBody = document.getElementById('educationTableBody');

    if (openAddEducationBtn && educationModal) {
        openAddEducationBtn.addEventListener('click', () => {
            educationModalForm.reset();
            document.getElementById('modal_edu_id').value = '';
            document.getElementById('educationModalTitle').textContent = 'Formal Education Entry';
            openModal('educationModal');
        });
    }

    if (educationTableBody) {
        educationTableBody.addEventListener('click', async (e) => {
            const editBtn = e.target.closest('.edit-edu-btn');
            const deleteBtn = e.target.closest('.delete-edu-btn');

            if (editBtn) {
                const id = editBtn.getAttribute('data-id');
                let edu = null;
                const raw = editBtn.getAttribute('data-edu');
                if (raw) {
                    try { edu = JSON.parse(raw); } catch (err) { console.warn(err); }
                }

                if (!edu && id) {
                    try {
                        const res = await apiFetch(`/education/${id}`);
                        edu = res.data;
                    } catch (err) {
                        showToast('Failed to load education details.', 'error');
                        return;
                    }
                }

                if (edu) {
                    document.getElementById('modal_edu_id').value = id || edu.id || '';
                    document.getElementById('modal_edu_school').value = edu.school_name || '';
                    document.getElementById('modal_edu_course').value = edu.course || '';
                    document.getElementById('modal_edu_dates').value = edu.date_display || '';
                    document.getElementById('modal_edu_focus').value = edu.focus_areas || '';
                    document.getElementById('educationModalTitle').textContent = 'Edit Education Entry';
                    openModal('educationModal');
                }
            }

            if (deleteBtn) {
                const id = deleteBtn.getAttribute('data-id');
                const name = deleteBtn.getAttribute('data-name') || 'education';

                openDeleteModal({
                    title: 'Delete Education Entry',
                    message: `Are you sure you want to delete <strong>${escapeHtml(name)}</strong>?`,
                    onConfirm: async () => {
                        await apiFetch(`/education/${id}`, { method: 'DELETE' });
                        showToast(`Education entry "${name}" deleted successfully!`, 'success');
                        const row = deleteBtn.closest('.education-row');
                        if (row) {
                            row.style.opacity = '0';
                            setTimeout(() => row.remove(), 250);
                        }
                    }
                });
            }
        });
    }

    if (educationModalForm) {
        educationModalForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = document.getElementById('educationModalSubmitBtn');
            const originalText = submitBtn ? submitBtn.innerHTML : 'Save Education';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span>Saving...</span>';
            }

            const id = document.getElementById('modal_edu_id')?.value;
            const payload = {
                school_name: document.getElementById('modal_edu_school')?.value.trim(),
                course: document.getElementById('modal_edu_course')?.value.trim(),
                date_display: document.getElementById('modal_edu_dates')?.value.trim(),
                focus_areas: document.getElementById('modal_edu_focus')?.value.trim()
            };

            try {
                let res;
                if (id) {
                    res = await apiFetch(`/education/${id}`, {
                        method: 'PUT',
                        body: JSON.stringify(payload)
                    });
                    showToast('Education updated successfully!', 'success');
                } else {
                    res = await apiFetch('/education', {
                        method: 'POST',
                        body: JSON.stringify(payload)
                    });
                    showToast('Education added successfully!', 'success');
                }

                if (res?.data && educationTableBody) {
                    const rowData = res.data;
                    const jsonSafe = JSON.stringify(rowData).replace(/"/g, '&quot;');
                    const existingRow = educationTableBody.querySelector(`.education-row[data-id="${rowData.id}"]`);
                    const rowHtml = `
                        <td class="cell-primary">${rowData.school_name || ''}</td>
                        <td>${rowData.course || ''}</td>
                        <td style="font-family:var(--font-mono); font-size:0.8rem;">${rowData.date_display || ''}</td>
                        <td style="font-size:0.82rem; max-width:260px;">${rowData.focus_areas || ''}</td>
                        <td>
                            <div class="cell-actions">
                                <button type="button" class="btn-icon edit-edu-btn" data-id="${rowData.id}" data-edu="${jsonSafe}" title="Edit">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                </button>
                                <button type="button" class="btn-icon delete-edu-btn" data-id="${rowData.id}" data-name="${rowData.school_name || ''}" title="Delete" style="color:var(--danger);">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </td>
                    `;

                    if (existingRow) {
                        existingRow.innerHTML = rowHtml;
                    } else {
                        const newTr = document.createElement('tr');
                        newTr.className = 'education-row';
                        newTr.setAttribute('data-id', rowData.id);
                        newTr.innerHTML = rowHtml;
                        educationTableBody.appendChild(newTr);
                    }
                }

                closeModal(educationModal);
            } catch (err) {
                showToast(err.message || 'Failed to save education entry.', 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            }
        });
    }

    // --------------------------------------------------------------------------
    // 7B: Honors & Certifications CRUD
    // --------------------------------------------------------------------------
    const certificateModal = document.getElementById('certificateModal');
    const certificateModalForm = document.getElementById('certificateModalForm');
    const openAddCertBtn = document.getElementById('openAddCertBtn');
    const certTableBody = document.getElementById('certTableBody');

    if (openAddCertBtn && certificateModal) {
        openAddCertBtn.addEventListener('click', () => {
            certificateModalForm.reset();
            document.getElementById('modal_cert_id').value = '';
            document.getElementById('certificateModalTitle').textContent = 'Add Honor & Certification';
            openModal('certificateModal');
        });
    }

    if (certTableBody) {
        certTableBody.addEventListener('click', async (e) => {
            const editBtn = e.target.closest('.edit-cert-btn');
            const deleteBtn = e.target.closest('.delete-cert-btn');

            if (editBtn) {
                const id = editBtn.getAttribute('data-id');
                let cert = null;
                const raw = editBtn.getAttribute('data-cert');
                if (raw) {
                    try { cert = JSON.parse(raw); } catch (err) { console.warn(err); }
                }

                if (!cert && id) {
                    try {
                        const res = await apiFetch(`/certificates/${id}`);
                        cert = res.data;
                    } catch (err) {
                        showToast('Failed to load certificate details.', 'error');
                        return;
                    }
                }

                if (cert) {
                    document.getElementById('modal_cert_id').value = id || cert.id || '';
                    document.getElementById('modal_cert_title').value = cert.title || '';
                    document.getElementById('modal_cert_issuer').value = cert.issuer || '';
                    document.getElementById('modal_cert_date').value = cert.date_display || '';
                    document.getElementById('modal_cert_num').value = cert.cert_id || '';
                    document.getElementById('modal_cert_url').value = cert.cert_url || '';
                    document.getElementById('certificateModalTitle').textContent = 'Edit Certification';
                    openModal('certificateModal');
                }
            }

            if (deleteBtn) {
                const id = deleteBtn.getAttribute('data-id');
                const name = deleteBtn.getAttribute('data-name') || 'certificate';

                openDeleteModal({
                    title: 'Delete Certification',
                    message: `Are you sure you want to delete <strong>${escapeHtml(name)}</strong>?`,
                    onConfirm: async () => {
                        await apiFetch(`/certificates/${id}`, { method: 'DELETE' });
                        showToast(`Certification "${name}" deleted successfully!`, 'success');
                        const row = deleteBtn.closest('.cert-row');
                        if (row) {
                            row.style.opacity = '0';
                            setTimeout(() => row.remove(), 250);
                        }
                    }
                });
            }
        });
    }

    if (certificateModalForm) {
        certificateModalForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = document.getElementById('certificateModalSubmitBtn');
            const originalText = submitBtn ? submitBtn.innerHTML : 'Save Certificate';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span>Saving...</span>';
            }

            const id = document.getElementById('modal_cert_id')?.value;
            const payload = {
                title: document.getElementById('modal_cert_title')?.value.trim(),
                issuer: document.getElementById('modal_cert_issuer')?.value.trim(),
                date_display: document.getElementById('modal_cert_date')?.value.trim(),
                cert_id: document.getElementById('modal_cert_num')?.value.trim(),
                cert_url: document.getElementById('modal_cert_url')?.value.trim()
            };

            try {
                let res;
                if (id) {
                    res = await apiFetch(`/certificates/${id}`, {
                        method: 'PUT',
                        body: JSON.stringify(payload)
                    });
                    showToast('Certification updated successfully!', 'success');
                } else {
                    res = await apiFetch('/certificates', {
                        method: 'POST',
                        body: JSON.stringify(payload)
                    });
                    showToast('Certification added successfully!', 'success');
                }

                if (res?.data && certTableBody) {
                    const rowData = res.data;
                    const jsonSafe = JSON.stringify(rowData).replace(/"/g, '&quot;');
                    const existingRow = certTableBody.querySelector(`.cert-row[data-id="${rowData.id}"]`);
                    const rowHtml = `
                        <td>
                            <div class="cell-primary">${rowData.title || ''}</div>
                            <div style="color:var(--text-muted); font-size:0.8rem;">${rowData.issuer || ''}</div>
                        </td>
                        <td style="font-family:var(--font-mono); font-size:0.8rem;">${rowData.date_display || ''}</td>
                        <td style="font-family:var(--font-mono); font-size:0.8rem;">${rowData.cert_id || '—'}</td>
                        <td>
                            ${rowData.cert_url ? `<a href="${rowData.cert_url}" target="_blank" class="badge badge-accent">Verify Link ↗</a>` : '<span style="color:var(--text-muted); font-size:0.8rem;">None</span>'}
                        </td>
                        <td>
                            <div class="cell-actions">
                                <button type="button" class="btn-icon edit-cert-btn" data-id="${rowData.id}" data-cert="${jsonSafe}" title="Edit">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                </button>
                                <button type="button" class="btn-icon delete-cert-btn" data-id="${rowData.id}" data-name="${rowData.title || ''}" title="Delete" style="color:var(--danger);">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </td>
                    `;

                    if (existingRow) {
                        existingRow.innerHTML = rowHtml;
                    } else {
                        const newTr = document.createElement('tr');
                        newTr.className = 'cert-row';
                        newTr.setAttribute('data-id', rowData.id);
                        newTr.innerHTML = rowHtml;
                        certTableBody.appendChild(newTr);
                    }
                }

                closeModal(certificateModal);
            } catch (err) {
                showToast(err.message || 'Failed to save certification.', 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            }
        });
    }

    // --------------------------------------------------------------------------
    // 7C: Inquiries Inbox Actions
    // --------------------------------------------------------------------------
    const inquiriesTableBody = document.getElementById('inquiriesTableBody');

    if (inquiriesTableBody) {
        inquiriesTableBody.addEventListener('click', async (e) => {
            const readBtn = e.target.closest('.open-inquiry-btn');
            const deleteBtn = e.target.closest('.delete-inquiry-btn');

            // 1. Mark as Read on Open
            if (readBtn) {
                const id = readBtn.getAttribute('data-id');
                const isRead = readBtn.getAttribute('data-read') === '1';

                if (!isRead && id) {
                    try {
                        await apiFetch(`/inquiries/${id}/read`, { method: 'PATCH' });
                        readBtn.setAttribute('data-read', '1');

                        const row = readBtn.closest('.inquiry-row');
                        const badge = row?.querySelector('.inquiry-status-badge');
                        if (badge) {
                            badge.textContent = 'Read';
                            badge.classList.remove('badge-success');
                            badge.classList.add('badge-accent');
                        }

                        // Decrement sidebar unread counter
                        const navCount = document.querySelector('.nav-item[data-tab="inquiries"] .nav-item-count');
                        if (navCount) {
                            const cur = parseInt(navCount.textContent, 10) || 0;
                            if (cur <= 1) {
                                navCount.remove();
                            } else {
                                navCount.textContent = (cur - 1).toString();
                            }
                        }
                    } catch (err) {
                        console.warn('Failed to mark inquiry as read:', err);
                    }
                }
            }

            // 2. Delete Inquiry
            if (deleteBtn) {
                const id = deleteBtn.getAttribute('data-id');
                const name = deleteBtn.getAttribute('data-name') || 'message';

                openDeleteModal({
                    title: 'Delete Inquiry',
                    message: `Are you sure you want to delete the inquiry from <strong>${escapeHtml(name)}</strong>?`,
                    onConfirm: async () => {
                        await apiFetch(`/inquiries/${id}`, { method: 'DELETE' });
                        showToast('Inquiry deleted successfully!', 'success');
                        const row = deleteBtn.closest('.inquiry-row');
                        if (row) {
                            row.style.opacity = '0';
                            setTimeout(() => row.remove(), 250);
                        }
                    }
                });
            }
        });
    }
});


