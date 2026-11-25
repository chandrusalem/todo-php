
<?php

require_once __DIR__ . "/../config/auth.php";
$style_path = __DIR__ . "/../assets/css/style.css";
require_once __DIR__ . "/../config/app.php";

?>
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>TaskFlow - My Tasks</title>

    <!-- <link rel="stylesheet"
          href="../assets/css/style.css?v=<?= filemtime($style_path) ?>"> -->

          <link rel="stylesheet"
      href="<?= BASE_URL ?>/assets/css/style.css">

</head>
<body>