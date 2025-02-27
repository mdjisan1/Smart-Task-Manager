<?php

// Activation Hook: Create Table
function stm_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'smart_tasks';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    // Clear the transient cache to remove old task data
    // delete_transient('stm_task_list');
}

// Deactivation Hook: Remove Table
function stm_remove_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'smart_tasks';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}