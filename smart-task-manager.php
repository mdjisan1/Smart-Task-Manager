<?php
/**
 * Plugin Name: Smart Task Manager
 * Description: A Smart task management plugin for WordPress.
 * Version: 1.0.0
 * Author: Md. Jisan Ahmed
 */

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include necessary files for the plugin's functionality
require_once plugin_dir_path(__FILE__) . 'includes/database.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajax.php';
require_once plugin_dir_path(__FILE__) . 'includes/csv-export.php';
require_once plugin_dir_path(__FILE__) . 'includes/functions.php';

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'stm_create_table');
register_deactivation_hook(__FILE__, 'stm_remove_table');

// Enqueue public styles for the plugin
function stm_enqueue_styles() {
    $plugin_url = plugin_dir_url(__FILE__);
    // Enqueue public CSS
    wp_enqueue_style('stm-public-style', $plugin_url . 'public/public-style.css', array(), '1.0.0', 'all');
}
add_action('wp_enqueue_scripts', 'stm_enqueue_styles');

// Enqueue JavaScript and styles for the admin page
function stm_enqueue_scripts($hook) {
    // Check if on the correct admin page
    if ($hook === 'toplevel_page_smart-tasks') {
        // Enqueue admin JavaScript
        wp_enqueue_script('stm-script', plugins_url('admin/admin-script.js', __FILE__), array('jquery'), null, true);

        // Enqueue admin CSS
        wp_enqueue_style('stm-admin-style', plugins_url('admin/admin-style.css', __FILE__), array(), '1.0.0', 'all');
        
        // Localize script with nonce for security
        wp_localize_script('stm-script', 'stm_data', array(
            'nonce' => wp_create_nonce('stm_nonce') // Create a nonce for AJAX requests
        ));
    }
}
add_action('admin_enqueue_scripts', 'stm_enqueue_scripts');
