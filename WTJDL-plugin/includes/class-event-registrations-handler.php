<?php
if (!defined('ABSPATH')) {
    exit;
}

class Event_Registrations_Handler {
    public function __construct() {
        add_action('init', array($this, 'add_custom_rewrite_rule'));
        add_filter('query_vars', array($this, 'register_query_vars'));
        add_action('template_redirect', array($this, 'handle_custom_template'));
        add_action('admin_post_submit_registration', array($this, 'handle_registration_submission'));
    }

    public function add_custom_rewrite_rule() {
        add_rewrite_rule(
            '^symposium/([^/]*)/inschrijven/?$',
            'index.php?post_type=symposium&name=$matches[1]&inschrijven=1',
            'top'
        );
        add_rewrite_rule(
            '^symposium/([^/]*)/inschrijven/bevestiging/?$',
            'index.php?post_type=symposium&name=$matches[1]&inschrijving_bevestigd=1',
            'top'
        );
    }

    public function register_query_vars($vars) {
        $vars[] = 'inschrijven';
        $vars[] = 'inschrijving_bevestigd';
        return $vars;
    }

    public function handle_custom_template() {
        if (get_query_var('inschrijven') && is_singular('symposium')) {
            include plugin_dir_path(__FILE__) . '../public/registrations-page.php';
            exit;
        }
        if (get_query_var('inschrijving_bevestigd') && is_singular('symposium')) {
            include plugin_dir_path(__FILE__) . '../public/registrations-success-page.php';
            exit;
        }
    }

    public function handle_registration_submission() {
        if ('POST' !== $_SERVER['REQUEST_METHOD']) {
            wp_die('Invalid request method.');
        }

        if (!isset($_POST['event_id'], $_POST['_wpnonce'], $_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['age'], $_POST['gender'], $_POST['education_level']) || !wp_verify_nonce($_POST['_wpnonce'], 'submit_registration')) {
            wp_die('Security check failed or form incomplete.');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'wtjdl_event_registrations';

        $data = array(
            'event_id'          => intval($_POST['event_id']),
            'first_name'        => sanitize_text_field($_POST['first_name']),
            'last_name'         => sanitize_text_field($_POST['last_name']),
            'email'             => sanitize_email($_POST['email']),
            'age'               => sanitize_text_field($_POST['age']),
            'gender'            => sanitize_text_field($_POST['gender']),
            'education_level'   => sanitize_text_field($_POST['education_level']),
            'association'       => isset($_POST['association']) ? sanitize_text_field($_POST['association']) : '',
            'registration_date' => current_time('mysql', 1),
            'status'            => 'pending'
        );

        $format = array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s');
        $success = $wpdb->insert($table_name, $data, $format);

        if (false === $success) {
            wp_die('Failed to register. Please try again.');
        }

        $post = get_post($_POST['event_id']);
        $symposium_name = $post ? $post->post_name : 'general';

        wp_redirect(home_url("/symposium/{$symposium_name}/inschrijven/bevestiging"));
        exit;
    }
}
