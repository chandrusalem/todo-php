<?php
    // session_start();
    require_once __DIR__ ."/config/session.php";
    require_once __DIR__ ."/config/database.php";
    require_once __DIR__ ."/config/csrf.php";

    if(isset($_SESSION["user_id"])){
        header("Location: index.php");
        exit;
    }
    $error = "";
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        verifyCsrfToken();
        $email = strtolower(trim($_POST["email"]));
        $password = $_POST["password"];

        if($email == ""|| $password == ""){
            $error = "All fields are required.";
        }elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $error = "Please enter a valid email address.";
        }else{
            $stmt = $pdo->prepare("
                SELECT id, name, email, password, is_verified
                FROM users
                WHERE email = :email LIMIT 1
            ");
            $stmt->execute(
                [
                    ":email"=> $email
                ]
            );

            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if($user && password_verify($password, $user["password"])){

                // Check email verification
                if((int)$user["is_verified"] !== 1){
                    $_SESSION["pending_user_id"] = (int)$user["id"];
                    header("Location: verify-email.php");
                    exit;
                }

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
        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
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
    <a href="forgot-password.php">
        Forgot Password?
    </a>
</body>
</html>