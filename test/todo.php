<?php
    $tasks = [
        [
            "id" => 1,
            "name" => "Learn PHP",
            "status" => "Completed"
        ],
        [
            "id" => 2,
            "name" => "Learn MySQL",
            "status" => "Pending"
        ],
    ];

    $error = "";
    if($_SERVER["REQUEST_METHOD"] === "POST" ){
        $taskName = trim($_POST["task"] ?? "");
        if($taskName === ""){
            $error = "Please enter a task.";
        }else{
            $tasks[] = [
                "id" => count($tasks) +1,
                "name" => $taskName,
                "status" => "Pending"
            ];
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        body{
            font-family: Arial, sans-serif;
            max-width: 600px
            margin: 40px auto;
            padding: 20px;
            background: #f4f4f4;
        }
        .container{
            background: white;
            padding: 25px;
            border-radius: 10px;
        }
        input{
            padding: 12px;
            width: 65%;
        }
        button{
            padding: 12px;
            cursor: pointer;
        }
        .task{
            padding: 12px;
            margin-top: 10px;
            background: #eeeeee;
        }
        .error{
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>My To-Do List</h1>
        <form method="POST" action="">
            <input type="text" name="task" id="" placeholder="Enter a new task">
            <button type="submit">Add Task</button>
        </form>
        <?php if ($error !== ""): ?>
            <p class="error">
                <?php echo htmlspecialchars($error) ?>
            </p>
        <?php endif; ?>
        <h2>My Task</h2>
        <?php foreach($tasks as $task): ?>
            <div class="task">
                <strong>
                    <?php echo htmlspecialchars($task["name"]) ?>
                </strong>
                <span>
                    - <?php echo htmlspecialchars($task["status"]) ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>