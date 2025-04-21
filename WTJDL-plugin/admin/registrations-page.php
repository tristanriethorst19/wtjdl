<?php
if (!defined('ABSPATH')) {
    exit;
}

?>
<div class="wrap">
    <h1>Inschrijvingen></h1>
    <h2>Selecteer hieronder een symposium:</h2>
    <select id="symposium-select" name="symposium" class="postform">
        <option value=""><?php esc_html_e('Selecteren', 'textdomain'); ?></option>
        <?php
        $symposia = get_posts([
            'post_type'      => 'symposium',
            'posts_per_page' => -1,
            'post_status'    => ['publish', 'afgelopen']
        ]);
        foreach ($symposia as $symposium) {
            printf(
                '<option value="%s">%s</option>',
                esc_attr($symposium->ID),
                esc_html($symposium->post_title)
            );
        }
        ?>
    </select>
    <button id="export-registrations" style="display: none;" class="button button-primary">Exporteer Registraties</button>
    <div id="registrations-table" style="margin-top:10px;">
        <?php esc_html_e('Geen symposium geselecteerd.', 'textdomain'); ?>
    </div>
    
</div>
