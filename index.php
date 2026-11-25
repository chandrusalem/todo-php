
<?php

require_once "config/database.php";
require_once "config/auth.php";
// $style_path = __DIR__ .'/assets/css/style.css';

$user_id = $_SESSION["user_id"];
// echo $_SESSION["csrf_token"];

// 1. Get search input
$search = trim($_GET['search'] ?? "");

// 2. Get status filter
$status = $_GET["status"] ?? "";

// 3. Get priority filter
$priority = $_GET["priority"] ?? "";

// 4. Validate filters
$allowedPriorities = ["Low","Medium","High"];
$allowedStatuses = ["Pending","Completed"];
if(!in_array($status, $allowedStatuses, true)) {
    $status = "";
}
if(!in_array($priority, $allowedPriorities, true)) {
    $priority = "";
}

// 5. Build SQL conditions
$conditions = ["user_id = :user_id"];
$params = [":user_id" => $user_id];

if($search !== "") {
    $conditions[] = "task_name LIKE :search";
    $params[":search"] = "%" . $search . "%";
}
if($status !== "") {
    $conditions[] = "status = :status";
    $params[":status"] = $status;
}
if($priority !== "") {
    $conditions[] = "priority = :priority";
    $params[":priority"] = $priority;
}


// sorting
$sort = $_GET["sort"] ?? "newest";
$allowedSorts = [
    "newest" => "created_at DESC",
    "oldest"=> "created_at ASC",
    "name_asc"=> "task_name ASC",
    "name_desc"=> "task_name DESC",
    "priority_high" => "
        CASE priority WHEN 'High' THEN 1
            WHEN 'Medium' THEN 2
            WHEN 'Low' THEN 3
        END ASC    
    ",
];
if(!isset($allowedSorts[$sort])){
    $sort = "newest";
}

// pagination
$limit = 1;
$page = filter_input(INPUT_GET,"page", FILTER_VALIDATE_INT);
$page = ($page && $page > 0) ? $page :1;
$offset = ($page -1) * $limit;

// page counting
$countSql = "SELECT COUNT(*) FROM tasks";
if(!empty($conditions)){
    $countSql .= " WHERE ". implode(" AND ", $conditions);
}
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalTasks = (int) $countStmt->fetchColumn();
$totalPages = (int) ceil($totalTasks / $limit);
$page = min($page, max(1, $totalPages));
$offset = ($page -1) * $limit;

// 6. Build query
$sql = "SELECT * FROM tasks";
if(!empty($conditions)) {
    $sql .= " WHERE ". implode(" AND ", $conditions);
}
$sql .= " ORDER BY ". $allowedSorts[$sort];
$sql .= " LIMIT :limit OFFSET :offset";

// 7. Execute query
$stmt = $pdo->prepare($sql);
// Bind search and filter values
foreach($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

// Bind pagination as integers
$stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);

$stmt->execute();
$tasks = $stmt->fetchAll();




// counting completion
$pageTotalTasks = count($tasks);
$completedTasks = 0;
foreach ($tasks as $task) {
    if ($task["status"] === "Completed") {
        $completedTasks++;
    }
}
$pendingTasks = $pageTotalTasks - $completedTasks;

