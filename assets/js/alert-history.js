// Alert History JavaScript Functionality
document.addEventListener('DOMContentLoaded', function() {
    initializeAlertHistory();
});

function initializeAlertHistory() {
    initializeFilters();
    initializeTableInteractions();
    initializeSearch();
}

// Filter Functionality
function initializeFilters() {
    const alertTypeFilter = document.getElementById('alertTypeFilter');
    const statusFilter = document.getElementById('statusFilter');
    const priorityFilter = document.getElementById('priorityFilter');
    
    if (alertTypeFilter) {
        alertTypeFilter.addEventListener('change', filterAlerts);
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterAlerts);
    }
    
    if (priorityFilter) {
        priorityFilter.addEventListener('change', filterAlerts);
    }
}

// Filter alerts based on selected filters
function filterAlerts() {
    const alertTypeFilter = document.getElementById('alertTypeFilter')?.value.toLowerCase() || '';
    const statusFilter = document.getElementById('statusFilter')?.value.toLowerCase() || '';
    const priorityFilter = document.getElementById('priorityFilter')?.value.toLowerCase() || '';
    
    const tableRows = document.querySelectorAll('.alert-table tbody tr');
    let visibleCount = 0;
    
    tableRows.forEach(row => {
        const alertType = row.dataset.type || '';
        const status = row.dataset.status || '';
        const priority = row.dataset.priority || '';
        
        // Check filters
        const matchesType = !alertTypeFilter || alertType.includes(alertTypeFilter);
        const matchesStatus = !statusFilter || status === statusFilter;
        const matchesPriority = !priorityFilter || priority === priorityFilter;
        
        if (matchesType && matchesStatus && matchesPriority) {
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
        const displayCount = Math.min(10, count);
        paginationInfo.textContent = `Showing 1-${displayCount} of ${count} alerts`;
    }
}

// Toggle empty state
function toggleEmptyState(show) {
    const tableContainer = document.querySelector('.alert-table-container');
    const paginationContainer = document.querySelector('.pagination-container');
    
    if (show) {
        if (tableContainer) tableContainer.style.display = 'none';
        if (paginationContainer) paginationContainer.style.display = 'none';
        
        // Show empty state message
        showEmptyState('No alerts match your current filters.');
    } else {
        if (tableContainer) tableContainer.style.display = 'block';
        if (paginationContainer) paginationContainer.style.display = 'flex';
        
        // Hide empty state message
        hideEmptyState();
    }
}

// Show empty state message
function showEmptyState(message) {
    const cardBody = document.querySelector('.card-body');
    let emptyState = document.querySelector('.filter-empty-state');
    
    if (!emptyState && cardBody) {
        emptyState = document.createElement('div');
        emptyState.className = 'filter-empty-state text-center py-5';
        emptyState.innerHTML = `
            <i class="bi bi-funnel display-1 text-muted"></i>
            <h3 class="mt-3">No Results Found</h3>
            <p class="text-muted">${message}</p>
            <button class="btn btn-outline-primary btn-sm" onclick="clearAllFilters()">
                <i class="bi bi-x-circle me-1"></i>Clear Filters
            </button>
        `;
        cardBody.appendChild(emptyState);
    }
    
    if (emptyState) {
        emptyState.style.display = 'block';
    }
}

// Hide empty state message
function hideEmptyState() {
    const emptyState = document.querySelector('.filter-empty-state');
    if (emptyState) {
        emptyState.style.display = 'none';
    }
}

// Clear all filters
function clearAllFilters() {
    const filters = ['alertTypeFilter', 'statusFilter', 'priorityFilter'];
    
    filters.forEach(filterId => {
        const filter = document.getElementById(filterId);
        if (filter) {
            filter.value = '';
        }
    });
    
    // Trigger filter update
    filterAlerts();
}

// Table interactions
function initializeTableInteractions() {
    const tableRows = document.querySelectorAll('.alert-table tbody tr');
    
    tableRows.forEach(row => {
        row.addEventListener('click', function() {
            const alertId = this.dataset.alertId;
            if (alertId) {
                viewAlertDetails(alertId);
            }
        });
        
        // Add hover effect
        row.style.cursor = 'pointer';
        
        // Add click animation
        row.addEventListener('click', function() {
            this.classList.add('highlighted');
            setTimeout(() => {
                this.classList.remove('highlighted');
            }, 1000);
        });
    });
}

// View alert details
function viewAlertDetails(alertId) {
    const row = document.querySelector(`tr[data-alert-id="${alertId}"]`);
    if (!row) return;
    
    // Extract alert information from the row
    const alertInfo = {
        id: alertId,
        datetime: row.querySelector('.datetime-cell .date')?.textContent + ' ' + 
                 row.querySelector('.datetime-cell .time')?.textContent,
        deviceId: row.querySelector('.device-id')?.textContent,
        alertType: row.querySelector('.alert-type-badge')?.textContent?.trim(),
        location: row.querySelector('.location-cell')?.textContent?.trim(),
        status: row.querySelector('.status-badge')?.textContent?.trim(),
        resolvedBy: row.querySelector('.resolved-by-cell')?.textContent?.trim(),
        priority: row.querySelector('.priority-badge')?.textContent?.trim()
    };
    
    // Show alert details modal (you can enhance this)
    showAlertModal(alertInfo);
}

// Show alert details modal
function showAlertModal(alertInfo) {
    // Create modal HTML
    const modalHtml = `
        <div class="modal fade" id="alertDetailsModal" tabindex="-1" aria-labelledby="alertDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="alertDetailsModalLabel">
                            <i class="bi bi-exclamation-triangle me-2"></i>Alert Details #${alertInfo.id}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Basic Information</h6>
                                <table class="table table-sm">
                                    <tr><td><strong>Alert ID:</strong></td><td>#${alertInfo.id}</td></tr>
                                    <tr><td><strong>Date & Time:</strong></td><td>${alertInfo.datetime}</td></tr>
                                    <tr><td><strong>Device ID:</strong></td><td>${alertInfo.deviceId}</td></tr>
                                    <tr><td><strong>Location:</strong></td><td>${alertInfo.location}</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Alert Status</h6>
                                <table class="table table-sm">
                                    <tr><td><strong>Type:</strong></td><td>${alertInfo.alertType}</td></tr>
                                    <tr><td><strong>Priority:</strong></td><td>${alertInfo.priority}</td></tr>
                                    <tr><td><strong>Status:</strong></td><td>${alertInfo.status}</td></tr>
                                    <tr><td><strong>Resolved By:</strong></td><td>${alertInfo.resolvedBy}</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="exportAlertDetails('${alertInfo.id}')">
                            <i class="bi bi-download me-1"></i>Export
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('alertDetailsModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('alertDetailsModal'));
    modal.show();
    
    // Clean up modal after it's hidden
    document.getElementById('alertDetailsModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// Initialize search functionality
function initializeSearch() {
    // You can add a search input if needed
    const searchInput = document.getElementById('alertSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            searchAlerts(searchTerm);
        });
    }
}

// Search alerts
function searchAlerts(searchTerm) {
    const tableRows = document.querySelectorAll('.alert-table tbody tr');
    let visibleCount = 0;
    
    tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        
        if (text.includes(searchTerm)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    updatePaginationInfo(visibleCount);
    toggleEmptyState(visibleCount === 0);
}

// Export alert details
function exportAlertDetails(alertId) {
    console.log('Exporting alert details for ID:', alertId);
    // Implement export functionality here
    alert('Export functionality would be implemented here');
}

// Refresh alerts
function refreshAlerts() {
    location.reload();
}

// Pagination functionality
function goToPage(page) {
    console.log('Going to page:', page);
    // Implement pagination logic here
}

// Initialize pagination buttons
document.addEventListener('DOMContentLoaded', function() {
    const paginationLinks = document.querySelectorAll('.pagination .page-link');
    
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (this.parentElement.classList.contains('disabled')) return;
            
            // Remove active class from all items
            document.querySelectorAll('.pagination .page-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Add active class to clicked item (if it's a number)
            if (!isNaN(this.textContent)) {
                this.parentElement.classList.add('active');
                goToPage(parseInt(this.textContent));
            }
        });
    });
});

// Utility functions
function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString();
}

function getRelativeTime(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
    return `${Math.floor(diffInSeconds / 86400)} days ago`;
}

// Export functionality
function exportAlerts() {
    console.log('Exporting all alerts...');
    // You can implement CSV export here
    alert('Export functionality would be implemented here');
}