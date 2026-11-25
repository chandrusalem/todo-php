
<?php

require_once __DIR__ . "/config/auth.php";
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/config/csrf.php";

$userId = (int) $_SESSION["user_id"];

$categoryId = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$categoryId || $categoryId < 1) {
    http_response_code(400);
    exit("Invalid category.");
}

// Find category owned by this user.
$stmt = $pdo->prepare("
    SELECT *
    FROM categories
    WHERE id = ? AND user_id = ?
");

$stmt->execute([$categoryId, $userId]);

$category = $stmt->fetch();

if (!$category) {
    http_response_code(404);
    exit("Category not found.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    verifyCsrfToken();

    $name = trim($_POST["category_name"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if ($name === "" || mb_strlen($name) > 100) {

        $message = "Invalid category name.";

    } else {

        try {

            $stmt = $pdo->prepare("
                UPDATE categories
                SET category_name = ?,
                    description = ?
                WHERE id = ? AND user_id = ?
            ");

            $stmt->execute([
                $name,
                $description ?: null,
                $categoryId,
                $userId
            ]);

            header("Location: categories.php");
            exit;

        } catch (PDOException $e) {

            if ($e->getCode() === "23000") {
                $message = "Category name already exists.";
            } else {
                error_log($e->getMessage());
                $message = "Unable to update category.";
            }
        }
    }
}
?>
```

HTML form:

```php
<h2>Edit Category</h2>

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
        value="<?= htmlspecialchars($category["category_name"]) ?>"
        maxlength="100"
        required
    >

    <label>Description</label>

    <input
        type="text"
        name="description"
        value="<?= htmlspecialchars($category["description"] ?? "") ?>"
        maxlength="255"
    >

    <button type="submit">Update Category</button>

</form>

<p><?= htmlspecialchars($message) ?></p>
```

## 7. Create delete category functionality

Create:

`delete-category.php`

Remember: deleting data should always use POST, not GET.

<CodeBlock language="php" editable>
<?php

require_once __DIR__ . "/config/auth.php";
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/config/csrf.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method not allowed.");
}

verifyCsrfToken();

$userId = (int) $_SESSION["user_id"];

$categoryId = filter_input(
    INPUT_POST,
    "category_id",
    FILTER_VALIDATE_INT
);

if (!$categoryId || $categoryId < 1) {
    http_response_code(400);
    exit("Invalid category.");
}

$stmt = $pdo->prepare("
    DELETE FROM categories
    WHERE id = ? AND user_id = ?
");

$stmt->execute([
    $categoryId,
    $userId
]);

header("Location: categories.php");
exit;

?>
