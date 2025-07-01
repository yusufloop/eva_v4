function sort(ascending, columnClassName, tableId) {
	var tbody = document.getElementById(tableId).getElementsByTagName(
			"tbody")[0];
	var rows = tbody.getElementsByTagName("tr");
	var unsorted = true;
	while (unsorted) {
		unsorted = false
		for (var r = 0; r < rows.length - 1; r++) {
			var row = rows[r];
			var nextRow = rows[r + 1];
			var value = row.getElementsByClassName(columnClassName)[0].innerHTML;
			var nextValue = nextRow.getElementsByClassName(columnClassName)[0].innerHTML;
			value = value.replace(',', ''); // in case a comma is used in float number
			nextValue = nextValue.replace(',', '');
			if (!isNaN(value)) {
				value = parseFloat(value);
				nextValue = parseFloat(nextValue);
			}
			if (ascending ? value > nextValue : value < nextValue) {
				tbody.insertBefore(nextRow, row);
				unsorted = true;
			}
		}
	}
};

function editDevice(U_Id) {
    // Send an AJAX request to the PHP backend to get the device data
    fetch('functions/admin_dashboard_action.php?u_id=' + U_Id)
        .then(response => response.json())
        .then(data => {
        	console.log(data); // Log the fetched data to the console to see the query result

            document.getElementById("addDeviceFormContainer").classList.add("active");
            document.getElementById("userId").value = data.UserId;
            document.getElementById("Symptom").value = data.Symptom;
            document.getElementById("serialNo").value = data.SerialNo;
            document.getElementById("emergencyNo1").value = data.EmergencyNo1;
            document.getElementById("emergencyNo2").value = data.EmergencyNo2;
            document.getElementById("Firstname").value = data.Firstname;
            document.getElementById("Lastname").value = data.Lastname;
            document.getElementById("Gender").value = data.Gender;
            document.getElementById("Age").value = data.Age;
            document.getElementById("Address").value = data.Address;

    		// document.getElementById("serialNo").setAttribute("readonly", true);
            document.querySelector('button[name="add_device"]').innerText = "Update Device";
        })
        .catch(error => console.error('Error:', error));
}


document.getElementById('searchBar').addEventListener('keyup', function() {
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
