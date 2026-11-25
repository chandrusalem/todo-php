<?php
    require_once "../config/database.php";
    require_once "../config/auth.php";
    if($_SERVER["REQUEST_METHOD"] !== "POST"){
        header("Location: ../index.php");
        exit;
    }

    $id = filter_input(INPUT_POST,"id", FILTER_VALIDATE_INT);
    $taskName = trim($_POST["task_name"] ?? "");
    $description = trim($_POST["description"] ??"");
    $priority = $_POST["priority"] ??"";

    $allowedPriorities = ["Low","Medium","High"];

    if(!$id || $id < 1 || $taskName === "" || !in_array($priority, $allowedPriorities, true)){
        die("Invalid task information.");
    }

    $sql = "UPDATE tasks 
            SET task_name = :task_name,
            description = :description,
            priority = :priority
            WHERE id= :id AND user_id = :user_id
    ";  
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":task_name"=> $taskName,
        ":description"=> $description,
        ":priority"=> $priority,
        ":id" => $id,
        ":user_id" => $_SESSION["user_id"],
    ]);

    header("Location: ../index.php");
    exit;