<?php

    require_once __DIR__ ."/config/session.php";
    require_once __DIR__ ."/config/database.php";
    require_once __DIR__ ."/config/csrf.php";
    require_once __DIR__ ."/config/otp_service.php";
    $message = "";

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        verifyCsrfToken();
        $name = trim($_POST["name"] ?? "");
        $email = strtolower(trim($_POST["email"] ??""));
        $password = $_POST["password"] ??"";

        if($name === "" || $email === "" || $password === "") {
            $message = "All fields are required";
        }elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $message = "Please enter valid registration details.";
        }elseif(strlen($password) < 8 ){
            $message = "Password must contain at least 8 characters.";
        }else{
            $check = $pdo->prepare("
                SELECT id FROM users WHERE email = :email
            ");
            $check->execute([":email" => $email]);

            if($check->fetch()){
                $message = "Unable to register with these details.";
            }else{
                try{
                    $hashedPassword = password_hash(
                        $password, PASSWORD_DEFAULT
                    );
                    $sql = "INSERT INTO users
                        (name, email, password) VALUES (:name, :email, :password)
                    ";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ":name" => $name,
                        ":email"=> $email,
                        ":password"=> $hashedPassword
                        ]);
                    $userId = (int) $pdo->lastInsertId();
                    $_SESSION["pending_user_id"] = $userId;
                    if(sendUserOtp($userId, $email)){
                        header("Location: verify-email.php");
                        exit;
                    }else{
                        // Keep the unverified account.
                        // The user can request another OTP.
                        $message = "Account created. Email deliver failed. Please request another OTP.";

                    }
                }catch(PDOException $e){
                    error_log($e->getMessage());
                    // var_dump($e->getMessage());
                    $message = "Registeration failed. Please try again.";
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
    <h1>Create Account</h1>
    <?php if($message !== ""): ?>
        <p style="color:red">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <form action="" method="POST">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <input type="text" name="name" placeholder="Your name" maxlength="100" required>
        <br><br>
        <input type="email" name="email" placeholder="Enter Email address" maxlength="190" required>
        <br><br>
        <input type="password" name="password" placeholder="Password (minimum 8 characters)" minlength="8" required>
        <br><br>
        <button type="submit">Register</button>
    </form>
    <p>
        Already registered?
        <a href="login.php">Login</a>
    </p>
</body>
</html>