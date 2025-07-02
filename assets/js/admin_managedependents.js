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


document.addEventListener("DOMContentLoaded", function () {
    const userIdInput = document.getElementById("userIdInput");
    const userDropdownOptions = document.querySelector(".user-dropdown-options");
    const hiddenUserIdInput = document.getElementById("hiddenUserIdInput");

    // Variables for Dependent dropdown
    // const dependentSelect = document.getElementById("dependentSelect");
    // const existingDependentContainer = document.getElementById("existingDependentContainer");
    // const dependentDropdownOptions = document.querySelector(".dependent-dropdown-options");
    // const existingDependentInput = document.getElementById("existingDependentInput");
    // const hiddenDependentInput = document.getElementById("existingDependent");

    // const newDependentContainer = document.getElementById("newDependentContainer");
    // const firstNameInput = document.getElementById("Firstname");
    // const lastNameInput = document.getElementById("Lastname");
    // let savedDependent = null;

    // dependentSelect.addEventListener("change", function () {
    //     if (dependentSelect.value === "existing") {
    //         existingDependentContainer.classList.remove("hidden");
    //         newDependentContainer.classList.add("hidden");

    //         existingDependent.value = savedDependent;
    //         const options = document.querySelectorAll('.dropdown-option');
    //         options.forEach(option => {
    //             if (option.getAttribute('data-value') === savedDependent.toString()) {
    //                 existingDependentInput.value = option.querySelector('strong').innerText;
    //             }
    //         });


    //         // Remove the required attribute when selecting an existing dependent
    //         firstNameInput.removeAttribute("required");
    //         lastNameInput.removeAttribute("required");
    //     } else if (dependentSelect.value === "new") {

            
    //         existingDependentContainer.classList.add("hidden");
    //         newDependentContainer.classList.remove("hidden");

    //         // Clear existing dependent dropdown
    //         savedDependent = existingDependent.value;
    //         existingDependentInput.value = "";
    //         existingDependent.value = "";

    //         // Add the required attribute when adding a new dependent
    //         firstNameInput.setAttribute("required", "required");
    //         lastNameInput.setAttribute("required", "required");
    //     } else {
    //         // Hide both if no valid selection
    //         existingDependentContainer.classList.add("hidden");
    //         newDependentContainer.classList.add("hidden");

    //         // Remove required attributes if neither is selected
    //         firstNameInput.removeAttribute("required");
    //         lastNameInput.removeAttribute("required");
    //     }
    // });

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

            // fetchDependents(userId);
        }
    });

    // // Hide dropdown when clicking outside
    // document.addEventListener("click", function (event) {
    //     if (!userIdInput.contains(event.target) && !userDropdownOptions.contains(event.target)) {
    //         userDropdownOptions.classList.add("hidden");
    //     }
    // });

    // // Dependent Select - Show/hide dependent options
    // dependentSelect.addEventListener("change", function () {
    //     if (this.value === "existing") {
    //         existingDependentContainer.classList.remove("hidden");
    //     } else {
    //         existingDependentContainer.classList.add("hidden");
    //     }
    // });

    // // Dependent Dropdown - Toggle visibility
    // existingDependentInput.addEventListener("click", function () {
    //     dependentDropdownOptions.classList.toggle("hidden");
    // });
});


function editDependent(dependentID) {
    // Send an AJAX request to the PHP backend to get the device data
    fetch('actions/admin_managedependents_action.php?dependentID=' + dependentID)
        .then(response => response.json())
        .then(data => {
            console.log(data); // Log the fetched data to the console to see the query result

            document.getElementById("addDependentFormContainer").classList.add("active");
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


