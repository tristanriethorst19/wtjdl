<?php
// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Admin_Export_Handler
 * Handles exporting of event registrations via AJAX as a downloadable CSV.
 */
class Admin_Export_Handler {
    public function __construct() {
        // Hook into WordPress AJAX for logged-in users (admin side)
        add_action('wp_ajax_export_registrations', array($this, 'export_registrations'));
    }

    /**
     * Exports registration data for a specific event as a CSV file.
     * Called via an AJAX request from the admin panel.
     */
    public function export_registrations() {
        // Permission and security check
        if (
            !current_user_can('manage_options') || // Only allow admins
            !check_ajax_referer('load_registrations_nonce', 'security', false) // Validate nonce
        ) {
            wp_die('You do not have sufficient permissions to access this page or security check failed.');
        }

        // Validate the symposium/event ID
        $symposium_id = isset($_GET['symposium_id']) ? intval($_GET['symposium_id']) : 0;
        if (!$symposium_id) {
            wp_die('Invalid Symposium ID');
        }

        // Fetch registration data from the custom table
        global $wpdb;
        $table_name = $wpdb->prefix . 'wtjdl_event_registrations';

        $registrations = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE event_id = %d", 
            $symposium_id
        ));

        if (!$registrations) {
            wp_die('No registrations found.');
        }

        // Set headers to trigger CSV download
        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"symposium-{$symposium_id}-registrations.csv\"");

        // Start output stream
        $output = fopen('php://output', 'w');

        // Add CSV column headers
        fputcsv($output, array('ID', 'Voornaam', 'Achternaam', 'E-mail', 'Leeftijd', 'Gender', 'Opleiding', 'Vereniging'));

        // Loop through each registration and add to CSV
        foreach ($registrations as $reg) {
            fputcsv($output, array(
                $reg->id,
                $reg->first_name,
                $reg->last_name,
                $reg->email,
                $reg->age,
                $reg->gender,
                $reg->education_level,
                $reg->association
            ));
        }

        // Close the output stream and terminate the script
        fclose($output);
        exit;
    }
}
