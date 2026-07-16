<?php
    session_start();
    require_once('../config/config.php');
    require_once('../config/audit.php');
    
    $userId = null;
    $userCode = null;
    $userName = null;
    $role = null;
    $action = 'logout';
    $module = 'auth';
    $entityType = '';
    $entityId = '';
    $details = 'User signed out';

    if (session_status() === PHP_SESSION_ACTIVE)
    {
        $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
        $userCode = isset($_SESSION['user_code']) ? (string) $_SESSION['user_code'] : null;
        $userName = isset($_SESSION['name']) ? (string) $_SESSION['name'] : null;
        $role = isset($_SESSION['role']) ? (string) $_SESSION['role'] : null;
    }
    
    $stmt = $mysqli->prepare("INSERT INTO logs (user_id, user_code, user_name, role, module, action, entity_type, entity_id, details) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {return;}
    $stmt->bind_param(
        'issssssss',
        $userId,
        $userCode,
        $userName,
        $role,
        $module,
        $action,
        $entityType,
        $entityId,
        $details
    );
    $stmt->execute();
    $stmt->close();
    $_SESSION = [];
    if (ini_get("session.use_cookies"))
    {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 3600,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
    header("Location: login.php");
    exit;
?>