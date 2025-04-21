<?php
/**
 * Admin_Registrations_Handler
 * Handles AJAX functionality for managing registrations from the WordPress admin panel.
 */
class Admin_Registrations_Handler {
    public function __construct() {
        // Register AJAX handlers for fetching and deleting registrations
        add_action('wp_ajax_fetch_registrations', array($this, 'fetch_registrations'));
        add_action('wp_ajax_delete_registration', array($this, 'delete_registration'));
    }

    /**
     * Fetches registrations for a given symposium ID via AJAX.
     * Returns HTML markup that can be inserted dynamically in the admin UI.
     */
    public function fetch_registrations() {
        // Security check: only admins can run this
        if (!current_user_can('manage_options')) {
            wp_die('You do not have sufficient permissions to access this page.');
        }

        // Verify the AJAX nonce
        check_ajax_referer('load_registrations_nonce', 'security');

        // Validate and sanitize symposium ID
        $symposium_id = isset($_POST['symposium_id']) ? intval($_POST['symposium_id']) : 0;
        if (!$symposium_id) {
            wp_send_json_error('Invalid Symposium ID');
            return;
        }

        // Query registrations from the custom database table
        global $wpdb;
        $table_name = $wpdb->prefix . 'wtjdl_event_registrations';

        $registrations = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE event_id = %d", 
            $symposium_id
        ));

        if (!$registrations) {
            wp_send_json_error('No registrations found.');
            return;
        }

        // Render the results into an HTML table using a partial template
        ob_start();
        include(plugin_dir_path(__FILE__) . 'partials/registrations-table.php');
        $html = ob_get_clean();

        // Send the HTML back to the browser
        wp_send_json_success($html);
    }

    /**
     * Deletes a specific registration by ID via AJAX.
     */
    public function delete_registration() {
        // Ensure the user is allowed to delete registrations
        if (!current_user_can('manage_options')) {
            wp_die('You do not have sufficient permissions to access this page.');
        }

        // Verify the nonce for secure AJAX call
        check_ajax_referer('load_registrations_nonce', 'security');

        // Validate the ID of the registration to delete
        $registration_id = isset($_POST['registration_id']) ? intval($_POST['registration_id']) : 0;
        if (!$registration_id) {
            wp_send_json_error('Invalid Registration ID');
            return;
        }

        // Delete the registration from the database
        global $wpdb;
        $table_name = $wpdb->prefix . 'wtjdl_event_registrations';
        $result = $wpdb->delete($table_name, array('id' => $registration_id), array('%d'));

        // Send a success or error response
        if ($result) {
            wp_send_json_success('Registration deleted successfully.');
        } else {
            wp_send_json_error('Failed to delete registration.');
        }
    }
}

// Automatically initialize the handler when the file loads
new Admin_Registrations_Handler();
