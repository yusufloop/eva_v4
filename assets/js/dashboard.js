// JavaScript to show the form when the button is clicked
document.getElementById("showFormBtn").onclick = function() {
    resetDeviceForm();
    document.getElementById("addDeviceFormContainer").classList.add("active");
    this.style.display = "none";
};

document.getElementById("addDeviceFormContainer").onclick = function(event) {
    if (event.target === this) {
        this.classList.remove("active");
        document.getElementById("showFormBtn").style.display = "inline-block";
    }
};

document.addEventListener("DOMContentLoaded", function() {
    const dependentSelect = document.getElementById("dependentSelect");
    const existingDependentContainer = document.getElementById("existingDependentContainer");
    const newDependentContainer = document.getElementById("newDependentContainer");
    const firstNameInput = document.getElementById("Firstname");
    const lastNameInput = document.getElementById("Lastname");
    const existingDependentInput = document.getElementById("existingDependentInput");
    const existingDependent = document.getElementById("existingDependent");
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

    document.getElementById('existingDependentInput').addEventListener('click', function () {
        var options = document.querySelector('.dropdown-options');
        options.classList.toggle('hidden');
    });
    document.querySelectorAll('.dropdown-option').forEach(function(option) {
        option.addEventListener('click', function () {
            var value = this.getAttribute('data-value');
            var name = this.querySelector('strong').innerText;
            document.getElementById('existingDependentInput').value = name;
            document.getElementById('existingDependent').value = value;

            // Hide dropdown after selection
            document.querySelector('.dropdown-options').classList.add('hidden');
        });
    });

    document.getElementById('dependentSelect').addEventListener('change', function() {
        // Get the value of the selected option
        var selectedOption = this.value;

        // Get the dependent form containers
        var existingDependentContainer = document.getElementById('existingDependentContainer');
        var newDependentContainer = document.getElementById('newDependentContainer');

        // Hide both containers initially
        existingDependentContainer.classList.add('hidden');
        newDependentContainer.classList.add('hidden');

        // Show the appropriate container based on the selected option
        if (selectedOption === 'existing') {
            existingDependentContainer.classList.remove('hidden');
        } else if (selectedOption === 'new') {
            newDependentContainer.classList.remove('hidden');
        }
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
    fetch('functions/dashboard_action.php?serialNo=' + serialNo)
        .then(response => response.json())
        .then(data => {
            console.log(data); // Log the fetched data to the console to see the query result

            document.getElementById("addDeviceFormContainer").classList.add("active");
            document.getElementById("serialNo").value = data.SerialNoFK;
            document.getElementById("emergencyNo1").value = data.EmergencyNo1;
            document.getElementById("emergencyNo2").value = data.EmergencyNo2;

            // Pre-fill dependent dropdown
            if (data.DependentIDFK) {
                document.getElementById("dependentSelect").value = "existing";
                document.getElementById("existingDependentContainer").classList.remove("hidden");
                document.getElementById("existingDependent").value = data.DependentIDFK;

                // Set the visible dropdown text
                const options = document.querySelectorAll('.dropdown-option');
                options.forEach(option => {
                    if (option.getAttribute('data-value') === data.DependentIDFK.toString()) {
                        document.getElementById("existingDependentInput").value = option.querySelector('strong').innerText;
                    }
                });

                document.getElementById("newDependentContainer").classList.add("hidden");
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



function resetDeviceForm() {
    // Clear all input fields
    document.getElementById("serialNo").value = "";
    document.getElementById("emergencyNo1").value = "";
    document.getElementById("emergencyNo2").value = "";
    document.getElementById("existingDependentInput").value = "";
    document.getElementById("dependentSelect").value = "";
    document.getElementById("existingDependent").value = "";
    document.getElementById("existingDependentInput").value = "";

    existingDependentContainer.classList.add('hidden');
    // newDependentContainer.classList.add("hidden");

    // Update the button text to "Add Dependent"
    document.querySelector('button[name="add_device"]').innerText = "Add Device";

    // Remove readonly attribute if previously set
    // document.getElementById("dependentId")?.removeAttribute("readonly");
}
