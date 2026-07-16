<?php
    $role    = $_SESSION['role'];
    $user_id = (int) $_SESSION['user_id'];
    if ($role === "admin")
    {
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM users WHERE role = 'student'");
        $stmt->execute();
        $stmt->bind_result($std);
        $stmt->fetch();
        $stmt->close();
        
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM users WHERE role = 'instructor'");
        $stmt->execute();
        $stmt->bind_result($instructors);
        $stmt->fetch();
        $stmt->close();

        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM courses");
        $stmt->execute();
        $stmt->bind_result($courses);
        $stmt->fetch();
        $stmt->close();

        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM units");
        $stmt->execute();
        $stmt->bind_result($units);
        $stmt->fetch();
        $stmt->close();
    }
    if ($role === 'instructor')
    {

        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM enrollments e JOIN courses c ON e.c_id = c.c_id WHERE c.i_id = ? ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($students_enrollments);
        $stmt->fetch();
        $stmt->close();

        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM courses WHERE i_id = ? ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($allocated_units);
        $stmt->fetch();
        $stmt->close();

        $stmt = $mysqli->prepare(" SELECT COALESCE(SUM(p.p_amt),0) FROM payments p JOIN materials m ON p.m_id = m.m_id JOIN units u ON m.u_id = u.u_id JOIN courses c ON u.c_id = c.c_id WHERE c.i_id = ? ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($paid_bills);
        $stmt->fetch();
        $stmt->close();
    }
    if ($role === 'student')
    {
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM enrollments WHERE s_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($students_enrollments);
        $stmt->fetch();
        $stmt->close();

        $stmt = $mysqli->prepare(" SELECT COUNT(*) FROM certificates c JOIN enrollments e ON c.en_id = e.en_id WHERE e.s_id = ? ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($complete_courses);
        $stmt->fetch();
        $stmt->close();

        $stmt = $mysqli->prepare("SELECT COALESCE(SUM(p_amt),0) FROM payments WHERE s_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($paid_bills);
        $stmt->fetch();
        $stmt->close();
    }
?>