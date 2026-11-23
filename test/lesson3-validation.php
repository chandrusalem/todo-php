<?php
session_start();

$_SESSION["username"] = "Chandru";
echo $_SESSION["username"];

    $task = "";
    $success = "";
    $error = "";

    if($_SERVER["REQUEST_METHOD"] === "POST"){
        $task = trim($_POST['task'] ?? "");
        if($task === ""){
            $error = "Please enter a task.";
        }else{
            $success = "Task added successfully!";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h2>Add New Task</h2>

    <form method="POST">

        <input
            type="text"
            name="task"
            placeholder="Enter your task"
            value="<?php echo htmlspecialchars($task); ?>"
        >

        <button type="submit">Add Task</button>

    </form>

    <?php if($error !== ""): ?>
        <p style="color:red;">
            <?php echo htmlspecialchars($error); ?>
        </p>
    <?php endif; ?>
    <?php if($success !== ""): ?>
        <p style="color:green;">
            <?php echo htmlspecialchars($success); ?>
        </p>
    <?php endif; ?>
</body>
</html>