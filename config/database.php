<?php
    
    $host = "localhost";
    $port = 3307;
    $dbname = "todo_db";
    $username = "root";
    $password = ""; 

    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

    $option = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    try{
        $pdo = new PDO($dsn, $username, $password, $option);
    }catch(PDOException $e){
        var_dump($e->errorInfo);
        error_log($e->getMessage());
        die("Database connection failed.Check configuration.");
    }