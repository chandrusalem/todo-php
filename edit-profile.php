<?php
    require_once __DIR__ ."/config/auth.php";
    require_once __DIR__ ."/config/database.php";
    require_once __DIR__ ."/config/csrf.php";

    $userId = (int) $_SESSION["user_id"];

    $stmt = $pdo->prepare("
        SELECT name, email FROM users WHERE id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if(!$user){
        http_response_code(404);
        exit("User not found.");
    }

    $message = "";

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        verifyCsrfToken();
        $name = trim($_POST["name"] ?? "");
        if($name == "" || mb_strlen($name) > 100){
            $message = "Enter a valid name";
        }else{
            $stmt = $pdo->prepare("
                UPDATE users SET name = ? WHERE id = ?
            ");

            $stmt->execute([$name, $userId]);

            $_SESSION["user_name"] = $name;
            exit;
        }
    }
?>

<h2>Edit Profile</h2>
<form action="" method="post">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">
    <label for="">Name</label>
    <input type="text" name="name" value="<?= htmlspecialchars($user["name"]) ?>" maxlength="100" required>

    <label for="">Email</label>
    <input type="email" name="" id="" value="<?= htmlspecialchars($user["email"]) ?>" readonly >

    <button type="submit">Save Changes</button>
</form>

<p><?= htmlspecialchars($message) ?></p>