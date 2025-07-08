// Call Logs JavaScript Functionality
document.addEventListener('DOMContentLoaded', function() {
    initializeCallLogs();
});

function initializeCallLogs() {
    initializeSearch();
    initializeFilters();
    initializeTableSorting();
    initializeModals();
}

// Search Functionality
function initializeSearch() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', EVA.utils.debounce(function(e) {
            filterCallLogs();
        }, 300));
    }
}

// Filter Functionality
function initializeFilters() {
    const statusFilter = document.getElementById('statusFilter');
    const directionFilter = document.getElementById('directionFilter');
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterCallLogs);
    }
    
    if (directionFilter) {
        directionFilter.addEventListener('change', filterCallLogs);
    }
}

// Filter call logs based on search and filters
function filterCallLogs() {
    const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const statusFilter = document.getElementById('statusFilter')?.value.toLowerCase() || '';
    const directionFilter = document.getElementById('directionFilter')?.value.toLowerCase() || '';
    
    const tableRows = document.querySelectorAll('#callLogsTable tbody tr');
    let visibleCount = 0;
    
    tableRows.forEach(row => {
        const recordId = row.dataset.recordId;
        const status = row.dataset.status || '';
        const direction = row.dataset.direction || '';
        
        // Get text content for search
        const searchableText = [
            row.querySelector('.device-serial')?.textContent || '',
            row.querySelector('.contact-name')?.textContent || '',
            row.querySelector('.contact-number')?.textContent || '',
            row.querySelector('.location-text')?.textContent || '',
            row.querySelector('.user-email')?.textContent || ''
        ].join(' ').toLowerCase();
        
        // Check if row matches all filters
        const matchesSearch = !searchTerm || searchableText.includes(searchTerm);
        const matchesStatus = !statusFilter || status === statusFilter;
        const matchesDirection = !directionFilter || direction === directionFilter;
        
        if (matchesSearch && matchesStatus && matchesDirection) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Show/hide empty state
    updateEmptyState(visibleCount === 0);
}

// Update empty state visibility
function updateEmptyState(show) {
    const emptyState = document.querySelector('.empty-state');
    const table = document.querySelector('.table-responsive');
    
    if (emptyState && table) {
        if (show) {
            table.style.display = 'none';
            emptyState.style.display = 'block';
        } else {
            table.style.display = 'block';
            emptyState.style.display = 'none';
        }
    }
}

// Table Sorting
function initializeTableSorting() {
    const headers = document.querySelectorAll('#callLogsTable thead th');
    
    headers.forEach((header, index) => {
        if (index < headers.length - 1) { // Skip actions column
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => sortTable(index));
            
            // Add sort indicator
            const sortIcon = document.createElement('i');
            sortIcon.className = 'bi bi-arrow-down-up ms-1 sort-icon';
            sortIcon.style.opacity = '0.5';
            header.appendChild(sortIcon);
        }
    });
}

function sortTable(columnIndex) {
    const table = document.getElementById('callLogsTable');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    // Determine sort direction
    const header = table.querySelectorAll('thead th')[columnIndex];
    const sortIcon = header.querySelector('.sort-icon');
    const isAscending = !header.classList.contains('sort-asc');
    
    // Reset all sort indicators
    table.querySelectorAll('thead th').forEach(th => {
        th.classList.remove('sort-asc', 'sort-desc');
        const icon = th.querySelector('.sort-icon');
        if (icon) {
            icon.className = 'bi bi-arrow-down-up ms-1 sort-icon';
            icon.style.opacity = '0.5';
        }
    });
    
    // Set current sort indicator
    header.classList.add(isAscending ? 'sort-asc' : 'sort-desc');
    sortIcon.className = `bi bi-arrow-${isAscending ? 'up' : 'down'} ms-1 sort-icon`;
    sortIcon.style.opacity = '1';
    
    // Sort rows
    rows.sort((a, b) => {
        const aCell = a.cells[columnIndex];
        const bCell = b.cells[columnIndex];
        
        let aValue = aCell.textContent.trim();
        let bValue = bCell.textContent.trim();
        
        // Handle different data types
        if (columnIndex === 4) { // Date column
            aValue = new Date(aValue.replace(/(\d{2})\/(\d{2})\/(\d{4})/, '$3-$2-$1'));
            bValue = new Date(bValue.replace(/(\d{2})\/(\d{2})\/(\d{4})/, '$3-$2-$1'));
        } else if (columnIndex === 3) { // Duration column
            aValue = convertDurationToSeconds(aValue);
            bValue = convertDurationToSeconds(bValue);
        }
        
        if (aValue < bValue) return isAscending ? -1 : 1;
        if (aValue > bValue) return isAscending ? 1 : -1;
        return 0;
    });
    
    // Reorder rows in DOM
    rows.forEach(row => tbody.appendChild(row));
}

function convertDurationToSeconds(duration) {
    const parts = duration.split(':');
    if (parts.length === 2) {
        return parseInt(parts[0]) * 60 + parseInt(parts[1]);
    }
    return 0;
}

// Modal Functionality
function initializeModals() {
    // Initialize Bootstrap modals if available
    if (typeof bootstrap !== 'undefined') {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            new bootstrap.Modal(modal);
        });
    }
}

