// Open Add Dependent Modal
function openAddDependentModal() {
    document.getElementById('modalTitle').textContent = 'Add New Dependent';
    document.getElementById('submitButtonText').textContent = 'Add Dependent';
    document.getElementById('dependentForm').reset();
    document.getElementById('dependentId').value = '';
    document.getElementById('addDependentModal').classList.add('active');
}

// Edit Dependent
function editDependent(dependentId) {
    fetch(`../actions/dependents/get.php?dependentId=${dependentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert('Error: ' + data.error);
                return;
            }
            
            // Populate form
            document.getElementById('modalTitle').textContent = 'Edit Dependent';
            document.getElementById('submitButtonText').textContent = 'Update Dependent';
            document.getElementById('dependentId').value = data.DependentID;
            document.getElementById('firstname').value = data.Firstname;
            document.getElementById('lastname').value = data.Lastname;
            document.getElementById('gender').value = data.Gender;
            document.getElementById('dob').value = data.DOB;
            document.getElementById('address').value = data.Address;
            document.getElementById('postalCode').value = data.PostalCode;
            document.getElementById('medicalCondition').value = data.MedicalCondition || '';
            
            if (document.getElementById('userId')) {
                document.getElementById('userId').value = data.UserIDFK;
            }
            
            document.getElementById('addDependentModal').classList.add('active');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading dependent data');
        });
}

// Delete Dependent
function deleteDependent(dependentId) {
    if (confirm('Are you sure you want to delete this dependent? This action cannot be undone.')) {
        window.location.href = `../actions/dependents/delete.php?dependentId=${dependentId}`;
    }
}

// View Dependent Details
function viewDependent(dependentId) {
    // You can implement a view modal or navigate to a detail page
    console.log('View dependent:', dependentId);
    // For now, just edit
    editDependent(dependentId);
}

// Close Modal
function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('dependentSearchBar');
    const statusFilter = document.getElementById('statusFilter');
    
    if (searchInput) {
        searchInput.addEventListener('input', filterDependents);
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterDependents);
    }
});

function filterDependents() {
    const searchTerm = document.getElementById('dependentSearchBar').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const dependentItems = document.querySelectorAll('.dependent-item');
    
    dependentItems.forEach(item => {
        const name = item.querySelector('.dependent-name').textContent.toLowerCase();
        const address = item.querySelector('.dependent-location').textContent.toLowerCase();
        const status = item.getAttribute('data-status');
        
        const matchesSearch = name.includes(searchTerm) || address.includes(searchTerm);
        const matchesStatus = !statusFilter || status === statusFilter;
        
        if (matchesSearch && matchesStatus) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

// Bulk actions placeholder
function openBulkActions() {
    alert('Bulk actions feature coming soon!');
}