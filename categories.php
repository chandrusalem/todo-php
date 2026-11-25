
<?php

require_once __DIR__ . "/config/auth.php";
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/config/csrf.php";

$userId = (int) $_SESSION["user_id"];

$message = "";

// CREATE CATEGORY
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    verifyCsrfToken();

    $name = trim($_POST["category_name"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if ($name === "" || mb_strlen($name) > 100) {

        $message = "Enter a valid category name.";

    } else {

        try {

            $stmt = $pdo->prepare("
                INSERT INTO categories
                (user_id, category_name, description)
                VALUES (?, ?, ?)
            ");

            $stmt->execute([
                $userId,
                $name,
                $description ?: null
            ]);

            header("Location: categories.php");
            exit;

        } catch (PDOException $e) {

            if ($e->getCode() === "23000") {
                $message = "This category already exists.";
            } else {
                error_log($e->getMessage());
                $message = "Unable to create category.";
            }
        }
    }
}

// READ CATEGORIES
$stmt = $pdo->prepare("
    SELECT *
    FROM categories
    WHERE user_id = ?
    ORDER BY category_name ASC
");

$stmt->execute([$userId]);

$categories = $stmt->fetchAll();

?>
<h2>Manage Categories</h2>

<form method="POST">

    <input
        type="hidden"
        name="csrf_token"
        value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>"
    >

    <label>Category Name</label>

    <input
        type="text"
        name="category_name"
        maxlength="100"
        placeholder="Example: Work"
        required
    >

    <label>Description</label>

    <input
        type="text"
        name="description"
        maxlength="255"
        placeholder="Enter description"
    >

    <button type="submit">
        Add Category
    </button>

</form>

<p><?= htmlspecialchars($message) ?></p>

<h3>Your Categories</h3>

<?php foreach ($categories as $category): ?>

    <div class="category-card">

        <h4>
            <?= htmlspecialchars($category["category_name"]) ?>
        </h4>

        <p>
            <?= htmlspecialchars($category["description"] ?? "") ?>
        </p>

        <a href="edit-category.php?id=<?= (int) $category["id"] ?>">
            Edit
        </a>

        <form
            method="POST"
            action="delete-category.php"
            onsubmit="return confirm('Delete this category?')"
        >

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>"
            >

            <input
                type="hidden"
                name="category_id"
                value="<?= (int) $category["id"] ?>"
            >

            <button type="submit">Delete</button>

        </form>

    </div>

<?php endforeach; ?>