// Call Details Modal
function viewCallDetails(recordId) {
    showLoading('Loading call details...');
    
    fetch(`../helpers/call_helper.php?action=get&id=${recordId}`)
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.error) {
                showToast(data.error, 'error');
                return;
            }
            
            displayCallDetails(data);
        })
        .catch(error => {
            hideLoading();
            console.error('Error fetching call details:', error);
            showToast('Failed to load call details', 'error');
        });
}

function displayCallDetails(callData) {
    const modalContent = document.getElementById('callDetailsContent');
    
    const detailsHTML = `
        <div class="call-details-grid">
            <div class="detail-section">
                <h6><i class="bi bi-phone me-2"></i>Call Information</h6>
                <div class="detail-row">
                    <span class="detail-label">Record ID:</span>
                    <span class="detail-value">${callData.RecordID}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Device Serial:</span>
                    <span class="detail-value">${callData.SerialNoFK}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Direction:</span>
                    <span class="badge ${callData.Direction === 'Incoming' ? 'bg-success' : 'bg-primary'}">${callData.Direction}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="badge ${getStatusBadgeClass(callData.Status)}">${callData.Status}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Duration:</span>
                    <span class="detail-value">${callData.Duration || '0:00'}</span>
                </div>
            </div>
            
            <div class="detail-section">
                <h6><i class="bi bi-person me-2"></i>Contact Information</h6>
                <div class="detail-row">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value">${callData.Firstname} ${callData.Lastname}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Phone Number:</span>
                    <span class="detail-value">${callData.Number || 'Unknown'}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Address:</span>
                    <span class="detail-value">${callData.Address || 'Unknown'}</span>
                </div>
                ${callData.MedicalCondition ? `
                <div class="detail-row">
                    <span class="detail-label">Medical Condition:</span>
                    <span class="detail-value text-warning">${callData.MedicalCondition}</span>
                </div>
                ` : ''}
            </div>
            
            <div class="detail-section">
                <h6><i class="bi bi-clock me-2"></i>Timing</h6>
                <div class="detail-row">
                    <span class="detail-label">Date & Time:</span>
                    <span class="detail-value">${formatDateTime(callData.Datetime)}</span>
                </div>
            </div>
        </div>
    `;
    
    modalContent.innerHTML = detailsHTML;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('callDetailsModal'));
    modal.show();
}

function getStatusBadgeClass(status) {
    switch(status) {
        case 'Unanswered': return 'bg-danger';
        case 'Active': return 'bg-warning text-dark';
        case 'Resolved': return 'bg-success';
        case 'Cancelled': return 'bg-secondary';
        default: return 'bg-secondary';
    }
}

function formatDateTime(datetime) {
    try {
        let date;
        if (datetime.includes(',')) {
            // Format: "24-10-21,16:13:52"
            const cleaned = datetime.replace(/\+\d+$/, '');
            date = new Date(cleaned.replace(/(\d{2})-(\d{2})-(\d{2}),(\d{2}):(\d{2}):(\d{2})/, '20$3-$2-$1T$4:$5:$6'));
        } else {
            date = new Date(datetime);
        }
        
        return date.toLocaleString();
    } catch (error) {
        return datetime;
    }
}

// Mark call as resolved
function markAsResolved(recordId) {
    if (!confirm('Mark this call as resolved?')) {
        return;
    }
    
    showLoading('Updating call status...');
    
    fetch('../helpers/call_helper.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'update_status',
            record_id: recordId,
            status: 'Resolved'
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            showToast('Call marked as resolved', 'success');
            // Refresh the page or update the row
            location.reload();
        } else {
            showToast(data.error || 'Failed to update call status', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error updating call status:', error);
        showToast('Failed to update call status', 'error');
    });
}

// Delete call log (admin only)
function deleteCallLog(recordId) {
    if (!confirm('Are you sure you want to delete this call log? This action cannot be undone.')) {
        return;
    }
    
    showLoading('Deleting call log...');
    
    fetch('../helpers/call_helper.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'delete',
            record_id: recordId
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            showToast('Call log deleted successfully', 'success');
            // Remove the row from table
            const row = document.querySelector(`tr[data-record-id="${recordId}"]`);
            if (row) {
                row.remove();
            }
        } else {
            showToast(data.error || 'Failed to delete call log', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error deleting call log:', error);
        showToast('Failed to delete call log', 'error');
    });
}

// Export call logs
function exportCallLogs() {
    showLoading('Preparing export...');
    
    // Get current filter values
    const searchTerm = document.getElementById('searchInput')?.value || '';
    const statusFilter = document.getElementById('statusFilter')?.value || '';
    const directionFilter = document.getElementById('directionFilter')?.value || '';
    
    const params = new URLSearchParams({
        action: 'export',
        search: searchTerm,
        status: statusFilter,
        direction: directionFilter,
        format: 'csv'
    });
    
    // Create download link
    const downloadUrl = `../helpers/call_helper.php?${params.toString()}`;
    
    // Create temporary link and trigger download
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = `call_logs_${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    hideLoading();
    showToast('Export started', 'success');
}

// Refresh call logs
function refreshCallLogs() {
    showLoading('Refreshing call logs...');
    location.reload();
}

// Auto-refresh functionality (optional)
function startAutoRefresh(intervalMinutes = 5) {
    setInterval(() => {
        // Only refresh if no modals are open
        if (!document.querySelector('.modal.show')) {
            refreshCallLogs();
        }
    }, intervalMinutes * 60 * 1000);
}

// Initialize auto-refresh (uncomment if needed)
// startAutoRefresh(5); // Refresh every 5 minutes