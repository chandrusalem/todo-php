<?php
    require_once __DIR__ ."/database.php";
    require_once __DIR__ ."/otp.php";
    require_once __DIR__ ."/mail.php";

    function createUserOtp(int $userId): string{
        global $pdo;
        $otp = generateOtp();
        $otpHash = password_hash($otp, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO email_otps
            (user_id, otp_hash, expires_at, attempts, resend_count, resend_window_started_at)
            VALUES (?, ?, DATE_ADD(UTC_TIMESTAMP(), INTERVAL 5 MINUTE), 0, 0, UTC_TIMESTAMP())
            ON DUPLICATE KEY UPDATE
                otp_hash = VALUES(otp_hash),
                expires_at = DATE_ADD(UTC_TIMESTAMP(), INTERVAL 5 MINUTE),
                attempts = 0,
                resend_count = 0,
                resend_window_started_at = UTC_TIMESTAMP(),
                created_at = CURRENT_TIMESTAMP
        ");

        $stmt->execute([
            $userId,
            $otpHash
        ]);
        return $otp;
    }

    function sendUserOtp(int $userId, string $email): bool{
        $otp = createUserOtp($userId);
        return sendOtpEmail($email, $otp);
    }