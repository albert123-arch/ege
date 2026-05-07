<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

logout_user();
header('Location: ' . SITE_URL . '/login.php');
exit();
