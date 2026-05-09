<aside class="main-sidebar sidebar-dark-primary elevation-4" style="background: #193b68ff !important;color: #fff !important;">
    <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
    <!-- Brand Logo -->
    <a href="ins_dashboard.php" class="brand-link" style="background: #193b68ff !important;color: #fff !important;border-bottom: 1px solid #ffffff71 !important;">
        <img src="../public/sys_data/logo/<?php echo isset($sys) ? $sys->sys_logo : ''; ?>" alt="Logo" class="brand-image" style="width:15%;">
        <span class="brand-text font-weight-light" style="padding-left: 10px;"><?php echo isset($sys) ? $sys->sys_name : 'System' ?></span>
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
                    <a href="ins_dashboard.php" class="nav-link <?php echo $currentPage === 'ins_dashboard.php' ? 'active' : ''; ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-columns"></i>
                        <p>
                            Dashboard
                        </p>
                    </a>
                </li>
                <li class="nav-item" style="color: #fff !important;">
                    <a href="ins_teaching_allocations.php" class="nav-link <?php echo $currentPage === 'ins_teaching_allocations.php' ? 'active' : ''; ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-chalkboard-teacher"></i>
                        <p>
                            Unit Allocations
                        </p>
                    </a>
                </li>
                <li class="nav-item" style="color: #fff !important;">
                    <a href="ins_unit_enrollments.php" class="nav-link <?php echo $currentPage === 'ins_unit_enrollments.php' ? 'active' : ''; ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-user-check"></i>
                        <p>
                            Unit Enrollments
                        </p>
                    </a>
                </li>
                <li class="nav-item" style="color: #fff !important;">
                    <a href="ins_study_materials.php" class="nav-link <?php echo $currentPage === 'ins_study_materials.php' ? 'active' : ''; ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-file"></i>
                        <p>
                            Study Materials
                        </p>
                    </a>
                </li>
                <li class="nav-item" style="color: #fff !important;">
                    <a href="ins_manage_payment_verification.php" class="nav-link <?php echo $currentPage === 'ins_manage_payment_verification.php' ? 'active' : ''; ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-check-circle"></i>
                        <p>
                            Payment Verification
                        </p>
                    </a>
                </li>
                <li class="nav-item" style="color: #fff !important;">
                    <a href="ins_billings.php" class="nav-link <?php echo $currentPage === 'ins_billings.php' ? 'active' : ''; ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-file-invoice-dollar"></i>
                        <p>
                            Billings
                        </p>
                    </a>
                </li>

                <li class="nav-item has-treeview <?php echo in_array($currentPage, ['ins_questions_bank.php', 'ins_answers_bank.php']) ? 'menu-open' : ''; ?>">
                    <a class="nav-link <?php echo in_array($currentPage, ['ins_questions_bank.php', 'ins_answers_bank.php']) ? 'active' : ''; ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-laptop-code"></i>
                        <p>
                            Test Engine
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item" style="color: #fff !important;">
                            <a href="ins_questions_bank.php" class="nav-link <?php echo $currentPage === 'ins_questions_bank.php' ? 'active' : ''; ?>" style="color: #fff !important;">
                                <i class="fas fa-chevron-right nav-icon"></i>
                                <p>Questions</p>
                            </a>
                        </li>
                        <li class="nav-item" style="color: #fff !important;">
                            <a href="ins_answers_bank.php" class="nav-link <?php echo $currentPage === 'ins_answers_bank.php' ? 'active' : ''; ?>" style="color: #fff !important;">
                                <i class="fas fa-chevron-right nav-icon"></i>
                                <p>Answers</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item" style="color: #fff !important;">
                    <a href="ins_marks.php" class="nav-link <?php echo $currentPage === 'ins_marks.php' ? 'active' : ''; ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-poll-h"></i>
                        <p>
                            Marks Entry
                        </p>
                    </a>
                </li>
                <li class="nav-item" style="color: #fff !important;">
                    <a href="ins_certificates.php" class="nav-link <?php echo $currentPage === 'ins_certificates.php' ? 'active' : ''; ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-certificate"></i>
                        <p>
                            Certificates
                        </p>
                    </a>
                </li>
                <li class="nav-item" style="color: #fff !important;">
                    <a href="ins_students.php" class="nav-link <?php echo $currentPage === 'ins_students.php' ? 'active' : ''; ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-user-graduate"></i>
                        <p>
                            Students
                        </p>
                    </a>
                </li>
                <li class="nav-item has-treeview <?php echo in_array($currentPage, ['ins_reports_allocations.php', 'ins_reports_student_enrollments.php', 'ins_reports_students.php', 'ins_reports_billings.php']) ? 'menu-open' : ''; ?>">
                    <a class="nav-link <?php echo in_array($currentPage, ['ins_reports_allocations.php', 'ins_reports_student_enrollments.php', 'ins_reports_students.php', 'ins_reports_billings.php']) ? 'active' : ''; ?>" style="color: #fff !important;">
                        <i class="nav-icon fas fa-chart-pie"></i>
                        <p>
                            Reports
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item" style="color: #fff !important;">
                            <a href="ins_reports_allocations.php" class="nav-link <?php echo $currentPage === 'ins_reports_allocations.php' ? 'active' : ''; ?>" style="color: #fff !important;">
                                <i class="fas fa-chevron-right nav-icon"></i>
                                <p>Unit Allocations</p>
                            </a>
                        </li>
                        <li class="nav-item" style="color: #fff !important;">
                            <a href="ins_reports_student_enrollments.php" class="nav-link <?php echo $currentPage === 'ins_reports_student_enrollments.php' ? 'active' : ''; ?>" style="color: #fff !important;">
                                <i class="fas fa-chevron-right nav-icon"></i>
                                <p>Unit Enrollments</p>
                            </a>
                        </li>
                        <li class="nav-item" style="color: #fff !important;">
                            <a href="ins_reports_students.php" class="nav-link <?php echo $currentPage === 'ins_reports_students.php' ? 'active' : ''; ?>" style="color: #fff !important;">
                                <i class="fas fa-chevron-right nav-icon"></i>
                                <p>Students</p>
                            </a>
                        </li>

                        <li class="nav-item" style="color: #fff !important;">
                            <a href="ins_reports_billings.php" class="nav-link <?php echo $currentPage === 'ins_reports_billings.php' ? 'active' : ''; ?>" style="color: #fff !important;">
                                <i class="fas fa-chevron-right nav-icon"></i>
                                <p>Billings</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item" style="color: #fff !important;">
                    <a href="ins_logout.php" class="nav-link" style="color: #fff !important;">
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