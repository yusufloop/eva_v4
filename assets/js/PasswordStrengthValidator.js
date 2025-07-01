//Begin supreme heuristics 
$('.password').on('keyup', function() {
    var confirmPassword = $('#confirmPassword');
    var newPassword = $('#newPassword');
    
    // Check if password fields have been filled
    if (newPassword.val().length > 0) {
        // Password Match Validation
        if (newPassword.val() != confirmPassword.val()) {
            $('#valid').html("Passwords Don't Match");
        } else {
            $('#valid').html('');  
        }

        // Determine password strength
        var strength = 'weak';
        if (newPassword.val().length > 5 && newPassword.val().match(/\d+/g)) {
            strength = 'medium';
        }
        if (newPassword.val().length > 6 && newPassword.val().match(/[^\w\s]/gi)) {
            strength = 'strong';
        }

        // Reset the strength indicator and apply the current strength
        $('#strong span').removeClass('weak medium strong').addClass(strength).html(strength);
    } else {
        // Reset the strength text and classes when there is no input
        $('#strong span').removeClass('weak medium strong').html('');
    }

});

function validateForm() {
    var newPassword = $('#newPassword').val();
    var confirmPassword = $('#confirmPassword').val();

    // Check if passwords match
    if (newPassword !== confirmPassword) {
        alert("Passwords do not match. Please try again.");
        return false; // Prevent form submission
    }

    // Check password strength
    var strength = $('#strong span').text();
    if (strength === 'weak' || strength === 'medium') {
        alert("Password strength is too weak. Please use a stronger password.");
        return false; // Prevent form submission
    }

    // If all validations pass
    return true; // Allow form submission
}