// Categories
$stmt = $pdo->prepare("
    SELECT id, category_name
    FROM categories
    WHERE user_id = ?
    ORDER BY category_name
");
$stmt->execute([$user_id]);
$categories = $stmt->fetchAll();

?>

<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My To-Do List</title>
    <link rel="stylesheet" href="./assets/css/style.css?v=<?= filemtime($style_path) ?>">
</head>
<body> -->
<?php require_once "includes/header.php" ?>
    <div class="dashboard">
        <header class="topbar">
            <div class="brand">
                <span class="brand-icon">✓</span>
                TaskFlow
            </div>
            <div class="user_menu">
                Welcome, <?= htmlspecialchars($_SESSION["user_name"]) ?>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </header>
    </div>
    <main class="main-contentr">
        <section class="page-heading">
            <div>
                <h1>My Dashboard</h1>
                <p>Manage your daily tasks easily.</p>
            </div>
        </section>
        
        <section class="stats-grid">
            <div class="stat-card blue">
                <span>Total</span>
                <h2><?= $totalTasks ?></h2>
            </div>
            <div class="stat-card green">
                <span>Completed</span>
                <h2><?= $completedTasks ?></h2>
            </div>
            <div class="stat-card yellow">
                <span>Pending</span>
                <h2><?= $pendingTasks ?></h2>
            </div>
        
        </section>

        <section class="content-card">
            <h2>Add New Task</h2>
            <form action="actions/create.php" method="POST">
                <input type="hidden"
                       name="csrf_token"
                       value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>" 
                >
                <input
                    type="text"
                    name="task_name"
                    placeholder="Enter task name"
                    maxlength="255"
                
                >
                <textarea
                    name="description"
                    placeholder="Task description"
                ></textarea>
                <select name="priority">
                    <option value="Low">Low</option>
                    <option value="Medium" selected>Medium</option>
                    <option value="High">High</option>
                </select>

                <label>Category</label>
                <select name="category_id">
                    <option value="">No Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category["id"] ?>">
                            <?= htmlspecialchars($category["category_name"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="primary-btn">Add Task</button>
            </form>
        </section>
        
        <section class="content-card">
            <h2>My Tasks</h2>
            <form action="index.php" method="GET">
                
                <input type="text" name="search" placeholder="Search task..." value="<?= htmlspecialchars($search) ?>">
                <select name="status" id="">
                    <option value="">All Status</option>
                    <option value="Pending" <?= $status === "Pending" ? "selected" : "" ?>>Pending</option>
                    <option value="Completed" <?= $status === "Completed" ? "selected" : "" ?> >Completed</option>
                </select>
                <select name="priority" id="">
                    <option value="">All Priorities</option>
                    <option value="Low" <?= $priority === "Low" ? "selected" : "" ?>>Low</option>
                    <option value="Medium" <?= $priority === "Medium" ? "selected" : "" ?>>Medium</option>
                    <option value="High" <?= $priority === "High" ? "selected" : "" ?>>High</option>
                </select>
                <select name="sort" id="">
                    <option value="newest" <?= $sort === "newest" ? "selected" : "" ?>>Newest First</option>
                    <option value="oldest" <?= $sort === "oldest" ? "selected" : "" ?>>Oldest First</option>
                    <option value="name_asc" <?= $sort === "name_asc" ? "selected" : "" ?>>Name A-Z</option>
                    <option value="name_desc" <?= $sort === "name_desc" ? "selected" : "" ?>>Name Z-A</option>
                    <option value="priority_high" <?= $sort === "priority_high" ? "selected" : "" ?>>High Priority First</option>
                </select>
                <button type="submit">Search / Filter</button>
                <a href="index.php">Clear Filters</a>
            </form>

            <?php if (empty($tasks)): ?>
                <p>No tasks found. Add your first task!</p>
            <?php else: ?>
                <?php foreach ($tasks as $task): ?>
                    <article class="task-card">
                        <div class="task-info">
                            <h3>
                                <?= htmlspecialchars($task["task_name"]) ?>
                            </h3>
                            <p>
                                <?= htmlspecialchars($task["description"] ?? "") ?>
                            </p>
                            <span class="priority <?= strtolower($task["priority"]) ?>">
                                <?= htmlspecialchars($task["priority"]) ?>
                            </span>
                            <span class="status <?= strtolower($task["status"]) ?>">
                                <?= htmlspecialchars($task["status"]) ?>
                            </span>
                            <p>
                                Category:
                                            
                                <?= htmlspecialchars(
                                    $task["category_name"] ?? "Uncategorized"
                                ) ?>
                            </p>
                        </div>
                        <div class="task-actions">
                            <form action="actions/toggle.php" method="POST">
                                <input type="hidden" name="id"
                                       value="<?= (int)$task["id"] ?>">
                                <input type="hidden" name="csrf_token"
                                       value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">
                                <button type="submit" class="secondary-btn">
                                    Toggle
                                </button>
                            </form>
                            <a href="edit.php?id=<?= (int)$task["id"] ?>"
                               class="secondary-btn">
                                Edit
                            </a>
                            <form action="actions/delete.php" method="POST"
                                  onsubmit="return confirm('Delete this task?')">
                                <input type="hidden" name="id"
                                       value="<?= (int)$task["id"] ?>">
                                <input type="hidden" name="csrf_token"
                                       value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">
                                <button type="submit" class="danger-btn">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Pagenation -->
            <?php if($totalPages > 1): ?>
                <div class="pagination">
                    <?php if($page > 1): ?>
                        <a href="?<?= 
                            http_build_query(array_merge($_GET, ["page"=> $page - 1]))
                        ?>">
                            Previous
                        </a>
                    <?php endif ?>
                    <?php for($i=1; $i<=$totalPages; $i++): ?>
                        <a href="?<?=
                            http_build_query(array_merge($_GET, ["page"=> $i]))
                            ?>"
                            class="<?= $page === $i ? "active" : "" ?>"
                        >
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    <?php if($page < $totalPages): ?>
                        <a href="?
                            <?= http_build_query(array_merge($_GET, ["page" => $page + 1])) ?>
                        ">
                            Next
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

<?php require_once "includes/footer.php"; ?>
