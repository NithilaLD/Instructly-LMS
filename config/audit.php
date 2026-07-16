<?php
    if (!function_exists('logAuditAction'))
    {
        function logAuditAction(mysqli $mysqli, string $action, string $details = '', string $module = '', string $entityType = '', string $entityId = ''): void
        {
            if (!$mysqli instanceof mysqli) {return;}
            $userId = null;
            $userCode = null;
            $userName = null;
            $role = null;
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
        }
    }
?>