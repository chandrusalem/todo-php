<?php
    require_once "../config/auth.php";
    require_once "../config/database.php";
    require_once "../config/csrf.php";

    if($_SERVER["REQUEST_METHOD"] !== "POST"){
        http_response_code(405);
        exit("Mothod not allowed.");
    }

    verifyCsrfToken();
    $id = filter_input(INPUT_POST,"id", FILTER_VALIDATE_INT);

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