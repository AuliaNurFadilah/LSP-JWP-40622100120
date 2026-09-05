<?php
/**
 * Simple To-Do app with session storage
 * - Tasks stored as arrays with keys: text, done
 * - Supports: add, toggle status, delete
 * - Uses Bootstrap for UI
 */
session_start();

// Initialize tasks (only when session is new)
$initialTasks = [
    ['text' => 'Belajar PHP', 'done' => false],
    ['text' => 'Beli bahan makanan', 'done' => false],
    ['text' => 'Mencuci baju', 'done' => false],
];

// Ensure session has tasks and migrate old format if necessary
if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = $initialTasks;
} else {
    // Migrate plain string items to structured arrays
    $migrated = false;
    foreach ($_SESSION['tasks'] as $i => $item) {
        if (!is_array($item)) {
            $_SESSION['tasks'][$i] = ['text' => (string)$item, 'done' => false];
            $migrated = true;
        } elseif (!array_key_exists('text', $item)) {
            $_SESSION['tasks'][$i] = ['text' => (string)$item, 'done' => (bool)($item['done'] ?? false)];
            $migrated = true;
        }
    }
    if ($migrated) {
        // reindex to keep indexes consistent
        $_SESSION['tasks'] = array_values($_SESSION['tasks']);
    }
}

// --- Reusable helpers ----------------------------------------------------
/** Get tasks array from session */
function getTasks(): array
{
    return $_SESSION['tasks'] ?? [];
}

/** Save tasks array into session */
function saveTasks(array $tasks): void
{
    $_SESSION['tasks'] = array_values($tasks);
}

/** Add a task (sanitized) */
function addTask(string $text): void
{
    $t = trim(strip_tags($text));
    if ($t === '') {
        return;
    }
    $tasks = getTasks();
    $tasks[] = ['text' => $t, 'done' => false];
    saveTasks($tasks);
}

/** Toggle task done state by index */
function toggleTask(int $index): void
{
    $tasks = getTasks();
    if (!isset($tasks[$index])) return;
    $tasks[$index]['done'] = !$tasks[$index]['done'];
    saveTasks($tasks);
}

/** Delete a task by index */
function deleteTask(int $index): void
{
    $tasks = getTasks();
    if (!isset($tasks[$index])) return;
    array_splice($tasks, $index, 1);
    saveTasks($tasks);
}

/** Update task text by index */
function editTask(int $index, string $text): void
{
    $tasks = getTasks();
    if (!isset($tasks[$index])) return;
    $t = trim(strip_tags($text));
    if ($t === '') return;
    $tasks[$index]['text'] = $t;
    saveTasks($tasks);
}

// --- Handle incoming POST actions ---------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' && isset($_POST['task'])) {
        addTask((string)$_POST['task']);
    } elseif ($action === 'toggle' && isset($_POST['index'])) {
        toggleTask((int)$_POST['index']);
    } elseif ($action === 'edit' && isset($_POST['index']) && isset($_POST['text'])) {
        editTask((int)$_POST['index'], (string)$_POST['text']);
    } elseif ($action === 'delete' && isset($_POST['index'])) {
        deleteTask((int)$_POST['index']);
    }

    // Post/Redirect/Get: prevent duplicate submissions
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>To-Do List</title>
    <!-- Bootstrap CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{background:#f8fafc;padding:40px;font-family:Segoe UI,Arial,Helvetica,sans-serif}
        .card{max-width:800px;margin:24px auto}
        .task-done{text-decoration:line-through;opacity:0.6}
    </style>
</head>
<body>
<div class="card shadow-sm">
    <div class="card-body">
        <h1 class="h5 mb-10">Aplikasi To-Do List</h1>
        <p class="text-muted mb-3">Tambahkan tugas, centang untuk menandai selesai, atau hapus tugas.</p>

        <!-- Add task form -->
        <form method="post" class="mb-3">
            <input type="hidden" name="action" value="add">
            <div class="input-group">
                <input name="task" type="text" class="form-control" placeholder="Tulis tugas baru..." aria-label="Tugas baru">
                <button class="btn btn-primary" type="submit">Tambah</button>
            </div>
        </form>

        <h2 class="h6 mb-2">Daftar Tugas</h2>

        <?php $tasks = getTasks(); ?>

        <?php if (empty($tasks)): ?>
            <div class="alert alert-secondary">Belum ada tugas.</div>
        <?php else: ?>
            <ul class="list-group">
                <?php foreach ($tasks as $index => $task): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center flex-grow-1">
                            <!-- Toggle form: submits when checkbox changes (JS triggers submit) -->
                            <form method="post" class="me-2 d-inline-block" style="margin:0;padding:0;" id="toggle-form-<?php echo $index; ?>">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="index" value="<?php echo $index; ?>">
                                <div class="form-check">
                                    <input class="form-check-input task-toggle" type="checkbox" value="1" <?php echo $task['done'] ? 'checked' : ''; ?> aria-label="Selesai" data-form-id="toggle-form-<?php echo $index; ?>">
                                </div>
                            </form>

                            <!-- Display area and inline edit form (hidden) -->
                            <div class="ms-2 flex-grow-1">
                                <div class="task-view <?php echo $task['done'] ? 'task-done' : ''; ?>" id="task-view-<?php echo $index; ?>">
                                    <?php echo htmlspecialchars($task['text'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>

                                <form method="post" class="task-edit-form d-none" id="task-edit-<?php echo $index; ?>">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="index" value="<?php echo $index; ?>">
                                    <div class="input-group mt-1">
                                        <input name="text" type="text" class="form-control form-control-sm" value="<?php echo htmlspecialchars($task['text'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <button class="btn btn-sm btn-success" type="submit">Simpan</button>
                                        <button class="btn btn-sm btn-secondary btn-cancel-edit" type="button" data-index="<?php echo $index; ?>">Batal</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="btn-group ms-3">
                            <button class="btn btn-sm btn-outline-primary btn-edit me-1" type="button" data-index="<?php echo $index; ?>">Edit</button>
                            <form method="post" onsubmit="return confirm('Hapus tugas ini?');" style="display:inline;margin:0;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="index" value="<?php echo $index; ?>">
                                <button class="btn btn-sm btn-outline-danger" type="submit">Hapus</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<!-- Minimal JS: submit toggle form when checkbox changes -->
<script>
document.querySelectorAll('.task-toggle').forEach(function(cb){
    cb.addEventListener('change', function(){
        var id = this.dataset.formId;
        var f = document.getElementById(id);
        if (f) f.submit();
    });
});
// Edit UI handlers: show edit form, cancel
document.querySelectorAll('.btn-edit').forEach(function(b){
    b.addEventListener('click', function(){
        var i = this.dataset.index;
        var view = document.getElementById('task-view-'+i);
        var edit = document.getElementById('task-edit-'+i);
        if (view && edit) {
            view.classList.add('d-none');
            edit.classList.remove('d-none');
        }
    });
});
document.querySelectorAll('.btn-cancel-edit').forEach(function(b){
    b.addEventListener('click', function(){
        var i = this.dataset.index;
        var view = document.getElementById('task-view-'+i);
        var edit = document.getElementById('task-edit-'+i);
        if (view && edit) {
            edit.classList.add('d-none');
            view.classList.remove('d-none');
        }
    });
});
</script>

</body>
</html>
