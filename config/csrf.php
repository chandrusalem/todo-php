<?php
    require_once __DIR__ ."/session.php";

    if(empty($_SESSION["csrf_token"])){
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }

    function verifyCsrfToken(): void{
        $token = $_POST["csrf_token"] ?? "";

        if(!is_string($token) || !hash_equals($_SESSION["csrf_token"], $token)){
            http_response_code(403);
            exit("Invalid security token.");
        }
    }