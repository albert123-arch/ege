<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../authentication/auth.php';

require_role($mysqli, ['admin', 'teacher']);

$taskTypes = [];
$topics = [];
$taskResult = $mysqli->query("SELECT id, task_number, title FROM ege_task_types WHERE is_active = 1 ORDER BY task_number ASC");
while ($row = $taskResult->fetch_assoc()) {
    $taskTypes[] = $row;
}
$taskResult->free();

$topicResult = $mysqli->query("SELECT id, title FROM ege_topics WHERE is_active = 1 ORDER BY sort_order ASC, title ASC");
while ($row = $topicResult->fetch_assoc()) {
    $topics[] = $row;
}
$topicResult->free();

$form = [
    'task_type_id' => '',
    'topic_id' => '',
    'title' => '',
    'body_html' => '',
    'solution_html' => '',
    'answer_text' => '',
    'difficulty' => 'medium',
    'source' => '',
    'is_published' => '1',
    'checked' => '0',
];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($form as $field => $default) {
        $form[$field] = trim((string)($_POST[$field] ?? $default));
    }

    $taskTypeId = (int)$form['task_type_id'];
    $topicId = $form['topic_id'] === '' ? null : (int)$form['topic_id'];
    $title = $form['title'];
    $bodyHtml = $form['body_html'];
    $solutionHtml = $form['solution_html'] === '' ? null : $form['solution_html'];
    $answerText = $form['answer_text'] === '' ? null : $form['answer_text'];
    $difficulty = in_array($form['difficulty'], ['easy', 'medium', 'hard'], true) ? $form['difficulty'] : 'medium';
    $source = $form['source'] === '' ? null : $form['source'];
    $isPublished = $form['is_published'] === '1' ? 1 : 0;
    $checked = $form['checked'] === '1' ? 1 : 0;
    $createdBy = (int)get_current_user_id();

    if ($taskTypeId <= 0 || $title === '' || $bodyHtml === '') {
        $error = 'Заполните обязательные поля: задание, заголовок и текст вопроса.';
    } else {
        $stmt = $mysqli->prepare(
            "INSERT INTO ege_questions (
                task_type_id, topic_id, title, body_html, solution_html, answer_text,
                difficulty, source, created_by, is_published, checked
             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            'iissssssiii',
            $taskTypeId,
            $topicId,
            $title,
            $bodyHtml,
            $solutionHtml,
            $answerText,
            $difficulty,
            $source,
            $createdBy,
            $isPublished,
            $checked
        );

        $stmt->execute();
        $newId = (int)$stmt->insert_id;
        $stmt->close();

        set_flash_message('success', 'Вопрос успешно создан (ID: ' . $newId . ').');
        header('Location: ' . SITE_URL . '/admin/questions.php');
        exit();
    }
}

$page_title = 'Создать вопрос';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Создать вопрос</h1>
    <a href="<?= SITE_URL ?>/admin/questions.php" class="btn btn-outline-secondary">Назад к списку</a>
</div>

<?php if ($error !== ''): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<form method="post" class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Номер задания</label>
                <select class="form-select" name="task_type_id" required>
                    <option value="">Выберите...</option>
                    <?php foreach ($taskTypes as $task): ?>
                        <option value="<?= (int)$task['id'] ?>" <?= (string)$task['id'] === $form['task_type_id'] ? 'selected' : '' ?>>
                            #<?= e($task['task_number']) ?> <?= e($task['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Тема</label>
                <select class="form-select" name="topic_id">
                    <option value="">Без темы</option>
                    <?php foreach ($topics as $topic): ?>
                        <option value="<?= (int)$topic['id'] ?>" <?= (string)$topic['id'] === $form['topic_id'] ? 'selected' : '' ?>>
                            <?= e($topic['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12">
                <label class="form-label">Заголовок</label>
                <input class="form-control" name="title" value="<?= e($form['title']) ?>" maxlength="255" required>
            </div>

            <div class="col-12">
                <label class="form-label">Текст вопроса (HTML)</label>
                <textarea class="form-control" name="body_html" rows="6" required><?= e($form['body_html']) ?></textarea>
            </div>

            <div class="col-12">
                <label class="form-label">Решение (HTML)</label>
                <textarea class="form-control" name="solution_html" rows="5"><?= e($form['solution_html']) ?></textarea>
            </div>

            <div class="col-md-6">
                <label class="form-label">Ответ</label>
                <input class="form-control" name="answer_text" value="<?= e($form['answer_text']) ?>" maxlength="255">
            </div>

            <div class="col-md-6">
                <label class="form-label">Источник</label>
                <input class="form-control" name="source" value="<?= e($form['source']) ?>" maxlength="255">
            </div>

            <div class="col-md-4">
                <label class="form-label">Сложность</label>
                <select class="form-select" name="difficulty">
                    <option value="easy" <?= $form['difficulty'] === 'easy' ? 'selected' : '' ?>>easy</option>
                    <option value="medium" <?= $form['difficulty'] === 'medium' ? 'selected' : '' ?>>medium</option>
                    <option value="hard" <?= $form['difficulty'] === 'hard' ? 'selected' : '' ?>>hard</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Публикация</label>
                <select class="form-select" name="is_published">
                    <option value="1" <?= $form['is_published'] === '1' ? 'selected' : '' ?>>published</option>
                    <option value="0" <?= $form['is_published'] === '0' ? 'selected' : '' ?>>unpublished</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Проверка</label>
                <select class="form-select" name="checked">
                    <option value="1" <?= $form['checked'] === '1' ? 'selected' : '' ?>>checked</option>
                    <option value="0" <?= $form['checked'] === '0' ? 'selected' : '' ?>>unchecked</option>
                </select>
            </div>
        </div>
    </div>
    <div class="card-footer bg-white d-flex justify-content-end gap-2">
        <a href="<?= SITE_URL ?>/admin/questions.php" class="btn btn-outline-secondary">Отмена</a>
        <button class="btn btn-primary" type="submit">Сохранить вопрос</button>
    </div>
</form>
