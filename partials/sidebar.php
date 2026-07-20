<?php

use Mpdf\CssManager;

    $role = $_SESSION['role'];
    $currentPage = basename($_SERVER['PHP_SELF']);
    $sysName = (isset($sys) && isset($sys->sys_name)) ? $sys->sys_name : 'Instructly';
    $sysLogo = (isset($sys) && isset($sys->sys_logo)) ? $sys->sys_logo : 'logo.png';
    function activeClass(string $currentPage, string $file) { return $currentPage === $file ? 'active' : ''; }
    function isActive(array $pages, string $currentPage) {return in_array($currentPage, (array)$pages) ? 'active' : '';}
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4" style="background: #193b68ff !important;color: #fff !important;">
    <a href="../views/index.php"
       class="brand-link"
       style="background: #193b68ff !important;color: #fff !important;border-bottom: 1px solid #ffffff71 !important;">
        <img src="../public/sys_data/logo/<?php echo $sysLogo; ?>" alt="Logo" class="brand-image" style="width:15%;">
        <span class="brand-text font-weight-light" style="padding-left: 10px;"><?php echo $sysName; ?></span>
    </a>
    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false" style="color: #fff !important;">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link <?php echo isActive(['dashboard.php'], $currentPage); ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-columns"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="courses.php" class="nav-link <?php echo isActive(['courses.php', 'view_course.php'], $currentPage); ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-book-open"></i>
                        <p>Courses</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="units.php" class="nav-link <?php echo isActive(['units.php', 'addunits.php'], $currentPage); ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-book-reader"></i>
                        <p>Units</p>
                    </a>
                </li>
                <?php if ($role === 'admin'): ?>
                <li class="nav-item">
                    <a href="instructors.php" class="nav-link <?php echo isActive(['instructors.php', 'view_instructor.php'], $currentPage); ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-user-tie"></i>
                        <p>Instructors</p>
                    </a>
                </li>
                <?php endif; ?>
                <?php if ($role === 'instructor' || $role === 'admin'): ?>
                <li class="nav-item">
                    <a href="students.php" class="nav-link <?php echo isActive(['students.php', 'view_student.php', 'enrollments.php', 'unenrollments.php'], $currentPage); ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-user-graduate"></i>
                        <p>Students</p>
                    </a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="materials.php" class="nav-link <?php echo isActive(['materials.php', 'manage_materials.php'], $currentPage); ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-file"></i>
                        <p>Materials</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="billings.php" class="nav-link <?php echo isActive(['billings.php', 'view_payment.php'], $currentPage); ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-file-invoice-dollar"></i>
                        <p>Billings</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="questions.php" class="nav-link <?php echo isActive(['questions.php', 'view_questions.php', 'manage_questions.php'], $currentPage); ?>" style="color: #fff !important;">
                        <i class="fas fa-question nav-icon"></i>
                        <p>Questions</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="answers.php" class="nav-link <?php echo isActive(['answers.php', 'view_answers.php'], $currentPage); ?>" style="color: #fff !important;">
                        <i class="fas fa-check-circle nav-icon"></i>
                        <p>Answers</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="marks.php" class="nav-link <?php echo isActive(['marks.php', 'gradeunits.php', 'gradestudents.php'], $currentPage); ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-chart-line"></i>
                        <p>Marks</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="certificates.php" class="nav-link <?php echo isActive(['certificates.php'], $currentPage); ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-certificate"></i>
                        <p>Certificates</p>
                    </a>
                </li>
                <?php if ($role === 'instructor' || $role === 'admin'): ?>
                <li class="nav-item">
                    <a href="reports.php" class="nav-link <?php echo isActive(['reports.php'], $currentPage); ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-file-alt"></i>
                        <p>Reports</p>
                    </a>
                </li>
                <?php endif; ?>
                <?php if ($role === 'admin'): ?>
                <li class="nav-item">
                    <a href="manage_users.php" class="nav-link <?php echo isActive(['manage_users.php'], $currentPage); ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Manage Users</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="audit_logs.php" class="nav-link <?php echo isActive(['audit_logs.php'], $currentPage); ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-history"></i>
                        <p>Audit Logs</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="settings.php" class="nav-link <?php echo isActive(['settings.php'], $currentPage); ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-cog"></i>
                        <p>Settings</p>
                    </a>
                </li>
                <?php endif; ?>   
                <li class="nav-item mt-2">
                    <a href="profile.php" class="nav-link <?php echo isActive(['profile.php'], $currentPage); ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-user-cog"></i>
                        <p>Profile</p>
                    </a>
                </li>
                <li class="nav-item mt-2">
                    <a href="logout.php" class="nav-link" style="color: #fff !important;">
                        <i class="nav-icon fas fa-power-off"></i>
                        <p>Log Out</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
