<?php
if (!defined('ABSPATH')) {
    exit;
}

class Trimbos_Admin_Handler {

    public function __construct() {
        // Register the admin actions
        add_action('admin_post_delete_submission', array($this, 'handle_delete_submission'));
        add_action('admin_post_export_trimbos_statistics', array($this, 'export_trimbos_statistics'));
    }

    /**
     * Handle the deletion of a submission.
     */
    public function handle_delete_submission() {
        // Security check
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'delete_submission')) {
            wp_die('Unauthorized request');
        }

        // Check if the submission ID is set
        if (isset($_GET['submission_id'])) {
            $submission_id = intval($_GET['submission_id']);

            global $wpdb;
            // Delete from the submissions and answers tables
            $wpdb->delete("{$wpdb->prefix}trimbos_form_submissions", array('id' => $submission_id));
            $wpdb->delete("{$wpdb->prefix}trimbos_form_answers", array('submission_id' => $submission_id));
        }

        // Redirect back to the Statistieken page
        wp_safe_redirect(admin_url('edit.php?post_type=trimbos&page=trimbos-statistics'));
        exit;
    }

    /**
     * Export Trimbos statistics to CSV.
     */
    public function export_trimbos_statistics() {
        // Security check
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'export_trimbos_statistics')) {
            wp_die('Unauthorized request');
        }
    
        global $wpdb;
    
        // Fetch data from the database
        $submissions_table = $wpdb->prefix . 'trimbos_form_submissions';
        $answers_table = $wpdb->prefix . 'trimbos_form_answers';
    
        $submissions = $wpdb->get_results("SELECT * FROM $submissions_table");
        $answers = $wpdb->get_results("SELECT * FROM $answers_table");
    
        // Fetch questions from ACF
        $post_id = 1671; // Replace with your post ID
        $questions = [];
    
        // Add the static question (Question 0)
        $static_question_text = get_field('extra_vraag_vraag', $post_id);
        if ($static_question_text) {
            $questions['0'] = $static_question_text;
        }
    
        // Add dynamic questions from the ACF repeater field
        if (have_rows('vragenlijst', $post_id)) {
            while (have_rows('vragenlijst', $post_id)) : the_row();
                $unique_identifier = get_sub_field('unique_identifier');
                $question_text = get_sub_field('hoofdvraag');
                if ($unique_identifier && $question_text) {
                    $questions[$unique_identifier] = $question_text;
                }
            endwhile;
        }
    
        // Sort questions so Question 0 always comes first
        ksort($questions, SORT_NUMERIC);
    
        // Prepare data for export
        $data = [];
        foreach ($submissions as $submission) {
            $row = [
                'ID' => $submission->id,
                'Submission Date' => $submission->submission_date,
            ];
    
            $submission_answers = array_filter($answers, function ($answer) use ($submission) {
                return $answer->submission_id == $submission->id;
            });
    
            // Map answers to the corresponding questions
            foreach ($questions as $question_id => $question_text) {
                $matching_answer = array_filter($submission_answers, function ($answer) use ($question_id) {
                    return $answer->question_id == $question_id;
                });
    
                $row[$question_text] = $matching_answer ? reset($matching_answer)->answer_text : '';
            }
    
            $data[] = $row;
        }
    
        // Generate and output CSV
        $this->export_to_csv($data);
    }
    
    

    /**
     * Generate and output CSV file.
     */
    private function export_to_csv($data) {
        $filename = 'trimbos_statistics_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=$filename");

        $output = fopen('php://output', 'w');

        if (!empty($data)) {
            // Output headers
            fputcsv($output, array_keys($data[0]));
            // Output rows
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }

        fclose($output);
        exit;
    }
}

// Initialize the admin handler
new Trimbos_Admin_Handler();
