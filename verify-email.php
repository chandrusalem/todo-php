<?php
    require_once __DIR__ . "/config/session.php";    
    require_once __DIR__ . "/config/database.php";
    require_once __DIR__ . "/config/csrf.php";

    if(empty($_SESSION["pending_user_id"])){
        header("Location: register.php");
        exit;
    }
    $userId = (int)$_SESSION["pending_user_id"];

    $message = "";
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        verifyCsrfToken();
        $otp = trim($_POST["otp"]??"");
        if(!preg_match("/^[0-9]{6}$/", $otp)){
            $message = "Enter a valid six-digit OTP.";
        }else{

            // OTP Verifcation logic
            try{
                $pdo->beginTransaction();

                //Lock OTP record while checking it.
                $stmt = $pdo->prepare("
                    SELECT *, (expires_at <= UTC_TIMESTAMP()) AS is_expired FROM email_otps WHERE user_id = ? FOR UPDATE
                ");

                $stmt->execute([$userId]);
                $otpRecord = $stmt->fetch();

                if(!$otpRecord){
                    $message = "OTP not found. Request a new OTP.";
                // }elseif(strtotime($otpRecord["expires_at"] . " UTC") <= time()){
                }elseif((int)$otpRecord["is_expired"] === 1){
                    $stmt = $pdo->prepare("
                        DELETE FROM email_otps WHERE user_id = ?
                    ");
                    $stmt->execute([$userId]);
                    $message = "OTP expire. Request a new OTP.";
                }elseif((int)$otpRecord["attempts"] >= 3){
                    $message = "Maximum attempts reached. Request a new OTP.";
                }elseif(!password_verify($otp, $otpRecord["otp_hash"])){
                    $newAttempts = (int)$otpRecord["attempts"] +1 ;
                    if($newAttempts >= 3){
                        $stmt = $pdo->prepare("
                            DELETE FROM email_otps WHERE user_id = ?
                        ");
                        $stmt->execute([$userId]);
                        $message = "Maximum attempts reached. Request a new OTP.";
                    }else{
                        $stmt = $pdo->prepare("
                            UPDATE email_otps SET attempts = ? WHERE user_id = ?
                        ");
                        $stmt->execute([$newAttempts, $userId]);
                        $remaining = 3 - $newAttempts;
                        $message = "Incorrect OTP. Attempts remaining: $remaining";
                    }
                }else{
                    // Correct OTP: verify account.
                    $stmt = $pdo->prepare("
                        UPDATE users SET is_verified = 1, email_verified_at = UTC_TIMESTAMP() WHERE id = ?
                    ");

                    $stmt->execute([$userId]);

                    //OTP must not be reused.
                    $stmt = $pdo->prepare("
                        DELETE FROM email_otps WHERE user_id = ?
                    ");
                    $stmt->execute([$userId]);
                    $pdo->commit();

                    unset($_SESSION["pending_user_id"]);
                    header("Location: login.php?verified=1");
                    exit;
                }
                $pdo->commit();
            } catch(PDOException $e) {
                if($pdo->inTransaction()){
                    $pdo->rollBack();
                }
                error_log($e->getMessage());
                $message = "Verification failed. Please try again.";
            }
        }
    }

?>
<h2>Verify Your Email</h2>
<form action="" method="post">
    <input type="hidden" name="csrf_token"
    value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">
    <label for="">Enter OTP</label>
    <input type="text" name="otp" placeholder="Enter 6-digit OTP" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" required>
    <button type="submit">Verify Email</button>
</form>
<p><?= htmlspecialchars($message)?></p>