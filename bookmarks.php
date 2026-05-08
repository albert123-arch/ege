<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/authentication/auth.php';

require_login();

$currentUserId = (int)get_current_user_id();
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_question_id'])) {
	$removeQuestionId = (int)$_POST['remove_question_id'];
	if ($removeQuestionId > 0) {
		try {
			$stmtDelete = $mysqli->prepare("DELETE FROM ege_bookmarks WHERE user_id = ? AND question_id = ?");
			$stmtDelete->bind_param('ii', $currentUserId, $removeQuestionId);
			$stmtDelete->execute();
			$stmtDelete->close();
		} catch (Throwable $exception) {
			$errorMessage = 'Не удалось удалить закладку.';
		}
	}
}

$bookmarks = [];

try {
	$stmt = $mysqli->prepare(
		"SELECT
			q.id,
			q.title,
			q.difficulty,
			q.answer_text,
			q.body_html,
			tt.task_number,
			tt.title AS task_title,
			b.created_at AS bookmarked_at
		 FROM ege_bookmarks b
		 JOIN ege_questions q ON q.id = b.question_id
		 JOIN ege_task_types tt ON tt.id = q.task_type_id
		 WHERE b.user_id = ? AND q.is_published = 1
		 ORDER BY b.created_at DESC"
	);
	$stmt->bind_param('i', $currentUserId);
	$stmt->execute();
	$result = $stmt->get_result();

	while ($row = $result->fetch_assoc()) {
		$bookmarks[] = $row;
	}
	$stmt->close();
} catch (Throwable $exception) {
	$errorMessage = 'Не удалось загрузить закладки. Проверьте таблицу ege_bookmarks.';
}

$page_title = 'Закладки';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
	<div>
		<h1 class="h3 mb-1">Закладки</h1>
		<p class="text-muted mb-0">Сохраненные вопросы для повторения.</p>
	</div>
	<a class="btn btn-outline-primary" href="<?= SITE_URL ?>/practice.php">Вернуться в практику</a>
</div>

<?php if ($errorMessage !== ''): ?>
	<div class="alert alert-warning"><?= e($errorMessage) ?></div>
<?php endif; ?>

<?php if (empty($bookmarks)): ?>
	<div class="alert alert-info">У вас пока нет закладок.</div>
<?php else: ?>
	<div class="vstack gap-3">
		<?php foreach ($bookmarks as $item): ?>
			<article class="card border-0 shadow-sm">
				<div class="card-body">
					<div class="d-flex justify-content-between align-items-start gap-3 mb-2">
						<div>
							<div class="text-muted small mb-1">#<?= e($item['task_number']) ?> <?= e($item['task_title']) ?></div>
							<h2 class="h5 mb-0"><?= e($item['title']) ?></h2>
						</div>
						<span class="badge text-bg-light"><?= e($item['difficulty']) ?></span>
					</div>

					<div class="mb-3"><?= $item['body_html'] ?></div>

					<div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
						<div>
							<span class="text-muted small">Добавлено: <?= e($item['bookmarked_at']) ?></span>
							<?php if (!empty($item['answer_text'])): ?>
								<span class="text-muted small ms-2">Ответ: <?= e($item['answer_text']) ?></span>
							<?php endif; ?>
						</div>
						<form method="post" class="mb-0">
							<input type="hidden" name="remove_question_id" value="<?= (int)$item['id'] ?>">
							<button class="btn btn-sm btn-outline-danger" type="submit">Удалить закладку</button>
						</form>
					</div>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
