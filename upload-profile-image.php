<?php

    require_once __DIR__ ."/config/auth.php";
    require_once __DIR__ ."/config/database.php";
    require_once __DIR__ ."/config/csrf.php";

    if($_SERVER["REQUEST_METHOD"] !== "POST") {
        http_response_code(405);
        exit("Medthod not allowed");
    }

    verifyCsrfToken();
    $userId = (int) $_SESSION["user_id"];
    if(!isset($_FILES["profile_image"])){
        exit("No image selected.");
    }
    $file = $_FILES["profile_image"];
    if($file["error"] !== UPLOAD_ERR_OK ){
        exit("Image upload failed.");
    }

    // Maximum 2 MB.
    if($file["size"] > 2 * 1024 * 1024){
        exit("Image must be smaller then 2 MB.");
    }

    // Detect actual MIME type.
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file["tmp_name"]);

    $allowedTypes = [
        "image/jpeg" => "jpg",
        "image/png"=> "png",
        "image/webp"=> "webp",
    ];

    if(!isset($allowedTypes[$mime])){
        exit("Only JPG, PNG and WEBP images are allowed.");
    }

    // Confirm that the uploaded file is a valid image.
    $imageInfo = getimagesize($file["tmp_name"]);
    if($imageInfo === false){
        exit("Invalid image file.");
    }

    $extension = $allowedTypes[$mime];

    //Geneate a random filename.
    $fileName = bin2hex(random_bytes(16)) . "." . $extension;

    $uploadDirectory = __DIR__ ."/uploads/avatars/";
    if(!is_dir($uploadDirectory)){
        mkdir($uploadDirectory,0755, true);
    }
    $destination = $uploadDirectory . $fileName;
    if(!move_uploaded_file($file["tmp_name"], $destination)){
        exit("Unable to save image");
    }

    $relativePath = "uploads/avatars/" . $fileName;

    // Update profile image.
    $stmt = $pdo->prepare("
        UPDATE users SET profile_image = ? WHERE id = ?
    ");
    $stmt->execute([
        $relativePath,
        $userId
    ]);

    header("Location: profile.php");
    exit;

?>