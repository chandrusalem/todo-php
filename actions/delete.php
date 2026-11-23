<?php
    require_once "../config/database.php";
    $id = filter_input(INPUT_GET,"id", FILTER_VALIDATE_INT);

    if(!$id || $id < 1) {
        die("Invalid task Id.");
    }
    $stmt = $pdo->prepare("
        DELETE FROM tasks WHERE id=:id AND user_id = :user_id
    ");
    $stmt->execute([
        ":id" => $id,
        ":user_id" => $_SESSION["user_id"],
    ]);
    header("Location: ../index.php");
    exit;