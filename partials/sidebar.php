<aside class="main-sidebar sidebar-dark-primary elevation-4" style="background: #193b68ff !important;color: #fff !important;">
    <!-- Brand Logo -->
    <a href="dashboard.php" class="brand-link" style="background: #193b68ff !important;color: #fff !important;border-bottom: 1px solid #ffffff71 !important;">
        <img src="../public/sys_data/logo/<?php echo $sys->sys_logo;?>" alt="Logo" class="brand-image" style="width:15%;">
        <span class="brand-text font-weight-light" style="padding-left: 10px;"><?php echo $sys->sys_name;?></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false" style="color: #fff !important;">
                <!-- <li class="nav-item" style="color: #fff !important;">
                    <a href="../index.php" class="nav-link" style="color: #fff !important;">
                        <i class="nav-icon fas fa-home"></i>
                        <p>
                            Home
                        </p>
                    </a>
                </li> -->
                <li class="nav-item" style="color: #fff !important;">
                    <a href="dashboard.php" class="nav-link" style="color: #fff !important;">
                        <i class="nav-icon fas fa-columns"></i>
                        <p>
                            Dashboard
                        </p>
                    </a>
                </li>
                <li class="nav-item" style="color: #fff !important;">
                    <a href="courses.php" class="nav-link" style="color: #fff !important;">
                        <i class="nav-icon fas fa-book-open"></i>
                        <p>
                            Courses
                        </p>
                    </a>
                </li>
                <li class="nav-item" style="color: #fff !important;">
                    <a href="units.php" class="nav-link" style="color: #fff !important;">
                        <i class="nav-icon fas fa-book-reader"></i>
                        <p>
                            Units
                        </p>
                    </a>
                </li>
                <li class="nav-item" style="color: #fff !important;">
                    <a href="teaching_allocations.php" class="nav-link" style="color: #fff !important;">
                        <i class="nav-icon fas fa-chalkboard-teacher"></i>
                        <p>
                            Teaching Allocations
                        </p>
                    </a>
                </li>
                <li class="nav-item" style="color: #fff !important;">
                    <a href="unit_enrollments.php" class="nav-link" style="color: #fff !important;">
                        <i class="nav-icon fas fa-user-check"></i>
                        <p>
                            Unit Enrollments
                        </p>
                    </a>
                </li>
                <li class="nav-item" style="color: #fff !important;">
                    <a href="billings.php" class="nav-link" style="color: #fff !important;">
                        <i class="nav-icon fas fa-file-invoice-dollar"></i>
                        <p>
                            Billings
                        </p>
                    </a>
                </li>
                <li class="nav-item" style="color: #fff !important;">
                    <a href="study_materials.php" class="nav-link" style="color: #fff !important;">
                        <i class="nav-icon fas fa-file"></i>
                        <p>
                            Study Materials
                        </p>
                    </a>
                </li>
                <li class="nav-item has-treeview" style="color: #fff !important;">
                    <a class="nav-link" style="color: #fff !important;">
                        <i class="nav-icon fas fa-laptop-code"></i>
                        <p>
                            Test Engine
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview" style="color: #fff !important;">
                        <li class="nav-item" style="color: #fff !important;">
                            <a href="questions_bank.php" class="nav-link" style="color: #fff !important;">
                                <i class="fas fa-chevron-right nav-icon"></i>
                                <p>Questions</p>
                            </a>
                        </li>
                        <li class="nav-item" style="color: #fff !important;">
                            <a href="answers_bank.php" class="nav-link" style="color: #fff !important;">
                                <i class="fas fa-chevron-right nav-icon"></i>
                                <p>Answers</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item" style="color: #fff !important;">
                    <a href="marks.php" class="nav-link" style="color: #fff !important;">
                        <i class="nav-icon fas fa-poll-h"></i>
                        <p>
                            Marks Entry
                        </p>
                    </a>
                </li>
                <li class="nav-item" style="color: #fff !important;">
                    <a href="certificates.php" class="nav-link" style="color: #fff !important;">
                        <i class="nav-icon fas fa-certificate"></i>
                        <p>
                            Certificates
                        </p>
                    </a>
                </li>
                <li class="nav-item" style="color: #fff !important;">
                    <a href="instructors.php" class="nav-link" style="color: #fff !important;">
                        <i class="nav-icon fas fa-user-tie"></i>
                        <p>
                            Instructors
                        </p>
                    </a>
                </li>
                <li class="nav-item" style="color: #fff !important;">
                    <a href="students.php" class="nav-link" style="color: #fff !important;">
                        <i class="nav-icon fas fa-user-graduate"></i>
                        <p>
                            Students
                        </p>
                    </a>
                </li>
                <li class="nav-item has-treeview" style="color: #fff !important;">
                    <a class="nav-link" style="color: #fff !important;">
                        <i class="nav-icon fas fa-chart-pie"></i>
                        <p>
                            Reports
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview" style="color: #fff !important;">
                        <li class="nav-item" style="color: #fff !important;">
                            <a href="reports_courses.php" class="nav-link" style="color: #fff !important;">
                                <i class="fas fa-chevron-right nav-icon"></i>
                                <p>Courses</p>
                            </a>
                        </li>
                        <li class="nav-item" style="color: #fff !important;">
                            <a href="reports_units.php" class="nav-link" style="color: #fff !important;">
                                <i class="fas fa-chevron-right nav-icon"></i>
                                <p>Units</p>
                            </a>
                        </li>
                        <li class="nav-item" style="color: #fff !important;">
                            <a href="reports_allocations.php" class="nav-link" style="color: #fff !important;">
                                <i class="fas fa-chevron-right nav-icon"></i>
                                <p>Teaching Allocations</p>
                            </a>
                        </li>
                        <li class="nav-item" style="color: #fff !important;">
                            <a href="reports_student_enrollments.php" class="nav-link" style="color: #fff !important;">
                                <i class="fas fa-chevron-right nav-icon"></i>
                                <p>Student Enrollments</p>
                            </a>
                        </li>
                        <li class="nav-item" style="color: #fff !important;">
                            <a href="reports_students.php" class="nav-link" style="color: #fff !important;">
                                <i class="fas fa-chevron-right nav-icon"></i>
                                <p>Students</p>
                            </a>
                        </li>
                        <li class="nav-item" style="color: #fff !important;">
                            <a href="reports_instructors.php" class="nav-link" style="color: #fff !important;">
                                <i class="fas fa-chevron-right nav-icon"></i>
                                <p>Instructors</p>
                            </a>
                        </li>
                        <li class="nav-item" style="color: #fff !important;">
                            <a href="reports_billings.php" class="nav-link" style="color: #fff !important;">
                                <i class="fas fa-chevron-right nav-icon"></i>
                                <p>Billings</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item" style="color: #fff !important;">
                    <a href="settings.php" class="nav-link" style="color: #fff !important;">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p>
                            System Settings
                        </p>
                    </a>
                </li>
                <li class="nav-item" style="color: #fff !important;">
                    <a href="logout.php" class="nav-link" style="color: #fff !important;">
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