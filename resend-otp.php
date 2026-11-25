
<?php

require_once __DIR__ . "/config/session.php";
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/config/csrf.php";
require_once __DIR__ . "/config/otp_service.php";

if (empty($_SESSION["pending_user_id"])) {
    header("Location: register.php");
    exit;
}

$userId = (int) $_SESSION["pending_user_id"];

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    verifyCsrfToken();

    try {

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            SELECT *
            FROM email_otps
            WHERE user_id = ?
            FOR UPDATE
        ");

        $stmt->execute([$userId]);

        $record = $stmt->fetch();

        if (!$record) {

            $message = "Request a new registration first.";

        } else {

            $now = new DateTimeImmutable("now", new DateTimeZone("UTC"));
            $window = new DateTimeImmutable(
                $record["resend_window_started_at"],
                new DateTimeZone("UTC")
            );

            $seconds = $now->getTimestamp() - $window->getTimestamp();

            $resendCount = (int) $record["resend_count"];

            if ($seconds < 3600 && $resendCount >= 5) {

                $message = "Hourly resend limit reached.";

            } elseif ($seconds < 60) {

                $message = "Please wait before requesting another OTP.";

            } else {

                if ($seconds >= 3600) {
                    $resendCount = 0;
                }

                $stmt = $pdo->prepare("
                    UPDATE email_otps
                    SET resend_count = ?,
                        resend_window_started_at =
                            IF(? = 0, UTC_TIMESTAMP(), resend_window_started_at)
                    WHERE user_id = ?
                ");

                $nextCount = $resendCount + 1;

                $stmt->execute([
                    $nextCount,
                    $resendCount,
                    $userId
                ]);

                // Commit before sending email.
                $pdo->commit();

                $stmt = $pdo->prepare("
                    SELECT email
                    FROM users
                    WHERE id = ? AND is_verified = 0
                ");

                $stmt->execute([$userId]);

                $user = $stmt->fetch();

                if ($user && sendUserOtp($userId, $user["email"])) {
                    $message = "A new OTP has been sent.";
                } else {
                    $message = "Email delivery failed. Please try again later.";
                }

                // Prevent another commit below.
                goto response_done;
            }
        }

        $pdo->commit();

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log($e->getMessage());

        $message = "Unable to resend OTP.";
    }
}

response_done:

?>
```

Add this form to `verify-email.php` or a separate resend section:

```php
<form method="POST" action="resend-otp.php">

    <input
        type="hidden"
        name="csrf_token"
        value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>"
    >

    <button type="submit">
        Resend OTP
    </button>

</form>
```

**Important production improvement:** The example above illustrates the resend policy, but the resend counter and OTP replacement should ideally be updated in the same transaction. Also enforce resend limits across multiple server instances, not just in PHP session data. We'll improve this design in the production-hardening stage.

## 6. Prevent unverified users from logging in

Open your existing `login.php`.

After finding the user by email, add this check:

```php
if (!$user["is_verified"]) {

    $message = "Please verify your email before logging in.";

} elseif (password_verify($password, $user["password"])) {

    session_regenerate_id(true);

    $_SESSION["user_id"] = $user["id"];
    $_SESSION["user_name"] = $user["name"];
    $_SESSION["user_email"] = $user["email"];

    header("Location: index.php");
    exit;

} else {

    $message = "Invalid email or password.";
}
```

The full login logic should also handle the case where the email does not exist, using a generic error message.

## 7. Understand the final database flow

<AsyncImage query="email OTP verification software architecture diagram PHP MySQL email SMTP user registration" aspectRatio="16:9" width="100%"/>

| Event | Users table | Email OTP table |
|---|---|---|
| Registration | Unverified | OTP created |
| Wrong OTP | Unverified | Attempts increased |
| OTP expires | Unverified | OTP removed |
| Correct OTP | Verified | OTP removed |
| Resend OTP | Unverified | New OTP stored |

## 8. Test your implementation

Use this test sequence:

{@body const tests = ["Register a new email address","Check whether an OTP arrives","Enter an incorrect OTP once","Enter an incorrect OTP two more times","Check that the third failed attempt blocks the old OTP","Request a new OTP","Try entering an expired OTP","Enter the correct OTP","Confirm is_verified changes to 1","Log in successfully","Try logging in with another unverified account"]}
{@body const [done,setDone] = DIL.useState([])}
<box border radius="lg" padding={3} gap={3}>
  <row justify="between" align="center">
    **OTP testing checklist**
    <title size="xl" color="success" tabularNums>{done.length}/{tests.length}</title>
  </row>
  <box background="surface-tertiary" height="8px" radius="full" clip>
    <box background="#16A34A" height="8px" radius="full" width={`${done.length/tests.length*100}%`}/>
  </box>
  {#each tests as t,i}
    <checkbox checked={done.includes(i)} onChange={v=>setDone(old=>v?[...old,i]:old.filter(x=>x!==i))}>{t}</checkbox>
  {/each}
</box>

## 9. Important security improvements

Before using this with real customers, remember:

- Use HTTPS.
- Keep SMTP passwords outside public source files.
- Rate-limit registration and verification requests by account and IP.
- Use generic responses to avoid revealing registered email addresses.
- Consider a background queue for sending emails.
- Add cleanup for expired OTP records.
- Avoid logging OTP values.
- Apply rate limits across multiple servers using a shared store such as Redis.
- Use database transactions for both OTP issuance and verification.

---

## 10. Knowledge test

{@body const questions = [{q:"What is the purpose of SELECT FOR UPDATE?",opts:["Delete an OTP","Lock the selected database row during a transaction","Generate an OTP"],a:1},{q:"How many incorrect OTP attempts do we allow?",opts:["3","10","Unlimited"],a:0},{q:"What should happen after successful verification?",opts:["Keep the OTP active","Delete the OTP and mark the user verified","Generate another OTP"],a:1}]}
{@body const [answers,setAnswers] = DIL.useState({})}
{@body const [submitted,setSubmitted] = DIL.useState(false)}
<box border radius="lg" padding={3} gap={3}>
  {#each questions as q,i}
    <box gap={2}>
      **{i+1}. {q.q}**
      <radio-group direction="col" value={answers[i]===undefined?"":String(answers[i])} onChange={v=>{setAnswers(old=>({...old,[i]:Number(v)}));setSubmitted(false);}}>
        {#each q.opts as option,j}
          <radio value={String(j)}>{option}</radio>
        {/each}
      </radio-group>
      {#if submitted}
        <text color={answers[i]===q.a?"success":"danger"} weight="medium">{answers[i]===q.a?"Correct":"Incorrect"}</text>
      {/if}
    </box>
    {#if i!==questions.length-1}
      <divider color="subtle"/>
    {/if}
  {/each}
  <button block disabled={Object.keys(answers).length!==3} onClick={()=>setSubmitted(true)}>Check answers</button>
  {#if submitted}
    **Score: {questions.filter((q,i)=>answers[i]===q.a).length}/3**
  {/if}
</box>

**Your next lesson:** Advanced PHP Lesson 3 – Forgot Password and Secure Password Reset, including reset tokens, expiration, one-time use, email delivery and password update.

Type **Next** when you're ready.