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

$number = isset($_GET['number']) ? (int)$_GET['number'] : 0;
$task = null;
$questions = [];
$errorMessage = '';

try {
    $stmt = $mysqli->prepare("SELECT * FROM ege_task_types WHERE task_number = ? AND is_active = 1 LIMIT 1");
    $stmt->bind_param('i', $number);
    $stmt->execute();
    $task = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($task) {
        $stmtQuestions = $mysqli->prepare(" 
            SELECT
                q.*,
                t.title AS topic_title
            FROM ege_questions q
            LEFT JOIN ege_topics t ON t.id = q.topic_id
            WHERE q.task_type_id = ?
              AND q.is_published = 1
            ORDER BY q.id DESC
        ");
        $taskId = (int)$task['id'];
        $stmtQuestions->bind_param('i', $taskId);
        $stmtQuestions->execute();
        $result = $stmtQuestions->get_result();
        while ($row = $result->fetch_assoc()) {
            $questions[] = $row;
        }
        $stmtQuestions->close();
    }
} catch (Throwable $exception) {
    $errorMessage = 'Не удалось загрузить задание.';
}

if (!$task) {
    http_response_code(404);
    die('Задание не найдено');
}

$page_title = 'Задание №' . (int)$task['task_number'] . ' — ' . $task['title'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1">Задание №<?= e($task['task_number']) ?>. <?= e($task['title']) ?></h1>
        <p class="text-muted mb-0">
            Часть <?= e($task['part_number'] ?? ((int)$task['task_number'] <= 12 ? 1 : 2)) ?>
            <?php if (!empty($task['difficulty_level'])): ?> · Сложность: <?= e($task['difficulty_level']) ?><?php endif; ?>
            <?php if (!empty($task['answer_format'])): ?> · Формат: <?= e($task['answer_format']) ?><?php endif; ?>
            <?php if (isset($task['max_score'])): ?> · Первичный балл: <?= (int)$task['max_score'] ?><?php endif; ?>
        </p>
    </div>
    <a class="btn btn-outline-secondary" href="/tasks.php">Все задания</a>
</div>

<?php if ($errorMessage !== ''): ?>
    <div class="alert alert-warning"><?= e($errorMessage) ?></div>
<?php endif; ?>

<?php if (!empty($task['short_description'])): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <p class="mb-0"><?= e($task['short_description']) ?></p>
        </div>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h2 class="h5 mb-3">Задачи этого номера</h2>

        <?php if (empty($questions)): ?>
            <div class="alert alert-info mb-0">Пока нет опубликованных задач для этого номера.</div>
        <?php else: ?>
            <div class="vstack gap-3">
                <?php foreach ($questions as $question): ?>
                    <article class="border rounded-3 p-3">
                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                            <div>
                                <div class="text-muted small mb-1">
                                    <?= !empty($question['topic_title']) ? e($question['topic_title']) . ' · ' : '' ?><?= e($question['difficulty']) ?>
                                </div>
                                <h3 class="h6 mb-2"><?= e($question['title']) ?></h3>
                                <div class="text-muted small"><?= mb_substr(strip_tags((string)$question['body_html']), 0, 180) ?><?= mb_strlen(strip_tags((string)$question['body_html'])) > 180 ? '...' : '' ?></div>
                            </div>
                            <a class="btn btn-sm btn-primary" href="/question.php?id=<?= (int)$question['id'] ?>">Открыть</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
