<?php
class Admin_Registrations_Handler {
    public function __construct() {
        // Register AJAX actions for admin
        add_action('wp_ajax_fetch_registrations', array($this, 'fetch_registrations'));
        add_action('wp_ajax_delete_registration', array($this, 'delete_registration'));  // Registering the delete action
    }

    public function fetch_registrations() {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have sufficient permissions to access this page.');
        }

        check_ajax_referer('load_registrations_nonce', 'security');

        $symposium_id = isset($_POST['symposium_id']) ? intval($_POST['symposium_id']) : 0;
        if (!$symposium_id) {
            wp_send_json_error('Invalid Symposium ID');
            return;
        }

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

        ob_start();
        include(plugin_dir_path(__FILE__) . 'partials/registrations-table.php');
        $html = ob_get_clean();
        wp_send_json_success($html);
    }

    public function delete_registration() {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have sufficient permissions to access this page.');
        }

        check_ajax_referer('load_registrations_nonce', 'security');

        $registration_id = isset($_POST['registration_id']) ? intval($_POST['registration_id']) : 0;
        if (!$registration_id) {
            wp_send_json_error('Invalid Registration ID');
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'wtjdl_event_registrations';
        $result = $wpdb->delete($table_name, array('id' => $registration_id), array('%d'));

        if ($result) {
            wp_send_json_success('Registration deleted successfully.');
        } else {
            wp_send_json_error('Failed to delete registration.');
        }
    }
}

// Initialize the handler
new Admin_Registrations_Handler();
