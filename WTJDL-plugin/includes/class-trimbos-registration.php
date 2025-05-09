<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Trimbos_Registration
 * Registers the custom post type for Trimbos and (optionally) adds a statistics dashboard page.
 */
class Trimbos_Registration {
    private $hook_suffix;

    /**
     * Constructor sets up the CPT and optional admin page.
     */
    public function __construct() {
        // Register the 'trimbos' custom post type
        add_action('init', array($this, 'register_trimbos_cpt'));

        // Optional: add an admin submenu page (currently commented out)
        // add_action('admin_menu', array($this, 'add_admin_pages'));

        // Hook for loading styles/scripts conditionally on the stats page
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    /**
     * Registers the 'trimbos' custom post type.
     * Used to manage ACF fields, question lists, and optionally track form entries.
     */
    public function register_trimbos_cpt() {
        $labels = array(
            'name'               => __( 'Trimbos' ),
            'singular_name'      => __( 'Trimbos' ),
            'menu_name'          => __( 'Trimbos' ),
            'name_admin_bar'     => __( 'Trimbos' ),
            'add_new'            => __( 'Nieuwe Trimbos toevoegen' ),
            'add_new_item'       => __( 'Nieuwe Trimbos toevoegen' ),
            'new_item'           => __( 'Nieuw Trimbos' ),
            'edit_item'          => __( 'Bewerk Trimbos' ),
            'view_item'          => __( 'Bekijk Trimbos' ),
            'all_items'          => __( 'Alle Trimbos' ),
            'search_items'       => __( 'Zoek Trimbos' ),
            'parent_item_colon'  => __( 'Hoofd Trimbos:' ),
            'not_found'          => __( 'Geen Trimbos gevonden.' ),
            'not_found_in_trash' => __( 'Geen Trimbos gevonden in prullenbak.' )
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,                       // Frontend visibility
            'publicly_queryable' => true,
            'show_ui'            => true,                       // Show in admin menu
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'trimbos'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title', 'editor'),   // Use editor to configure questions, texts
            'menu_icon'          => 'dashicons-analytics',
        );

        register_post_type('trimbos', $args);
    }

    /**
     * Optional: Adds a 'Statistieken' submenu page (commented out).
     *
     * This would allow viewing of all submissions and exports from the WP Admin UI.
     */
    /*
    public function add_admin_pages() {
        $this->hook_suffix = add_submenu_page(
            'edit.php?post_type=trimbos',
            __('Statistieken', 'textdomain'),
            __('Statistieken', 'textdomain'),
            'manage_options',
            'trimbos-statistics',
            array($this, 'display_statistics_page')
        );
    }
    */

    /**
     * Enqueue admin styles and scripts only on the statistics page (if it were enabled).
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook === $this->hook_suffix) {
            wp_enqueue_style(
                'trimbos-admin-style',
                plugins_url('/admin/css/trimbos-admin.css', dirname(__FILE__)),
                array(),
                '1.0.0'
            );

            wp_enqueue_script(
                'trimbos-admin-script',
                plugins_url('/admin/js/trimbos-admin.js', dirname(__FILE__)),
                array('jquery'),
                '1.0.0',
                true
            );
        }
    }

    /**
     * Include the admin template for the 'Statistieken' page.
     * (Currently not active)
     */
    /*
    public function display_statistics_page() {
        include plugin_dir_path(dirname(__FILE__)) . 'admin/trimbos-statistieken.php';
    }
    */
}
