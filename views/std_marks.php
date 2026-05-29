<?php
session_start();
include('../config/config.php');
include('../config/checklogin.php');
student();
require_once('../config/codeGen.php');



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
            <div class="content-wrapper">
                <!-- Main content -->
                <section class="content">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-12">

                                <div class="card-body">
                                    <table id="dash-2" class="table table-striped table-bordered display" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Entry Code</th>
                                                <th>Unit Code</th>
                                                <th>Unit Name</th>
                                                <th>Std Admn</th>
                                                <th>Std Name</th>
                                                <th>Date Added</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $id = $_SESSION['s_id'];
                                            $ret = "SELECT  * FROM  lms_results WHERE s_id = '$id' ";
                                            $stmt = $mysqli->prepare($ret);
                                            $stmt->execute(); //ok
                                            $res = $stmt->get_result();
                                            while ($results = $res->fetch_object()) {
                                                $mysqlDateTime = $results->c_date_added; ?>
                                                <tr>
                                                    <td><?php echo $results->rs_code; ?></td>
                                                    <td><?php echo $results->s_unit_code; ?></td>
                                                    <td><?php echo $results->s_unit_name; ?></td>
                                                    <td><?php echo $results->s_regno; ?></td>
                                                    <td><?php echo $results->s_name; ?>
                                                    <td><?php echo date("d M Y", strtotime($mysqlDateTime)); ?></td>
                                                    <td>
                                                        <a class="badge badge-outline-warning" href="std_view_transcript.php?view=<?php echo $results->rs_id; ?>">
                                                            <i class="fas fa-external-link-alt"></i>
                                                            View
                                                        </a>


                                                    </td>
                                                </tr>

                                            <?php
                                            } ?>

                                        </tbody>
                                    </table>
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