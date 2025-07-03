// JavaScript to show the form when the button is clicked
document.getElementById("showAddDeviceFormBtn").onclick = function() {
    document.getElementById("addDeviceFormContainer").classList.add("active");
    this.style.display = "none";
};

document.getElementById("addDeviceFormContainer").onclick = function(event) {
    if (event.target === this) {
        this.classList.remove("active");
        document.getElementById("showAddDeviceFormBtn").style.display = "inline-block";
    }
};

// JavaScript to show the form when the button is clicked
document.getElementById("showAddUserFormBtn").onclick = function() {
    document.getElementById("addUserFormContainer").classList.add("active");
    this.style.display = "none";
};

// document.getElementById("addUserFormContainer").onclick = function(event) {
//     if (event.target === this) {
//         this.classList.remove("active");
//         document.getElementById("showAddUserFormBtn").style.display = "inline-block";
//     }
// };


document.addEventListener("DOMContentLoaded", function () {
    const userIdInput = document.getElementById("userIdInput");
    const userDropdownOptions = document.querySelector(".user-dropdown-options");
    const hiddenUserIdInput = document.getElementById("hiddenUserIdInput");

    // Variables for Dependent dropdown
    const dependentSelect = document.getElementById("dependentSelect");
    const existingDependentContainer = document.getElementById("existingDependentContainer");
    const dependentDropdownOptions = document.querySelector(".dependent-dropdown-options");
    const existingDependentInput = document.getElementById("existingDependentInput");
    const hiddenDependentInput = document.getElementById("existingDependent");

    const newDependentContainer = document.getElementById("newDependentContainer");
    const firstNameInput = document.getElementById("Firstname");
    const lastNameInput = document.getElementById("Lastname");
    let savedDependent = null;

    dependentSelect.addEventListener("change", function () {
        if (dependentSelect.value === "existing") {
            existingDependentContainer.classList.remove("hidden");
            newDependentContainer.classList.add("hidden");

            existingDependent.value = savedDependent;
            const options = document.querySelectorAll('.dropdown-option');
            options.forEach(option => {
                if (option.getAttribute('data-value') === savedDependent.toString()) {
                    existingDependentInput.value = option.querySelector('strong').innerText;
                }
            });


            // Remove the required attribute when selecting an existing dependent
            firstNameInput.removeAttribute("required");
            lastNameInput.removeAttribute("required");
        } else if (dependentSelect.value === "new") {

            
            existingDependentContainer.classList.add("hidden");
            newDependentContainer.classList.remove("hidden");

            // Clear existing dependent dropdown
            savedDependent = existingDependent.value;
            existingDependentInput.value = "";
            existingDependent.value = "";

            // Add the required attribute when adding a new dependent
            firstNameInput.setAttribute("required", "required");
            lastNameInput.setAttribute("required", "required");
        } else {
            // Hide both if no valid selection
            existingDependentContainer.classList.add("hidden");
            newDependentContainer.classList.add("hidden");

            // Remove required attributes if neither is selected
            firstNameInput.removeAttribute("required");
            lastNameInput.removeAttribute("required");
        }
    });

    // Show dropdown when clicking on the input
    userIdInput.addEventListener("focus", function () {
        userDropdownOptions.classList.remove("hidden");
    });

    // Filter dropdown options as the user types
    userIdInput.addEventListener("input", function () {
        const filter = userIdInput.value.toLowerCase();
        const options = userDropdownOptions.querySelectorAll(".user-dropdown-option");
        options.forEach(option => {
            const email = option.innerText.toLowerCase();
            if (email.includes(filter)) {
                option.style.display = ""; // Show matching options
            } else {
                option.style.display = "none"; // Hide non-matching options
            }
        });
    });

    // Handle option selection
    userDropdownOptions.addEventListener("click", function (event) {
        if (event.target.classList.contains("user-dropdown-option")) {
            const selectedOption = event.target;
            const userId = selectedOption.getAttribute("data-value");
            const email = selectedOption.innerText.trim();

            // Set values
            userIdInput.value = email; // Show email in the input
            hiddenUserIdInput.value = userId; // Set UserID in the hidden input

            // Hide the dropdown
            userDropdownOptions.classList.add("hidden");

            fetchDependents(userId);
        }
    });

    // Hide dropdown when clicking outside
    document.addEventListener("click", function (event) {
        if (!userIdInput.contains(event.target) && !userDropdownOptions.contains(event.target)) {
            userDropdownOptions.classList.add("hidden");
        }
    });

    // Dependent Select - Show/hide dependent options
    dependentSelect.addEventListener("change", function () {
        if (this.value === "existing") {
            existingDependentContainer.classList.remove("hidden");
        } else {
            existingDependentContainer.classList.add("hidden");
        }
    });

    // Dependent Dropdown - Toggle visibility
    existingDependentInput.addEventListener("click", function () {
        dependentDropdownOptions.classList.toggle("hidden");
    });
});

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


