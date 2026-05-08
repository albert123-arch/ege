<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/authentication/auth.php';

function detect_table(mysqli $mysqli, array $candidates): string {
	foreach ($candidates as $tableName) {
		$query = "SHOW TABLES LIKE '" . $mysqli->real_escape_string($tableName) . "'";
		$result = $mysqli->query($query);

		if ($result && $result->num_rows > 0) {
			$result->free();
			return $tableName;
		}

		if ($result) {
			$result->free();
		}
	}

	return '';
}

function table_has_column(mysqli $mysqli, string $tableName, string $columnName): bool {
	$safeTable = str_replace('`', '``', $tableName);
	$stmt = $mysqli->prepare("SHOW COLUMNS FROM `{$safeTable}` LIKE ?");
	$stmt->bind_param('s', $columnName);
	$stmt->execute();
	$result = $stmt->get_result();
	$hasColumn = $result->num_rows > 0;
	$stmt->close();

	return $hasColumn;
}

function normalize_answer(string $value): string {
	$value = trim($value);
	$value = mb_strtolower($value, 'UTF-8');
	$value = preg_replace('/\s+/u', ' ', $value);
	$value = str_replace(',', '.', $value);

	return (string)$value;
}

function log_attempt(mysqli $mysqli, int $userId, int $questionId, string $submittedAnswer, int $isCorrect): void {
	$attemptsTable = detect_table($mysqli, ['ege_question_attempts', 'question_attempts']);
	if ($attemptsTable === '') {
		return;
	}

	$columns = ['user_id', 'question_id', 'is_correct'];
	$types = 'iii';
	$params = [$userId, $questionId, $isCorrect];

	foreach (['submitted_answer', 'answer_text', 'user_answer'] as $answerColumn) {
		if (table_has_column($mysqli, $attemptsTable, $answerColumn)) {
			$columns[] = $answerColumn;
			$types .= 's';
			$params[] = $submittedAnswer;
			break;
		}
	}

	if (table_has_column($mysqli, $attemptsTable, 'created_at')) {
		$columns[] = 'created_at';
	}
	if (table_has_column($mysqli, $attemptsTable, 'updated_at')) {
		$columns[] = 'updated_at';
	}

	$safeTable = str_replace('`', '``', $attemptsTable);
	$columnSql = implode(', ', array_map(static function ($column) {
		return '`' . str_replace('`', '``', $column) . '`';
	}, $columns));

	$valueSqlParts = [];
	$bindColumnCount = count($params);
	for ($i = 0; $i < count($columns); $i++) {
		if ($i < $bindColumnCount) {
			$valueSqlParts[] = '?';
		} else {
			$valueSqlParts[] = 'NOW()';
		}
	}
	$valueSql = implode(', ', $valueSqlParts);

	$stmt = $mysqli->prepare("INSERT INTO `{$safeTable}` ({$columnSql}) VALUES ({$valueSql})");
	$stmt->bind_param($types, ...$params);
	$stmt->execute();
	$stmt->close();
}

$isLoggedIn = function_exists('is_logged_in')
	? is_logged_in()
	: (function_exists('is_user_logged_in') ? is_user_logged_in() : false);
$currentUserId = $isLoggedIn ? (int)get_current_user_id() : 0;
$currentRole = $isLoggedIn ? (string)get_user_role() : '';

$questionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($questionId <= 0) {
	http_response_code(404);
	die('Вопрос не найден');
}

$mediaTable = detect_table($mysqli, ['question_media', 'ege_question_media']);
$errorMessage = '';
$successMessage = '';
$submittedAnswer = '';
$isCorrect = null;
$showSolution = false;

$stmtQuestion = $mysqli->prepare(
	"SELECT
		q.*,
		tt.task_number,
		tt.title AS task_title,
		t.title AS topic_title,
		CASE
			WHEN ? > 0 THEN EXISTS(
				SELECT 1
				FROM ege_bookmarks b
				WHERE b.user_id = ? AND b.question_id = q.id
			)
			ELSE 0
		END AS is_bookmarked
	 FROM ege_questions q
	 JOIN ege_task_types tt ON tt.id = q.task_type_id
	 LEFT JOIN ege_topics t ON t.id = q.topic_id
	 WHERE q.id = ?
	 LIMIT 1"
);
$stmtQuestion->bind_param('iii', $currentUserId, $currentUserId, $questionId);
$stmtQuestion->execute();
$question = $stmtQuestion->get_result()->fetch_assoc();
$stmtQuestion->close();

if (!$question) {
	http_response_code(404);
	die('Вопрос не найден');
}

