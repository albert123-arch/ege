<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/authentication/auth.php';

if (!function_exists('e')) {
    function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
}

if (function_exists('require_login')) {
    require_login();
} elseif (empty($_SESSION['ege_user_id'])) {
    header('Location: /authentication/login.php');
    exit;
}

$currentUserId = function_exists('get_current_user_id') ? (int)get_current_user_id() : (int)($_SESSION['ege_user_id'] ?? 0);
$errorMessage = '';
$summary = ['total_attempts' => 0, 'correct_attempts' => 0, 'accuracy' => 0];
$byTask = [];
$latestAttempts = [];

try {
    $stmt = $mysqli->prepare(" 
        SELECT
            COUNT(*) AS total_attempts,
            COALESCE(SUM(is_correct = 1), 0) AS correct_attempts,
            CASE WHEN COUNT(*) > 0 THEN ROUND(COALESCE(SUM(is_correct = 1), 0) / COUNT(*) * 100, 1) ELSE 0 END AS accuracy
        FROM ege_question_attempts
        WHERE user_id = ?
    ");
    $stmt->bind_param('i', $currentUserId);
    $stmt->execute();
    $summary = $stmt->get_result()->fetch_assoc() ?: $summary;
    $stmt->close();

    $stmtTask = $mysqli->prepare(" 
        SELECT
            tt.task_number,
            tt.title,
            COUNT(a.id) AS attempts,
            COALESCE(SUM(a.is_correct = 1), 0) AS correct,
            CASE WHEN COUNT(a.id) > 0 THEN ROUND(COALESCE(SUM(a.is_correct = 1), 0) / COUNT(a.id) * 100, 1) ELSE 0 END AS accuracy
        FROM ege_question_attempts a
        JOIN ege_questions q ON q.id = a.question_id
        JOIN ege_task_types tt ON tt.id = q.task_type_id
        WHERE a.user_id = ?
        GROUP BY tt.id
        ORDER BY tt.task_number ASC
    ");
    $stmtTask->bind_param('i', $currentUserId);
    $stmtTask->execute();
    $resultTask = $stmtTask->get_result();
    while ($row = $resultTask->fetch_assoc()) {
        $byTask[] = $row;
    }
    $stmtTask->close();

    $stmtLatest = $mysqli->prepare(" 
        SELECT
            a.*,
            q.title AS question_title,
            tt.task_number,
            tt.title AS task_title
        FROM ege_question_attempts a
        JOIN ege_questions q ON q.id = a.question_id
        JOIN ege_task_types tt ON tt.id = q.task_type_id
        WHERE a.user_id = ?
        ORDER BY a.created_at DESC
        LIMIT 20
    ");
    $stmtLatest->bind_param('i', $currentUserId);
    $stmtLatest->execute();
    $resultLatest = $stmtLatest->get_result();
    while ($row = $resultLatest->fetch_assoc()) {
        $latestAttempts[] = $row;
    }
    $stmtLatest->close();
} catch (Throwable $exception) {
    $errorMessage = 'Не удалось загрузить прогресс. Проверь таблицу ege_question_attempts.';
}

$page_title = 'Мой прогресс';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1">Мой прогресс</h1>
        <p class="text-muted mb-0">Статистика по решённым задачам.</p>
    </div>
    <a class="btn btn-outline-primary" href="/practice.php">Продолжить практику</a>
</div>

<?php if ($errorMessage !== ''): ?>
    <div class="alert alert-warning"><?= e($errorMessage) ?></div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted">Попыток</div><div class="display-6 fw-bold"><?= (int)$summary['total_attempts'] ?></div></div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted">Верно</div><div class="display-6 fw-bold"><?= (int)$summary['correct_attempts'] ?></div></div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted">Точность</div><div class="display-6 fw-bold"><?= e($summary['accuracy']) ?>%</div></div></div>
    </div>
</div>

<section class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h2 class="h5 mb-3">Прогресс по номерам ЕГЭ</h2>
        <?php if (empty($byTask)): ?>
            <div class="alert alert-info mb-0">Пока нет попыток.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>№</th><th>Тема</th><th>Попытки</th><th>Верно</th><th>Точность</th></tr></thead>
                    <tbody>
                        <?php foreach ($byTask as $row): ?>
                            <tr>
                                <td>#<?= e($row['task_number']) ?></td>
                                <td><?= e($row['title']) ?></td>
                                <td><?= (int)$row['attempts'] ?></td>
                                <td><?= (int)$row['correct'] ?></td>
                                <td><?= e($row['accuracy']) ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="card border-0 shadow-sm">
    <div class="card-body">
        <h2 class="h5 mb-3">Последние попытки</h2>
        <?php if (empty($latestAttempts)): ?>
            <div class="alert alert-info mb-0">Попыток пока нет.</div>
        <?php else: ?>
            <div class="vstack gap-2">
                <?php foreach ($latestAttempts as $attempt): ?>
                    <div class="border rounded-3 p-3 d-flex justify-content-between align-items-center gap-3 flex-wrap">
                        <div>
                            <div class="small text-muted">#<?= e($attempt['task_number']) ?> <?= e($attempt['task_title']) ?> · <?= e($attempt['created_at']) ?></div>
                            <a href="/question.php?id=<?= (int)$attempt['question_id'] ?>" class="fw-bold text-decoration-none"><?= e($attempt['question_title']) ?></a>
                        </div>
                        <span class="badge <?= (int)$attempt['is_correct'] === 1 ? 'text-bg-success' : 'text-bg-danger' ?>">
                            <?= (int)$attempt['is_correct'] === 1 ? 'Верно' : 'Ошибка' ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
