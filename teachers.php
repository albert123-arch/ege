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

$isLoggedIn = function_exists('is_logged_in') ? is_logged_in() : !empty($_SESSION['ege_user_id']);
$currentUserId = function_exists('get_current_user_id') ? (int)get_current_user_id() : (int)($_SESSION['ege_user_id'] ?? 0);
$currentRole = function_exists('get_user_role') ? (string)get_user_role() : (string)($_SESSION['ege_role'] ?? '');

if (!$isLoggedIn) {
    header('Location: /authentication/login.php');
    exit;
}
if (!in_array($currentRole, ['admin', 'teacher'], true)) {
    http_response_code(403);
    die('Access denied.');
}

function can_manage_ege_question(array $question, string $role, int $userId): bool {
    if ($role === 'admin') {
        return true;
    }
    return (int)($question['created_by'] ?? 0) === $userId;
}

$successMessage = '';
$errorMessage = '';
$editQuestion = null;

$taskTypes = [];
$topics = [];
try {
    $taskResult = $mysqli->query("SELECT id, task_number, title FROM ege_task_types WHERE is_active = 1 ORDER BY task_number ASC");
    while ($row = $taskResult->fetch_assoc()) { $taskTypes[] = $row; }
    $taskResult->free();

    $topicResult = $mysqli->query("SELECT id, title FROM ege_topics WHERE is_active = 1 ORDER BY sort_order ASC, title ASC");
    while ($row = $topicResult->fetch_assoc()) { $topics[] = $row; }
    $topicResult->free();
} catch (Throwable $exception) {
    $errorMessage = 'Не удалось загрузить номера заданий или темы.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'save_question') {
        $questionId = (int)($_POST['question_id'] ?? 0);
        $taskTypeId = (int)($_POST['task_type_id'] ?? 0);
        $topicIdRaw = (int)($_POST['topic_id'] ?? 0);
        $topicId = $topicIdRaw > 0 ? $topicIdRaw : null;
        $title = trim((string)($_POST['title'] ?? ''));
        $bodyHtml = trim((string)($_POST['body_html'] ?? ''));
        $solutionHtml = trim((string)($_POST['solution_html'] ?? ''));
        $answerText = trim((string)($_POST['answer_text'] ?? ''));
        $source = trim((string)($_POST['source'] ?? ''));
        $difficulty = (string)($_POST['difficulty'] ?? 'medium');
        $isPublished = (int)(($_POST['is_published'] ?? '0') === '1');
        $checked = (int)(($_POST['checked'] ?? '0') === '1');

        if (!in_array($difficulty, ['easy', 'medium', 'hard'], true)) {
            $difficulty = 'medium';
        }

        if ($taskTypeId <= 0 || $title === '' || $bodyHtml === '') {
            $errorMessage = 'Заполни номер задания, заголовок и текст вопроса.';
        } else {
            try {
                if ($questionId > 0) {
                    $stmtLoad = $mysqli->prepare("SELECT id, created_by FROM ege_questions WHERE id = ? LIMIT 1");
                    $stmtLoad->bind_param('i', $questionId);
                    $stmtLoad->execute();
                    $existing = $stmtLoad->get_result()->fetch_assoc();
                    $stmtLoad->close();

                    if (!$existing || !can_manage_ege_question($existing, $currentRole, $currentUserId)) {
                        throw new RuntimeException('Нет прав на редактирование этой задачи.');
                    }

                    $stmt = $mysqli->prepare(" 
                        UPDATE ege_questions
                        SET task_type_id = ?, topic_id = ?, title = ?, body_html = ?, solution_html = ?, answer_text = ?, difficulty = ?, source = ?, is_published = ?, checked = ?
                        WHERE id = ?
                    ");
                    $stmt->bind_param('iissssssiii', $taskTypeId, $topicId, $title, $bodyHtml, $solutionHtml, $answerText, $difficulty, $source, $isPublished, $checked, $questionId);
                    $stmt->execute();
                    $stmt->close();
                    $successMessage = 'Задача обновлена.';
                } else {
                    $stmt = $mysqli->prepare(" 
                        INSERT INTO ege_questions
                            (task_type_id, topic_id, title, body_html, solution_html, answer_text, difficulty, source, created_by, is_published, checked)
                        VALUES
                            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->bind_param('iissssssiii', $taskTypeId, $topicId, $title, $bodyHtml, $solutionHtml, $answerText, $difficulty, $source, $currentUserId, $isPublished, $checked);
                    $stmt->execute();
                    $stmt->close();
                    $successMessage = 'Задача добавлена.';
                }
            } catch (Throwable $exception) {
                $errorMessage = 'Не удалось сохранить задачу: ' . $exception->getMessage();
            }
        }
    }

    if ($action === 'delete_question') {
        $questionId = (int)($_POST['question_id'] ?? 0);
        if ($questionId > 0) {
            try {
                $stmtLoad = $mysqli->prepare("SELECT id, created_by FROM ege_questions WHERE id = ? LIMIT 1");
                $stmtLoad->bind_param('i', $questionId);
                $stmtLoad->execute();
                $existing = $stmtLoad->get_result()->fetch_assoc();
                $stmtLoad->close();

                if (!$existing || !can_manage_ege_question($existing, $currentRole, $currentUserId)) {
                    throw new RuntimeException('Нет прав на удаление этой задачи.');
                }

                $stmt = $mysqli->prepare("DELETE FROM ege_questions WHERE id = ?");
                $stmt->bind_param('i', $questionId);
                $stmt->execute();
                $stmt->close();
                $successMessage = 'Задача удалена.';
            } catch (Throwable $exception) {
                $errorMessage = 'Не удалось удалить задачу: ' . $exception->getMessage();
            }
        }
    }
}

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
if ($editId > 0) {
    try {
        $stmt = $mysqli->prepare("SELECT * FROM ege_questions WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $editId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row && can_manage_ege_question($row, $currentRole, $currentUserId)) {
            $editQuestion = $row;
        } else {
            $errorMessage = 'Задача для редактирования не найдена или нет прав.';
        }
    } catch (Throwable $exception) {
        $errorMessage = 'Не удалось открыть задачу для редактирования.';
    }
}

$questions = [];
try {
    if ($currentRole === 'admin') {
        $sql = " 
            SELECT q.*, tt.task_number, tt.title AS task_title, t.title AS topic_title, u.full_name AS author_name, u.email AS author_email
            FROM ege_questions q
            JOIN ege_task_types tt ON tt.id = q.task_type_id
            LEFT JOIN ege_topics t ON t.id = q.topic_id
            LEFT JOIN ege_users u ON u.id = q.created_by
            ORDER BY q.updated_at DESC, q.id DESC
            LIMIT 100
        ";
        $result = $mysqli->query($sql);
    } else {
        $stmt = $mysqli->prepare(" 
            SELECT q.*, tt.task_number, tt.title AS task_title, t.title AS topic_title
            FROM ege_questions q
            JOIN ege_task_types tt ON tt.id = q.task_type_id
            LEFT JOIN ege_topics t ON t.id = q.topic_id
            WHERE q.created_by = ?
            ORDER BY q.updated_at DESC, q.id DESC
            LIMIT 100
        ");
        $stmt->bind_param('i', $currentUserId);
        $stmt->execute();
        $result = $stmt->get_result();
    }

    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }
    if (isset($stmt) && $stmt instanceof mysqli_stmt) { $stmt->close(); }
    elseif ($result) { $result->free(); }
} catch (Throwable $exception) {
    $errorMessage = 'Не удалось загрузить список задач.';
}

$formQuestion = $editQuestion ?: [
    'id' => 0,
    'task_type_id' => $taskTypes[0]['id'] ?? 0,
    'topic_id' => 0,
    'title' => '',
    'body_html' => '',
    'solution_html' => '',
    'answer_text' => '',
    'source' => '',
    'difficulty' => 'medium',
    'is_published' => 1,
    'checked' => 0,
];

$page_title = 'Teacher dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1"><?= $currentRole === 'admin' ? 'Админ-панель задач' : 'Кабинет учителя' ?></h1>
        <p class="text-muted mb-0">Добавление, редактирование и удаление задач ЕГЭ.</p>
    </div>
    <a class="btn btn-outline-primary" href="/practice.php">Практика</a>
</div>

<?php if ($successMessage !== ''): ?>
    <div class="alert alert-success"><?= e($successMessage) ?></div>
<?php endif; ?>
<?php if ($errorMessage !== ''): ?>
    <div class="alert alert-warning"><?= e($errorMessage) ?></div>
<?php endif; ?>

<section class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h2 class="h5 mb-3"><?= (int)$formQuestion['id'] > 0 ? 'Редактировать задачу' : 'Добавить задачу' ?></h2>
        <form method="post">
            <input type="hidden" name="action" value="save_question">
            <input type="hidden" name="question_id" value="<?= (int)$formQuestion['id'] ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Номер задания</label>
                    <select class="form-select" name="task_type_id" required>
                        <?php foreach ($taskTypes as $task): ?>
                            <option value="<?= (int)$task['id'] ?>" <?= (int)$formQuestion['task_type_id'] === (int)$task['id'] ? 'selected' : '' ?>>
                                #<?= e($task['task_number']) ?> <?= e($task['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Тема</label>
                    <select class="form-select" name="topic_id">
                        <option value="0">Без темы</option>
                        <?php foreach ($topics as $topic): ?>
                            <option value="<?= (int)$topic['id'] ?>" <?= (int)($formQuestion['topic_id'] ?? 0) === (int)$topic['id'] ? 'selected' : '' ?>><?= e($topic['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Заголовок</label>
                    <input class="form-control" name="title" value="<?= e($formQuestion['title']) ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Текст вопроса (HTML + MathJax)</label>
                    <textarea class="form-control" name="body_html" rows="6" required><?= e($formQuestion['body_html']) ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Решение (HTML + MathJax)</label>
                    <textarea class="form-control" name="solution_html" rows="6"><?= e($formQuestion['solution_html']) ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Ответ</label>
                    <input class="form-control" name="answer_text" value="<?= e($formQuestion['answer_text']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Источник</label>
                    <input class="form-control" name="source" value="<?= e($formQuestion['source']) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Сложность</label>
                    <select class="form-select" name="difficulty">
                        <?php foreach (['easy', 'medium', 'hard'] as $difficulty): ?>
                            <option value="<?= e($difficulty) ?>" <?= $formQuestion['difficulty'] === $difficulty ? 'selected' : '' ?>><?= e($difficulty) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Публикация</label>
                    <select class="form-select" name="is_published">
                        <option value="1" <?= (int)$formQuestion['is_published'] === 1 ? 'selected' : '' ?>>published</option>
                        <option value="0" <?= (int)$formQuestion['is_published'] === 0 ? 'selected' : '' ?>>draft</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Проверка</label>
                    <select class="form-select" name="checked">
                        <option value="0" <?= (int)$formQuestion['checked'] === 0 ? 'selected' : '' ?>>unchecked</option>
                        <option value="1" <?= (int)$formQuestion['checked'] === 1 ? 'selected' : '' ?>>checked</option>
                    </select>
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button class="btn btn-primary" type="submit"><?= (int)$formQuestion['id'] > 0 ? 'Сохранить изменения' : 'Добавить задачу' ?></button>
                <?php if ((int)$formQuestion['id'] > 0): ?>
                    <a class="btn btn-outline-secondary" href="/teachers.php">Отмена</a>
                    <a class="btn btn-outline-primary" href="/question.php?id=<?= (int)$formQuestion['id'] ?>">Открыть задачу</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</section>

<section class="card border-0 shadow-sm">
    <div class="card-body">
        <h2 class="h5 mb-3"><?= $currentRole === 'admin' ? 'Все задачи' : 'Мои задачи' ?></h2>
        <?php if (empty($questions)): ?>
            <div class="alert alert-info mb-0">Задач пока нет.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Номер</th>
                            <th>Заголовок</th>
                            <th>Статус</th>
                            <th>Проверка</th>
                            <?php if ($currentRole === 'admin'): ?><th>Автор</th><?php endif; ?>
                            <th class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($questions as $question): ?>
                            <tr>
                                <td><?= (int)$question['id'] ?></td>
                                <td>#<?= e($question['task_number']) ?></td>
                                <td>
                                    <div class="fw-bold"><?= e($question['title']) ?></div>
                                    <div class="text-muted small"><?= e($question['topic_title'] ?? '') ?> · <?= e($question['difficulty']) ?></div>
                                </td>
                                <td><?= (int)$question['is_published'] === 1 ? '<span class="badge text-bg-success">published</span>' : '<span class="badge text-bg-secondary">draft</span>' ?></td>
                                <td><?= (int)$question['checked'] === 1 ? '<span class="badge text-bg-success">checked</span>' : '<span class="badge text-bg-warning">unchecked</span>' ?></td>
                                <?php if ($currentRole === 'admin'): ?>
                                    <td><?= e($question['author_name'] ?: $question['author_email'] ?: '—') ?></td>
                                <?php endif; ?>
                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        <a class="btn btn-sm btn-outline-primary" href="/question.php?id=<?= (int)$question['id'] ?>">View</a>
                                        <a class="btn btn-sm btn-outline-secondary" href="/teachers.php?edit=<?= (int)$question['id'] ?>">Edit</a>
                                        <form method="post" class="mb-0" onsubmit="return confirm('Удалить задачу?');">
                                            <input type="hidden" name="action" value="delete_question">
                                            <input type="hidden" name="question_id" value="<?= (int)$question['id'] ?>">
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
