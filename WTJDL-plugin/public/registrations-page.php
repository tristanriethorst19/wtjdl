<?php
get_header();

if (have_posts()) : 
    while (have_posts()) : the_post();
        global $post;
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
<div class="registration-page-container">
    <div class="registration-container">
        <div class="registration-title-container">
            <h2>Aanmelden</h2>
            <p>Neem deel aan het symposium <strong><?php the_title(); ?></strong> door hieronder je gegevens in te vullen.</p>
        </div>
        <div class="registration-form-container">
            <?php
            if ($post->post_status === 'publish') {
            ?>
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                <!-- First Name -->
                <label for="first_name">Voornaam:</label>
                <input type="text" id="first_name" name="first_name" required>

                <!-- Last Name -->
                <label for="last_name">Achternaam:</label>
                <input type="text" id="last_name" name="last_name" required>

                <!-- Email Address -->
                <label for="email">E-mail adres:</label>
                <input type="email" id="email" name="email" required>

                <!-- Age Range -->
                <label for="age">Leeftijd:</label>
                <select id="age" name="age" required>
                    <option value="">Selecteer je leeftijd</option>
                    <?php for ($i = 18; $i <= 28; $i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
                    <option value="28+">28 of ouder</option>
                </select>

                <!-- Gender -->
                <label for="gender">Geslacht:</label>
                <select id="gender" name="gender" required>
                    <option value="">Selecteer geslacht</option>
                    <option value="man">Man</option>
                    <option value="vrouw">Vrouw</option>
                    <option value="prefer_not_to_say">Zeg ik liever niet</option>
                </select>

                <!-- Educational Level -->
                <label for="education_level">Opleidingsniveau:</label>
                <select id="education_level" name="education_level" required>
                    <option value="">Selecteer opleidingsniveau</option>
                    <option value="mbo">MBO</option>
                    <option value="hbo">HBO</option>
                    <option value="wo">WO</option>
                    <option value="none">Geen van bovenstaande</option>
                </select>

                <!-- Association Membership -->
                <label for="association">Zit je bij een vereniging? Zo ja, welke?</label>
                <input type="text" id="association" name="association">

                <!-- Hidden Fields -->
                <input type="hidden" name="action" value="submit_registration">
                <input type="hidden" name="event_id" value="<?php the_ID(); ?>">
                <?php wp_nonce_field('submit_registration'); ?>

                <input type="submit" value="Aanmelden">

                <!-- Agreement Checkboxes -->
                <div class="form-agreements">
                    <input type="checkbox" id="privacy_policy" name="privacy_policy" required>
                    <label for="privacy_policy">Door je in te schrijven voor dit symposium ga je akkoord met onze <a href="https://waartrekjijdelijn.nl/privacy-verklaring/" target="_blank">privacyverklaring</a>.</label>
                </div>
            </form>
            <?php
            }
            else {
                $message = get_field('inschrijvingen_gesloten', 'options');
                echo '<div class="registration-closed-message"><span>' . esc_html($message) . '</span></div>';
            }
            ?>
        </div>
    </div>
</div>
<?php
    endwhile;
endif;

get_footer();
?>
