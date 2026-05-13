<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/authentication/auth.php';

function e($value) {
	return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function fetchOne(mysqli $mysqli, string $sql): ?array {
	$result = $mysqli->query($sql);

	if (!$result) {
		return null;
	}

	$row = $result->fetch_assoc();
	$result->free();

	return $row ?: null;
}

function fetchAll(mysqli $mysqli, string $sql): array {
	$result = $mysqli->query($sql);

	if (!$result) {
		return [];
	}

	$rows = [];

	while ($row = $result->fetch_assoc()) {
		$rows[] = $row;
	}

	$result->free();

	return $rows;
}

$page = fetchOne($mysqli, "
	SELECT 
		title,
		meta_description,
		h1,
		intro_html
	FROM ege_pages
	WHERE slug = 'home'
	  AND is_published = 1
	LIMIT 1
");

$homeBlocks = fetchAll($mysqli, "
	SELECT 
		title,
		body_html,
		button_text,
		button_url
	FROM ege_home_blocks
	WHERE is_active = 1
	ORDER BY sort_order ASC, id ASC
");

$topics = fetchAll($mysqli, "
	SELECT 
		slug,
		title,
		short_description,
		icon
	FROM ege_topics
	WHERE is_active = 1
	ORDER BY sort_order ASC, title ASC
	LIMIT 6
");

$taskTypes = fetchAll($mysqli, "
	SELECT 
		task_number,
		title,
		short_description
	FROM ege_task_types
	WHERE is_active = 1
	ORDER BY task_number ASC
");

$pageTitle = $page['title'] ?? 'Подготовка к ЕГЭ по математике — Maths4U';
$pageDescription = $page['meta_description'] ?? 'Подготовка к ЕГЭ по математике: теория, задания, практика, варианты и подробные решения.';
$h1 = $page['h1'] ?? 'Подготовка к ЕГЭ по математике';
$introHtml = $page['intro_html'] ?? '<p>Теория, практика, задания по номерам и варианты для подготовки к ЕГЭ по математике.</p>';
?>
<!doctype html>
<html lang="ru">
<head>
	<meta charset="utf-8">
	<title><?= e($pageTitle) ?></title>
	<meta name="description" content="<?= e($pageDescription) ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" href="/assets/icons/favicon.ico" sizes="any">
	<link rel="icon" href="/assets/icons/favicon.svg" type="image/svg+xml">
	<link rel="apple-touch-icon" href="/assets/icons/apple-touch-icon.png">
	<link rel="manifest" href="/assets/icons/site.webmanifest">
	<link rel="mask-icon" href="/assets/icons/safari-pinned-tab.svg" color="#2563eb">
	<meta name="theme-color" content="#2563eb">

	<link rel="canonical" href="<?= SITE_URL ?>/">

	<meta property="og:title" content="<?= e($pageTitle) ?>">
	<meta property="og:description" content="<?= e($pageDescription) ?>">
	<meta property="og:type" content="website">
	<meta property="og:url" content="<?= SITE_URL ?>/">
	<meta property="og:image" content="https://ege.maths4u.sbs/assets/icons/og-image.png">
	<meta property="og:image:width" content="1200">
	<meta property="og:image:height" content="630">

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

	<?php require_once __DIR__ . '/includes/mathjax.php'; ?>

	<style>
		:root {
			--bg: #f5f7fb;
			--card: #ffffff;
			--dark: #132238;
			--muted: #64748b;
			--primary: #2563eb;
			--primary-dark: #1d4ed8;
			--soft: #eaf1ff;
			--border: #e2e8f0;
		}

		body {
			background: var(--bg);
			color: var(--dark);
			font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
		}

		.navbar {
			background: rgba(255, 255, 255, .95);
			backdrop-filter: blur(12px);
			border-bottom: 1px solid var(--border);
		}

		.navbar-brand {
			font-weight: 800;
			letter-spacing: -0.03em;
			color: var(--dark);
		}

		.brand-mark {
			width: 34px;
			height: 34px;
			border-radius: 10px;
			background: linear-gradient(135deg, #2563eb, #14b8a6);
			color: #fff;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			font-weight: 800;
			margin-right: 8px;
		}

		.nav-link {
			font-weight: 600;
			color: #334155;
		}

		.nav-link:hover {
			color: var(--primary);
		}

		.hero {
			padding: 72px 0 48px;
			background:
				radial-gradient(circle at top left, rgba(37, 99, 235, .16), transparent 34%),
				radial-gradient(circle at bottom right, rgba(20, 184, 166, .16), transparent 30%);
		}

		.hero-card {
			background: rgba(255, 255, 255, .92);
			border: 1px solid rgba(226, 232, 240, .95);
			border-radius: 28px;
			box-shadow: 0 24px 60px rgba(15, 23, 42, .08);
		}

		.hero-title {
			font-size: clamp(2.1rem, 4vw, 4.3rem);
			line-height: 1.05;
			letter-spacing: -0.06em;
			font-weight: 850;
		}

		.hero-text {
			color: var(--muted);
			font-size: 1.08rem;
			line-height: 1.7;
		}

		.btn-main {
			background: var(--primary);
			border-color: var(--primary);
			color: #fff;
			font-weight: 700;
			border-radius: 14px;
			padding: .85rem 1.15rem;
		}

		.btn-main:hover {
			background: var(--primary-dark);
			border-color: var(--primary-dark);
			color: #fff;
		}

		.btn-light-main {
			background: #fff;
			border: 1px solid var(--border);
			color: var(--dark);
			font-weight: 700;
			border-radius: 14px;
			padding: .85rem 1.15rem;
		}

		.btn-light-main:hover {
			border-color: var(--primary);
			color: var(--primary);
			background: #f8fbff;
		}

		.section-title {
			font-weight: 850;
			letter-spacing: -0.045em;
			font-size: clamp(1.65rem, 3vw, 2.45rem);
		}

		.section-text {
			color: var(--muted);
			line-height: 1.7;
		}

		.card-link {
			text-decoration: none;
			color: inherit;
		}

		.site-card {
			background: var(--card);
			border: 1px solid var(--border);
			border-radius: 22px;
			height: 100%;
			transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
		}

		.site-card:hover {
			transform: translateY(-3px);
			border-color: #bfdbfe;
			box-shadow: 0 18px 38px rgba(15, 23, 42, .08);
		}

		.task-number {
			width: 46px;
			height: 46px;
			border-radius: 15px;
			background: linear-gradient(135deg, #2563eb, #14b8a6);
			color: #fff;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			font-weight: 850;
			font-size: 1.1rem;
			flex: 0 0 auto;
		}

		.topic-icon {
			width: 52px;
			height: 52px;
			border-radius: 17px;
			background: var(--soft);
			color: var(--primary);
			display: inline-flex;
			align-items: center;
			justify-content: center;
			font-weight: 850;
			font-size: 1.35rem;
			margin-bottom: 14px;
		}

		.footer {
			background: #fff;
			border-top: 1px solid var(--border);
			color: var(--muted);
		}

		.footer a {
			color: var(--muted);
			text-decoration: none;
		}

		.footer a:hover {
			color: var(--primary);
		}

		@media (max-width: 575.98px) {
			.hero {
				padding-top: 42px;
			}

			.hero-card {
				border-radius: 22px;
			}

			.btn-main,
			.btn-light-main {
				width: 100%;
			}
		}
	</style>
</head>

<body>

<nav class="navbar navbar-expand-lg sticky-top">
	<div class="container">
		<a class="navbar-brand d-flex align-items-center" href="/">
			<span class="brand-mark">M</span>
			<span><?= e(SITE_NAME) ?></span>
		</a>

		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
			<span class="navbar-toggler-icon"></span>
		</button>

		<div id="mainNav" class="collapse navbar-collapse">
			<ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
				<li class="nav-item"><a class="nav-link" href="/topics.php">Темы</a></li>
				<li class="nav-item"><a class="nav-link" href="/tasks.php">Задания ЕГЭ</a></li>
				<li class="nav-item"><a class="nav-link" href="/practice.php">Практика</a></li>
				<li class="nav-item"><a class="nav-link" href="/variants.php">Варианты</a></li>
				<li class="nav-item"><a class="nav-link" href="/diagnostic.php">Диагностика</a></li>
				<?php if (is_user_logged_in()): ?>
					<li class="nav-item"><span class="nav-link">Привет, <?= e(get_user_name()) ?></span></li>
					<li class="nav-item ms-lg-2">
						<a class="btn btn-sm btn-light-main px-3 py-2" href="/authentication/logout.php">Выход</a>
					</li>
				<?php else: ?>
					<li class="nav-item"><a class="nav-link" href="/authentication/login.php">Вход</a></li>
					<li class="nav-item ms-lg-2">
						<a class="btn btn-sm btn-main px-3 py-2" href="/authentication/register.php">Регистрация</a>
					</li>
				<?php endif; ?>
			</ul>
		</div>
	</div>
</nav>

<header class="hero">
	<div class="container">
		<div class="hero-card p-4 p-md-5">
			<div class="row align-items-center g-4">
				<div class="col-lg-8">
					<h1 class="hero-title mb-4"><?= e($h1) ?></h1>

					<div class="hero-text mb-4">
						<?= $introHtml ?>
					</div>

					<div class="d-flex flex-column flex-sm-row gap-3">
						<a href="/practice.php" class="btn btn-main">Начать практику</a>
						<a href="/tasks.php" class="btn btn-light-main">Задания по номерам</a>
					</div>
				</div>

				<div class="col-lg-4">
					<div class="site-card p-4">
						<h2 class="h5 fw-bold mb-3">Быстрый старт</h2>
						<p class="section-text mb-3">
							Выбери номер задания, повтори теорию и решай задачи с подробными объяснениями.
						</p>

						<div class="d-grid gap-2">
							<a href="/diagnostic.php" class="btn btn-light-main">Диагностика</a>
							<a href="/variants.php" class="btn btn-light-main">Пробные варианты</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</header>

<main>

	<?php if (!empty($homeBlocks)): ?>
		<section class="py-5">
			<div class="container">
				<div class="row g-3">
					<?php foreach ($homeBlocks as $block): ?>
						<div class="col-md-6 col-lg-4">
							<div class="site-card p-4">
								<h2 class="h5 fw-bold mb-3"><?= e($block['title']) ?></h2>

								<div class="section-text mb-3">
									<?= $block['body_html'] ?>
								</div>

								<?php if (!empty($block['button_text']) && !empty($block['button_url'])): ?>
									<a href="<?= e($block['button_url']) ?>" class="fw-bold text-decoration-none">
										<?= e($block['button_text']) ?> →
									</a>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section class="py-5 bg-white border-top border-bottom">
		<div class="container">
			<div class="row align-items-end mb-4">
				<div class="col-lg-8">
					<h2 class="section-title mb-2">Задания по номерам ЕГЭ</h2>
					<p class="section-text mb-0">
						Открой нужный номер, повтори метод и решай задачи этого типа.
					</p>
				</div>

				<div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
					<a href="/tasks.php" class="btn btn-light-main">Все номера</a>
				</div>
			</div>

			<?php if (!empty($taskTypes)): ?>
				<div class="row g-3">
					<?php foreach ($taskTypes as $task): ?>
						<div class="col-md-6 col-xl-4">
							<a class="card-link" href="/task.php?number=<?= e($task['task_number']) ?>">
								<div class="site-card p-3 p-md-4">
									<div class="d-flex gap-3 align-items-start">
										<div class="task-number"><?= e($task['task_number']) ?></div>

										<div>
											<h3 class="h6 fw-bold mb-1">
												Задание <?= e($task['task_number']) ?>. <?= e($task['title']) ?>
											</h3>

											<?php if (!empty($task['short_description'])): ?>
												<p class="section-text small mb-0">
													<?= e($task['short_description']) ?>
												</p>
											<?php endif; ?>
										</div>
									</div>
								</div>
							</a>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else: ?>
				<div class="alert alert-light border mb-0">
					Задания пока не добавлены в базу.
				</div>
			<?php endif; ?>
		</div>
	</section>

	<section class="py-5">
		<div class="container">
			<div class="row align-items-end mb-4">
				<div class="col-lg-8">
					<h2 class="section-title mb-2">Темы для подготовки</h2>
					<p class="section-text mb-0">
						Разделы можно вести отдельно от номеров ЕГЭ: алгебра, функции, геометрия, вероятность и текстовые задачи.
					</p>
				</div>

				<div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
					<a href="/topics.php" class="btn btn-light-main">Все темы</a>
				</div>
			</div>

			<?php if (!empty($topics)): ?>
				<div class="row g-3">
					<?php foreach ($topics as $topic): ?>
						<div class="col-md-6 col-xl-4">
							<a class="card-link" href="/topic.php?slug=<?= e($topic['slug']) ?>">
								<div class="site-card p-4">
									<div class="topic-icon">
										<?= e($topic['icon'] ?: '∑') ?>
									</div>

									<h3 class="h5 fw-bold mb-2"><?= e($topic['title']) ?></h3>

									<?php if (!empty($topic['short_description'])): ?>
										<p class="section-text mb-0">
											<?= e($topic['short_description']) ?>
										</p>
									<?php endif; ?>
								</div>
							</a>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else: ?>
				<div class="alert alert-light border mb-0">
					Темы пока не добавлены в базу.
				</div>
			<?php endif; ?>
		</div>
	</section>

	<section class="py-5 bg-white border-top">
		<div class="container">
			<div class="site-card p-4 p-md-5">
				<div class="row align-items-center g-4">
					<div class="col-lg-8">
						<h2 class="section-title mb-3">Практика и пробные варианты</h2>
						<p class="section-text mb-0">
							После добавления задач в базу сайт сможет показывать практику по номеру,
							теме, сложности, ошибкам ученика и избранным заданиям.
						</p>
					</div>

					<div class="col-lg-4">
						<div class="d-grid gap-2">
							<a href="/practice.php" class="btn btn-main">Открыть практику</a>
							<a href="/variants.php" class="btn btn-light-main">Пробные варианты</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

</main>

<footer class="footer py-4">
	<div class="container">
		<div class="d-flex flex-column flex-md-row justify-content-between gap-3">
			<div>
				<strong><?= e(SITE_NAME) ?></strong>
				<div class="small">Подготовка к ЕГЭ по математике</div>
			</div>

			<div class="d-flex flex-wrap gap-3 small">
				<a href="/topics.php">Темы</a>
				<a href="/tasks.php">Задания</a>
				<a href="/practice.php">Практика</a>
				<a href="/variants.php">Варианты</a>
				<a href="/admin/">Админка</a>
			</div>
		</div>
	</div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
