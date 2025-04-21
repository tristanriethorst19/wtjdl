<?php
if (!defined('ABSPATH')) {
    exit;
}

class Symposium_Registration {
    private $hook_suffix;

    /**
     * Constructor to initialize the class.
     */
    public function __construct() {
        add_action('init', array($this, 'register_post_type_and_taxonomy'));
        add_action('admin_menu', array($this, 'add_submenu'));
    	add_action('wp_enqueue_scripts', array($this, 'enqueue_symposium_scripts'));
    }

/**
     * Register the 'Symposium' custom post type and 'Steden' taxonomy.
     */
    public function register_post_type_and_taxonomy() {
        $labels = array(
            'name'               => __( 'Symposia' ),
            'singular_name'      => __( 'Symposium' ),
            'menu_name'          => __( 'Symposia' ),
            'name_admin_bar'     => __( 'Symposium' ),
            'add_new'            => __( 'Nieuw Symposium toevoegen' ),
            'add_new_item'       => __( 'Nieuw Symposium toevoegen' ),
            'new_item'           => __( 'Nieuw Symposium' ),
            'edit_item'          => __( 'Bewerk Symposium' ),
            'view_item'          => __( 'Bekijk Symposium' ),
            'all_items'          => __( 'Alle Symposia' ),
            'search_items'       => __( 'Zoek Symposia' ),
            'parent_item_colon'  => __( 'Hoofd Symposium:' ),
            'not_found'          => __( 'Geen symposia gevonden.' ),
            'not_found_in_trash' => __( 'Geen symposia gevonden in prullenbak.' )
        );
        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'symposium' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title', 'editor'), // Adjust as needed
            'taxonomies'         => array( 'steden' ),
            'menu_icon'				=> 'dashicons-microphone',
        );
        register_post_type( 'symposium', $args );

        $labels = array(
            'name'                       => __( 'Steden' ),
            'singular_name'              => __( 'Stad' ),
            'search_items'               => __( 'Zoek Steden' ),
            'popular_items'              => __( 'Populaire Steden' ),
            'all_items'                  => __( 'Alle Steden' ),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'edit_item'                  => __( 'Bewerk Stad' ),
            'update_item'                => __( 'Update Stad' ),
            'add_new_item'               => __( 'Nieuwe Stad toevoegen' ),
            'new_item_name'              => __( 'Nieuwe Stad' ),
            'separate_items_with_commas' => __( 'Scheid steden met komma\'s' ),
            'add_or_remove_items'        => __( 'Voeg steden toe of verwijder ze' ),
            'choose_from_most_used'      => __( 'Kies uit meest gebruikte steden' ),
            'not_found'                  => __( 'Geen steden gevonden.' ),
            'menu_name'                  => __( 'Steden' ),
        );
        $args = array(
            'hierarchical'          => false,
            'labels'                => $labels,
            'show_ui'               => true,
            'show_admin_column'     => true,
            'update_count_callback' => '_update_post_term_count',
            'query_var'             => true,
            'rewrite'               => array( 'slug' => 'stad' ),
            'menu_icon'				=> 'dashicons-location',
        );
        register_taxonomy( 'steden', 'symposium', $args );
    }

    /**
     * Adds a submenu item to the 'Symposium' custom post type and stores the hook suffix.
     */
    public function add_submenu() {
        $this->hook_suffix = add_submenu_page(
            'edit.php?post_type=symposium',
            __('Inschrijvingen', 'textdomain'),
            __('Inschrijvingen', 'textdomain'),
            'manage_options',
            'registrations-admin',
            array($this, 'display_admin_page')
        );

        // Enqueue scripts specifically for this page
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    /**
     * Enqueues scripts and styles for the admin page.
     */
    public function enqueue_admin_scripts($hook_suffix) {
        if ($hook_suffix === $this->hook_suffix) {
            wp_enqueue_script(
                'admin-ajax-script', 
                plugins_url('/admin/js/admin-scripts.js', dirname(__FILE__)), 
                array('jquery'), 
                null, 
                true
            );
			wp_localize_script(
            	'admin-ajax-script',
            	'adminAjax',
            	array(
                	'ajaxurl' => admin_url('admin-ajax.php'),
                	'security' => wp_create_nonce('load_registrations_nonce')  // Create and pass the nonce here
            	)
        	);
        }
    }
        public function enqueue_symposium_scripts() {
        if (is_singular('symposium')) {
            wp_enqueue_script(
                'symposium-script',
                plugins_url('/public/js/symposium.js', dirname(__FILE__)),  // Adjust the path as needed.
                array('jquery'),
                '1.0.0',
                true
            );
        }
    }

    /**
     * Include the admin page.
     */
    public function display_admin_page() {
        include plugin_dir_path(dirname(__FILE__)) . 'admin/registrations-page.php';
    }
}

