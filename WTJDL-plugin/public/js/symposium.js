jQuery(document).ready(function($) {
    // Check if the .status-afgelopen class is present on the page
    if ($('.status-afgelopen').length > 0) {
        // Hide the button if the class is present
        $('#registration').hide();
    } else {
        // Get the current post URL
        var postUrl = window.location.href;

        // Add /inschrijven to the post URL
        var registrationUrl = postUrl + '/inschrijven';

        // Add the registration URL to the button click event
        $('#registration').click(function() {
            window.location.href = registrationUrl;
        });
    }
});
