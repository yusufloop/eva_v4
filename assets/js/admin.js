function editInventory(InventoryNo) {
    // Send an AJAX request to the PHP backend to get the device data
    fetch('functions/admin_inventory_tabs.php?InventoryNo=' + InventoryNo)
        .then(response => response.json())
        .then(data => {
            console.log(data); // Log the fetched data to the console to see the query result
            
            document.getElementById("editInventoryFormContainer").classList.add("active");
            document.getElementById("hiddenInventoryIdInput").value = data.InventoryNo;
            document.getElementById("SerialNo").value = data.SerialNo;
            document.getElementById("DeviceType").value = data.DeviceType;
        })
        .catch(error => console.error('Error:', error));
}

function editUser(userId) {
    // Send an AJAX request to the PHP backend to get the device data
    fetch('functions/admin_users_tabs.php?userId=' + userId)
        .then(response => response.json())
        .then(data => {
        	console.log(data); // Log the fetched data to the console to see the query result
            
            document.getElementById("addUserFormContainer").classList.add("active");
            document.getElementById("hiddenUserIdInput").value = data.UserID;
            document.getElementById("email").value = data.Email;

            // Set the visible dropdown text
            const isAdminSelect = document.getElementById("IsAdmin");
            if (data.IsAdmin === "Yes") {
                isAdminSelect.value = "Yes";
            } else if (data.IsAdmin === "No") {
                isAdminSelect.value = "No";
            } else {
                isAdminSelect.value = ""; // Default to blank if not set
            }

        })
        .catch(error => console.error('Error:', error));
}

document.getElementById('user-searchBar').addEventListener('keyup', function() {
    var filter = this.value.toUpperCase();
    var table = document.getElementById('content-table3');
    var tr = table.getElementsByTagName('tr');

    // Loop through all table rows, and hide those who don't match the search query
    for (var i = 1; i < tr.length; i++) {
        var td = tr[i].getElementsByTagName('td');
        var found = false;
        for (var j = 0; j < td.length; j++) {
            if (td[j]) {
                if (td[j].innerText.toUpperCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }
        if (found) {
            tr[i].style.display = '';
        } else {
            tr[i].style.display = 'none';
        }
    }
});

document.getElementById('inventory-searchBar').addEventListener('keyup', function() {
    var filter = this.value.toUpperCase();
    var table = document.getElementById('content-table4');
    var tr = table.getElementsByTagName('tr');

    // Loop through all table rows, and hide those who don't match the search query
    for (var i = 1; i < tr.length; i++) {
        var td = tr[i].getElementsByTagName('td');
        var found = false;
        for (var j = 0; j < td.length; j++) {
            if (td[j]) {
                if (td[j].innerText.toUpperCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }
        if (found) {
            tr[i].style.display = '';
        } else {
            tr[i].style.display = 'none';
        }
    }
});
