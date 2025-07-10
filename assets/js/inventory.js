// Inventory Management JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initializeFileUpload();
    initializeSearch();
    initializeFilters();
    initializeModals();
});

// File Upload Functionality
function initializeFileUpload() {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('csv_file');
    const selectedFile = document.getElementById('selectedFile');
    const fileName = document.querySelector('.file-name');
    const removeFile = document.getElementById('removeFile');
    const browseButton = document.getElementById('browseButton');
    
    if (!dropZone || !fileInput) return;
    
    // Handle drag and drop events
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    // Highlight drop zone when dragging over it
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });
    
    function highlight() {
        dropZone.classList.add('dragover');
    }
    
    function unhighlight() {
        dropZone.classList.remove('dragover');
    }
    
    // Handle dropped files
    dropZone.addEventListener('drop', handleDrop, false);
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length) {
            fileInput.files = files;
            updateFileInfo();
        }
    }
    
    // Handle file selection via input
    fileInput.addEventListener('change', updateFileInfo);
    
    function updateFileInfo() {
        if (fileInput.files.length) {
            fileName.textContent = fileInput.files[0].name;
            selectedFile.style.display = 'flex';
        } else {
            selectedFile.style.display = 'none';
        }
    }
    
    // Remove selected file
    if (removeFile) {
        removeFile.addEventListener('click', function() {
            fileInput.value = '';
            selectedFile.style.display = 'none';
        });
    }
    
    // Trigger file input click when browse button is clicked
    if (browseButton) {
        browseButton.addEventListener('click', function() {
            fileInput.click();
        });
    }
    
    // Click on drop zone to select file
    dropZone.addEventListener('click', function() {
        fileInput.click();
    });
}

// Search Functionality
function initializeSearch() {
    const searchInput = document.getElementById('inventorySearch');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            filterInventory();
        });
    }
}

// Filter Functionality
function initializeFilters() {
    const statusFilter = document.getElementById('statusFilter');
    const typeFilter = document.getElementById('typeFilter');
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterInventory);
    }
    
    if (typeFilter) {
        typeFilter.addEventListener('change', filterInventory);
    }
}

