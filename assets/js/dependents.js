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
    fetch(`../actions/dependent/get.php?dependentId=${dependentId}`)
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
        window.location.href = `../actions/dependent/delete.php?dependentId=${dependentId}`;
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
    const tableRows = document.querySelectorAll('.eva-table tbody tr');
    
    tableRows.forEach(row => {
        const firstname = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
        const lastname = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        const address = row.querySelector('td:nth-child(5)').textContent.toLowerCase();
        const medicalCondition = row.querySelector('td:nth-child(7)').textContent.toLowerCase();
        const status = row.getAttribute('data-status');
        
        const matchesSearch = firstname.includes(searchTerm) || 
                            lastname.includes(searchTerm) || 
                            address.includes(searchTerm) ||
                            medicalCondition.includes(searchTerm);
        
        let matchesStatus = true;
        if (statusFilter) {
            if (statusFilter === 'medical-condition') {
                matchesStatus = medicalCondition !== 'none' && medicalCondition.trim() !== '';
            } else {
                matchesStatus = status === statusFilter;
            }
        }
        
        if (matchesSearch && matchesStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Bulk actions placeholder
function openBulkActions() {
    alert('Bulk actions feature coming soon!');
}