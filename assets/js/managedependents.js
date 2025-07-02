// JavaScript to show the form when the button is clicked
document.getElementById("showFormBtn").onclick = function() {
    resetDependentForm();
    document.getElementById("addDependentFormContainer").classList.add("active");
    this.style.display = "none";
};

document.getElementById("addDependentFormContainer").onclick = function(event) {
    if (event.target === this) {
        this.classList.remove("active");
        document.getElementById("showFormBtn").style.display = "inline-block";
    }
};

function editDependent(dependentID) {
    // Send an AJAX request to the PHP backend to get the device data
    fetch('actions/managedependents_action.php?dependentID=' + dependentID)
        .then(response => response.json())
        .then(data => {
            console.log(data); // Log the fetched data to the console to see the query result

            document.getElementById("addDependentFormContainer").classList.add("active");
            document.getElementById("dependentId").value = data.DependentID;
            document.getElementById("Firstname").value = data.Firstname;
            document.getElementById("Lastname").value = data.Lastname;
            const genderSelect = document.getElementById("Gender");
            if (genderSelect) {
                // Set the value based on the Gender
                genderSelect.value = data.Gender;
            }
            const dobField = document.getElementById("DOB");
            if (dobField) {
                dobField.value = data.DOB; // Assuming `data.DOB` is in the format 'YYYY-MM-DD'
            }
            document.getElementById("Address").value = data.Address;
            document.getElementById("PostalCode").value = data.PostalCode;
            document.getElementById("MedicalCondition").value = data.MedicalCondition;

            // document.getElementById("dependentId").setAttribute("readonly", true);
            document.querySelector('button[name="add_dependent"]').innerText = "Update Dependent";
        })
        .catch(error => console.error('Error:', error));
}

function resetDependentForm() {
    // Clear all input fields
    document.getElementById("dependentId").value = "";
    document.getElementById("Firstname").value = "";
    document.getElementById("Lastname").value = "";
    document.getElementById("Gender").value = ""; // Reset dropdown
    document.getElementById("DOB").value = "2000-01-01"; // Clear the date
    document.getElementById("Address").value = "";
    document.getElementById("PostalCode").value = "";
    document.getElementById("MedicalCondition").value = "";

    // Update the button text to "Add Dependent"
    document.querySelector('button[name="add_dependent"]').innerText = "Add Dependent";

    // Remove readonly attribute if previously set
    // document.getElementById("dependentId")?.removeAttribute("readonly");
}


