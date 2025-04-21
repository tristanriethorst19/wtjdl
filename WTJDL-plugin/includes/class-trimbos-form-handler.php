<?php
// Block direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Trimbos_Form_Handler
 * Handles the logic for storing anonymous form submissions and answers.
 * Supports both logged-in and anonymous users (via admin-post hooks).
 */
class Trimbos_Form_Handler {

    public function __construct() {
        // Handle form submissions for non-logged-in users
        add_action('admin_post_nopriv_trimbos_form_submission', array($this, 'handle_form_submission'));

        // Handle form submissions for logged-in users
        add_action('admin_post_trimbos_form_submission', array($this, 'handle_form_submission'));
    }

    /**
     * Main handler for form submissions
     * - Stores submission metadata and answers
     * - Generates a unique token for secure public results access
     */
    public function handle_form_submission() {
        global $wpdb;

        // 1. Insert a new empty submission row (captures timestamp)
        $result = $wpdb->insert(
            "{$wpdb->prefix}trimbos_form_submissions", 
            array('submission_date' => current_time('mysql'))
        );

        if ($result === false) {
            wp_die('Failed to save the form submission.');
        }

        $submission_id = $wpdb->insert_id;

        // 2. Generate a unique token to identify this submission anonymously
        $unique_token = wp_generate_uuid4(); // Safer than exposing numeric ID
        $wpdb->update(
            "{$wpdb->prefix}trimbos_form_submissions",
            array('unique_token' => $unique_token),
            array('id' => $submission_id)
        );

        // 3. Save each submitted answer in the answers table
        foreach ($_POST as $key => $value) {
            // Only process fields that match the "question_" prefix
            if (strpos($key, 'question_') === 0) {
                $question_id = intval(str_replace('question_', '', $key));
                $answer_text = sanitize_text_field($value);

                $wpdb->insert("{$wpdb->prefix}trimbos_form_answers", array(
                    'submission_id' => $submission_id,
                    'question_id'   => $question_id,
                    'answer_text'   => $answer_text,
                ));
            }
        }

        // 4. Redirect user to a public result page with the token in the URL
        $redirect_url = home_url('/alcohol-en-drugsbeleid-verenigingen/resultaat/' . $unique_token);
        wp_safe_redirect($redirect_url);
        exit;
    }
}

// Initialize the form handler
new Trimbos_Form_Handler();
