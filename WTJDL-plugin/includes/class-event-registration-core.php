<?php
if (!defined('ABSPATH')) {
    exit;
}

class Event_Registration_Core {
    private $plugin_file;

    public function __construct($plugin_file) {
        $this->plugin_file = $plugin_file;
        register_activation_hook($this->plugin_file, array($this, 'activate'));
    }

    public function activate() {
        $this->create_database_tables();
    }

private function create_database_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'wtjdl_event_registrations';

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

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

}
