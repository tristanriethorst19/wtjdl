<?php
if (!defined('ABSPATH')) {
    exit;
}

// Assume $registrations is passed correctly
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
// echo '<th>Status</th>';
echo '<th>Acties</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

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
   // echo '<td>' . esc_html($registration->status) . '</td>';
    echo '<td><button class="button action delete-registration" data-id="' . esc_attr($registration->id) . '">Verwijderen</button></td>';
    echo '</tr>';
}

echo '</tbody>';
echo '</table>';
