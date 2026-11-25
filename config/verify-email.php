<?php
    require_once "./session.php";
    require_once "./database.php";
    require_once "./csrf.php";

    $message = "";
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        verifyCsrfToken();
        $otp = trim($_POST["otp"]);
        if(!preg_match("/^[0-9]{6}$/", $otp)){
            $message = "Enter a valid six-digit OTP.";
        }else{

            // OTP Verifcation logic
        }
    }

?>
<h2>Verify Your Email</h2>
<form action="" method="post">
    <input type="hidden" name="csrf_token"
    value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">
    <label for="">Enter OTP</label>
    <input type="text" name="otp" maxlength="numeric" pattern="[0-9]{6}" required>
    <button type="submit">Verify Email</button>
</form>
<p><?= htmlspecialchars($message)?></p>