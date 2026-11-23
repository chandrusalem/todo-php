<?php
    session_start();
    require_once "config/database.php";

    if(isset($_SESSION["user_id"])){
        header("Location: index.php");
        exit;
    }
    $error = "";
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $email = strtolower(trim($_POST["email"]));
        $password = $_POST["password"];

        $stmt = $pdo->prepare("
            SELECT id, name, email, password
            FROM users
            WHERE email = :email
        ");
        $stmt->execute(
            [
                ":email"=> $email
            ]
        );

        $user = $stmt->fetch();
        if($user && password_verify($password, $user["password"])){

            // Prevent session fixation
            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["user_email"] = $user["email"];

            header("Location: index.php");
            exit;
        }else{
            $error = "Invalid email or password";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>
    <?php if($error !== ""): ?>
        <p style="color:red">
            <?= htmlspecialchars($error) ?>
        </p>
    <?php endif; ?>

    <form action="" method="POST">
        <input type="email" name="email" placeholder="Email address" required>
        <br><br>
        <input type="password" name="password" placeholder="Password" required>
        <br><br>
        <button type="submit">Login</button>
    </form>
    <p>
        Don't have an account?
        <a href="register.php">Register</a>
    </p>
</body>
</html>