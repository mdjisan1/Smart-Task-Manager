<?php

// Add Admin Menu for the Smart Task Manager
function stm_add_admin_page() {
    add_menu_page(
        'Smart Tasks',                   // Page title
        'Smart Tasks',                   // Menu title
        'manage_options',                // Capability required
        'smart-tasks',                   // Menu slug
        'stm_admin_page',                // Function to display the page content
        'dashicons-list-view'            // Icon for the menu
    );
}
add_action('admin_menu', 'stm_add_admin_page');

// Logs task creation details if WP_DEBUG is enabled.
function stm_log_task_creation($task_id, $task_title) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        $timestamp = current_time('mysql'); // Get the current time
        $message = "Task Added: {$task_title} | Task ID: {$task_id} | Time: {$timestamp} \n";
        error_log($message);
    }
}

// Hook into the action 'stm_task_added'
add_action('stm_task_added', 'stm_log_task_creation', 10, 2);


// Modify Task Title for Urgent Tasks
function stm_modify_task_title($title, $description) {
    if (strpos(strtolower($description), 'urgent') !== false) {
        return '[URGENT] ' . $title; // Prefix urgent tasks
    }
    return $title; // Return original title for non-urgent tasks
}

// Highlight Tasks related to Orders if WooCommerce is active
function stm_highlight_order_tasks($tasks) {
    foreach ($tasks as $task) {
        // Ensure the highlight property is always set
        $task->highlight = ''; // Default empty value

        // Check if WooCommerce is active and title contains 'order'
        if (class_exists('WooCommerce') && stripos($task->title, 'order') !== false) {
            $task->highlight = 'background-color: yellow;'; // Apply highlight
        }
    }
    return $tasks; // Return modified tasks
}
add_filter('stm_filter_tasks', 'stm_highlight_order_tasks');


// Admin Page Content for displaying tasks
function stm_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'smart_tasks'; // Define the tasks table
    $tasks = get_transient('stm_task_list'); // Get tasks from transient

    // Fetch tasks from the database if not available in transient
    if (!$tasks) {
        $tasks = $wpdb->get_results("SELECT * FROM $table_name");
        set_transient('stm_task_list', $tasks, 60 * 5); // Cache tasks for 5 minutes
    }

    $tasks = apply_filters('stm_filter_tasks', $tasks); // Apply filters to tasks
    ?>
    <div class="wrap">
        <h1>Smart Task Manager</h1>
        <form method="post" id="stm-add-task">
            <input type="text" name="title" placeholder="Task Title" required>
            <textarea name="description" placeholder="Task Description"></textarea>
            <button type="submit">Add Task</button>
        </form>
        <table>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($tasks as $task) : ?>
                <tr data-task-id="<?php echo esc_attr($task->id); ?>" style="<?php echo esc_attr($task->highlight); ?>">
                    <td><?php echo esc_html($task->id); ?></td>
                    <td><?php echo esc_html($task->title); ?></td>
                    <td class="task-status"><?php echo esc_html($task->status); ?></td>
                    <td><?php echo esc_html($task->created_at); ?></td>
                    <td>
                        <?php if ($task->status !== 'completed') : ?>
                            <button class="mark-completed" data-id="<?php echo esc_attr($task->id); ?>">Complete</button>
                        <?php else : ?>
                            <span class="completed-text">✔ Completed</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php
            // Provide a link to download tasks as CSV
            echo '<a href="' . esc_url(admin_url('admin-post.php?action=export_csv')) . '" class="download-csv-button">Download CSV</a>';
        ?>
    </div>
    <?php
}
