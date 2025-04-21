<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Pagination setup
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$records_per_page = 20;
$offset = ($current_page - 1) * $records_per_page;

// Define table names
$submissions_table = $wpdb->prefix . 'trimbos_form_submissions';
$answers_table = $wpdb->prefix . 'trimbos_form_answers';

// Get total record count
$total_records = $wpdb->get_var("SELECT COUNT(*) FROM $submissions_table");

// Fetch submissions with pagination
$submissions = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $submissions_table ORDER BY id DESC LIMIT %d OFFSET %d",
    $records_per_page,
    $offset
));

// Retrieve questions from ACF, including static Question 0
$post_id = 1671; // Adjust to your actual post ID where ACF fields are stored
$questions = [];

// Add Question 0 to the beginning
$static_question_text = get_field('extra_vraag_vraag', $post_id);
if ($static_question_text) {
    $questions['0'] = $static_question_text;
}

// Retrieve remaining questions from the ACF repeater field
if (have_rows('vragenlijst', $post_id)) {
    while (have_rows('vragenlijst', $post_id)) : the_row();
        $unique_identifier = get_sub_field('unique_identifier');
        $question_text = get_sub_field('hoofdvraag');
        $questions[$unique_identifier] = $question_text;
    endwhile;
}

?>
<div class="wrap">
    <h1>Statistieken</h1>

    <!-- Display Table -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Submission Date</th>
                <?php foreach ($questions as $question_text): ?>
                    <th scope="col"><?php echo esc_html($question_text); ?></th>
                <?php endforeach; ?>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($submissions): ?>
                <?php foreach ($submissions as $submission): ?>
                    <tr>
                        <td><?php echo esc_html($submission->id); ?></td>
                        <td><?php echo esc_html($submission->submission_date); ?></td>
                        <?php
                        // Fetch answers for each question
                        $answers = $wpdb->get_results($wpdb->prepare(
                            "SELECT * FROM $answers_table WHERE submission_id = %d ORDER BY question_id ASC",
                            $submission->id
                        ));

                        // Map answers to question IDs
                        $answer_map = [];
                        foreach ($answers as $answer) {
                            $answer_map[$answer->question_id] = $answer->answer_text;
                        }

                        // Display each question's answer, or leave empty if no answer found
                        foreach ($questions as $question_id => $question_text) {
                            echo '<td>' . esc_html($answer_map[$question_id] ?? '') . '</td>';
                        }
                        ?>
                        <td>
    <a href="<?php echo esc_url(add_query_arg(array(
        'action' => 'delete_submission',
        'submission_id' => $submission->id,
        '_wpnonce' => wp_create_nonce('delete_submission')
    ), admin_url('admin-post.php'))); ?>" class="button delete">Delete</a>
</td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="<?php echo count($questions) + 3; ?>">Geen inzendingen gevonden.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php
// Calculate total pages for pagination
$total_pages = ceil($total_records / $records_per_page);
$base_url = add_query_arg(array('paged' => '%#%'), admin_url('admin.php?page=trimbos_statistics'));

// Display pagination
echo paginate_links(array(
    'base'      => $base_url,
    'format'    => '',
    'current'   => $current_page,
    'total'     => $total_pages,
    'prev_text' => __('« Vorige'),
    'next_text' => __('Volgende »'),
));
    ?>

    <!-- Export to CSV Button -->
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="export_trimbos_statistics">
    <?php wp_nonce_field('export_trimbos_statistics'); ?>
    <input type="submit" class="button-primary" value="Export to CSV">
</form>

</div>
