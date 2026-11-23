<?php
    require_once "../config/database.php";
    $id = filter_input(INPUT_GET,"id", FILTER_VALIDATE_INT);

    if(!$id || $id < 1) {
        die("Invalid task ID");
    }

    $stmt = $pdo->prepare("
        SELECT status FROM tasks WHERE id=:id AND user_id = :user_id
    ");
    $stmt->execute([
        ":id"=> $id,
        ":user_id" => $_SESSION["user_id"],
    ]);
    $task = $stmt->fetch();
    if(!$task) {
        die("Task not found.");
    }
    $newStatus = $task["status"] === "Completed" ? "Pending" : "Completed";
    $update = $pdo->prepare("
        UPDATE tasks SET status = :status WHERE id = :id AND user_id = :user_id
    ");
    $update->execute(
        [
            ":status"=> $newStatus,
            ":id"=> $id,
            ":user_id" => $_SESSION["user_id"],
        ]
    );

    header("Location: ../index.php");
