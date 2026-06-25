<?php
    $user_id = $_SESSION['user_id'];
    $role    = $_SESSION['role'];
    $stmt = $mysqli->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_object();
    if (!$user)
    {
        header("Location: logout.php");
        exit();
    }
    $currentPage = basename($_SERVER['PHP_SELF']); 
?>
<nav class="main-header navbar navbar-expand navbar-white navbar-light alert-info" style="background-color: #84B7F9 !important;border-color:#84B7F9 !important;">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#">
                <i class="fas fa-bars"></i>
            </a>
        </li>
    </ul>
    <ul class="navbar-nav ml-auto navbar-right-area">
        <li class="nav-item navbar-greeting">
            Welcome, <?php echo htmlspecialchars($user->name); ?>
        </li>
        <li class="nav-item">
            <a href="logout.php" class="navbar-icon-link ti" title="Logout">
                <i class="fas fa-power-off"></i>
            </a>
        </li>
    </ul>
</nav>