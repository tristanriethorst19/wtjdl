<?php
if (!defined('ABSPATH')) {
    exit;
}

class Admin_Export_Handler {
    public function __construct() {
        add_action('wp_ajax_export_registrations', array($this, 'export_registrations'));
    }

    public function export_registrations() {
        if (!current_user_can('manage_options') || !check_ajax_referer('load_registrations_nonce', 'security', false)) {
            wp_die('You do not have sufficient permissions to access this page or security check failed.');
        }

        $symposium_id = isset($_GET['symposium_id']) ? intval($_GET['symposium_id']) : 0;
        if (!$symposium_id) {
            wp_die('Invalid Symposium ID');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'wtjdl_event_registrations';
        $registrations = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE event_id = %d", 
            $symposium_id
        ));

        if (!$registrations) {
            wp_die('No registrations found.');
        }

        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"symposium-{$symposium_id}-registrations.csv\"");

        $output = fopen('php://output', 'w');
        // Add CSV headers
        fputcsv($output, array('ID', 'Voornaam', 'Achternaam', 'E-mail', 'Leeftijd', 'Gender', 'Opleiding', 'Vereniging'));

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
        fclose($output);
        exit;
    }
}
