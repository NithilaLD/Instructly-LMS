<?php
session_start();
include('../config/config.php');
include('../config/checklogin.php');
instructor();
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
            <?php require_once('../partials/ins_navbar.php'); ?>
            <!-- /.navbar -->

            <!-- Main Sidebar Container -->
            <?php require_once('../partials/ins_sidebar.php'); ?>

            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <!-- Main content -->
                <section class="content">
                    <div class="container-fluid">
                        <div class="container">
                            <!-- <div class="text-right text-dark">
                                <a class="btn btn-outline-warning" href="ins_billings.php">Add Study Materials Payments</a>
                            </div> -->
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card-body">
                                    <table id="dash-1" class="table table-striped table-bordered display no-wrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Paid Unit</th>
                                                <th>Payment Code</th>
                                                <th>Amount Paid</th>
                                                <th>Date Paid</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $id = $_SESSION['i_id'];
                                            $ret = "SELECT  * FROM  lms_paid_study_materials WHERE i_id = '$id' ";
                                            $stmt = $mysqli->prepare($ret);
                                            $stmt->execute(); //ok
                                            $res = $stmt->get_result();
                                            while ($payments = $res->fetch_object()) {
                                                $mysqlDateTime = $payments->p_date_paid; ?>
                                                <tr>
                                                    <td><?php echo $payments->c_name; ?></td>
                                                    <td><?php echo $payments->p_code; ?></td>
                                                    <td> Rs. <?php echo $payments->p_amt; ?></td>
                                                    <td><?php echo date("d M Y g:ia", strtotime($mysqlDateTime)); ?></td>
                                                    <td>
                                                        <a class='badge badge-outline-warning' href='ins_view_payment.php?view=<?php echo $payments->psm_id; ?>'>
                                                            <i class='fas fa-external-link-alt'></i>
                                                            View Payment
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