// Filter inventory based on search and filters
function filterInventory() {
    const searchTerm = document.getElementById('inventorySearch')?.value.toLowerCase() || '';
    const statusFilter = document.getElementById('statusFilter')?.value || '';
    const typeFilter = document.getElementById('typeFilter')?.value || '';
    
    const tableRows = document.querySelectorAll('#inventoryTableBody tr');
    let visibleCount = 0;
    
    tableRows.forEach(row => {
        const serialNo = row.querySelector('.serial-cell')?.textContent.toLowerCase() || '';
        const deviceType = row.querySelector('.device-type-cell')?.textContent.toLowerCase() || '';
        const status = row.dataset.status;
        const type = row.dataset.type;
        
        // Check if matches search term
        const matchesSearch = serialNo.includes(searchTerm) || deviceType.includes(searchTerm);
        
        // Check if matches status filter
        const matchesStatus = !statusFilter || status === statusFilter;
        
        // Check if matches type filter
        const matchesType = !typeFilter || type.toLowerCase() === typeFilter.toLowerCase();
        
        if (matchesSearch && matchesStatus && matchesType) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update pagination info
    updatePaginationInfo(visibleCount);
    
    // Show/hide empty state
    toggleEmptyState(visibleCount === 0);
}

// Update pagination information
function updatePaginationInfo(count) {
    const paginationInfo = document.querySelector('.pagination-info');
    if (paginationInfo) {
        paginationInfo.textContent = `Showing 1-${Math.min(10, count)} of ${count} devices`;
    }
}

// Toggle empty state
function toggleEmptyState(show) {
    const tableContainer = document.querySelector('.inventory-table-container');
    const paginationContainer = document.querySelector('.pagination-container');
    let emptyState = document.querySelector('.empty-state');
    
    if (show) {
        if (!emptyState) {
            emptyState = document.createElement('div');
            emptyState.className = 'empty-state';
            emptyState.innerHTML = `
                <i class="bi bi-search display-1 text-muted"></i>
                <h3 class="mt-3">No Results Found</h3>
                <p class="text-muted">No devices match your search criteria.</p>
                <button class="btn btn-outline" onclick="resetFilters()">
                    <i class="bi bi-arrow-counterclockwise me-2"></i>Reset Filters
                </button>
            `;
            
            if (tableContainer) {
                tableContainer.style.display = 'none';
                tableContainer.parentNode.insertBefore(emptyState, tableContainer);
            }
        } else {
            emptyState.style.display = 'block';
        }
        
        if (tableContainer) tableContainer.style.display = 'none';
        if (paginationContainer) paginationContainer.style.display = 'none';
    } else {
        if (emptyState) emptyState.style.display = 'none';
        if (tableContainer) tableContainer.style.display = 'block';
        if (paginationContainer) paginationContainer.style.display = 'flex';
    }
}

// Reset all filters
function resetFilters() {
    const searchInput = document.getElementById('inventorySearch');
    const statusFilter = document.getElementById('statusFilter');
    const typeFilter = document.getElementById('typeFilter');
    
    if (searchInput) searchInput.value = '';
    if (statusFilter) statusFilter.value = '';
    if (typeFilter) typeFilter.value = '';
    
    filterInventory();
}

// Modal Functionality
function initializeModals() {
    // Initialize Bootstrap modals if needed
}

// Open Add Device Modal
function openAddDeviceModal() {
    const modal = new bootstrap.Modal(document.getElementById('addDeviceModal'));
    modal.show();
}

// Edit Device
function editDevice(serialNo) {
    // Fetch device data
    fetch(`../actions/inventory/get.php?serialNo=${encodeURIComponent(serialNo)}`)
        .then(response => response.json())
        .then(device => {
            if (device.error) {
                showToast(device.error, 'error');
                return;
            }
            
            // Populate form
            document.getElementById('editSerialNo').value = device.SerialNo;
            document.getElementById('editDeviceType').value = device.DeviceType || '';
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('editDeviceModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Error fetching device:', error);
            showToast('Error loading device data', 'error');
        });
}

// Delete Device
function deleteDevice(serialNo) {
    if (confirm(`Are you sure you want to delete device ${serialNo}?\n\nThis action cannot be undone.`)) {
        // Send delete request
        fetch(`../actions/inventory/delete.php?serialNo=${encodeURIComponent(serialNo)}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showToast(result.message, 'success');
                
                // Remove from table
                const row = document.querySelector(`tr[data-device-id="${serialNo}"]`);
                if (row) {
                    row.remove();
                }
                
                // Update counts
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showToast(result.message || 'Error deleting device', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error deleting device', 'error');
        });
    }
}

// Export Inventory
function exportInventory() {
    // Get filtered data
    const tableRows = document.querySelectorAll('#inventoryTableBody tr');
    const visibleRows = Array.from(tableRows).filter(row => row.style.display !== 'none');
    
    if (visibleRows.length === 0) {
        showToast('No data to export', 'warning');
        return;
    }
    
    // Create CSV content
    let csvContent = 'Serial No,Device Type,Added By,Added On,Status\n';
    
    visibleRows.forEach(row => {
        const serialNo = row.querySelector('.serial-cell').textContent.trim();
        const deviceType = row.querySelector('.device-type-cell').textContent.trim();
        const addedBy = row.querySelector('.added-by-cell').textContent.trim();
        const addedOn = row.querySelector('.added-on-cell').textContent.trim();
        const status = row.querySelector('.status-badge').textContent.trim();
        
        csvContent += `"${serialNo}","${deviceType}","${addedBy}","${addedOn}","${status}"\n`;
    });
    
    // Create download link
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.setAttribute('href', url);
    link.setAttribute('download', `inventory_export_${new Date().toISOString().slice(0,10)}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    showToast('Export completed successfully', 'success');
}

// Show toast notification
function showToast(message, type = 'info') {
    // Create toast element
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : type === 'warning' ? 'warning' : 'primary'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    // Add to toast container
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '1055';
        document.body.appendChild(toastContainer);
    }
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    // Show toast
    const toastElement = toastContainer.lastElementChild;
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    
    // Remove toast element after it's hidden
    toastElement.addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}