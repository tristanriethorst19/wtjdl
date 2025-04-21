<?php
// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Trimbos_Core
 * Sets up the database tables used for storing form submissions and their answers.
 */
class Trimbos_Core {
    private $plugin_file;

    /**
     * Constructor receives the main plugin file path and registers the activation hook.
     */
    public function __construct($plugin_file) {
        $this->plugin_file = $plugin_file;

        // Hook into plugin activation to set up database tables
        register_activation_hook($this->plugin_file, array($this, 'activate'));
    }

    /**
     * Activation callback — sets up the required database structure.
     */
    public function activate() {
        $this->create_database_tables();
    }

    /**
     * Creates two database tables:
     * 1. trimbos_form_submissions – for tracking form submissions (with timestamp & token)
     * 2. trimbos_form_answers – for storing answers linked to those submissions
     */
    private function create_database_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Table 1: Submissions metadata
        $submissions_table = $wpdb->prefix . 'trimbos_form_submissions';
        $sql_submissions = "CREATE TABLE $submissions_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            submission_date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
            unique_token VARCHAR(36) NOT NULL, /* Used for public result links */
            PRIMARY KEY (id),
            UNIQUE (unique_token) /* Prevent duplicate tokens */
        ) $charset_collate;";

        // Table 2: Individual answers tied to submission_id
        $answers_table = $wpdb->prefix . 'trimbos_form_answers';
        $sql_answers = "CREATE TABLE $answers_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            submission_id BIGINT(20) UNSIGNED NOT NULL,
            question_id VARCHAR(255) NOT NULL,     /* Refers to ACF unique_identifier */
            question_text TEXT NOT NULL,           /* Stores the label for readability in exports */
            answer_text TEXT NOT NULL,             /* Stores actual user input */
            PRIMARY KEY (id),
            FOREIGN KEY (submission_id) REFERENCES $submissions_table(id) ON DELETE CASCADE
        ) $charset_collate;";

        // Use WordPress dbDelta to safely create or update both tables
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_submissions);
        dbDelta($sql_answers);

        // Log any errors for debugging
        if (!empty($wpdb->last_error)) {
            error_log('Database Error: ' . $wpdb->last_error);
        }
    }
}
