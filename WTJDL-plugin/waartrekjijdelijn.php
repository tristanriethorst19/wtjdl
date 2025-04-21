<?php
/**
 * Plugin Name: WTJDL plugin
 * Plugin URI: https://waartrekjijdelijn.nl
 * Description: WTJDL plugin maakt het mogelijk inschrijvingen te verzamelen.
 * Version: 1.0
 * Author: Tristan Riethorst
 * Author URI: https://bytris.nl/
 */

// Make sure we don't expose any info if called directly
if (!defined('ABSPATH')) {
    exit;
}

// Main file basics remain unchanged
require_once plugin_dir_path(__FILE__) . 'includes/class-event-registration-core.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-symposium-registration.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-event-registrations-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-admin-registrations-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-symposium-post-status.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-admin-registrations-export.php';

// Trimbos
require_once plugin_dir_path(__FILE__) . 'includes/class-trimbos-core.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-trimbos-registration.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-trimbos-form.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-trimbos-form-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-trimbos-admin-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-trimbos-results-handler.php';

// Instantiate classes globally if needed throughout the plugin
$event_registration_core = new Event_Registration_Core(__FILE__);
$symposium_registration = new Symposium_Registration();
$event_registrations_handler = new Event_Registrations_Handler();
$admin_registrations_handler = new Admin_Registrations_Handler();
$symposium_post_status = new Symposium_Post_Status();
$admin_export_handler = new Admin_Export_handler();

// Trimbos
$trimbos_core = new Trimbos_Core(__FILE__);
$trimbos_registration = new Trimbos_Registration();
$trimbos_results = new Trimbos_Results_Handler();


/**
 * Flush rewrite rules on plugin activation.
 */
register_activation_hook(__FILE__, 'wtjdl_plugin_rewrite_flush');
function wtjdl_plugin_rewrite_flush() {
    // Ensure CPTs are registered before flushing
    global $symposium_registration, $trimbos_registration;

    if (isset($symposium_registration) && method_exists($symposium_registration, 'register_post_type_and_taxonomy')) {
        $symposium_registration->register_post_type_and_taxonomy();
    }

    if (isset($trimbos_registration) && method_exists($trimbos_registration, 'register_post_type')) {
        $trimbos_registration->register_post_type();
    }

    flush_rewrite_rules();
}

/**
 * Flush rewrite rules on plugin deactivation.
 */
register_deactivation_hook(__FILE__, 'wtjdl_plugin_rewrite_remove');
function wtjdl_plugin_rewrite_remove() {
    flush_rewrite_rules();
}

/**
 * Activation hook callback function.
 * This function initializes specific activation tasks.
 */
register_activation_hook(__FILE__, 'wtjdl_plugin_activate');
function wtjdl_plugin_activate() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-event-registration-core.php';
    $event_registration_core = new Event_Registration_Core(__FILE__);
    if (method_exists($event_registration_core, 'activate')) {
        $event_registration_core->activate();
    }
}

/**
 * Deactivation hook callback function.
 */
register_deactivation_hook(__FILE__, 'wtjdl_plugin_deactivate');
function wtjdl_plugin_deactivate() {
    // If specific tasks need to be performed, define them here
}
