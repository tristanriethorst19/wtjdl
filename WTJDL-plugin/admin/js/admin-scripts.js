jQuery(document).ready(function ($) {
    $('#symposium-select').change(function () {
        var symposiumId = $(this).val();
        if (symposiumId) {
            $.ajax({
                url: adminAjax.ajaxurl,  // Ensure this is using the localized 'ajaxurl'
                type: 'POST',
                data: {
                    action: 'fetch_registrations',
                    symposium_id: symposiumId,
                    security: adminAjax.security  // If a nonce was passed in wp_localize_script
                },
                success: function (response) {
                    if(response.success) {
                        $('#registrations-table').html(response.data);
                        $('#export-registrations').show().data('symposium-id', symposiumId);
                    } else {
                        $('#registrations-table').html('Geen inschrijvingen gevonden.');
                        $('#export-registrations').hide();
                    }
                },
                error: function () {
                    $('#registrations-table').html('Data ophalen mislukt..');
                }
            });
        } else {
            $('#registrations-table').html('Geen symposium geselecteerd.');
            $('#export-registrations').hide();
        }
    });
        $('#export-registrations').click(function () {
        var symposiumId = $(this).data('symposium-id');
        window.location.href = adminAjax.ajaxurl + '?action=export_registrations&symposium_id=' + symposiumId + '&security=' + adminAjax.security;
    });
});

jQuery(document).ready(function ($) {
    $('#registrations-table').on('click', '.delete-registration', function () {
        var registrationId = $(this).data('id');
        var row = $(this).closest('tr'); // Get the closest table row (tr) to the button clicked
        var confirmDelete = confirm('Are you sure you want to delete this registration?');

        if (confirmDelete && registrationId) {
            $.ajax({
                url: adminAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'delete_registration',
                    registration_id: registrationId,
                    security: adminAjax.security
                },
                success: function (response) {
                    if (response.success) {
                        alert('Registration deleted successfully.');
                        row.fadeOut(400, function () { // Fade out the row and remove it
                            $(this).remove();
                            // Optionally, check if the table is empty and display a message or reload a portion of the page
                            if ($('#registrations-table tbody').children().length === 0) {
                                $('#registrations-table').append('<p>No registrations found.</p>');
                            }
                        });
                    } else {
                        alert('Failed to delete registration: ' + response.data);
                    }
                },
                error: function () {
                    alert('Failed to delete registration.');
                }
            });
        }
    });
});


