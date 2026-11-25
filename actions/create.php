<?php

require_once "../config/database.php";
require_once "../config/auth.php";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    verifyCsrfToken();
    $taskName = trim($_POST["task_name"] ?? "");
    $description = trim($_POST["description"] ??"");
    $priority = trim($_POST["priority"] ??"Medium");
    $userId = $_SESSION["user_id"];

    $errors = [];

    $allowedPriorities = ["Low", "Medium", "High"];
    if($taskName == ""){
        // die("Task name is required");
        $errors[] = "Task name is required.";
    }
    if (strlen($taskName) > 255) {
        $errors[] = "Task name is too long.";
    }
    if(!in_array($priority, $allowedPriorities, true)){
        // die("Invalid priority.");
        $errors[] = "Invalid priority.";
    }
    if (strlen($description) > 5000) {
        $errors[] = "Description is too long.";
    }

    // Validate category ID.
    $rawCategoryId = $_POST["category_id"] ?? "";
    if ($rawCategoryId === "") {
        $categoryId = null;

    }else{
        // Categories
        $categoryId = filter_input(
            INPUT_POST,
            "category_id",
            FILTER_VALIDATE_INT
        );
        if ($categoryId === false || $categoryId <= 0) {
            $errors[] = "Invalid category.";
        }
    }


    // Display validation errors.
    if (!empty($errors)) {
        http_response_code(422);
        foreach ($errors as $error) {
            echo htmlspecialchars($error) . "<br>";
        }
        exit;
    }


    // Verify category belongs to logged-in user.
    if ($categoryId !== null && $categoryId !== false) {
        $stmt = $pdo->prepare("
            SELECT id
            FROM categories
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$categoryId, $userId]);
        if (!$stmt->fetch()) {
            http_response_code(400);
            exit("Invalid category.");
        }
    }

    // Insert task with category.
    $sql = "INSERT INTO tasks (task_name, description, priority, user_id, category_id)
            VALUES (:task_name, :description, :priority, :user_id, :category_id)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":task_name"=> $taskName,
        ":description"=> $description,
        ":priority"=> $priority,
        ":user_id"=> $userId,
        ":category_id" => $categoryId
    ]);
    header("Location: ../index.php");
    exit;
}