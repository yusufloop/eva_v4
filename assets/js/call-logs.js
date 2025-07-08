// Call Logs JavaScript Functionality
document.addEventListener('DOMContentLoaded', function() {
    initializeCallLogs();
});

function initializeCallLogs() {
    initializeFilters();
    initializeTableInteractions();
}

// Filter Functionality
function initializeFilters() {
    const statusFilter = document.getElementById('statusFilter');
    const periodFilter = document.getElementById('periodFilter');
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterCallLogs);
    }
    
    if (periodFilter) {
        periodFilter.addEventListener('change', filterCallLogs);
    }
}

// Filter call logs based on filters
function filterCallLogs() {
    const statusFilter = document.getElementById('statusFilter')?.value.toLowerCase() || '';
    const periodFilter = document.getElementById('periodFilter')?.value || '';
    
    const tableRows = document.querySelectorAll('.device-table tbody tr');
    let visibleCount = 0;
    
    tableRows.forEach(row => {
        const status = row.dataset.status || '';
        const timestamp = row.querySelector('.timestamp')?.textContent || '';
        
        // Check status filter
        const matchesStatus = !statusFilter || status === statusFilter;
        
        // Check period filter (simplified - you can enhance this)
        let matchesPeriod = true;
        if (periodFilter && periodFilter !== 'all') {
            const days = parseInt(periodFilter);
            const callDate = new Date(timestamp);
            const cutoffDate = new Date();
            cutoffDate.setDate(cutoffDate.getDate() - days);
            matchesPeriod = callDate >= cutoffDate;
        }
        
        if (matchesStatus && matchesPeriod) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update pagination info
    updatePaginationInfo(visibleCount);
}

// Update pagination information
function updatePaginationInfo(count) {
    const paginationInfo = document.querySelector('.pagination-info');
    if (paginationInfo) {
        paginationInfo.textContent = `Showing 1-${Math.min(10, count)} of ${count} devices`;
    }
}

// Table interactions
function initializeTableInteractions() {
    const tableRows = document.querySelectorAll('.device-table tbody tr');
    
    tableRows.forEach(row => {
        row.addEventListener('click', function() {
            const recordId = this.dataset.recordId;
            if (recordId) {
                viewCallDetails(recordId);
            }
        });
        
        // Add hover effect
        row.style.cursor = 'pointer';
    });
}

// View call details (simplified)
function viewCallDetails(recordId) {
    // For now, just show an alert with the record ID
    // You can enhance this to show a modal with detailed information
    console.log('Viewing call details for record:', recordId);
    
    // Example: Show basic info
    const row = document.querySelector(`tr[data-record-id="${recordId}"]`);
    if (row) {
        const deviceName = row.querySelector('.device-name')?.textContent;
        const status = row.querySelector('.status-badge')?.textContent;
        const user = row.querySelector('.user-name')?.textContent;
        
        alert(`Call Details:\nDevice: ${deviceName}\nStatus: ${status}\nUser: ${user}`);
    }
}

// Export functionality (simplified)
function exportCallLogs() {
    console.log('Exporting call logs...');
    // You can implement CSV export here
    alert('Export functionality would be implemented here');
}

// Refresh call logs
function refreshCallLogs() {
    location.reload();
}

// Pagination functionality
function goToPage(page) {
    console.log('Going to page:', page);
    // Implement pagination logic here
}

// Initialize pagination buttons
document.addEventListener('DOMContentLoaded', function() {
    const paginationBtns = document.querySelectorAll('.pagination-btn');
    
    paginationBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.disabled) return;
            
            // Remove active class from all buttons
            paginationBtns.forEach(b => b.classList.remove('active'));
            
            // Add active class to clicked button (if it's a number)
            if (!isNaN(this.textContent)) {
                this.classList.add('active');
                goToPage(parseInt(this.textContent));
            }
        });
    });
});