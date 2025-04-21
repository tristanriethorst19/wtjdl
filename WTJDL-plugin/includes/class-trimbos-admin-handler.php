<?php
// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Trimbos_Admin_Handler
 * Handles deletion of form submissions and CSV export of Trimbos statistics
 * via secure admin actions.
 */
class Trimbos_Admin_Handler {

    public function __construct() {
        // Hook admin actions for deletion and export
        add_action('admin_post_delete_submission', array($this, 'handle_delete_submission'));
        add_action('admin_post_export_trimbos_statistics', array($this, 'export_trimbos_statistics'));
    }

    /**
     * Deletes a specific submission and its answers from the database.
     * Triggered via GET request with nonce from an admin page.
     */
    public function handle_delete_submission() {
        // Security check via nonce
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'delete_submission')) {
            wp_die('Unauthorized request');
        }

        // Ensure a submission ID was provided
        if (isset($_GET['submission_id'])) {
            $submission_id = intval($_GET['submission_id']);
            global $wpdb;

            // Remove both the submission and its related answers
            $wpdb->delete("{$wpdb->prefix}trimbos_form_submissions", array('id' => $submission_id));
            $wpdb->delete("{$wpdb->prefix}trimbos_form_answers", array('submission_id' => $submission_id));
        }

        // Redirect the admin user back to the statistics page
        wp_safe_redirect(admin_url('edit.php?post_type=trimbos&page=trimbos-statistics'));
        exit;
    }

    /**
     * Handles export of all submissions and answers as a CSV file.
     * Includes dynamically defined questions via ACF.
     */
    public function export_trimbos_statistics() {
        // Security check via nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'export_trimbos_statistics')) {
            wp_die('Unauthorized request');
        }

        global $wpdb;

        // Retrieve all submissions and answers from the database
        $submissions_table = $wpdb->prefix . 'trimbos_form_submissions';
        $answers_table = $wpdb->prefix . 'trimbos_form_answers';

        $submissions = $wpdb->get_results("SELECT * FROM $submissions_table");
        $answers = $wpdb->get_results("SELECT * FROM $answers_table");

        // Pull question definitions from ACF (custom post/page)
        $post_id = 1671; // ACF post ID where questions are defined
        $questions = [];

        // Add static question 0 from a separate field
        $static_question_text = get_field('extra_vraag_vraag', $post_id);
        if ($static_question_text) {
            $questions['0'] = $static_question_text;
        }

        // Load questions from the ACF repeater field
        if (have_rows('vragenlijst', $post_id)) {
            while (have_rows('vragenlijst', $post_id)) : the_row();
                $unique_identifier = get_sub_field('unique_identifier');
                $question_text = get_sub_field('hoofdvraag');
                if ($unique_identifier && $question_text) {
                    $questions[$unique_identifier] = $question_text;
                }
            endwhile;
        }

        // Ensure that the static question is placed at the top
        ksort($questions, SORT_NUMERIC);

        // Format data for export
        $data = [];
        foreach ($submissions as $submission) {
            $row = [
                'ID' => $submission->id,
                'Submission Date' => $submission->submission_date,
            ];

            // Filter answers belonging to this submission
            $submission_answers = array_filter($answers, function ($answer) use ($submission) {
                return $answer->submission_id == $submission->id;
            });

            // Match each question to its answer for this submission
            foreach ($questions as $question_id => $question_text) {
                $matching_answer = array_filter($submission_answers, function ($answer) use ($question_id) {
                    return $answer->question_id == $question_id;
                });

                $row[$question_text] = $matching_answer ? reset($matching_answer)->answer_text : '';
            }

            $data[] = $row;
        }

        // Send data to CSV exporter
        $this->export_to_csv($data);
    }

    /**
     * Outputs CSV headers and rows to the browser.
     */
    private function export_to_csv($data) {
        $filename = 'trimbos_statistics_' . date('Y-m-d') . '.csv';

        // Set download headers
        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=$filename");

        $output = fopen('php://output', 'w');

        if (!empty($data)) {
            // Output column headers
            fputcsv($output, array_keys($data[0]));

            // Output each row
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }

        fclose($output);
        exit;
    }
}

// Instantiate the admin handler class on load
new Trimbos_Admin_Handler();
