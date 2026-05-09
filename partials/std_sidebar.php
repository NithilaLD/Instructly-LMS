<?php
$sysName = (isset($sys) && isset($sys->sys_name)) ? $sys->sys_name : 'Instructly';
$sysLogo = (isset($sys) && isset($sys->sys_logo)) ? $sys->sys_logo : 'default.png';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4" style="background: #193b68ff !important;color: #fff !important;">
    <!-- Brand Logo -->
    <a href="std_dashboard.php" class="brand-link" style="background: #193b68ff !important;color: #fff !important;border-bottom: 1px solid #ffffff71 !important;">
        <img src="../public/sys_data/logo/<?php echo $sysLogo; ?>" alt="Logo" class="brand-image" style="width:15%;">
        <span class="brand-text font-weight-light" style="padding-left: 10px;"><?php echo $sysName; ?></span>
    </a>
    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- <li class="nav-item" style="color: #fff !important;">
                    <a href="../index.php" class="nav-link" style="color: #fff !important;">
                        <i class="nav-icon fas fa-home"></i>
                        <p>
                            Home
                        </p>
                    </a>
                </li> -->
                <li class="nav-item" style="color: #fff !important;">
                    <a href="std_dashboard.php" class="nav-link <?php echo $currentPage === 'std_dashboard.php' ? 'active' : ''; ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-columns"></i>
                        <p>
                            Dashboard
                        </p>
                    </a>
                </li>

                <li class="nav-item" style="color: #fff !important;">
                    <a href="std_enrolled_units.php" class="nav-link <?php echo $currentPage === 'std_enrolled_units.php' ? 'active' : ''; ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-user-check"></i>
                        <p>
                            Unit Enrollments
                        </p>
                    </a>
                </li>
                <li class="nav-item" style="color: #fff !important;">
                    <a href="std_manage_payments.php" class="nav-link <?php echo $currentPage === 'std_manage_payments.php' ? 'active' : ''; ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-file-invoice-dollar"></i>
                        <p>
                            Billings
                        </p>
                    </a>
                </li>
                <li class="nav-item" style="color: #fff !important;">
                    <a href="std_study_materials.php" class="nav-link <?php echo $currentPage === 'std_study_materials.php' ? 'active' : ''; ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-file"></i>
                        <p>
                            Study Materials
                        </p>
                    </a>
                </li>
                
                <li class="nav-item has-treeview <?php echo in_array($currentPage, ['std_questions_bank.php', 'std_answers_bank.php']) ? 'menu-open' : ''; ?>">
                    <a class="nav-link <?php echo in_array($currentPage, ['std_questions_bank.php', 'std_answers_bank.php']) ? 'active' : ''; ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-laptop-code"></i>
                        <p>
                            Test Engine
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item" style="color: #fff !important;">
                            <a href="std_questions_bank.php" class="nav-link <?php echo $currentPage === 'std_questions_bank.php' ? 'active' : ''; ?>" style="color: #fff !important;">
                                <i class="fas fa-chevron-right nav-icon"></i>
                                <p>Questions</p>
                            </a>
                        </li>
                        <li class="nav-item" style="color: #fff !important;">
                            <a href="std_answers_bank.php" class="nav-link <?php echo $currentPage === 'std_answers_bank.php' ? 'active' : ''; ?>" style="color: #fff !important;">
                                <i class="fas fa-chevron-right nav-icon"></i>
                                <p>Answers</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item" style="color: #fff !important;">
                    <a href="std_marks.php" class="nav-link <?php echo $currentPage === 'std_marks.php' ? 'active' : ''; ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-poll-h"></i>
                        <p>
                            Transcripts
                        </p>
                    </a>
                </li>
                <li class="nav-item" style="color: #fff !important;">
                    <a href="std_certificates.php" class="nav-link <?php echo $currentPage === 'std_certificates.php' ? 'active' : ''; ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-certificate"></i>
                        <p>
                            Certificates
                        </p>
                    </a>
                </li>

                <li class="nav-item" style="color: #fff !important;">
                    <a href="std_logout.php" class="nav-link" style="color: #fff !important;">
                        <i class="nav-icon fas fa-power-off"></i>
                        <p>
                            Logout
                        </p>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>