<?php

require_once "../config/database.php";
require_once "../config/auth.php";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $taskName = trim($_POST["task_name"] ?? "");
    $discription = trim($_POST["description"] ??"");
    $priority = trim($_POST["priority"] ??"Medium");
    $userId = $_SESSION["user_id"];

    $allowedPriorities = ["Low", "Medium", "High"];
    if($taskName == ""){
        die("Task name is required");
    }
    if(!in_array($priority, $allowedPriorities, true)){
        die("Invalid priority.");
    }
    $sql = "INSERT INTO tasks (task_name, description, priority, user_id)
            VALUES (:task_name, :description, :priority, :user_id)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":task_name"=> $taskName,
        ":description"=> $discription,
        ":priority"=> $priority,
        ":user_id"=> $userId
    ]);
    header("Location: ../index.php");
    exit;
}