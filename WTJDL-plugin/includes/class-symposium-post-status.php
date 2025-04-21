<?php
if (!defined('ABSPATH')) {
    exit;
}

class Symposium_Post_Status {

    public function __construct() {
        add_action('init', array($this, 'register_post_status'));
        add_action('admin_footer-post.php', array($this, 'append_post_status_list'));
        add_action('admin_footer-edit.php', array($this, 'append_post_status_list'));
        add_filter('display_post_states', array($this, 'display_afgelopen_status_label'));
        add_action('admin_head-edit.php', array($this, 'add_afgelopen_status_to_quick_edit'));
        add_action('init', array($this, 'schedule_event'));
        add_action('check_symposium_event_dates', array($this, 'check_event_dates'));
    }

    /**
     * Register custom post status.
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
     * Append the new post status to post status list in the admin UI.
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
     * Display custom post state next to post titles.
     */
    public function display_afgelopen_status_label($states) {
        global $post;
        if (isset($post) && $post->post_type == 'symposium' && $post->post_status == 'afgelopen') {
            $states[] = __('Afgelopen', 'textdomain');
            // Ensure 'Afgelopen' is only added once
            $states = array_unique($states);
        }
        return $states;
    }

    /**
     * Add custom status to quick edit.
     */
    public function add_afgelopen_status_to_quick_edit() {
        global $typenow;
        if ($typenow == 'symposium') {
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
     * Schedule the custom cron event.
     */
    public function schedule_event() {
        if (!wp_next_scheduled('check_symposium_event_dates')) {
            // Schedule the event to run daily at 6 AM Amsterdam time
            $timestamp = strtotime('today 06:00 Europe/Amsterdam');
            wp_schedule_event($timestamp, 'daily', 'check_symposium_event_dates');
        }
    }

    /**
     * Unschedule the custom cron event on deactivation.
     */
    public static function deactivate() {
        $timestamp = wp_next_scheduled('check_symposium_event_dates');
        wp_unschedule_event($timestamp, 'check_symposium_event_dates');
    }

    /**
     * Check event dates and update post status if the date has passed.
     */
    public function check_event_dates() {
        // Get today's date in d/m/Y format in Amsterdam timezone
        $timezone = new DateTimeZone('Europe/Amsterdam');
        $today = new DateTime('now', $timezone);
        $today_formatted = $today->format('d/m/Y');

        // Query symposium posts where the date has passed
        $args = array(
            'post_type' => 'symposium',
            'posts_per_page' => -1,
            'post_status' => 'publish', // Only check published posts
            'meta_query' => array(
                array(
                    'key' => 'datum',
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

                // Update post status to 'afgelopen'
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_status' => 'afgelopen'
                ));
            }
            wp_reset_postdata();
        }
    }
}

// Initialize the class
new Symposium_Post_Status();

// Hook for plugin deactivation to unschedule the event
register_deactivation_hook(__FILE__, array('Symposium_Post_Status', 'deactivate'));
