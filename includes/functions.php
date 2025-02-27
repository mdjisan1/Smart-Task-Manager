<?php
// Shortcode for Front-End Display of Completed Tasks
function stm_shortcode_display_tasks() {
    global $wpdb;

    // Get the search query from the URL, if set
    $search_query = isset($_GET['task_search']) ? sanitize_text_field($_GET['task_search']) : '';

    // Prepare the SQL query to fetch completed tasks
    $query = "SELECT * FROM {$wpdb->prefix}smart_tasks WHERE status='completed'";
    
    // If a search query is provided, modify the SQL to include a title filter
    if (!empty($search_query)) {
        $query .= $wpdb->prepare(" AND title LIKE %s", '%' . $wpdb->esc_like($search_query) . '%');
    }

    // Execute the query and get results
    $tasks = $wpdb->get_results($query);

    // Enqueue the CSS file for styling the tasks
    wp_enqueue_style('tasks-style', plugin_dir_url(__FILE__) . 'tasks-style.css');

    ob_start(); // Start output buffering
    ?>
    <div class="stm-task-container">
        <h2 class="stm-task-heading">Completed Tasks</h2>
        
        <!-- Search Form -->
        <form method="GET" class="stm-search-form">
            <input type="text" name="task_search" value="<?php echo esc_attr($search_query); ?>" placeholder="Search by title...">
            <button type="submit">Search</button>
        </form>

        <div class="table-responsive">
            <table class="stm-task-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th class="stm-description">Description</th>
                        <th>Completion Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($tasks)) : ?>
                        <?php foreach ($tasks as $task) : ?>
                            <tr>
                                <td><?php echo esc_html($task->id); ?></td>
                                <td><?php echo esc_html($task->title); ?></td>
                                <td class="stm-description"><?php echo esc_html($task->description); ?></td>
                                <td><?php echo esc_html($task->created_at); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="4" class="no-tasks">No completed tasks found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean(); // Return the buffered output
}

// Register the shortcode
add_shortcode('smart_tasks', 'stm_shortcode_display_tasks');
