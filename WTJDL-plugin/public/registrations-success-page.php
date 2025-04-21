<?php
// Security check: prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

// Use the global $post object to fetch the event title
$post_title = get_the_title();

// Retrieve a success message from the ACF options page (customizable via admin)
$success_text = get_field('inschrijving_succesvol_tekst', 'option');

// Load the WordPress theme header
get_header();

// Standard WordPress Loop — ensure there's a post to work with
if (have_posts()) : 
    while (have_posts()) : the_post();
?>
<head>
    <style>
        /* Inline CSS variables, most likely for Elementor or other visual page builder */
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

<!-- Confirmation message layout -->
<div class="registration-succes-container">
    <div class="registration-success">
        <h2>Bevestiging</h2>
        <p>
            Je hebt je succesvol aangemeld voor het symposium 
            <strong><?php echo esc_html($post_title); ?></strong>
        </p>
    </div>
</div>

<?php
    endwhile;
endif;

// Load the WordPress footer
get_footer();
