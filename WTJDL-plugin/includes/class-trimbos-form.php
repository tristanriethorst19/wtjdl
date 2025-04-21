<?php
if (!defined('ABSPATH')) {
    exit;
}

class Trimbos_Form {
    public function __construct() {
        add_shortcode('trimbos_form', array($this, 'render_form'));
    }

    public function render_form() {
        // Get the Trimbos post with ID 1671
        $post_id = 1671;

        // Fetch title, subtitle, submit button text, and placeholder text from ACF fields
        $title = get_field('form_title', $post_id);
        $subtitle = get_field('form_subtitle', $post_id);
        $submit_button_text = get_field('form_button', $post_id);
        $placeholder_text = get_field('placeholder_text', $post_id);

        ob_start();
        ?>

        <div class="trimbos-container">
            <div class="trimbos-form-container">
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="trimbos_form_submission">

                    <?php
                    // Loop through each question in the 'vragenlijst' repeater field
                    if (have_rows('vragenlijst', $post_id)) :
                        while (have_rows('vragenlijst', $post_id)) : the_row();
                            $question_text = get_sub_field('hoofdvraag');
                            $answer_1 = get_sub_field('keuze_1');
                            $answer_2 = get_sub_field('keuze_2');
                            $unique_identifier = get_sub_field('unique_identifier'); // Get unique ID for each question
                    ?>
                            <div class="trimbos-question">
                                <label for="question_<?php echo esc_attr($unique_identifier); ?>"><?php echo esc_html($question_text); ?></label>
                                <select name="question_<?php echo esc_attr($unique_identifier); ?>" id="question_<?php echo esc_attr($unique_identifier); ?>" required>
                                    <option value="" disabled selected><?php echo esc_html($placeholder_text); ?></option>
                                    <option value="<?php echo esc_attr($answer_1); ?>"><?php echo esc_html($answer_1); ?></option>
                                    <option value="<?php echo esc_attr($answer_2); ?>"><?php echo esc_html($answer_2); ?></option>
                                </select>
                            </div>
                    <?php
                        endwhile;
                    endif;
                    ?>

                    <?php
                    // Display the additional static question with unique identifier 0
                    $extra_question_label = get_field('extra_vraag_vraag', $post_id);

                    if ($extra_question_label && have_rows('extra_vraag_antwoord', $post_id)) :
                    ?>
                        <div class="trimbos-question">
                            <label for="extra_question"><?php echo esc_html($extra_question_label); ?></label>
                            <select name="question_0" id="extra_question" required>
                                <option value="" disabled selected><?php echo esc_html($placeholder_text); ?></option>
                                <?php
                                while (have_rows('extra_vraag_antwoord', $post_id)) : the_row();
                                    $extra_answer = get_sub_field('antwoord');
                                ?>
                                    <option value="<?php echo esc_attr($extra_answer); ?>"><?php echo esc_html($extra_answer); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <input type="submit" value="<?php echo esc_attr($submit_button_text); ?>">
                </form>
            </div>
        </div>

        <?php
        return ob_get_clean();
    }
}

new Trimbos_Form();
