<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055">
    <!-- Success Toast -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="toast eva-toast success-toast show" role="alert">
            <div class="toast-header">
                <div class="toast-icon success">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <strong class="me-auto">Success</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                <?php echo htmlspecialchars($_SESSION['success_message']); ?>
            </div>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <!-- Error Toast -->
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="toast eva-toast error-toast show" role="alert">
            <div class="toast-header">
                <div class="toast-icon error">
                    <i class="bi bi-exclamation-circle-fill"></i>
                </div>
                <strong class="me-auto">Error</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                <?php echo htmlspecialchars($_SESSION['error_message']); ?>
            </div>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Warning Toast -->
    <?php if (isset($_SESSION['warning_message'])): ?>
        <div class="toast eva-toast warning-toast show" role="alert">
            <div class="toast-header">
                <div class="toast-icon warning">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <strong class="me-auto">Warning</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                <?php echo htmlspecialchars($_SESSION['warning_message']); ?>
            </div>
        </div>
        <?php unset($_SESSION['warning_message']); ?>
    <?php endif; ?>

    <!-- Info Toast -->
    <?php if (isset($_SESSION['info_message'])): ?>
        <div class="toast eva-toast info-toast show" role="alert">
            <div class="toast-header">
                <div class="toast-icon info">
                    <i class="bi bi-info-circle-fill"></i>
                </div>
                <strong class="me-auto">Information</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                <?php echo htmlspecialchars($_SESSION['info_message']); ?>
            </div>
        </div>
        <?php unset($_SESSION['info_message']); ?>
    <?php endif; ?>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-content">
        <div class="eva-loading-spinner"></div>
        <p>Loading...</p>
    </div>
</div>

<!-- Mobile Sidebar Overlay -->
<div class="mobile-sidebar-overlay" id="mobileSidebarOverlay" onclick="toggleMobileSidebar()"></div>

<style>
/* Toast Notifications */
.eva-toast {
    border: none;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    backdrop-filter: blur(15px);
    margin-bottom: 10px;
    min-width: 350px;
}

.eva-toast .toast-header {
    background: transparent;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    padding: 15px 20px 10px;
}

.eva-toast .toast-body {
    padding: 10px 20px 15px;
    font-size: 14px;
    line-height: 1.5;
}

.toast-icon {
    width: 24px;
    height: 24px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    color: white;
    margin-right: 8px;
}

.toast-icon.success {
    background: var(--eva-success);
}

.toast-icon.error {
    background: var(--eva-danger);
}

.toast-icon.warning {
    background: var(--eva-warning);
    color: var(--eva-dark);
}

.toast-icon.info {
    background: var(--eva-info);
}

.success-toast {
    background: rgba(40, 167, 69, 0.05);
    border-left: 4px solid var(--eva-success);
}

.error-toast {
    background: rgba(220, 53, 69, 0.05);
    border-left: 4px solid var(--eva-danger);
}

.warning-toast {
    background: rgba(255, 193, 7, 0.05);
    border-left: 4px solid var(--eva-warning);
}

.info-toast {
    background: rgba(23, 162, 184, 0.05);
    border-left: 4px solid var(--eva-info);
}

/* Loading Overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(5px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 2000;
}

.loading-overlay.show {
    display: flex;
}

.loading-content {
    text-align: center;
    padding: 30px;
    background: white;
    border-radius: var(--eva-border-radius);
    box-shadow: var(--eva-shadow);
}

.eva-loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid rgba(66, 133, 244, 0.2);
    border-top: 4px solid var(--eva-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 15px;
}

.loading-content p {
    margin: 0;
    color: var(--eva-secondary);
    font-weight: 500;
}

/* Mobile Sidebar Overlay */
.mobile-sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    z-index: 1040;
}

@media (max-width: 768px) {
    .mobile-sidebar-overlay.show {
        display: block;
    }
}
</style>

