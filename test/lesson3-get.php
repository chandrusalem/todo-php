<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h2>GET form Example</h2>
    <form method="Post" action="">
        <label for="">Enter Task:</label>
        <input type="text" name="task" placeholder="Enter your task">
        <button type="submit">Submit</button>
    </form>
    <?php
        // GET Method
        // if(isset($_GET["task"])){
        //     $task = $_GET["task"];
        //     echo "<h3>Your Task: </h3>";
        //     echo $task;
        // }

        // Server Method Check
        // echo "<pre>";
        // echo print_r($_SERVER);
        // echo "</pre>";

        // Post Method
        if($_SERVER["REQUEST_METHOD"] === "POST"){
            $task = $_POST["task"] ?? "";
            echo "<h3>Submitted Task:</h3>";
            echo htmlspecialchars($task);
        }
    ?>
</body>
</html>