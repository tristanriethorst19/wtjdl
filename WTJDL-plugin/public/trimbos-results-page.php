<?php
// Security check: prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get the unique token from the URL query variable
$unique_token = get_query_var('unique_token');

// Validate the token — if it’s missing, we halt with a user-friendly message
if (!$unique_token) {
    wp_die('Invalid or missing results token.');
}

// Fetch the user's form submission from the custom submissions table
global $wpdb;
$submission = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}trimbos_form_submissions WHERE unique_token = %s",
    $unique_token
));

// If the token is not found in the database, stop and show error
if (!$submission) {
    wp_die('No results found for the provided token.');
}

// Now fetch all individual answers tied to this submission
$answers = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}trimbos_form_answers WHERE submission_id = %d",
    $submission->id
));

// Static ACF post ID where questions and answer content are stored
$post_id = 1671;

// Load intro and outro WYSIWYG content, filtered for styling
$introductie_content = apply_filters('the_content', get_field('introductie', $post_id));
$extra_informatie_content = apply_filters('the_content', get_field('extra_informatie', $post_id));

// Build an array of questions + possible answer outcomes
$questions = [];

if (have_rows('vragenlijst', $post_id)) {
    while (have_rows('vragenlijst', $post_id)) : the_row();
        $unique_identifier = get_sub_field('unique_identifier');

        $questions[$unique_identifier] = [
            'question' => get_sub_field('hoofdvraag'),
            'keuze_1' => get_sub_field('keuze_1'),
            'keuze_1_resultaat_titel' => get_sub_field('keuze_1_resultaat_titel'),
            'antwoord_1' => apply_filters('the_content', get_sub_field('antwoord_1')),

            'keuze_2' => get_sub_field('keuze_2'),
            'keuze_2_resultaat_titel' => get_sub_field('keuze_2_resultaat_titel'),
            'antwoord_2' => apply_filters('the_content', get_sub_field('antwoord_2')),
        ];
    endwhile;
}

// Load WordPress header
get_header();

// Begin rendering the results page if there’s content
if (have_posts()) : 
    while (have_posts()) : the_post();
?>
<head>
    <style>
        /* Global design tokens, likely from Elementor or your design system */
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

<!-- Main results page structure -->
<div class="trimbos-results-page">
    <div class="trimbos-container">

        <!-- Intro section (rich text, from ACF) -->
        <div class="trimbos-intro">
            <?php echo $introductie_content; ?>
        </div>

        <!-- Dynamic accordion list based on user's answers -->
        <div class="trimbos-accordions">
            <?php
            foreach ($answers as $answer) {
                $question_id = $answer->question_id;
                $user_answer = $answer->answer_text;

                // Skip if we don’t have matching question metadata
                if (isset($questions[$question_id])) {
                    $question_data = $questions[$question_id];

                    // Determine which answer the user chose and show the correct content
                    if ($user_answer === $question_data['keuze_1']) {
                        $answer_title = $question_data['keuze_1_resultaat_titel'] ?: $question_data['question'];
                        $answer_content = $question_data['antwoord_1'];
                    } else {
                        $answer_title = $question_data['keuze_2_resultaat_titel'] ?: $question_data['question'];
                        $answer_content = $question_data['antwoord_2'];
                    }

                    // Render the accordion component
                    echo '<div class="accordion">';
                    echo '<div class="accordion-header" id="accordion-header-' . esc_attr($question_id) . '">';
                    echo '<span>' . esc_html($answer_title) . '</span>';
                    echo '<svg class="accordion-arrow" width="24" height="24" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.33301 13.3333L15.9997 20L22.6663 13.3333" stroke="#B3B3B3" stroke-width="4.6875" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                    echo '</div>';
                    echo '<div class="accordion-content" id="accordion-content-' . esc_attr($question_id) . '">';
                    echo $answer_content;
                    echo '</div>';
                    echo '</div>';
                }
            }
            ?>
        </div>

        <!-- Outro / further info (from ACF) -->
        <div class="trimbos-outro">
            <?php echo $extra_informatie_content; ?>
        </div>
    </div>
</div>

<?php
    endwhile;
endif;

// Load WordPress footer
get_footer();
