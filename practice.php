<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/authentication/auth.php';

require_login();

$currentUserId = (int)get_current_user_id();
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bookmark_question_id'])) {
	$bookmarkQuestionId = (int)$_POST['bookmark_question_id'];
	if ($bookmarkQuestionId > 0) {
		try {
			$stmtToggle = $mysqli->prepare(
				"SELECT 1 FROM ege_bookmarks WHERE user_id = ? AND question_id = ? LIMIT 1"
			);
			$stmtToggle->bind_param('ii', $currentUserId, $bookmarkQuestionId);
			$stmtToggle->execute();
			$exists = $stmtToggle->get_result()->fetch_assoc();
			$stmtToggle->close();

			if ($exists) {
				$stmtDelete = $mysqli->prepare(
					"DELETE FROM ege_bookmarks WHERE user_id = ? AND question_id = ?"
				);
				$stmtDelete->bind_param('ii', $currentUserId, $bookmarkQuestionId);
				$stmtDelete->execute();
				$stmtDelete->close();
			} else {
				$stmtInsert = $mysqli->prepare(
					"INSERT INTO ege_bookmarks (user_id, question_id, created_at) VALUES (?, ?, NOW())"
				);
				$stmtInsert->bind_param('ii', $currentUserId, $bookmarkQuestionId);
				$stmtInsert->execute();
				$stmtInsert->close();
			}
		} catch (Throwable $exception) {
			$errorMessage = 'Не удалось обновить закладки. Проверьте таблицу ege_bookmarks.';
		}
	}
}

$filterTaskType = isset($_GET['task_type_id']) ? (int)$_GET['task_type_id'] : 0;
$filterTopic = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : 0;
$filterDifficulty = trim((string)($_GET['difficulty'] ?? ''));
$filterNotSolved = isset($_GET['not_solved']) && $_GET['not_solved'] === '1';
$filterWrong = isset($_GET['wrong']) && $_GET['wrong'] === '1';
$filterBookmarked = isset($_GET['bookmarked']) && $_GET['bookmarked'] === '1';

if (!in_array($filterDifficulty, ['', 'easy', 'medium', 'hard'], true)) {
	$filterDifficulty = '';
}

$taskTypes = [];
$topics = [];

try {
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
} catch (Throwable $exception) {
	$errorMessage = 'Не удалось загрузить фильтры из таблиц ege_task_types/ege_topics.';
}

$questions = [];

try {
	$sql = "
		SELECT
			q.*,
			tt.task_number,
			tt.title AS task_title,
			t.title AS topic_title,
			EXISTS(
				SELECT 1
				FROM ege_bookmarks b
				WHERE b.user_id = ? AND b.question_id = q.id
			) AS is_bookmarked
		FROM ege_questions q
		JOIN ege_task_types tt ON tt.id = q.task_type_id
		LEFT JOIN ege_topics t ON t.id = q.topic_id
		WHERE q.is_published = 1
	";

	$types = 'i';
	$params = [$currentUserId];

	if ($filterTaskType > 0) {
		$sql .= " AND q.task_type_id = ?";
		$types .= 'i';
		$params[] = $filterTaskType;
	}

	if ($filterTopic > 0) {
		$sql .= " AND q.topic_id = ?";
		$types .= 'i';
		$params[] = $filterTopic;
	}

	if ($filterDifficulty !== '') {
		$sql .= " AND q.difficulty = ?";
		$types .= 's';
		$params[] = $filterDifficulty;
	}

	if ($filterBookmarked) {
		$sql .= " AND EXISTS (SELECT 1 FROM ege_bookmarks b2 WHERE b2.user_id = ? AND b2.question_id = q.id)";
		$types .= 'i';
		$params[] = $currentUserId;
	}

	if ($filterNotSolved) {
		$sql .= " AND NOT EXISTS (SELECT 1 FROM ege_question_attempts a1 WHERE a1.user_id = ? AND a1.question_id = q.id)";
		$types .= 'i';
		$params[] = $currentUserId;
	}

	if ($filterWrong) {
		$sql .= " AND EXISTS (SELECT 1 FROM ege_question_attempts a2 WHERE a2.user_id = ? AND a2.question_id = q.id AND a2.is_correct = 0)";
		$types .= 'i';
		$params[] = $currentUserId;
	}

	$sql .= " ORDER BY q.id DESC LIMIT 20";

	$stmt = $mysqli->prepare($sql);
	$stmt->bind_param($types, ...$params);
	$stmt->execute();
	$result = $stmt->get_result();

	while ($row = $result->fetch_assoc()) {
		$questions[] = $row;
	}
	$stmt->close();
} catch (Throwable $exception) {
	$errorMessage = 'Не удалось получить вопросы. Проверьте таблицы ege_questions, ege_question_attempts и ege_bookmarks.';
}

