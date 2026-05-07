<?php
require_once __DIR__ . '/../includes/db.php';

/**
 * Хешировать пароль
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Проверить пароль
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Зарегистрировать нового пользователя
 */
function register_user($email, $password, $full_name) {
    global $mysqli;
    
    // Проверить, существует ли email
    $stmt = $mysqli->prepare("SELECT id FROM ege_users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        return ['success' => false, 'message' => 'Пользователь с таким email уже существует'];
    }
    $stmt->close();
    
    // Вставить нового пользователя
    $password_hash = hash_password($password);
    $role = 'student';
    $status = 'active';
    
    $stmt = $mysqli->prepare(
        "INSERT INTO ege_users (email, password_hash, full_name, role, status) 
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("sssss", $email, $password_hash, $full_name, $role, $status);
    
    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        $stmt->close();
        return ['success' => true, 'message' => 'Пользователь успешно зарегистрирован', 'user_id' => $user_id];
    } else {
        $stmt->close();
        return ['success' => false, 'message' => 'Ошибка при регистрации: ' . $mysqli->error];
    }
}

/**
 * Авторизовать пользователя
 */
function login_user($email, $password) {
    global $mysqli;
    
    $stmt = $mysqli->prepare(
        "SELECT id, password_hash, full_name, role, status FROM ege_users WHERE email = ? AND status = 'active'"
    );
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return ['success' => false, 'message' => 'Email или пароль неверны'];
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!verify_password($password, $user['password_hash'])) {
        return ['success' => false, 'message' => 'Email или пароль неверны'];
    }
    
    // Обновить last_login_at
    $user_id = $user['id'];
    $stmt = $mysqli->prepare("UPDATE ege_users SET last_login_at = NOW() WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    
    // Установить сессию
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $email;
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];
    
    return ['success' => true, 'message' => 'Успешная авторизация', 'user_id' => $user['id']];
}

/**
 * Проверить, авторизован ли пользователь
 */
function is_user_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Получить ID текущего пользователя
 */
function get_current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Получить email текущего пользователя
 */
function get_user_email() {
    return $_SESSION['email'] ?? null;
}

/**
 * Получить полное имя текущего пользователя
 */
function get_user_name() {
    return $_SESSION['full_name'] ?? 'Пользователь';
}

/**
 * Получить роль текущего пользователя
 */
function get_user_role() {
    return $_SESSION['role'] ?? null;
}

/**
 * Требовать, чтобы пользователь был авторизован
 */
function require_login() {
    if (!is_user_logged_in()) {
        header('Location: ' . SITE_URL . '/authentication/login.php');
        exit();
    }
}

/**
 * Требовать, чтобы пользователь был администратором
 */
function require_admin() {
    require_login();
    if (get_user_role() !== 'admin') {
        http_response_code(403);
        die('Доступ запрещен');
    }
}

/**
 * Требовать, чтобы пользователь был администратором или учителем
 */
function require_teacher() {
    require_login();
    $role = get_user_role();
    if ($role !== 'admin' && $role !== 'teacher') {
        http_response_code(403);
        die('Доступ запрещен');
    }
}

/**
 * Завершить сессию пользователя
 */
function logout_user() {
    session_destroy();
}
