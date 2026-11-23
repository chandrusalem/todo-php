<?php
    if(session_start() === PHP_SESSION_ACTIVE){
        session_start();
    }
    if(!isset($_SESSION["user_id"])){
        header("Location: login.php");
        exit;
    }