<!-- Core JavaScript Libraries -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- EVA Core JavaScript -->
<script>
// Global EVA namespace
window.EVA = {
    // Configuration
    config: {
        toastDuration: 5000,
        animationDuration: 300
    },
    
    // Utility functions
    utils: {
        // Show loading overlay
        showLoading: function(message = 'Loading...') {
            const overlay = document.getElementById('loadingOverlay');
            const text = overlay.querySelector('p');
            text.textContent = message;
            overlay.classList.add('show');
        },
        
        // Hide loading overlay
        hideLoading: function() {
            const overlay = document.getElementById('loadingOverlay');
            overlay.classList.remove('show');
        },
        
        // Show toast notification
        showToast: function(message, type = 'info') {
            const toastContainer = document.querySelector('.toast-container');
            const toastId = 'toast-' + Date.now();
            
            const toastHTML = `
                <div class="toast eva-toast ${type}-toast show" role="alert" id="${toastId}">
                    <div class="toast-header">
                        <div class="toast-icon ${type}">
                            <i class="bi bi-${this.getToastIcon(type)}"></i>
                        </div>
                        <strong class="me-auto">${this.getToastTitle(type)}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">${message}</div>
                </div>
            `;
            
            toastContainer.insertAdjacentHTML('beforeend', toastHTML);
            
            // Auto-hide after duration
            setTimeout(() => {
                const toast = document.getElementById(toastId);
                if (toast) {
                    const bsToast = new bootstrap.Toast(toast);
                    bsToast.hide();
                }
            }, EVA.config.toastDuration);
        },
        
        getToastIcon: function(type) {
            const icons = {
                success: 'check-circle-fill',
                error: 'exclamation-circle-fill',
                warning: 'exclamation-triangle-fill',
                info: 'info-circle-fill'
            };
            return icons[type] || icons.info;
        },
        
        getToastTitle: function(type) {
            const titles = {
                success: 'Success',
                error: 'Error',
                warning: 'Warning',
                info: 'Information'
            };
            return titles[type] || titles.info;
        },
        
        // Format date
        formatDate: function(date, format = 'short') {
            const d = new Date(date);
            if (format === 'short') {
                return d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            }
            return d.toLocaleString();
        },
        
        // Debounce function
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    },
    
    // UI functions
    ui: {
        // Initialize all UI components
        init: function() {
            this.initToasts();
            this.initMobileSidebar();
            this.initSearchFunctionality();
        },
        
        // Initialize toast notifications
        initToasts: function() {
            const toasts = document.querySelectorAll('.toast');
            toasts.forEach(toast => {
                setTimeout(() => {
                    const bsToast = new bootstrap.Toast(toast);
                    bsToast.hide();
                }, EVA.config.toastDuration);
            });
        },
        
        // Initialize mobile sidebar
        initMobileSidebar: function() {
            const overlay = document.getElementById('mobileSidebarOverlay');
            const sidebar = document.querySelector('.eva-sidebar');
            
            if (overlay && sidebar) {
                // Show overlay when sidebar is open on mobile
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                            if (sidebar.classList.contains('mobile-open')) {
                                overlay.classList.add('show');
                            } else {
                                overlay.classList.remove('show');
                            }
                        }
                    });
                });
                
                observer.observe(sidebar, { attributes: true });
            }
        },
        
        // Initialize search functionality
        initSearchFunctionality: function() {
            const searchInput = document.querySelector('.search-input');
            if (searchInput) {
                const debouncedSearch = EVA.utils.debounce(function(query) {
                    // Implement search functionality here
                    console.log('Searching for:', query);
                }, 300);
                
                searchInput.addEventListener('input', function(e) {
                    debouncedSearch(e.target.value);
                });
            }
        }
    }
};

// Initialize EVA when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    EVA.ui.init();
});

// Global helper functions for backward compatibility
function showLoading(message) {
    EVA.utils.showLoading(message);
}

function hideLoading() {
    EVA.utils.hideLoading();
}

function showToast(message, type) {
    EVA.utils.showToast(message, type);
}

// Form validation helpers
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePhone(phone) {
    const re = /^[\+]?[1-9][\d]{0,15}$/;
    return re.test(phone.replace(/\s/g, ''));
}

// Device management functions
function viewDevice(deviceId) {
    window.location.href = `device_details.php?id=${deviceId}`;
}

function editDevice(deviceId) {
    window.location.href = `devices.php?action=edit&id=${deviceId}`;
}

function deleteDevice(deviceId) {
    if (confirm('Are you sure you want to delete this device? This action cannot be undone.')) {
        showLoading('Deleting device...');
        window.location.href = `devices.php?action=delete&id=${deviceId}`;
    }
}

// User management functions
function viewUser(userId) {
    window.location.href = `user_details.php?id=${userId}`;
}

function editUser(userId) {
    window.location.href = `family_members.php?action=edit&id=${userId}`;
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        showLoading('Deleting user...');
        window.location.href = `family_members.php?action=delete&id=${userId}`;
    }
}
</script>

<!-- Additional JavaScript -->
<?php if (isset($additionalJS)): ?>
    <?php foreach ($additionalJS as $js): ?>
        <script src="<?php echo $js; ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
