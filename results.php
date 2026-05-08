<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/authentication/auth.php';

require_login();

$currentUserId = (int)get_current_user_id();

$summary = [
	'attempts_total' => 0,
	'correct_total' => 0,
	'wrong_total' => 0,
	'solved_unique' => 0,
	'accuracy' => 0,
];
$byDifficulty = [];
$recentAttempts = [];
$errorMessage = '';

try {
	$stmtSummary = $mysqli->prepare(
		"SELECT
			COUNT(*) AS attempts_total,
			SUM(CASE WHEN a.is_correct = 1 THEN 1 ELSE 0 END) AS correct_total,
			SUM(CASE WHEN a.is_correct = 0 THEN 1 ELSE 0 END) AS wrong_total,
			COUNT(DISTINCT a.question_id) AS solved_unique
		 FROM ege_question_attempts a
		 WHERE a.user_id = ?"
	);
	$stmtSummary->bind_param('i', $currentUserId);
	$stmtSummary->execute();
	$rowSummary = $stmtSummary->get_result()->fetch_assoc();
	$stmtSummary->close();

	if ($rowSummary) {
		$summary['attempts_total'] = (int)($rowSummary['attempts_total'] ?? 0);
		$summary['correct_total'] = (int)($rowSummary['correct_total'] ?? 0);
		$summary['wrong_total'] = (int)($rowSummary['wrong_total'] ?? 0);
		$summary['solved_unique'] = (int)($rowSummary['solved_unique'] ?? 0);
	}

	if ($summary['attempts_total'] > 0) {
		$summary['accuracy'] = (int)round(($summary['correct_total'] / $summary['attempts_total']) * 100);
	}

	$stmtDifficulty = $mysqli->prepare(
		"SELECT
			q.difficulty,
			COUNT(*) AS attempts_count,
			SUM(CASE WHEN a.is_correct = 1 THEN 1 ELSE 0 END) AS correct_count
		 FROM ege_question_attempts a
		 JOIN ege_questions q ON q.id = a.question_id
		 WHERE a.user_id = ?
		 GROUP BY q.difficulty
		 ORDER BY FIELD(q.difficulty, 'easy', 'medium', 'hard')"
	);
	$stmtDifficulty->bind_param('i', $currentUserId);
	$stmtDifficulty->execute();
	$resultDifficulty = $stmtDifficulty->get_result();

	while ($row = $resultDifficulty->fetch_assoc()) {
		$attemptsCount = (int)$row['attempts_count'];
		$correctCount = (int)$row['correct_count'];
		$row['accuracy'] = $attemptsCount > 0 ? (int)round(($correctCount / $attemptsCount) * 100) : 0;
		$byDifficulty[] = $row;
	}
	$stmtDifficulty->close();

	$stmtRecent = $mysqli->prepare(
		"SELECT
			a.id,
			a.is_correct,
			a.created_at,
			q.title,
			q.difficulty,
			tt.task_number,
			tt.title AS task_title
		 FROM ege_question_attempts a
		 JOIN ege_questions q ON q.id = a.question_id
		 JOIN ege_task_types tt ON tt.id = q.task_type_id
		 WHERE a.user_id = ?
		 ORDER BY a.created_at DESC
		 LIMIT 20"
	);
	$stmtRecent->bind_param('i', $currentUserId);
	$stmtRecent->execute();
	$resultRecent = $stmtRecent->get_result();

	while ($row = $resultRecent->fetch_assoc()) {
		$recentAttempts[] = $row;
	}
	$stmtRecent->close();
} catch (Throwable $exception) {
	$errorMessage = 'Не удалось загрузить прогресс. Проверьте таблицу ege_question_attempts.';
}

$page_title = 'Результаты';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
	<div>
		<h1 class="h3 mb-1">Результаты</h1>
		<p class="text-muted mb-0">Ваш прогресс по решенным заданиям.</p>
	</div>
	<a class="btn btn-outline-primary" href="<?= SITE_URL ?>/practice.php">Продолжить практику</a>
</div>

<?php if ($errorMessage !== ''): ?>
	<div class="alert alert-warning"><?= e($errorMessage) ?></div>
<?php endif; ?>

<div class="row g-3 mb-4">
	<div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><p class="text-muted mb-1">Попыток</p><p class="h3 mb-0"><?= e($summary['attempts_total']) ?></p></div></div></div>
	<div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><p class="text-muted mb-1">Верных</p><p class="h3 mb-0 text-success"><?= e($summary['correct_total']) ?></p></div></div></div>
	<div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><p class="text-muted mb-1">Ошибок</p><p class="h3 mb-0 text-danger"><?= e($summary['wrong_total']) ?></p></div></div></div>
	<div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><p class="text-muted mb-1">Точность</p><p class="h3 mb-0"><?= e($summary['accuracy']) ?>%</p></div></div></div>
</div>

<div class="card border-0 shadow-sm mb-3">
	<div class="card-body">
		<h2 class="h5 mb-3">По сложности</h2>
		<?php if (empty($byDifficulty)): ?>
			<p class="text-muted mb-0">Пока нет данных по сложности.</p>
		<?php else: ?>
			<div class="table-responsive">
				<table class="table align-middle mb-0">
					<thead>
						<tr>
							<th>Сложность</th>
							<th>Попыток</th>
							<th>Верных</th>
							<th>Точность</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($byDifficulty as $row): ?>
						<tr>
							<td><?= e($row['difficulty']) ?></td>
							<td><?= e($row['attempts_count']) ?></td>
							<td><?= e($row['correct_count']) ?></td>
							<td><?= e($row['accuracy']) ?>%</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>
</div>

<div class="card border-0 shadow-sm">
	<div class="card-body">
		<h2 class="h5 mb-3">Последние попытки</h2>
		<?php if (empty($recentAttempts)): ?>
			<p class="text-muted mb-0">Пока нет решенных вопросов.</p>
		<?php else: ?>
			<div class="table-responsive">
				<table class="table align-middle mb-0">
					<thead>
						<tr>
							<th>Дата</th>
							<th>Задание</th>
							<th>Вопрос</th>
							<th>Сложность</th>
							<th>Результат</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($recentAttempts as $attempt): ?>
						<tr>
							<td><?= e($attempt['created_at']) ?></td>
							<td>#<?= e($attempt['task_number']) ?> <?= e($attempt['task_title']) ?></td>
							<td><?= e($attempt['title']) ?></td>
							<td><?= e($attempt['difficulty']) ?></td>
							<td><?= (int)$attempt['is_correct'] === 1 ? '<span class="badge text-bg-success">correct</span>' : '<span class="badge text-bg-danger">wrong</span>' ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
