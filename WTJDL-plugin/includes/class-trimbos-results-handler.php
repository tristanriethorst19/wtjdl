<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Trimbos_Results_Handler
 * Handles custom URL routing and token-based result access for Trimbos submissions.
 */
class Trimbos_Results_Handler {
    public function __construct() {
        // Register routing + variable parsing
        add_action('init', array($this, 'add_rewrite_rules'));

        // Allow 'unique_token' to be used in URLs and queries
        add_filter('query_vars', array($this, 'add_query_vars'));

        // When user visits result URL, load the custom results page
        add_action('template_redirect', array($this, 'handle_results_page'));
    }

    /**
     * Adds a custom URL pattern for tokenized result access:
     * /alcohol-en-drugsbeleid-verenigingen/resultaat/{token}
     */
    public function add_rewrite_rules() {
        add_rewrite_rule(
            '^alcohol-en-drugsbeleid-verenigingen/resultaat/([^/]*)/?$',
            'index.php?page_id=1730&unique_token=$matches[1]', // Replace 1730 with your result page ID
            'top'
        );
    }

    /**
     * Registers custom query variables to capture from URL.
     * - unique_token is used to look up submission data.
     */
    public function add_query_vars($vars) {
        $vars[] = 'trimbos_results'; // (unused in rewrite rule, can be removed if not used elsewhere)
        $vars[] = 'unique_token';    // Token passed via friendly URL
        return $vars;
    }

    /**
     * Loads the result template when the correct token URL is visited.
     */
    public function handle_results_page() {
        // Optional: only needed if 'trimbos_results' is used in URL patterns
        if (get_query_var('trimbos_results')) {
            include plugin_dir_path(__FILE__) . '../public/trimbos-results-page.php';
            exit;
        }
    }
}

// Initialize the class immediately
new Trimbos_Results_Handler();
