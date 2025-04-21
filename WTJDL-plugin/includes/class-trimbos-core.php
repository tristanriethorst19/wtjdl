<?php
if (!defined('ABSPATH')) {
    exit;
}

class Trimbos_Core {
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

        // Table for form submissions metadata
        $submissions_table = $wpdb->prefix . 'trimbos_form_submissions';
        $sql_submissions = "CREATE TABLE $submissions_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            submission_date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
            unique_token VARCHAR(36) NOT NULL, /* Unique identifier for anonymous URL */
            PRIMARY KEY (id),
            UNIQUE (unique_token) /* Ensures tokens are unique */
        ) $charset_collate;";

        // Table for individual answers linked to submissions
        $answers_table = $wpdb->prefix . 'trimbos_form_answers';
        $sql_answers = "CREATE TABLE $answers_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            submission_id BIGINT(20) UNSIGNED NOT NULL,
            question_id VARCHAR(255) NOT NULL,     /* Unique identifier */
            question_text TEXT NOT NULL,           /* Store question text for export readability */
            answer_text TEXT NOT NULL,
            PRIMARY KEY (id),
            FOREIGN KEY (submission_id) REFERENCES $submissions_table(id) ON DELETE CASCADE
        ) $charset_collate;";

        // Execute SQL to create or update tables
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_submissions);
        dbDelta($sql_answers);

        // Debugging
        global $wpdb;
        if (!empty($wpdb->last_error)) {
            error_log('Database Error: ' . $wpdb->last_error);
        }
    }
}
