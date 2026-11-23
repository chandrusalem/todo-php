<?php
    require_once "config/database.php";
    $id = filter_input(INPUT_GET,"id", FILTER_VALIDATE_INT);
    if(!$id || $id < 1) {
        die("Invalid task ID.");
    }
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = :id AND user_id = :user_id");
    $stmt->execute([
        ":id"=> $id,
        ":user_id" => $_SESSION["user_id"],
    ]);
    $task = $stmt->fetch();
    if(!$task) {
        die("Task not found.");
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Task</title>
</head>

<body>

    <h1>Edit Task</h1>
    <form action="actions/update.php" method="POST">
        <input
            type="hidden"
            name="id"
            value="<?= (int)$task["id"] ?>"
        >
        <input
            type="text"
            name="task_name"
            value="<?= htmlspecialchars($task["task_name"]) ?>"
            maxlength="255"
            required
        >
        <textarea name="description"><?= htmlspecialchars($task["description"]) ?></textarea>
        <select name="priority" id="">
            <?php foreach(["Low", "Medium", "High"] as $priority): ?>
                <option value="<?= $priority ?>"
                    <?= $task["priority"] === $priority ? 'selected' : ""  ?>
                >
                    <?= $priority ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Update Task</button>
    </form>

</body>
</html>
