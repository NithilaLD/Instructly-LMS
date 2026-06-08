<?php
session_start();
include('../config/config.php');
include('../config/checklogin.php');
student();
require_once('../partials/std_analytics.php');
/* Persist System Settings  */
$ret = "SELECT * FROM `lms_sys_setttings` ";
$stmt = $mysqli->prepare($ret);
$stmt->execute(); //ok
$res = $stmt->get_result();
while ($sys = $res->fetch_object()) {
    require_once('../partials/head.php'); ?>

    <body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
        <div class="wrapper">
            <!-- Navbar -->
            <?php require_once('../partials/std_navbar.php'); ?>
            <!-- /.navbar -->

            <!-- Main Sidebar Container -->
            <?php require_once('../partials/std_sidebar.php'); ?>

            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper"><br>
                <!-- Main content -->
                <section class="content">
                    <div class="container-fluid">
                        <!-- Info boxes -->
                        <div class="row">
                            <!-- fix for small devices only -->
                            <div class="clearfix hidden-md-up"></div>

                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="info-box mb-3">
                                    <span class="info-box-icon bg-outline-warning elevation-1"><i class="fas fa-user-check"></i></span>

                                    <div class="info-box-content">
                                        <span class="info-box-text">Completed Courses</span>
                                        <span class="info-box-number"><?php echo $students_enrollments ?? 0; ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="info-box mb-3">
                                    <span class="info-box-icon bg-outline-warning elevation-1"><i class="fas fa-user-check"></i></span>

                                    <div class="info-box-content">
                                        <span class="info-box-text">On Going Courses </span>
                                        <span class="info-box-number"><?php echo $complete_courses ?? 0; ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="info-box mb-3">
                                    <span class="info-box-icon bg-outline-warning elevation-1"><i class="fas fa-file-invoice-dollar"></i></span>

                                    <div class="info-box-content">
                                        <span class="info-box-text">Payements Did</span>
                                        <span class="info-box-number">Rs. <?php echo isset($paid_bills) ? $paid_bills : '0'; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.row -->

                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">My Enrollement Reports Recarp</h5>
                                    </div>
                                    <div class="card-body">
                                        <table id="dash-1" class="table table-striped table-bordered display" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Unit Code</th>
                                                    <th>Unit Name</th>
                                                    <th>Instructor Name</th>
                                                    <th>Enroll date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $id = $_SESSION['s_id'];
                                                $ret = "SELECT  * FROM  lms_enrollments WHERE s_id = '$id' ";
                                                $stmt = $mysqli->prepare($ret);
                                                $stmt->execute(); //ok
                                                $res = $stmt->get_result();
                                                while ($row = $res->fetch_object()) {
                                                ?>
                                                    <tr>
                                                        <td><?php echo $row->s_unit_code; ?></td>
                                                        <td><?php echo $row->s_unit_name; ?></td>
                                                        <td><?php echo $row->i_name; ?></td>
                                                        <td><?php echo date("d M Y", strtotime($row->en_date)); ?></td>
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
                </section>
            </div>

            </div>
        <!-- ./wrapper -->

        <!-- Scripts -->
        <?php require_once('../partials/scripts.php'); ?>

    </body>

    </html>
<?php
} ?>