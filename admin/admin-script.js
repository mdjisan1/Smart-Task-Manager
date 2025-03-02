jQuery(document).ready(function($){
    // Handle form submission for adding a task
    $('#stm-add-task').on('submit', function(e){
        //Prevents the default form submission behavior (which would reload the page) so we can handle the form submission using AJAX.
        e.preventDefault();
        
        var title = $('input[name="title"]').val().trim();
        var description = $('textarea[name="description"]').val().trim();
        var nonce = stm_data.nonce; // Use the localized nonce

        // Validate title and description
        if (title === '' || description === '') {
            alert('Please fill in both title and description.');
            return;
        }

        $.post(ajaxurl, {
            action: 'stm_add_task',
            title: title,
            description: description,
            nonce: nonce
        }, function(response){
            if (response.success) {
                var highlightStyle = response.data.highlight ? `style="${response.data.highlight}"` : '';
                $('table').append(`
                    <tr data-task-id="${response.data.id}" ${highlightStyle}>
                        <td>${response.data.id}</td>
                        <td>${response.data.title}</td>
                        <td class="task-status">pending</td>
                        <td>${response.data.created_at}</td>
                        <td><button class="mark-completed" data-id="${response.data.id}">Mark as Completed</button></td>
                    </tr>
                `);
                // Clear the form fields after adding a task
                $('input[name="title"]').val('');
                $('textarea[name="description"]').val('');
            } else {
                alert('Error adding task: ' + response.data.message);
            }
        });
    });

    // Handle marking a task as completed
    $(document).on('click', '.mark-completed', function(){
        var button = $(this);
        var taskId = button.data('id');

        $.post(ajaxurl, {
            action: 'stm_mark_completed',
            task_id: taskId,
            nonce: stm_data.nonce // Use the localized nonce
        }, function(response){
            if (response.success) {
                button.closest('tr').find('.task-status').text('completed'); // Update UI
                button.replaceWith('<span class="completed-text">✔ Marked as Completed</span>'); // Persist change
            } else {
                alert('Error marking task as completed: ' + response.data.message);
            }
        });
    });
});
