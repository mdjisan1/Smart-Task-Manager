<?php

// AJAX Add Task Function (Includes Highlight)
function stm_add_task() {
    check_ajax_referer('stm_nonce', 'nonce'); // Verify nonce for security
    global $wpdb;
    $table_name = $wpdb->prefix . 'smart_tasks';

    // Sanitize input data
    $title = sanitize_text_field($_POST['title']);
    $description = sanitize_textarea_field($_POST['description']);
    $title = stm_modify_task_title($title, $description); // Modify title for urgent tasks

    // Insert new task into the database
    $wpdb->insert($table_name, [
        'title' => $title,
        'description' => $description,
        'status' => 'pending'
    ]);

    $task_id = $wpdb->insert_id; // Get the ID of the newly inserted task
    if (!$task_id) {
        wp_send_json_error(['message' => 'Task could not be added']); // Return error if insertion fails
    }

    // Trigger the logging action
    do_action('stm_task_added', $task_id, $title);

    delete_transient('stm_task_list'); // Clear the transient cache for tasks

    // Apply highlight if WooCommerce is active and the title contains 'order'
    $highlight = '';
    if (class_exists('WooCommerce') && stripos($title, 'order') !== false) {
        $highlight = 'background-color: yellow;'; // Set highlight style
    }

    // Send success response with task details
    wp_send_json_success([
        'id' => $task_id,
        'title' => $title,
        'created_at' => current_time('mysql'),
        'highlight' => $highlight
    ]);

    wp_die(); // End AJAX processing
}
add_action('wp_ajax_stm_add_task', 'stm_add_task'); // Register AJAX action for adding tasks

// AJAX Mark as Completed Function
function stm_mark_completed() {
    check_ajax_referer('stm_nonce', 'nonce'); // Verify nonce for security
    global $wpdb;
    $table_name = $wpdb->prefix . 'smart_tasks';
    $task_id = intval($_POST['task_id']); // Sanitize task ID input

    // Update task status to 'completed' in the database
    $updated = $wpdb->update($table_name, ['status' => 'completed'], ['id' => $task_id]);

    if ($updated !== false) {
        delete_transient('stm_task_list'); // Clear the transient cache for tasks
        wp_send_json_success(['message' => 'Task marked as completed']); // Send success response
    } else {
        wp_send_json_error(['message' => 'Database update failed']); // Send error response if update fails
    }

    wp_die(); // End AJAX processing
}
add_action('wp_ajax_stm_mark_completed', 'stm_mark_completed'); // Register AJAX action for marking tasks completed
