<?php
if (!defined('ABSPATH')) {
    exit;
}

class Trimbos_Results_Handler {
    public function __construct() {
        add_action('init', array($this, 'add_rewrite_rules'));
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('template_redirect', array($this, 'handle_results_page'));
    }

    // Add rewrite rules
    public function add_rewrite_rules() {
        // Rule for /trimbos-wtjdl/{unique-token}
        add_rewrite_rule(
            '^alcohol-en-drugsbeleid-verenigingen/resultaat/([^/]*)/?$',
            'index.php?page_id=1730&unique_token=$matches[1]', // Replace 1234 with the page ID
            'top'
        );        
    }

    // Add query vars to handle tokens
    public function add_query_vars($vars) {
        $vars[] = 'trimbos_results'; // Custom variable for results
        $vars[] = 'unique_token';    // Token variable
        return $vars;
    }

    // Handle results page logic
    public function handle_results_page() {
        if (get_query_var('trimbos_results')) {
            include plugin_dir_path(__FILE__) . '../public/trimbos-results-page.php';
            exit;
        }
    }
}

new Trimbos_Results_Handler();
