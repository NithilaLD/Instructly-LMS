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
            <div class="content-wrapper"><br>
                <!-- Main content -->
                <section class="content">
                    <div class="container-fluid">

                        <div class="row">
                            <div class="col-md-12">
                                <div class="card-body">
                                    <table id="dash-1" class="table table-striped table-bordered display no-wrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Question Bank Code</th>
                                                <th>Unit Code</th>
                                                <th>Unit Name</th>
                                                <th>Manage Question Bank</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $course = $_GET['course'];
                                            $ret = "SELECT  *  FROM  lms_questions WHERE cc_id = '$course'  ";
                                            $stmt = $mysqli->prepare($ret);
                                            $stmt->execute(); //ok
                                            $res = $stmt->get_result();
                                            while ($questions = $res->fetch_object()) {
                                            ?>
                                                <tr>
                                                    <td><?php echo $questions->q_code; ?></td>
                                                    <td><?php echo $questions->c_code; ?></td>
                                                    <td><?php echo $questions->c_name; ?></td>
                                                    <td>
                                                        <a class="badge badge-outline-warning" href="std_view_bank.php?view=<?php echo $questions->q_id; ?>">
                                                            <i class="fas fa-external-link-alt"></i>
                                                            View Questions
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

            <!-- Main Footer -->
            <?php require_once('../partials/footer.php'); ?>
        </div>
        <!-- ./wrapper -->

        <!-- Scripts -->
        <?php require_once('../partials/scripts.php'); ?>

    </body>

    </html>
<?php
} ?>