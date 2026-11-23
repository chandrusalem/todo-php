<?php
    session_start();
    if(!isset($_SESSION["tasks"])){
        $_SESSION["tasks"] = [
            [
                "id" => 1,
                "name" => "Learn PHP",
                "priority" => "High",
                "status" => "Pending"
            ],
            [
                "id" => 2,
                "name" => "Learn MySQL",
                "priority" => "Medium",
                "status" => "Completed"
            ]
        ];
        $_SESSION["next_id"] = 3;
    }
    $tasks = &$_SESSION["tasks"];

    $message = "";
    $error = "";
    $editingTask = null;

    // Handle form submissions
    if($_SERVER["REQUEST_METHOD"] === "POST"){
        $action = $_POST["action"] ?? "";
        
        // Create Task
        if($action === "add"){
            $name = trim($_POST["task"] ?? "");
            $priority = trim($_POST["priority"] ?? "Medium");

            if($name === ""){
                $error = "Please enter a task name.";
            }elseif(!in_array($priority, ["Low", "Medium", "High"], true)){
                $error = "Invalid proiority.";
            }else{
                $tasks[] = [
                    "id"=> $_SESSION["next_id"],
                    "name" => $name,
                    "priority" => $priority,
                    "status" => "Pending"
                ];
                $_SESSION["next_id"]++;
                $message = "Task added successfully!";
            }
        }

        // Find task editing
        elseif($action === "edit"){
            $id = (int)($_POST["id"] ?? 0);
            foreach($tasks as $task){
                if($task["id"] === $id){
                    $editingTask = $task;
                    break;
                }
            }
            
            if($editingTask === null){
                $error = "Task not found.";
            }
        }

        // Update Task
        elseif($action === "update"){
            $id = (int)($_POST["id"] ?? 0);
            $name = trim($_POST["task"] ?? "");
            $priority = trim($_POST["priority"] ?? "Medium");

            if($name === ""){
                $error = "Task name cannot be empty.";
            }elseif(!in_array($priority, ["Low", "Medium", "High"], true)){
                $error = "Invalid priority.";
            }else{
                foreach($tasks as &$task){
                    if($task["id"] === $id){
                        $task["name"] = $name;
                        $task["priority"] = $priority;

                        $message = "Task update successfully!";
                        break;
                    }
                }
                unset($task);
            }
        }

        // Delete Task
        elseif($action === "delete"){
            $id = (int)($_POST["id"] ?? 0);
            $found = false;
            foreach($tasks as $index => $task){
                if($task["id"] === $id){
                    unset($tasks[$index]);
                    $found = true;
                    break;
                }
            }
            if($found){
                $tasks = array_values($tasks);
                $message = "Task deleted successfully!";
            }else{
                $error = "Task not found.";
            }
        }

        // Toggle task status
        elseif($action === "toggle"){
            $id = (int)($_POST["id"] ?? 0);
            foreach($tasks as &$task){
                if($task["id"] === $id){
                    $task["status"] = $task["status"] === "Completed" ? "Pending" : "Completed";
                    $message = "Task status updated!";
                    break;
                }
            }
            unset($task);
        }

    }


    //Calculation task counts
    $totalTasks = count($tasks);
    $completedTasks = 0;
    foreach($tasks as $task){
        if($task["status"] === "Completed"){
            $completedTasks++;
        }
    }

    $pendingTasks = $totalTasks - $completedTasks;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP To-Do Appliction</title>
    <style>
        *{
            box-sizing: border-box;
        }
        body{
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            padding: 30px 15px;
        }
        .container{
            max-width: 750px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
        }
        h1{
            text-align: center;
        }
        .summary{
            display: flex;
            justify-content: space-around;
            gap: 10px;
            margin: 25px 0;
            text-align: center;
        }
        .summary div{
            background: #f0f2f5;
            padding: 15px;
            border-radius: 8px;
            flex: 1;
        }
        .task-form,
        .edit-form{
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        input, select, button{
            padding: 11px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        input{
            flex: 1;
            min-width: 150px;
        }
        button{
            cursor: pointer;
            border: none;
        }
        .add-btn{
            background: #2563eb;
            color: white;
        }
        .task{
            background: #f7f8fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 12px;
        }
        .task-info{
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 12px;
        }
        .task-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .task-actions form {
            margin: 0;
        }

        .task-actions button {
            min-width: 90px;
        }
        .edit-btn{
            color: white;
            background: #f59e0b;
        }
        .delete-btn{
            background: #dc2626;
            color: white;
        }
        .delete-btn {
            background: #dc2626;
            color: white;
        }

        .status-btn {
            background: #16a34a;
            color: white;
        }

        .message {
            color: green;
            margin-bottom: 15px;
        }

        .error {
            color: red;
            margin-bottom: 15px;
        }

        .completed {
            text-decoration: line-through;
            color: gray;
        }

        @media (max-width: 500px) {

            .summary {
                flex-direction: column;
            }

            .container {
                padding: 15px;
            }

        }
    </style>
</head>
<body>
    <div class="container">
        <h1>To-Do Appliction</h1>
        <!-- TASK SUMMARY -->
        <div class="summary">
            <div>
                <h2><?php echo $totalTasks; ?></h2>
                <p>Total</p>
            </div>
            <div>
                <h2><?php echo $completedTasks; ?></h2>
                <p>Completed</p>
            </div>
            <div>
                <h2><?php echo $pendingTasks; ?></h2>
                <p>Pending</p>
            </div>
        </div>

        <!-- MESSAGES -->
        <?php if($message !== ""): ?>
            <p class="message">
                <?php echo htmlspecialchars($message); ?>
            </p>            
        <?php endif; ?>
        <?php if($error !== ""): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <!-- ADD TASK FORM -->
        <h2>Add New Task</h2>
        <form action="" method="POST" class="task-form">
            <input type="hidden" name="action" value="add">
            <input type="text" name="task" id="" placeholder="Enter task name" >
            <select name="priority" id="">
                <option value="Low">Low</option>
                <option value="Medium">Medium</option>
                <option value="High">High</option>
            </select>
            <button type="submit" class="add-btn">Add Task</button>
        </form>

        <!-- EDIT TASK FORM -->
         <!-- <?php print_r($editingTask) ?> -->
        <?php if($editingTask !== null): ?>
        <form action="" method="POST" class="edit-form">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($editingTask["id"]); ?>">
            <input type="text" name="task" value="<?php echo htmlspecialchars($editingTask["name"]); ?>" required>
            <select name="priority">
                <?php foreach(["Low", "Medium", "High"] as $level): ?>
                    <option value="<?php echo $level; ?>"
                    <?php echo $editingTask["priority"] === $level ? "selected" : ""; ?>
                    >
                        <?php echo $level; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button class="add-btn" type="submit">
                Update Task
            </button>
        </form>
        <?php endif; ?>

        <!-- TASK LIST -->
        <h2>My Tasks</h2>
        <?php if(empty($tasks)): ?>
            <p>No tasks available. Add your first task!</p>
        <?php else: ?>
            <?php foreach($tasks as $task): ?>
                <div class="task">
                    <div class="task-info">
                        <h3 class="<?php 
                            echo $task["status"] === "Completed" ? "completed" : "";
                        ?>">
                            <?php echo htmlspecialchars($task["name"]); ?>
                        </h3>
                        <p>
                            Priority: 
                            <?php echo htmlspecialchars($task["priority"]); ?>
                        </p>
                        <p>
                            Status: 
                            <?php echo htmlspecialchars($task["status"]); ?>
                        </p>
                    </div>

                    <div class="task-actions">
                        <!-- COMPLETED / PENDING -->
                        <form action="" method="POST">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?php echo $task["id"]; ?>">
                            <button class="status-btn" type="submit">
                                <?php echo $task["status"] === "Completed" ? "Mark Pending" : "Completed" ?>
                            </button>
                        </form>

                        <!-- Edit -->
                        <form action="" method="POST">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?php echo $task["id"]; ?>">
                            <button class="edit-btn" type="submit">Edit</button>
                        </form>

                        <!-- DELETE -->
                        <form action="" method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this task?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $task["id"]; ?>">
                            <button class="delete-btn" type="submit">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
    </div>
</body>
</html>