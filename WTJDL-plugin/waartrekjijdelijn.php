<?php
/**
 * Plugin Name: WTJDL plugin
 * Plugin URI: https://waartrekjijdelijn.nl
 * Description: WTJDL plugin maakt het mogelijk inschrijvingen te verzamelen.
 * Version: 1.0
 * Author: Tristan Riethorst
 * Author URI: https://bytris.nl/
 */

// Safety check to prevent direct file access.
if (!defined('ABSPATH')) {
    exit; // Only allow execution within WordPress.
}

/**
 * This plugin is organized using a modular architecture. 
 * Each responsibility (form handling, registrations, admin features) is placed in its own class.
 * These files are loaded here to initialize the plugin.
 */

// General event registration logic
require_once plugin_dir_path(__FILE__) . 'includes/class-event-registration-core.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-symposium-registration.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-event-registrations-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-admin-registrations-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-symposium-post-status.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-admin-registrations-export.php';

// Trimbos-specific extensions — separate module for partner/project functionality
require_once plugin_dir_path(__FILE__) . 'includes/class-trimbos-core.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-trimbos-registration.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-trimbos-form.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-trimbos-form-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-trimbos-admin-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-trimbos-results-handler.php';

/**
 * Instantiate global classes immediately so they can hook into WordPress
 * and be used across the plugin's lifecycle.
 */
$event_registration_core = new Event_Registration_Core(__FILE__);
$symposium_registration = new Symposium_Registration();
$event_registrations_handler = new Event_Registrations_Handler();
$admin_registrations_handler = new Admin_Registrations_Handler();
$symposium_post_status = new Symposium_Post_Status();
$admin_export_handler = new Admin_Export_handler();

// Trimbos-related instantiations
$trimbos_core = new Trimbos_Core(__FILE__);
$trimbos_registration = new Trimbos_Registration();
$trimbos_results = new Trimbos_Results_Handler();

/**
 * Hook: Plugin activation.
 * Purpose: Register custom post types and flush rewrite rules to prevent 404 errors.
 */
register_activation_hook(__FILE__, 'wtjdl_plugin_rewrite_flush');
function wtjdl_plugin_rewrite_flush() {
    global $symposium_registration, $trimbos_registration;

    // Register CPTs before flushing to ensure rewrite rules are generated
    if (isset($symposium_registration) && method_exists($symposium_registration, 'register_post_type_and_taxonomy')) {
        $symposium_registration->register_post_type_and_taxonomy();
    }

    if (isset($trimbos_registration) && method_exists($trimbos_registration, 'register_post_type')) {
        $trimbos_registration->register_post_type();
    }

    flush_rewrite_rules(); // Ensures WordPress recognizes new routes
}

/**
 * Hook: Plugin deactivation.
 * Purpose: Clean up rewrite rules to prevent leftover permalinks.
 */
register_deactivation_hook(__FILE__, 'wtjdl_plugin_rewrite_remove');
function wtjdl_plugin_rewrite_remove() {
    flush_rewrite_rules();
}

/**
 * Additional activation tasks such as setting options or DB tables.
 */
register_activation_hook(__FILE__, 'wtjdl_plugin_activate');
function wtjdl_plugin_activate() {
    // Load core logic (defensive check even if already loaded above)
    require_once plugin_dir_path(__FILE__) . 'includes/class-event-registration-core.php';
    $event_registration_core = new Event_Registration_Core(__FILE__);

    // Call custom activation logic if defined
    if (method_exists($event_registration_core, 'activate')) {
        $event_registration_core->activate();
    }
}

/**
 * Deactivation hook.
 * Reserved for any cleanup tasks if needed later.
 */
register_deactivation_hook(__FILE__, 'wtjdl_plugin_deactivate');
function wtjdl_plugin_deactivate() {
    // Placeholder: add deactivation logic here if needed
}

