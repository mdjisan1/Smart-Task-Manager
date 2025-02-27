<?php

// Generate CSV file and initiate download
function generate_csv_file() {
    // Check if the user is logged in and has the required capability
    if (!is_user_logged_in() || !current_user_can('manage_options')) {
        wp_die('Unauthorized access.'); // Exit if unauthorized
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'smart_tasks';
    $tasks = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A); // Fetch tasks as associative array

    if (empty($tasks)) {
        wp_die('No data found.'); // Exit if no tasks found
    }

    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="tasks.csv"');

    // Open output stream for writing
    $output = fopen('php://output', 'w');

    // Define CSV column headers
    fputcsv($output, ['Task ID', 'Title', 'Description', 'Status', 'Created At']);

    // Write data to CSV
    foreach ($tasks as $task) {
        fputcsv($output, [
            esc_html($task['id']),
            esc_html($task['title']),
            esc_html($task['description']),
            esc_html($task['status']),
            esc_html($task['created_at'])
        ]);
    }

    fclose($output); // Close the output stream
    exit; // Terminate script execution to avoid extra output
}

// Register the function to the 'admin_post_' action for CSV export
add_action('admin_post_export_csv', 'generate_csv_file');
