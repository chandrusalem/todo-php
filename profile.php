
<?php
    require_once __DIR__ ."/config/database.php";
    require_once __DIR__ ."/config/auth.php";
    $userId = (int) $_SESSION["user_id"];

    $stmt = $pdo->prepare("
        SELECT id, name, email, profile_image, created_at FROM users WHERE id = ?
    ");
    $stmt->execute([$userId]);

    $user = $stmt->fetch();
    if(!$user){
        http_response_code(404);
        exit("User not found.");
    }

?>
<?php require_once "includes/header.php" ?>
<h2>My Profile</h2>
<div class="profile-card">
    <?php if(!empty($user["profile_image"])): ?>
        <img src="
            <?= htmlspecialchars($user["profile_image"]) ?>
        " alt="Profile picture" width="120">
    <?php else: ?>
        <div class="avatar-placeholder">
            No Profile Picture
        </div>
    <?php endif; ?>
    <h3>
        <!-- <?= htmlspecialchars($user["name"]) ?> -->
    </h3>
    <p>
        <?= htmlspecialchars($user["email"]) ?>
    </p>
    <p>
        Joined:
        <?= htmlspecialchars($user["created_at"]) ?>
    </p>
    <a href="edit-profile.php">Edit Profile</a>
    <a href="change-password.php">Change Password</a>

</div>
<form
    method="POST"
    action="upload-profile-image.php"
    enctype="multipart/form-data"
>

    <input
        type="hidden"
        name="csrf_token"
        value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>"
    >

    <label>Choose Profile Picture</label>

    <input
        type="file"
        name="profile_image"
        accept="image/jpeg,image/png,image/webp"
        required
    >

    <button type="submit">
        Upload Picture
    </button>

</form>
<?php require_once "includes/footer.php"; ?>