$page_title = 'Практика';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
	<div>
		<h1 class="h3 mb-1">Практика</h1>
		<p class="text-muted mb-0">SQL-подборка вопросов с фильтрами по заданию, теме и прогрессу.</p>
	</div>
</div>

<?php if ($errorMessage !== ''): ?>
	<div class="alert alert-warning"><?= e($errorMessage) ?></div>
<?php endif; ?>

<form method="get" class="card border-0 shadow-sm mb-3">
	<div class="card-body">
		<div class="row g-2 align-items-end">
			<div class="col-md-3">
				<label class="form-label">Номер задания</label>
				<select class="form-select" name="task_type_id">
					<option value="0">Все</option>
					<?php foreach ($taskTypes as $task): ?>
						<option value="<?= (int)$task['id'] ?>" <?= $filterTaskType === (int)$task['id'] ? 'selected' : '' ?>>
							#<?= e($task['task_number']) ?> <?= e($task['title']) ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="col-md-3">
				<label class="form-label">Тема</label>
				<select class="form-select" name="topic_id">
					<option value="0">Все</option>
					<?php foreach ($topics as $topic): ?>
						<option value="<?= (int)$topic['id'] ?>" <?= $filterTopic === (int)$topic['id'] ? 'selected' : '' ?>>
							<?= e($topic['title']) ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="col-md-2">
				<label class="form-label">Сложность</label>
				<select class="form-select" name="difficulty">
					<option value="" <?= $filterDifficulty === '' ? 'selected' : '' ?>>Все</option>
					<option value="easy" <?= $filterDifficulty === 'easy' ? 'selected' : '' ?>>easy</option>
					<option value="medium" <?= $filterDifficulty === 'medium' ? 'selected' : '' ?>>medium</option>
					<option value="hard" <?= $filterDifficulty === 'hard' ? 'selected' : '' ?>>hard</option>
				</select>
			</div>
			<div class="col-md-4">
				<div class="form-check">
					<input class="form-check-input" type="checkbox" name="not_solved" value="1" id="notSolved" <?= $filterNotSolved ? 'checked' : '' ?>>
					<label class="form-check-label" for="notSolved">Не решенные</label>
				</div>
				<div class="form-check">
					<input class="form-check-input" type="checkbox" name="wrong" value="1" id="wrongOnly" <?= $filterWrong ? 'checked' : '' ?>>
					<label class="form-check-label" for="wrongOnly">С ошибками</label>
				</div>
				<div class="form-check">
					<input class="form-check-input" type="checkbox" name="bookmarked" value="1" id="bookmarkedOnly" <?= $filterBookmarked ? 'checked' : '' ?>>
					<label class="form-check-label" for="bookmarkedOnly">Только закладки</label>
				</div>
			</div>
		</div>
	</div>
	<div class="card-footer bg-white d-flex justify-content-end">
		<button class="btn btn-outline-primary" type="submit">Применить фильтры</button>
	</div>
</form>

<?php if (empty($questions)): ?>
	<div class="alert alert-info">По выбранным фильтрам ничего не найдено.</div>
<?php else: ?>
	<div class="vstack gap-3">
		<?php foreach ($questions as $question): ?>
			<article class="card border-0 shadow-sm">
				<div class="card-body">
					<div class="d-flex justify-content-between align-items-start gap-3 mb-2">
						<div>
							<div class="text-muted small mb-1">
								#<?= e($question['task_number']) ?> <?= e($question['task_title']) ?>
								<?php if (!empty($question['topic_title'])): ?>
									· <?= e($question['topic_title']) ?>
								<?php endif; ?>
							</div>
							<h2 class="h5 mb-0"><?= e($question['title']) ?></h2>
						</div>
						<span class="badge text-bg-light"><?= e($question['difficulty']) ?></span>
					</div>

					<div class="mb-3"><?= $question['body_html'] ?></div>

					<div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
						<div>
							<?php if (!empty($question['answer_text'])): ?>
								<span class="text-muted">Ответ: <?= e($question['answer_text']) ?></span>
							<?php endif; ?>
						</div>
						<form method="post" class="mb-0">
							<input type="hidden" name="bookmark_question_id" value="<?= (int)$question['id'] ?>">
							<button class="btn btn-sm <?= (int)$question['is_bookmarked'] === 1 ? 'btn-warning' : 'btn-outline-warning' ?>" type="submit">
								<?= (int)$question['is_bookmarked'] === 1 ? 'Убрать из закладок' : 'В закладки' ?>
							</button>
						</form>
					</div>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
