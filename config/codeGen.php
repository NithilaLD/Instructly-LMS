<?php
    $alpha = 5;
    $a = substr(str_shuffle("QWERTYUIOPLKJHGFDSAZXCVBNM1234567890"), 1, $alpha);
    $b = substr(str_shuffle("1234567890"), 1, $alpha);
    function getNextCode(mysqli $mysqli, string $prefix)
    {
        if ($prefix === 'INS') { $role = 'instructor'; }
        elseif ($prefix === 'STD') { $role = 'student'; } 
        else { $role = null; }
        $count = 0;
        if ($role !== null)
        {
            $stmt = $mysqli->prepare("SELECT COUNT(*) AS total FROM users WHERE role = ?");
            $stmt->bind_param("s", $role);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) { $count = (int)$row['total']; }
        }
        $next = $count + 1;
        return $prefix . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
?>