function editDevice(serialNo) {
    // Send an AJAX request to the PHP backend to get the device data
    fetch('actions/admin_dashboard_action.php?serialNo=' + serialNo)
        .then(response => response.json())
        .then(data => {
        	console.log(data); // Log the fetched data to the console to see the query result
            
            document.getElementById("addDeviceFormContainer").classList.add("active");
            document.getElementById("serialNo").value = data.SerialNoFK;
            document.getElementById("emergencyNo1").value = data.EmergencyNo1;
            document.getElementById("emergencyNo2").value = data.EmergencyNo2;

            if (data.UserIDFK) {
                document.getElementById("hiddenUserIdInput").value = data.UserIDFK;

                // Set the visible dropdown text
                const options = document.querySelectorAll('.user-dropdown-option');
                options.forEach(option => {
                    if (option.getAttribute('data-value') === data.UserIDFK.toString()) {
                        document.getElementById("userIdInput").value = data.Email.toString();
                    }
                });

            }

            if (data.DependentIDFK) {
                // Fetch dependents for the user
                fetchDependents(data.UserIDFK).then(() => {
                    document.getElementById("dependentSelect").value = "existing";
                    document.getElementById("existingDependentContainer").classList.remove("hidden");
                    document.getElementById("existingDependent").value = data.DependentIDFK;

                    // Set the visible dropdown text
                    const options = document.querySelectorAll('.dependent-dropdown-option');
                    options.forEach(option => {
                        if (option.getAttribute('data-value') === data.DependentIDFK.toString()) {
                            document.getElementById("existingDependentInput").value = option.querySelector('strong').innerText;
                        }
                    });

                    document.getElementById("newDependentContainer").classList.add("hidden");
                });
            } else {
                document.getElementById("dependentSelect").value = "new";
                document.getElementById("newDependentContainer").classList.remove("hidden");
                document.getElementById("existingDependentContainer").classList.add("hidden");
            }

    		document.getElementById("serialNo").setAttribute("readonly", true);
            document.querySelector('button[name="add_device"]').innerText = "Update Device";
        })
        .catch(error => console.error('Error:', error));
}

function fetchDependents(userId) {
    const dependentDropdownOptions = document.querySelector(".dependent-dropdown-options");
    const existingDependentInput = document.getElementById("existingDependentInput");
    const hiddenDependentInput = document.getElementById("existingDependent");

    // Return the fetch Promise
    return fetch(`actions/dependent/get.php?userId=${userId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log("Fetched dependents:", data);

            // Update dependent dropdown
            dependentDropdownOptions.innerHTML = ''; // Clear existing options
            data.forEach(dependent => {
                const optionDiv = document.createElement('div');
                optionDiv.classList.add('dependent-dropdown-option');
                optionDiv.setAttribute('data-value', dependent.DependentID);

                // Populate dependent name and address
                optionDiv.innerHTML = `
                    <strong>${dependent.Firstname} ${dependent.Lastname}</strong>
                    <div class="address">${dependent.Address}</div>
                `;

                // Add click event for dependent selection
                optionDiv.addEventListener("click", function () {
                    existingDependentInput.value = `${dependent.Firstname} ${dependent.Lastname}`;
                    hiddenDependentInput.value = dependent.DependentID;
                    dependentDropdownOptions.classList.add("hidden"); // Hide options
                });

                dependentDropdownOptions.appendChild(optionDiv);
            });
        })
        .catch(error => console.error("Error fetching dependents:", error));
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