$canViewUnpublished = $isLoggedIn && ($currentRole === 'admin' || $currentRole === 'teacher');
if ((int)$question['is_published'] !== 1 && !$canViewUnpublished) {
	http_response_code(404);
	die('Вопрос не найден');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (isset($_POST['bookmark_action'])) {
		if (!$isLoggedIn) {
			$errorMessage = 'Для работы с закладками нужно войти в аккаунт.';
		} else {
			try {
				$stmtBookmarkCheck = $mysqli->prepare(
					"SELECT 1 FROM ege_bookmarks WHERE user_id = ? AND question_id = ? LIMIT 1"
				);
				$stmtBookmarkCheck->bind_param('ii', $currentUserId, $questionId);
				$stmtBookmarkCheck->execute();
				$bookmarkExists = $stmtBookmarkCheck->get_result()->fetch_assoc();
				$stmtBookmarkCheck->close();

				if ($bookmarkExists) {
					$stmtBookmarkDelete = $mysqli->prepare("DELETE FROM ege_bookmarks WHERE user_id = ? AND question_id = ?");
					$stmtBookmarkDelete->bind_param('ii', $currentUserId, $questionId);
					$stmtBookmarkDelete->execute();
					$stmtBookmarkDelete->close();
					$question['is_bookmarked'] = 0;
					$successMessage = 'Закладка удалена.';
				} else {
					$stmtBookmarkInsert = $mysqli->prepare(
						"INSERT INTO ege_bookmarks (user_id, question_id, created_at) VALUES (?, ?, NOW())"
					);
					$stmtBookmarkInsert->bind_param('ii', $currentUserId, $questionId);
					$stmtBookmarkInsert->execute();
					$stmtBookmarkInsert->close();
					$question['is_bookmarked'] = 1;
					$successMessage = 'Вопрос добавлен в закладки.';
				}
			} catch (Throwable $exception) {
				$errorMessage = 'Не удалось обновить закладку.';
			}
		}
	}

	if (isset($_POST['submit_answer'])) {
		$submittedAnswer = trim((string)($_POST['submitted_answer'] ?? ''));
		if ($submittedAnswer === '') {
			$errorMessage = 'Введите ответ перед проверкой.';
		} elseif (empty($question['answer_text'])) {
			$errorMessage = 'Для этого вопроса пока не задан эталонный ответ.';
		} else {
			$normalizedUser = normalize_answer($submittedAnswer);
			$normalizedRight = normalize_answer((string)$question['answer_text']);
			$isCorrect = $normalizedUser !== '' && $normalizedUser === $normalizedRight;
			$showSolution = true;

			if ($isCorrect) {
				$successMessage = 'Верно! Ответ совпадает.';
			} else {
				$errorMessage = 'Пока неверно. Проверьте вычисления и попробуйте снова.';
			}

			if ($isLoggedIn) {
				try {
					log_attempt($mysqli, $currentUserId, $questionId, $submittedAnswer, $isCorrect ? 1 : 0);
				} catch (Throwable $exception) {
					// Do not block UX if attempts table has schema differences.
				}
			}
		}
	}

	if (isset($_POST['reveal_solution'])) {
		$showSolution = true;
	}
}

$mediaByRole = [
	'question' => [],
	'solution' => [],
	'hint' => [],
	'extra' => [],
];

if ($mediaTable !== '') {
	$safeMediaTable = str_replace('`', '``', $mediaTable);
	try {
		$stmtMedia = $mysqli->prepare(
			"SELECT id, role, file_path, file_type, alt_text, sort_order
			 FROM `{$safeMediaTable}`
			 WHERE question_id = ?
			 ORDER BY sort_order ASC, id ASC"
		);
		$stmtMedia->bind_param('i', $questionId);
		$stmtMedia->execute();
		$resultMedia = $stmtMedia->get_result();

		while ($row = $resultMedia->fetch_assoc()) {
			$role = (string)($row['role'] ?? 'question');
			if (!isset($mediaByRole[$role])) {
				$mediaByRole[$role] = [];
			}
			$mediaByRole[$role][] = $row;
		}

		$stmtMedia->close();
	} catch (Throwable $exception) {
		// Optional media block should not block page rendering.
	}
}

