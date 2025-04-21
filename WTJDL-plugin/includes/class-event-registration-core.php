<?php
// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Event_Registration_Core
 * Handles setup tasks for the plugin, including database table creation on activation.
 */
class Event_Registration_Core {
    private $plugin_file;

    /**
     * Constructor receives the main plugin file path and registers the activation hook.
     */
    public function __construct($plugin_file) {
        $this->plugin_file = $plugin_file;

        // Register a hook to run when the plugin is activated
        register_activation_hook($this->plugin_file, array($this, 'activate'));
    }

    /**
     * Activation logic — called when the plugin is first activated.
     */
    public function activate() {
        $this->create_database_tables(); // Setup custom database tables
    }

    /**
     * Creates the custom database table used to store event registrations.
     */
    private function create_database_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate(); // Handle character set and collation
        $table_name = $wpdb->prefix . 'wtjdl_event_registrations';

        // Define the structure of the registration table
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            event_id mediumint(9) NOT NULL,
            first_name text NOT NULL,
            last_name text NOT NULL,
            email text NOT NULL,
            age text NOT NULL,
            gender text NOT NULL,
            education_level text NOT NULL,
            association text DEFAULT '' NOT NULL,
            registration_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            status tinytext NOT NULL,
            PRIMARY KEY  (id),
            KEY event_id (event_id)
        ) $charset_collate;";

        // Include WordPress’s dbDelta utility and run the SQL
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql); // Safely creates or updates the table as needed
    }
}
