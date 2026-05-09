<?php
session_start();
include('../config/config.php');
include('../config/checklogin.php');
admin();
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
            <?php require_once('../partials/navbar.php'); ?>
            <!-- /.navbar -->

            <!-- Main Sidebar Container -->
            <?php require_once('../partials/sidebar.php'); ?>

            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <!-- Main content -->
                <section class="content">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card-body">
                                    <table id="reports" class="table table-striped table-bordered display no-wrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Paid Unit</th>
                                                <th>Payment Code</th>
                                                <th>Amount Paid</th>
                                                <th>Date Paid</th>
                                                <th>Verification Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $ret = "SELECT  * FROM  lms_paid_study_materials ";
                                            $stmt = $mysqli->prepare($ret);
                                            $stmt->execute(); //ok
                                            $res = $stmt->get_result();
                                            while ($payments = $res->fetch_object()) {
                                                $mysqlDateTime = $payments->p_date_paid; ?>
                                                <?php
                                                $verification_status = isset($payments->p_verification_status) ? strtolower(trim($payments->p_verification_status)) : 'pending';
                                                if ($verification_status == 'verified') {
                                                    $status_html = '<span class="badge badge-success">VERIFIED</span>';
                                                } elseif ($verification_status == 'rejected') {
                                                    $status_html = '<span class="badge badge-danger">REJECTED</span>';
                                                } else {
                                                    $status_html = '<span class="badge badge-warning text-white">PENDING</span>';
                                                }
                                                ?>
                                                <tr>
                                                    <td><?php echo $payments->c_name; ?></td>
                                                    <td><?php echo $payments->p_code; ?></td>
                                                    <td>Rs. <?php echo $payments->p_amt; ?></td>
                                                    <td><?php echo date("d M Y g:ia", strtotime($mysqlDateTime)); ?></td>
                                                    <td><?php echo $status_html; ?></td>
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