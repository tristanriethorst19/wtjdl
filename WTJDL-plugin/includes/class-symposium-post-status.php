<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Symposium_Post_Status
 * Registers and manages a custom post status called "afgelopen"
 * Automatically applies the status when the event date is in the past.
 */
class Symposium_Post_Status {

    public function __construct() {
        // Register the new status
        add_action('init', array($this, 'register_post_status'));

        // Inject custom status into post edit UI and post list UI
        add_action('admin_footer-post.php', array($this, 'append_post_status_list'));
        add_action('admin_footer-edit.php', array($this, 'append_post_status_list'));

        // Display the custom status next to post titles
        add_filter('display_post_states', array($this, 'display_afgelopen_status_label'));

        // Support custom status in the quick edit dropdown
        add_action('admin_head-edit.php', array($this, 'add_afgelopen_status_to_quick_edit'));

        // Schedule a daily cron job to check event dates
        add_action('init', array($this, 'schedule_event'));
        add_action('check_symposium_event_dates', array($this, 'check_event_dates'));
    }

    /**
     * Registers the 'afgelopen' post status with WordPress
     */
    public function register_post_status() {
        register_post_status('afgelopen', array(
            'label'                     => _x('Afgelopen', 'post'),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('Afgelopen <span class="count">(%s)</span>', 'Afgelopen <span class="count">(%s)</span>')
        ));
    }

    /**
     * Appends the custom status to the post status dropdown (in edit screen)
     */
    public function append_post_status_list() {
        global $post;

        if (isset($post) && $post->post_type == 'symposium') {
            $complete = '';
            $label = '';

            if ($post->post_status == 'afgelopen') {
                $complete = ' selected="selected"';
                $label = '<span id="post-status-display"> Afgelopen</span>';
            }

            // Inject JS into admin footer to update status UI
            echo '
                <script>
                jQuery(document).ready(function($){
                    if ($("#post_status option[value=\'afgelopen\']").length == 0) {
                        $("select#post_status").append("<option value=\"afgelopen\" ' . $complete . '>Afgelopen</option>");
                    }
                    if ($(".misc-pub-section #post-status-display").length == 0) {
                        $(".misc-pub-section label").append("' . $label . '");
                    }
                });
                </script>
            ';
        }
    }

    /**
     * Displays 'Afgelopen' label next to post titles in admin list
     */
    public function display_afgelopen_status_label($states) {
        global $post;

        if (isset($post) && $post->post_type === 'symposium' && $post->post_status === 'afgelopen') {
            $states[] = __('Afgelopen', 'textdomain');
            $states = array_unique($states); // Prevent duplicate labels
        }

        return $states;
    }

    /**
     * Adds the 'afgelopen' option to the Quick Edit dropdown
     */
    public function add_afgelopen_status_to_quick_edit() {
        global $typenow;

        if ($typenow === 'symposium') {
            echo '
                <script>
                jQuery(document).ready(function($) {
                    if ($("select[name=\'_status\'] option[value=\'afgelopen\']").length == 0) {
                        $("select[name=\'_status\']").append("<option value=\"afgelopen\">Afgelopen</option>");
                    }
                });
                </script>
            ';
        }
    }

    /**
     * Schedules a custom daily cron job to check symposium dates.
     * It runs every morning at 6:00 AM Amsterdam time.
     */
    public function schedule_event() {
        if (!wp_next_scheduled('check_symposium_event_dates')) {
            $timestamp = strtotime('today 06:00 Europe/Amsterdam');
            wp_schedule_event($timestamp, 'daily', 'check_symposium_event_dates');
        }
    }

    /**
     * Unhooks the daily event when the plugin is deactivated.
     */
    public static function deactivate() {
        $timestamp = wp_next_scheduled('check_symposium_event_dates');
        wp_unschedule_event($timestamp, 'check_symposium_event_dates');
    }

    /**
     * Cron job logic:
     * Check all symposiums with a date older than today and mark them as 'afgelopen'.
     */
    public function check_event_dates() {
        $timezone = new DateTimeZone('Europe/Amsterdam');
        $today = new DateTime('now', $timezone);
        $today_formatted = $today->format('d/m/Y'); // Match ACF date format

        // Find symposiums where the date has passed
        $args = array(
            'post_type' => 'symposium',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => 'datum', // ACF field with the event date
                    'value' => $today_formatted,
                    'compare' => '<',
                    'type' => 'DATE'
                )
            )
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();

                $post_id = get_the_ID();

                // Update post to status 'afgelopen'
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_status' => 'afgelopen'
                ));
            }

            wp_reset_postdata();
        }
    }
}

// Initialize the post status logic immediately
new Symposium_Post_Status();

// Unschedule event on plugin deactivation
register_deactivation_hook(__FILE__, array('Symposium_Post_Status', 'deactivate'));
