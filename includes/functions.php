<?php

function e($value) {
	return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function set_flash_message($type, $message) {
	$_SESSION['flash_message'] = [
		'type' => $type,
		'message' => $message,
	];
}

function get_flash_message() {
	if (!isset($_SESSION['flash_message'])) {
		return null;
	}

	$message = $_SESSION['flash_message'];
	unset($_SESSION['flash_message']);

	return $message;
}

function sourcePeriodLabel(string $period): string {
	$map = [
		'demo'     => 'демонстрационный вариант',
		'early'    => 'досрочный период',
		'march'    => 'март',
		'april'    => 'апрель',
		'may'      => 'май',
		'june'     => 'июнь',
		'main'     => 'основная волна',
		'reserve'  => 'резервный день',
		'teacher'  => 'учительский вариант',
		'training' => 'тренировочный вариант',
		'other'    => 'другое',
	];
	return $map[$period] ?? $period;
}
