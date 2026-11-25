
document.addEventListener("DOMContentLoaded", function () {

    const taskInput = document.querySelector(
        'input[name="task_name"]'
    );

    const taskForm = document.querySelector(
        'form[action="actions/create.php"]'
    );

    if (!taskInput || !taskForm) {
        return;
    }

    taskForm.addEventListener("submit", function (event) {

        const taskName = taskInput.value.trim();

        if (taskName.length === 0) {

            event.preventDefault();

            alert("Please enter a task name.");

            taskInput.focus();

        }

    });

});