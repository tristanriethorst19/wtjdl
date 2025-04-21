jQuery(document).ready(function ($) {

    // When the dropdown value changes (a symposium is selected)
    $('#symposium-select').change(function () {
        var symposiumId = $(this).val();

        if (symposiumId) {
            // Fetch registration table via AJAX
            $.ajax({
                url: adminAjax.ajaxurl, // Localized via wp_localize_script
                type: 'POST',
                data: {
                    action: 'fetch_registrations',
                    symposium_id: symposiumId,
                    security: adminAjax.security // Nonce check for security
                },
                success: function (response) {
                    if (response.success) {
                        // Display the table HTML returned by PHP
                        $('#registrations-table').html(response.data);

                        // Show the export button and attach symposium ID to it
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
            // Reset state if no symposium selected
            $('#registrations-table').html('Geen symposium geselecteerd.');
            $('#export-registrations').hide();
        }
    });

    // Export button clicked → force download via GET
    $('#export-registrations').click(function () {
        var symposiumId = $(this).data('symposium-id');

        // Redirect to export handler with security nonce
        window.location.href = adminAjax.ajaxurl
            + '?action=export_registrations'
            + '&symposium_id=' + symposiumId
            + '&security=' + adminAjax.security;
    });

    // Handle inline registration deletion
    $('#registrations-table').on('click', '.delete-registration', function () {
        var registrationId = $(this).data('id');
        var row = $(this).closest('tr'); // Find the table row
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

                        // Visually remove the row
                        row.fadeOut(400, function () {
                            $(this).remove();

                            // Check if the table is now empty
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
