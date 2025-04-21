<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Partial: Admin Registrations Table
 * Displays a list of all symposium registrations in the admin dashboard.
 * Uses native WP admin table classes for consistent styling.
 */

// $registrations is assumed to be passed in from the handler (as an array of DB results)

// Begin the table
echo '<table class="wp-list-table widefat fixed striped">';
echo '<thead>';
echo '<tr>';
echo '<th>ID</th>';
echo '<th>Voornaam</th>';
echo '<th>Achternaam</th>';
echo '<th>E-mail</th>';
echo '<th>Leeftijd</th>';
echo '<th>Geslacht</th>';
echo '<th>Opleiding</th>';
echo '<th>Vereniging</th>';
echo '<th>Datum</th>';
// Optional status column if needed later
// echo '<th>Status</th>';
echo '<th>Acties</th>';
echo '</tr>';
echo '</thead>';

echo '<tbody>';

// Loop over each registration and display a row
foreach ($registrations as $registration) {
    echo '<tr>';
    echo '<td>' . esc_html($registration->id) . '</td>';
    echo '<td>' . esc_html($registration->first_name) . '</td>';
    echo '<td>' . esc_html($registration->last_name) . '</td>';
    echo '<td>' . esc_html($registration->email) . '</td>';
    echo '<td>' . esc_html($registration->age) . '</td>';
    echo '<td>' . esc_html($registration->gender) . '</td>';
    echo '<td>' . esc_html($registration->education_level) . '</td>';
    echo '<td>' . esc_html($registration->association) . '</td>';
    echo '<td>' . esc_html($registration->registration_date) . '</td>';

    // Optionally show status column
    // echo '<td>' . esc_html($registration->status) . '</td>';

    // Action button (connected to AJAX JS)
    echo '<td>
            <button class="button action delete-registration" data-id="' . esc_attr($registration->id) . '">
                Verwijderen
            </button>
          </td>';
    echo '</tr>';
}

echo '</tbody>';
echo '</table>';
