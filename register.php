<?php

    require_once "config/database.php";
    $error = "";

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $name = trim($_POST["name"] ?? "");
        $email = strtolower(trim($_POST["email"] ??""));
        $password = $_POST["password"] ??"";

        if($name === "" || $email === "" || $password === ""){
            $error = "All fields are required";
        }elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $error = "Enter a valid email address.";
        }else{
            $check = $pdo->prepare("
                SELECT id FROM users WHERE email = :email
            ");
            $check->execute([":email" => $email]);

            if($check->fetch()){
                $error = "Email already registerd.";
            }else{
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
                
                header("Location: login.php");
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
    <?php if($error !== ""): ?>
        <p style="color:red">
            <?= htmlspecialchars($error) ?>
        </p>
    <?php endif; ?>

    <form action="" method="POST">
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