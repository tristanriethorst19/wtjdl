<?php
if (!defined('ABSPATH')) {
    exit;
}

// Capture the unique token from the URL
$unique_token = get_query_var('unique_token');

// Validate the token
if (!$unique_token) {
    wp_die('Invalid or missing results token.');
}

// Fetch submission data
global $wpdb;
$submission = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}trimbos_form_submissions WHERE unique_token = %s",
    $unique_token
));

if (!$submission) {
    wp_die('No results found for the provided token.');
}

// Fetch answers linked to this submission
$answers = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}trimbos_form_answers WHERE submission_id = %d",
    $submission->id
));

// Retrieve ACF data for questions and answers
$post_id = 1671; // ID of the ACF post with the questions
$questions = [];

// Fetch intro and outro content
$introductie_content = apply_filters('the_content', get_field('introductie', $post_id));
$extra_informatie_content = apply_filters('the_content', get_field('extra_informatie', $post_id));

if (have_rows('vragenlijst', $post_id)) {
    while (have_rows('vragenlijst', $post_id)) : the_row();
        $unique_identifier = get_sub_field('unique_identifier');
        $questions[$unique_identifier] = [
            'question' => get_sub_field('hoofdvraag'),
            'keuze_1' => get_sub_field('keuze_1'),
            'keuze_1_resultaat_titel' => get_sub_field('keuze_1_resultaat_titel'), // Custom title for keuze_1
            'antwoord_1' => apply_filters('the_content', get_sub_field('antwoord_1')), // Process WYSIWYG content
            'keuze_2' => get_sub_field('keuze_2'),
            'keuze_2_resultaat_titel' => get_sub_field('keuze_2_resultaat_titel'), // Custom title for keuze_2
            'antwoord_2' => apply_filters('the_content', get_sub_field('antwoord_2')), // Process WYSIWYG content
        ];
    endwhile;
}

get_header();

if (have_posts()) : 
    while (have_posts()) : the_post();
?>
<head>
    <style>
        body {
            --e-global-color-primary: #3AB5FF;
            --e-global-color-secondary: #0091E8;
            --e-global-color-text: #FF0000;
            --e-global-color-accent: #FFFFFF;
            --e-global-color-c985802: #F5F5F5;
            --e-global-color-22a0a5d: #B3B3B3;
            --e-global-color-f655ba3: #121212;
            --e-global-color-73a374d: #A7E38F;
            --e-global-color-50a0f15: #FF9A99;
            --e-global-typography-primary-font-family: "Arimo";
            --e-global-typography-primary-font-size: 18px;
            --e-global-typography-primary-font-weight: 400;
            --e-global-typography-secondary-font-family: "Roboto Slab";
            --e-global-typography-secondary-font-weight: 400;
            --e-global-typography-text-font-family: "Roboto";
            --e-global-typography-text-font-weight: 400;
            --e-global-typography-accent-font-family: "Roboto";
            --e-global-typography-accent-font-weight: 500;
            --e-global-typography-fa161f1-font-family: "Arimo";
            --e-global-typography-fa161f1-font-size: 14px;
            --e-global-typography-fa161f1-font-weight: 900;
            --e-global-typography-fa161f1-font-style: italic;
            --e-global-typography-589dcec-font-family: "Arimo";
            --e-global-typography-589dcec-font-size: 14px;
            --e-global-typography-884d98c-font-family: "Arial";
            --e-global-typography-884d98c-font-size: 18px;
            --e-global-typography-884d98c-font-weight: 900;
            --e-global-typography-884d98c-font-style: italic;
        }
    </style>
</head>
<div class="trimbos-results-page">
    <div class="trimbos-container">
        <div class="trimbos-intro">
            <?php echo $introductie_content; // Render intro content ?>
        </div>

        <div class="trimbos-accordions">
            <?php
            foreach ($answers as $answer) {
                $question_id = $answer->question_id;
                $user_answer = $answer->answer_text;

                if (isset($questions[$question_id])) {
                    $question_data = $questions[$question_id];

                    // Determine the answer content and custom title
                    if ($user_answer === $question_data['keuze_1']) {
                        $answer_title = $question_data['keuze_1_resultaat_titel'] ?: $question_data['question'];
                        $answer_content = $question_data['antwoord_1'];
                    } else {
                        $answer_title = $question_data['keuze_2_resultaat_titel'] ?: $question_data['question'];
                        $answer_content = $question_data['antwoord_2'];
                    }

                    // Display accordion
                    echo '<div class="accordion">';
                    echo '<div class="accordion-header" id="accordion-header-' . esc_attr($question_id) . '">';
                    echo '<span>' . esc_html($answer_title) . '</span>';
                    echo '<svg class="accordion-arrow" width="24" height="24" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.33301 13.3333L15.9997 20L22.6663 13.3333" stroke="#B3B3B3" stroke-width="4.6875" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                    echo '</div>';
                    echo '<div class="accordion-content" id="accordion-content-' . esc_attr($question_id) . '">';
                    echo $answer_content; // Render WYSIWYG content properly
                    echo '</div>';
                    echo '</div>';
                }
            }
            ?>
        </div>

        <div class="trimbos-outro">
            <?php echo $extra_informatie_content; // Render outro content ?>
        </div>
    </div>
</div>
<?php
    endwhile;
endif;

get_footer();
