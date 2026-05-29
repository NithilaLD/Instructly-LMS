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
                        <div class="container">
                            <div class="text-right text-dark">
                                <a class="btn btn-outline-warning" href="std_billings.php">Pay for Study Materials</a>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card-body">
                                    <table id="dash-1" class="table table-striped table-bordered display no-wrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Paid Unit</th>
                                                <th>Material</th>
                                                <th>Payment Code</th>
                                                <th>Amount Paid</th>
                                                <th>Verification Status</th>
                                                <th>Date Paid</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $id = $_SESSION['s_id'];
                                            $ret = "SELECT pm.*, sm.sm_number AS material_number, sm.sm_materials AS material_file
                                                    FROM lms_paid_study_materials pm
                                                    LEFT JOIN lms_study_material sm ON pm.ls_id = sm.ls_id
                                                    WHERE pm.s_id = '$id' ORDER BY pm.p_date_paid DESC";
                                            $stmt = $mysqli->prepare($ret);
                                            $stmt->execute();
                                            $res = $stmt->get_result();
                                            while ($payments = $res->fetch_object()) {
                                                $mysqlDateTime = $payments->p_date_paid;

                                                // Status badge
                                                if ($payments->p_verification_status == 'pending') {
                                                    $status_html = '<span class="badge badge-warning text-white">⏳ PENDING</span><br><small>Waiting for Approval</small>';
                                                } elseif ($payments->p_verification_status == 'verified') {
                                                    $status_html = '<span class="badge badge-success">✓ VERIFIED</span><br><small>Access granted</small>';
                                                } elseif ($payments->p_verification_status == 'rejected') {
                                                    $status_html = '<span class="badge badge-danger">✗ REJECTED</span><br><small>' . $payments->p_rejection_reason . '</small>';
                                                } else {
                                                    $status_html = '<span class="badge badge-secondary">UNKNOWN</span>';
                                                }
                                            ?>
                                                <tr>
                                                    <td><?php echo $payments->c_name; ?></td>
                                                    <td><?php echo !empty($payments->material_number) ? $payments->material_number : (isset($payments->sm_number) ? $payments->sm_number : '-'); ?></td>
                                                    <td><?php echo $payments->p_code; ?></td>
                                                    <td>Rs. <?php echo $payments->p_amt; ?></td>
                                                    <td><?php echo $status_html; ?></td>
                                                    <td><?php echo date("d M Y g:ia", strtotime($mysqlDateTime)); ?></td>
                                                    <td>
                                                        <a class='badge badge-outline-warning' href='std_get_receipt.php?view=<?php echo $payments->psm_id; ?>'>
                                                            <i class='fas fa-external-link-alt'></i>
                                                            View Details
                                                        </a>
                                                        <?php if ($payments->p_verification_status == 'rejected') { ?>
                                                            <a class='badge badge-outline-primary' href='add_payments.php?view=<?php echo $payments->c_id; ?>&retry=<?php echo $payments->psm_id; ?>'>
                                                                <i class='fas fa-redo'></i>
                                                                Resubmit Payment
                                                            </a>
                                                        <?php } ?>
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