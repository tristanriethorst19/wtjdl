<?php
if (!defined('ABSPATH')) {
    exit;
}

class Trimbos_Form_Handler {

    public function __construct() {
        add_action('admin_post_nopriv_trimbos_form_submission', array($this, 'handle_form_submission'));
        add_action('admin_post_trimbos_form_submission', array($this, 'handle_form_submission'));
    }

    /**
     * Handle the form submission.
     */
    public function handle_form_submission() {
        global $wpdb;
    
        // Insert a new submission
        $result = $wpdb->insert("{$wpdb->prefix}trimbos_form_submissions", array('submission_date' => current_time('mysql')));
        if ($result === false) {
            wp_die('Failed to save the form submission.');
        }
    
        $submission_id = $wpdb->insert_id;
    
        // Generate unique token
        $unique_token = wp_generate_uuid4();
        $wpdb->update(
            "{$wpdb->prefix}trimbos_form_submissions",
            array('unique_token' => $unique_token),
            array('id' => $submission_id)
        );
    
        // Save answers
        foreach ($_POST as $key => $value) {
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
    
        // Redirect to the results page with the unique token
        $redirect_url = home_url('/alcohol-en-drugsbeleid-verenigingen/resultaat/' . $unique_token);
        wp_safe_redirect($redirect_url);
        exit;
    }    
}
new Trimbos_Form_Handler();
