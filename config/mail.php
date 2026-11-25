<?php
    use PHPMailer\PHPMailer\PHPMailer;
    require_once __DIR__ ."/../vendor/autoload.php";
    require_once __DIR__ . "/env.php";

    // Common SMTP configuration
    function createMailer(): PHPMailer{
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = getenv("MAIL_HOST");
        $mail->SMTPAuth = true;
        $mail->Username = getenv("MAIL_USERNAME");
        $mail->Password = getenv("MAIL_PASSWORD");
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom(
            getenv("MAIL_FROM"),
            "My To-Do Application"
        );

        return $mail;
    }

    // 1. Email verification OTP
    function sendOtpEmail(string $email, string $otp): bool{
        
        try{
            $mail = createMailer();
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = "Your Email Verification OTP";

            $mail->Body = "
                <h2>Email Verification</h2>
                <p>Your verification code is:</p>
                <h1>".htmlspecialchars($otp)."</p>
                <php>This code expires in 5 minutes.</p>
            ";

            $mail->AltBody = "Your verification code is $otp. It expires in 5 minutes.";
            $mail->send();
            return true;
        
        }catch(\Throwable $e){
            error_log("OTP Email sending failed: ". $e->getMessage());
            return false;
        }
    }


    // 2. Password reset email
    function sendPasswordResetEmail(string $email, string $resetLink): bool{
        try{
            $mail = createMailer();
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = "Reset Your Password";

            $safeLink = htmlspecialchars($resetLink, ENT_QUOTES, "UTF-8");

            $mail->Body = "
                <h2>Password Reset Request</h2>
                <p>We received a request to reset your password.</p>
                <p>Click below to create a new password.</p>
                <p>
                    <a href='$safeLink' 
                        style='background:#2563eb;
                            color:white;
                            padding: 12px 20px;
                            text-decoration: none;
                            border-radius: 5px;'
                    >
                        Reset Password
                    </a>
                </p>
                <p>This link expires in 15 minutes.</p>
                <p>If you did not request this, ignore this email.</p>
            ";
            $mail->AltBody = "Reset your password using this link: " . $safeLink ;
            $mail->send();
            return true;
        }catch(\Throwable $e){
            error_log("Password Reset Email Error". $e->getMessage());
            return false;
        }
    }

    // 3. Welcome email
    // Send a welcome message after successful registration and verification.
    function sendWelcomeEmail(string $email, string $username): bool{
        try{
            $mail = createMailer();
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = "Welcome to TaskFlow!";
            
            $safeName = htmlspecialchars($username, ENT_QUOTES, "UTF-8");

            $mail->Body = "
                <h2>Whelcome to TaskFlow!</h2>
                <p>Hellow $safeName,</p>
                <p>Your account has been successfully verified.</p>
                <p>You can now start managing your daily tasks.</p>
                <p>Thank you for joining us!</p>
            ";
            $mail->AltBody = "Welcome to TaskFlow, $username!";
            $mail->send();
            return true;
        }catch(\Throwable $e){
            error_log("Welcome Email Error". $e->getMessage());
            return false;
        }
    }

    // 4. Task reminder email
    // Remind users about pending tasks.
    function sendTaskReminderEmail(string $email, array $tasks): bool{
        if(empty($tasks)){
            return false;
        }
        try{
            $mail = createMailer();
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = "TaskFlow - Pending Task Reminder";
            $taskList = "";
            foreach($tasks as $task){
                $taskName = htmlspecialchars(
                    $task["task_name"], ENT_QUOTES, "UTF-8"
                );
                $preiority = htmlspecialchars(
                    $task["priority"], ENT_QUOTES, "UTF-8"
                );

                $taskList .= "
                    <li style='margin-bottom:10px;'>
                        <strong>$taskName</strong>
                        <span> - $preiority Priority</span>
                    </li>
                
                ";
            }
            $mail->Body = "
                <h2>Task Reminder</h2>
                <p>Hello!</p>
                <p>You have pending tasks waiting to be completed.</p>
                <ul>$taskList</ul>
                <p>Open TaskFlow and complete your tasks today!</p>
                <p>Thank you, <br>TaskFlow Team</p>
            ";
            $mail->AltBody = "You have " . count($tasks) . " pending tasks. Please log in to TaskFlow.";
            $mail->send();
            return true;
        }catch(\Throwable $e){
            error_log("Task Reminder Email Error: ". $e->getMessage());
            return false;
        }
    }


    // 5. Task completion notification
    // Send an email when a task is completed.


    // 6. Password change notification
    // Notify users when their password changes successfully.
