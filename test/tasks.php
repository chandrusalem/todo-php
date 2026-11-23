
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

    [
        "id" => 3,
        "name" => "Practice JavaScript",
        "status" => "Pending"
    ],

    [
        "id" => 4,
        "name" => "Build To-Do Application",
        "status" => "In Progress"
    ]

];

function getStatusLabel($status) {

    if ($status === "Completed") {
        return "Task completed";
    } elseif ($status === "In Progress") {
        return "Task in progress";
    } else {
        return "Task pending";
    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Task List</title>
</head>

<body>

    <h1>My To-Do List</h1>

    <?php foreach ($tasks as $task): ?>

        <div>

            <h3>
                <?php echo htmlspecialchars($task["name"]); ?>
            </h3>

            <p>
                <?php echo htmlspecialchars(
                    getStatusLabel($task["status"])
                ); ?>
            </p>

        </div>

        <hr>

    <?php endforeach; ?>

</body>

</html>
