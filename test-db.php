<?php

    require_once __DIR__ ."/config/database.php";
    $stmt = $pdo->query("SELECT DATABASE() As db_name");
    $result = $stmt->fetch();
    echo "Database connected successfully! <br>";
    echo "Database name: " . htmlspecialchars($result["db_name"]);