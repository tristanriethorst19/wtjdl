<?php
// Security: Block direct access
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// --- Pagination Setup ---
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$records_per_page = 20;
$offset = ($current_page - 1) * $records_per_page;

// --- Table Names ---
$submissions_table = $wpdb->prefix . 'trimbos_form_submissions';
$answers_table = $wpdb->prefix . 'trimbos_form_answers';

// --- Total number of submissions ---
$total_records = $wpdb->get_var("SELECT COUNT(*) FROM $submissions_table");

// --- Paginated Submissions ---
$submissions = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $submissions_table ORDER BY id DESC LIMIT %d OFFSET %d",
    $records_per_page,
    $offset
));

// --- Questions from ACF ---
$post_id = 1671; // ACF post containing form structure
$questions = [];

// Add static question (ID = 0)
$static_question_text = get_field('extra_vraag_vraag', $post_id);
if ($static_question_text) {
    $questions['0'] = $static_question_text;
}

// Add dynamic questions from repeater field
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

    <!-- Table Output -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Submission Date</th>
                <?php foreach ($questions as $question_text): ?>
                    <th><?php echo esc_html($question_text); ?></th>
                <?php endforeach; ?>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($submissions): ?>
                <?php foreach ($submissions as $submission): ?>
                    <tr>
                        <td><?php echo esc_html($submission->id); ?></td>
                        <td><?php echo esc_html($submission->submission_date); ?></td>

                        <?php
                        // Fetch answers for this submission
                        $answers = $wpdb->get_results($wpdb->prepare(
                            "SELECT * FROM $answers_table WHERE submission_id = %d ORDER BY question_id ASC",
                            $submission->id
                        ));

                        // Map answers by question_id for lookup
                        $answer_map = [];
                        foreach ($answers as $answer) {
                            $answer_map[$answer->question_id] = $answer->answer_text;
                        }

                        // Display all answers in order of questions
                        foreach ($questions as $question_id => $question_text) {
                            echo '<td>' . esc_html($answer_map[$question_id] ?? '') . '</td>';
                        }
                        ?>

                        <!-- Delete Action -->
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(array(
                                'action' => 'delete_submission',
                                'submission_id' => $submission->id,
                                '_wpnonce' => wp_create_nonce('delete_submission')
                            ), admin_url('admin-post.php'))); ?>" class="button delete">
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- No results fallback -->
                <tr><td colspan="<?php echo count($questions) + 3; ?>">Geen inzendingen gevonden.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination Controls -->
    <?php
    $total_pages = ceil($total_records / $records_per_page);
    $base_url = add_query_arg(array('paged' => '%#%'), admin_url('admin.php?page=trimbos_statistics'));

    echo paginate_links(array(
        'base'      => $base_url,
        'format'    => '',
        'current'   => $current_page,
        'total'     => $total_pages,
        'prev_text' => __('« Vorige'),
        'next_text' => __('Volgende »'),
    ));
    ?>

    <!-- Export Button -->
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="export_trimbos_statistics">
        <?php wp_nonce_field('export_trimbos_statistics'); ?>
        <input type="submit" class="button-primary" value="Export to CSV">
    </form>
</div>
