
<?php

require_once __DIR__ . "/config/session.php";
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/config/csrf.php";
require_once __DIR__ . "/config/password_reset.php";

$message = "";

$token = $_GET["token"] ?? "";

$validTokenFormat =
    is_string($token) &&
    preg_match('/^[a-f0-9]{64}$/', $token);

if (!$validTokenFormat) {

    $message = "Invalid or expired reset link.";

} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {

    verifyCsrfToken();

    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";

    if (strlen($password) < 8) {

        $message = "Password must contain at least 8 characters.";

    } elseif ($password !== $confirmPassword) {

        $message = "Passwords do not match.";

    } else {

        try {

            $pdo->beginTransaction();

            $tokenHash = hashResetToken($token);

            $stmt = $pdo->prepare("
                SELECT id, user_id
                FROM password_resets
                WHERE token_hash = ?
                AND expires_at > UTC_TIMESTAMP()
                LIMIT 1
                FOR UPDATE
            ");

            $stmt->execute([$tokenHash]);

            $reset = $stmt->fetch();

            if (!$reset) {

                $pdo->rollBack();

                $message = "Invalid or expired reset link.";

            } else {

                $passwordHash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $stmt = $pdo->prepare("
                    UPDATE users
                    SET password = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    $passwordHash,
                    $reset["user_id"]
                ]);

                // Consume this token.
                $stmt = $pdo->prepare("
                    DELETE FROM password_resets
                    WHERE user_id = ?
                ");

                $stmt->execute([
                    $reset["user_id"]
                ]);

                $pdo->commit();

                header("Location: login.php?password_reset=1");
                exit;
            }

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log($e->getMessage());

            $message = "Unable to reset password. Please try again.";
        }
    }
}
?>
<h2>Create New Password</h2>

<form method="POST">

    <input
        type="hidden"
        name="csrf_token"
        value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>"
    >

    <input
        type="hidden"
        name="token"
        value="<?= htmlspecialchars($token) ?>"
    >

    <label>New Password</label>

    <input
        type="password"
        name="password"
        minlength="8"
        required
    >

    <label>Confirm Password</label>

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