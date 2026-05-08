<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/authentication/auth.php';

require_role($mysqli, ['admin', 'teacher']);

$currentUserId = (int)get_current_user_id();
$currentRole = get_user_role();

if ($currentRole === 'admin') {
    $sql = "
        SELECT
            q.*, tt.task_number, tt.title AS task_title, u.full_name
        FROM ege_questions q
        JOIN ege_task_types tt ON tt.id = q.task_type_id
        LEFT JOIN ege_users u ON u.id = q.created_by
        ORDER BY q.created_at DESC
    ";
    $result = $mysqli->query($sql);
} else {
    $sql = "
        SELECT
            q.*, tt.task_number, tt.title AS task_title
        FROM ege_questions q
        JOIN ege_task_types tt ON tt.id = q.task_type_id
        WHERE q.created_by = ?
        ORDER BY q.created_at DESC
    ";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $currentUserId);
    $stmt->execute();
    $result = $stmt->get_result();
}

$page_title = 'Teacher Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Teacher Dashboard</h1>
        <p class="text-muted mb-0">Добавление, редактирование и контроль публикации вопросов.</p>
    </div>
    <a href="<?= SITE_URL ?>/admin/question-create.php" class="btn btn-primary">Add question</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Task</th>
                    <th>Title</th>
                    <th>Published</th>
                    <th>Checked</th>
                    <?php if ($currentRole === 'admin'): ?>
                        <th>Author</th>
                    <?php endif; ?>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= e($row['id']) ?></td>
                    <td>#<?= e($row['task_number']) ?> <?= e($row['task_title']) ?></td>
                    <td><?= e($row['title']) ?></td>
                    <td><?= (int)$row['is_published'] === 1 ? 'published' : 'unpublished' ?></td>
                    <td><?= (int)$row['checked'] === 1 ? 'checked' : 'unchecked' ?></td>
                    <?php if ($currentRole === 'admin'): ?>
                        <td><?= e($row['full_name'] ?: '—') ?></td>
                    <?php endif; ?>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary" href="<?= SITE_URL ?>/admin/question-edit.php?id=<?= (int)$row['id'] ?>">Edit</a>
                        <a class="btn btn-sm btn-outline-danger" href="<?= SITE_URL ?>/admin/question-delete.php?id=<?= (int)$row['id'] ?>">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
if (isset($stmt)) {
    $stmt->close();
}
require_once __DIR__ . '/includes/footer.php';
