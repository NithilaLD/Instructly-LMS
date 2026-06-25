<?php
    include('../config/config.php');
    include('../config/checklogin.php');
    userRoles(['admin', 'instructor']);
    require_once('../partials/analytics.php');
    
    /* Persist System Settings  */
    $ret = "SELECT * FROM system ";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($sys = $res->fetch_object()) {
        require_once('../partials/head.php');
    ?>

        <body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
            <div class="wrapper">
                <!-- Navbar -->
                <?php require_once('../partials/navbar.php'); ?>
                <!-- /.navbar -->

                <!-- Main Sidebar Container -->
                <?php
                require_once('../partials/sidebar.php');
                $view = isset($_GET['view']) ? (int)$_GET['view'] : 0;
                $stmt = $mysqli->prepare("SELECT  * FROM  users WHERE user_id = ?");
                $stmt->bind_param("i", $view);
                $stmt->execute(); //ok
                $res = $stmt->get_result();
                while ($student = $res->fetch_object()) {
                ?>
                    <!-- Content Wrapper. Contains page content -->
                    <div class="content-wrapper"><br>
                        <!-- Main content -->
                        <section class="content">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-md-3">
                                        <!-- Course Details -->
                                        <div class="card card-warning card-outline">
                                            <div class="card-body box-profile">
                                                <div class="text-center">
                                                    <?php
                                                    if ($student->dpic == '') {
                                                        $dpic = $student->user_code.'.png';
                                                    } else {
                                                        $dpic = $student->dpic;
                                                    } ?>
                                                    <img class="img-fluid img-rectangle" src="../public/sys_data/uploads/users/<?php echo $dpic; ?>" alt="Image">

                                                </div>

                                                <h3 class="profile-username text-center"><?php echo $student->user_code; ?></h3>

                                                <p class="text-muted text-center"><?php echo $student->name; ?></p>
                                                <table class="table mb-3">
                                                    <tbody>
                                                        <tr>
                                                            <th style="width: 25%;padding-left: 0.25rem;">Contact:</th>
                                                            <td style=" word-break: break-word;overflow-wrap: anywhere; padding-right: 0.25rem;"><?php echo htmlspecialchars($student->phone); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th style="width: 25%;padding-left: 0.25rem;">Email:</th>
                                                            <td style=" word-break: break-word;overflow-wrap: anywhere; padding-right: 0.25rem;"><?php echo htmlspecialchars($student->email); ?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="card card-warning card-outline">
                                            <div class="card-header p-2">
                                                <ul class="nav nav-pills">
                                                    <li class="nav-item"><a class="nav-link active" href="#units" data-toggle="tab">Enrolled Courses</a></li>
                                                </ul>
                                            </div>
                                            <div class="card-body">
                                                <div class="tab-content">
                                                    <div class="active tab-pane" id="units">
                                                        <table id="dash-1" class="table table-striped table-bordered display " style="width:100%">
                                                            <thead>
                                                                <tr>
                                                                    <th>Course Code</th>
                                                                    <th>Course</th>
                                                                    <th>Instructor</th>
                                                                    <th>Date Enrolled</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                $stmt = $mysqli->prepare("
                                                                    SELECT
                                                                        c.c_code,
                                                                        c.c_name,
                                                                        ins.name AS instructor,
                                                                        e.en_date
                                                                    FROM enrollments e
                                                                    INNER JOIN courses c ON c.c_id = e.c_id
                                                                    INNER JOIN users ins ON ins.user_id = c.i_id
                                                                    WHERE e.s_id = ?
                                                                ");

                                                                $stmt->bind_param("i", $view);
                                                                $stmt->execute();
                                                                $res = $stmt->get_result();

                                                                while ($enrollments = $res->fetch_object()) {
                                                                ?>
                                                                    <tr>
                                                                        <td><?php echo $enrollments->c_code; ?></td>
                                                                        <td><?php echo $enrollments->c_name; ?></td>
                                                                        <td><?php echo $enrollments->instructor; ?></td>
                                                                        <td><?php echo date('d M Y', strtotime($enrollments->en_date)); ?></td>
                                                                    </tr>
                                                                <?php
                                                                } ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </section>
                    </div>
                    <!-- Main Footer -->
                <?php 
                } ?>
            </div>
            <!-- ./wrapper -->
            <!-- Scripts -->
            <?php require_once('../partials/scripts.php'); ?>
        </body>
        </html>
    <?php
        }
?>