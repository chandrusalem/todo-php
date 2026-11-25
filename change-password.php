<?php
    require_once __DIR__ ."/config/auth.php";
    require_once __DIR__ ."/config/database.php";
    require_once __DIR__ ."/config/csrf.php";

    $userId = (int) $_SESSION["user_id"];
    $message = "";

    if($_SERVER["REQUEST_METHOD"] == "POST") {
        verifyCsrfToken();

        $currentPassword = $_POST["current_password"] ?? "";
        $newPassword = $_POST["new_password"] ??"";
        $confirmPassword = $_POST["confirm_password"] ??"";

        $stmt = $pdo->prepare("
            SELECT password FROM users WHERE id = ?
        ");
        $stmt->execute([$userId]);

        $user = $stmt->fetch();


        if(!$user || !password_verify($currentPassword, $user["password"])) {
            $message = "Current password is incorrect";
        }elseif(strlen($newPassword) < 0){
            $message = "New password must contain at least 8 characters.";
        }elseif($newPassword != $confirmPassword) {
            $message = "New password do not match.";
        }else{
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                UPDATE users SET password = ? WHERE id = ?
            ");

            $stmt->execute([$newHash, $userId]);

            // Password changed.
            // A production implementation should also
            // invalidate existing sessions.
            session_regenerate_id(true);
            $message = "Password changed successfully.";
        }
    }

?>

<h2>Change Password</h2>

<form method="POST">

    <input
        type="hidden"
        name="csrf_token"
        value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>"
    >

    <label>Current Password</label>
    <input
        type="password"
        name="current_password"
        required
    >

    <label>New Password</label>
    <input
        type="password"
        name="new_password"
        minlength="8"
        required
    >

    <label>Confirm New Password</label>
    <input
        type="password"
        name="confirm_password"
        minlength="8"
        required
    >

    <button type="submit">
        Update Password
    </button>

</form>

<p><?= htmlspecialchars($message) ?></p>