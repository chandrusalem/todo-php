
<?php

require_once __DIR__ . "/config/session.php";
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/config/csrf.php";
require_once __DIR__ . "/config/password_reset.php";
require_once __DIR__ . "/config/mail.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verifyCsrfToken();
    $email = strtolower(trim($_POST["email"] ?? ""));
    $message = "If an eligible account exists, a password reset link will be sent.";

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            // Find verified user.
            $stmt = $pdo->prepare("
                SELECT id, email
                FROM users
                WHERE email = ?
                AND is_verified = 1
                LIMIT 1
            ");

            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // Generate secure token.
                $token = generateResetToken();

                $tokenHash = hashResetToken($token);

                // Expiration after 15 minutes.
                $expiresAt = gmdate(
                    "Y-m-d H:i:s",
                    time() + 900
                );

                $pdo->beginTransaction();

                // Remove previous reset links.
                $stmt = $pdo->prepare("
                    DELETE FROM password_resets
                    WHERE user_id = ?
                ");

                $stmt->execute([$user["id"]]);

                // Save only the hashed token.
                $stmt = $pdo->prepare("
                    INSERT INTO password_resets
                    (user_id, token_hash, expires_at)
                    VALUES (?, ?, ?)
                ");

                $stmt->execute([
                    $user["id"],
                    $tokenHash,
                    $expiresAt
                ]);

                $pdo->commit();

                // Get application URL.
                $baseUrl = rtrim(
                    $_ENV["APP_BASE_URL"] ?? "",
                    "/"
                );
                if ($baseUrl === "") {
                    throw new RuntimeException(
                        "APP_BASE_URL is missing."
                    );
                }

                // Generate reset link.
                $resetLink = $baseUrl .
                    "/reset-password.php?token=" .
                    urlencode($token);

                // Send reset email using config/mail.php.
                // Send password reset email.
                $sent = sendPasswordResetEmail(
                    $user["email"],
                    $resetLink
                );

                // Remove unused token if email sending fails.
                if (!$sent) {
                    $stmt = $pdo->prepare("
                        DELETE FROM password_resets WHERE user_id = ? AND token_hash = ?
                    ");
                    $stmt->execute([$user["id"], $tokenHash]);
                    error_log(
                        "Password reset email could not be delivered."
                    );
                }

                // $mail = new PHPMailer\PHPMailer\PHPMailer(true);

                // Configure SMTP as in Lesson 1.
                // Then send the reset link to $user["email"].
                //
                // Use a mail function that accepts the
                // reset link, rather than sending an OTP.

                // $mail->isSMTP();
                // $mail->Host = getenv("MAIL_HOST");
                // $mail->SMTPAuth = true;
                // $mail->Username = getenv("MAIL_USERNAME");
                // $mail->Password = getenv("MAIL_PASSWORD");
                // $mail->SMTPSecure =
                //     PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                // $mail->Port = 587;

                // $mail->setFrom(
                //     getenv("MAIL_FROM"),
                //     "My To-Do Application"
                // );

                // $mail->addAddress($user["email"]);
                // $mail->isHTML(true);

                // $mail->Subject = "Reset Your Password";

                // $safeLink = htmlspecialchars(
                //     $resetLink,
                //     ENT_QUOTES,
                //     "UTF-8"
                // );

                // $mail->Body = "
                //     <h2>Password Reset</h2>
                //     <p>Click the link below to reset your password.</p>
                //     <a href='$safeLink'>Reset Password</a>
                //     <p>This link expires in 15 minutes.</p>
                // ";

                // $mail->AltBody =
                //     "Reset your password using: " . $resetLink;

                // $mail->send();
            }

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log("Forgot Password: ", $e->getMessage());
        }
    }
}
?>
<h2>Forgot Password</h2>

<form method="POST">

    <input
        type="hidden"
        name="csrf_token"
        value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>"
    >

    <label>Email Address</label>

    <input
        type="email"
        name="email"
        placeholder="Enter your registered email"
        required
    >

    <button type="submit">
        Send Reset Link
    </button>

</form>

<p><?= htmlspecialchars($message) ?></p>
<a href="login.php">Back to Login</a>