$page_title = 'Задание #' . (int)$question['task_number'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 gap-3 flex-wrap">
	<div>
		<h1 class="h3 mb-1">Задание #<?= e($question['task_number']) ?>: <?= e($question['task_title']) ?></h1>
		<p class="text-muted mb-0">
			<?= !empty($question['topic_title']) ? 'Тема: ' . e($question['topic_title']) . ' · ' : '' ?>
			Сложность: <?= e($question['difficulty']) ?>
		</p>
	</div>
	<a class="btn btn-outline-secondary" href="/practice.php">Назад к практике</a>
</div>

<?php if ($successMessage !== ''): ?>
	<div class="alert alert-success"><?= e($successMessage) ?></div>
<?php endif; ?>
<?php if ($errorMessage !== ''): ?>
	<div class="alert alert-warning"><?= e($errorMessage) ?></div>
<?php endif; ?>

<article class="card border-0 shadow-sm mb-4">
	<div class="card-body">
		<div class="d-flex justify-content-between align-items-start gap-3 mb-3">
			<h2 class="h4 mb-0"><?= e($question['title']) ?></h2>
			<span class="badge text-bg-light"><?= e($question['difficulty']) ?></span>
		</div>

		<div class="mb-3"><?= $question['body_html'] ?></div>

		<?php if (!empty($mediaByRole['question'])): ?>
			<div class="row g-3 mb-3">
				<?php foreach ($mediaByRole['question'] as $media): ?>
					<div class="col-md-6">
						<figure class="mb-0">
							<img class="img-fluid rounded border" src="<?= e($media['file_path']) ?>" alt="<?= e($media['alt_text'] ?: 'Иллюстрация к задаче') ?>">
							<?php if (!empty($media['alt_text'])): ?>
								<figcaption class="small text-muted mt-1"><?= e($media['alt_text']) ?></figcaption>
							<?php endif; ?>
						</figure>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<form method="post" class="row g-2 align-items-end">
			<div class="col-md-8">
				<label class="form-label">Ваш ответ</label>
				<input class="form-control" type="text" name="submitted_answer" value="<?= e($submittedAnswer) ?>" placeholder="Введите ответ" required>
			</div>
			<div class="col-md-4 d-grid">
				<button class="btn btn-primary" type="submit" name="submit_answer" value="1">Проверить</button>
			</div>
		</form>

		<div class="d-flex gap-2 flex-wrap mt-3">
			<?php if ($isLoggedIn): ?>
				<form method="post" class="mb-0">
					<button class="btn btn-sm <?= (int)$question['is_bookmarked'] === 1 ? 'btn-warning' : 'btn-outline-warning' ?>" type="submit" name="bookmark_action" value="toggle">
						<?= (int)$question['is_bookmarked'] === 1 ? 'Убрать из закладок' : 'В закладки' ?>
					</button>
				</form>
			<?php else: ?>
				<a class="btn btn-sm btn-outline-secondary" href="/authentication/login.php">Войти для закладок и прогресса</a>
			<?php endif; ?>
		</div>
	</div>
</article>

<?php if (!empty($mediaByRole['hint'])): ?>
	<section class="card border-0 shadow-sm mb-4">
		<div class="card-body">
			<h3 class="h5">Подсказки</h3>
			<div class="row g-3">
				<?php foreach ($mediaByRole['hint'] as $media): ?>
					<div class="col-md-6">
						<img class="img-fluid rounded border" src="<?= e($media['file_path']) ?>" alt="<?= e($media['alt_text'] ?: 'Подсказка') ?>">
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<section class="card border-0 shadow-sm mb-4">
	<div class="card-body">
		<h3 class="h5">Решение</h3>

		<?php if (!$showSolution): ?>
			<p class="text-muted mb-3">Решение и правильный ответ скрыты до проверки.</p>
			<form method="post" class="mb-0">
				<button class="btn btn-sm btn-outline-secondary" type="submit" name="reveal_solution" value="1">Показать решение</button>
			</form>
		<?php else: ?>
			<?php if (!empty($question['solution_html'])): ?>
				<div class="mb-3"><?= $question['solution_html'] ?></div>
			<?php else: ?>
				<p class="text-muted mb-3">Решение пока не добавлено.</p>
			<?php endif; ?>

			<?php if (!empty($mediaByRole['solution'])): ?>
				<div class="row g-3 mb-3">
					<?php foreach ($mediaByRole['solution'] as $media): ?>
						<div class="col-md-6">
							<img class="img-fluid rounded border" src="<?= e($media['file_path']) ?>" alt="<?= e($media['alt_text'] ?: 'Иллюстрация решения') ?>">
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if (!empty($question['answer_text'])): ?>
				<div class="alert alert-light border mb-0">Правильный ответ: <strong><?= e($question['answer_text']) ?></strong></div>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</section>

<?php if (!empty($mediaByRole['extra'])): ?>
	<section class="card border-0 shadow-sm mb-4">
		<div class="card-body">
			<h3 class="h5">Дополнительно</h3>
			<div class="row g-3">
				<?php foreach ($mediaByRole['extra'] as $media): ?>
					<div class="col-md-6">
						<img class="img-fluid rounded border" src="<?= e($media['file_path']) ?>" alt="<?= e($media['alt_text'] ?: 'Дополнительный материал') ?>">
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
