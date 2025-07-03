// Dashboard functionality
document.addEventListener('DOMContentLoaded', function() {
    initializeModal();
    initializeSearch();
    initializeDependentForm();
    initializeDeviceActions();
});

// Modal Management
function initializeModal() {
    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-overlay')) {
            closeModal(e.target.id);
        }
    });
    
    // Close modal with escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const activeModal = document.querySelector('.modal-overlay.active');
            if (activeModal) {
                closeModal(activeModal.id);
            }
        }
    });
}

function openAddDeviceModal() {
    openModal('addDeviceModal');
}

function openAddUserModal() {
    openModal('addUserModal');
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
        
        // Reset form if it exists
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            // Hide conditional fields
            hideElement('existingDependentGroup');
            hideElement('newDependentGroup');
        }
    }
}

// Search functionality
function initializeSearch() {
    const searchInput = document.getElementById('deviceSearchBar');
    const statusFilter = document.getElementById('statusFilter');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterDevices();
        });
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            filterDevices();
        });
    }
}

function filterDevices() {
    const searchTerm = document.getElementById('deviceSearchBar')?.value.toLowerCase() || '';
    const statusFilter = document.getElementById('statusFilter')?.value || '';
    const deviceItems = document.querySelectorAll('.device-item');
    
    deviceItems.forEach(item => {
        const serialNo = item.querySelector('.device-serial')?.textContent.toLowerCase() || '';
        const userEmail = item.querySelector('.device-user')?.textContent.toLowerCase() || '';
        const dependentName = item.querySelector('.device-dependent')?.textContent.toLowerCase() || '';
        const deviceStatus = item.dataset.status || '';
        
        const matchesSearch = serialNo.includes(searchTerm) || 
                            userEmail.includes(searchTerm) || 
                            dependentName.includes(searchTerm);
        
        const matchesStatus = !statusFilter || deviceStatus === statusFilter;
        
        if (matchesSearch && matchesStatus) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

// Dependent form handling
function initializeDependentForm() {
    const dependentSelect = document.getElementById('dependentSelect');
    if (dependentSelect) {
        dependentSelect.addEventListener('change', function() {
            const value = this.value;
            
            hideElement('existingDependentGroup');
            hideElement('newDependentGroup');
            
            if (value === 'existing') {
                showElement('existingDependentGroup');
            } else if (value === 'new') {
                showElement('newDependentGroup');
            }
        });
    }
}

function showElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.classList.remove('hidden');
    }
}

function hideElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.classList.add('hidden');
    }
}

// Device actions
function initializeDeviceActions() {
    // Device action buttons will be handled by individual functions
}

function viewDevice(serialNo) {
    // Navigate to device details page
    window.location.href = `/device/view/${serialNo}`;
}

function editDevice(serialNo) {
    // Open edit modal or navigate to edit page
    window.location.href = `/device/edit/${serialNo}`;
}

function deleteDevice(serialNo) {
    if (confirm('Are you sure you want to delete this device?')) {
        // Submit delete request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/device/delete/${serialNo}`;
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = csrfToken.getAttribute('content');
            form.appendChild(tokenInput);
        }
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Bulk actions (admin only)
function openBulkActions() {
    // Implementation for bulk device actions
    alert('Bulk actions functionality coming soon!');
}



function updateStatistics(data) {
    // Update statistic numbers on the page
    const onlineElement = document.querySelector('.stat-number.online');
    const offlineElement = document.querySelector('.stat-number.offline');
    const totalElement = document.querySelector('.stat-number.total');
    const emergencyElement = document.querySelector('.stat-number.emergency');
    
    if (onlineElement) onlineElement.textContent = data.online_devices;
    if (offlineElement) offlineElement.textContent = data.offline_devices;
    if (totalElement) totalElement.textContent = data.total_devices;
    if (emergencyElement) emergencyElement.textContent = data.active_emergencies;
}