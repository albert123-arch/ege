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
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_question_id'])) {
    $questionId = (int)$_POST['remove_question_id'];
    if ($questionId > 0) {
        try {
            $stmt = $mysqli->prepare("DELETE FROM ege_bookmarks WHERE user_id = ? AND question_id = ?");
            $stmt->bind_param('ii', $currentUserId, $questionId);
            $stmt->execute();
            $stmt->close();
            $successMessage = 'Закладка удалена.';
        } catch (Throwable $exception) {
            $errorMessage = 'Не удалось удалить закладку.';
        }
    }
}

$questions = [];
try {
    $stmt = $mysqli->prepare(" 
        SELECT
            q.*,
            b.created_at AS bookmarked_at,
            tt.task_number,
            tt.title AS task_title,
            t.title AS topic_title
        FROM ege_bookmarks b
        JOIN ege_questions q ON q.id = b.question_id
        JOIN ege_task_types tt ON tt.id = q.task_type_id
        LEFT JOIN ege_topics t ON t.id = q.topic_id
        WHERE b.user_id = ?
        ORDER BY b.created_at DESC
    ");
    $stmt->bind_param('i', $currentUserId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }
    $stmt->close();
} catch (Throwable $exception) {
    $errorMessage = 'Не удалось загрузить закладки.';
}

$page_title = 'Закладки';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1">Закладки</h1>
        <p class="text-muted mb-0">Сохранённые задачи для повторения.</p>
    </div>
    <a class="btn btn-outline-primary" href="/practice.php">Практика</a>
</div>

<?php if ($successMessage !== ''): ?>
    <div class="alert alert-success"><?= e($successMessage) ?></div>
<?php endif; ?>
<?php if ($errorMessage !== ''): ?>
    <div class="alert alert-warning"><?= e($errorMessage) ?></div>
<?php endif; ?>

<?php if (empty($questions)): ?>
    <div class="alert alert-info">У тебя пока нет задач в закладках.</div>
<?php else: ?>
    <div class="vstack gap-3">
        <?php foreach ($questions as $question): ?>
            <article class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div>
                            <div class="text-muted small mb-1">
                                Задание #<?= e($question['task_number']) ?> · <?= e($question['task_title']) ?>
                                <?= !empty($question['topic_title']) ? ' · ' . e($question['topic_title']) : '' ?>
                            </div>
                            <h2 class="h5 mb-2"><?= e($question['title']) ?></h2>
                            <div class="text-muted small"><?= mb_substr(strip_tags((string)$question['body_html']), 0, 180) ?><?= mb_strlen(strip_tags((string)$question['body_html'])) > 180 ? '...' : '' ?></div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a class="btn btn-sm btn-primary" href="/question.php?id=<?= (int)$question['id'] ?>">Открыть</a>
                            <form method="post" class="mb-0">
                                <input type="hidden" name="remove_question_id" value="<?= (int)$question['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger" type="submit">Удалить</button>
                            </form>
                        </div>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
