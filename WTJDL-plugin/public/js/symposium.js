jQuery(document).ready(function($) {
    // Check if the current post has the class `.status-afgelopen` (meaning the event is over)
    if ($('.status-afgelopen').length > 0) {
        // If the post is marked as "afgelopen" (ended), hide the registration button
        $('#registration').hide();
    } else {
        // Otherwise, build the registration URL dynamically

        // Get the current post URL from the browser
        var postUrl = window.location.href;

        // Append '/inschrijven' to create the registration URL
        var registrationUrl = postUrl + '/inschrijven';

        // When the user clicks the registration button, redirect to the registration URL
        $('#registration').click(function() {
            window.location.href = registrationUrl;
        });
    }
});
