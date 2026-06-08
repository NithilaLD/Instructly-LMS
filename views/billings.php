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
            <div class="content-wrapper"><br>
                <!-- Main content -->
                <section class="content">
                    <div class="container-fluid">
                        <div class="container">
                            <div class="text-right text-dark">
                                <a class="btn btn-outline-warning" href="manage_billings.php">Manage Study Materials Payments</a>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card-body">
                                    <table id="dash-1" class="table table-striped table-bordered display no-wrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Study Material Code</th>
                                                <th>Unit Code</th>
                                                <th>Unit Name</th>
                                                <th>Instructor Name</th>
                                                <th>Verification Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $ret = "SELECT  *  FROM  lms_paid_study_materials ";
                                            $stmt = $mysqli->prepare($ret);
                                            $stmt->execute(); //ok
                                            $res = $stmt->get_result();
                                            while ($paid_study_materials = $res->fetch_object()) {
                                                $verification_status = isset($paid_study_materials->p_verification_status) ? strtolower(trim($paid_study_materials->p_verification_status)) : 'pending';
                                                    if ($verification_status == 'verified') {
                                                        $status_html = '<span class="badge badge-success">VERIFIED</span>';
                                                    } elseif ($verification_status == 'rejected') {
                                                        $status_html = '<span class="badge badge-danger">REJECTED</span>';
                                                    } else {
                                                        $status_html = '<span class="badge badge-warning text-white">PENDING</span>';
                                                    }
                                            ?>
                                                <tr>
                                                    <td><?php echo $paid_study_materials->sm_number; ?></td>
                                                    <td><?php echo $paid_study_materials->c_code; ?></td>
                                                    <td><?php echo $paid_study_materials->c_name; ?></td>
                                                    <td><?php echo $paid_study_materials->i_name; ?></td>
                                                    <td><?php echo $status_html; ?></td>
                                                    <td><a class='badge badge-outline-warning' data-toggle='modal' href='#view-<?php echo $paid_study_materials->psm_id; ?>'><i class='fas fa-file-invoice-dollar'></i> View</a>

                                                        <!-- Add Study Materials Payments Modal -->
                                                        <!-- <div class="modal fade" id="add-">
                                                            <div class="modal-dialog  modal-lg">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h4 class="modal-title">Add Price For Study Materials</h4>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body"> -->
                                                                        <!-- Form -->
                                                                        <!-- <form method="post" enctype="multipart/form-data">

                                                                            <div class="row">
                                                                                <div class="form-group col-md-6">
                                                                                    <label for="c_name">Unit Name</label>
                                                                                    <input type="text" name="c_name" value="" readonly required class="form-control"> -- id="c_name" autocomplete="name">
                                                                                    <!-- Hidden Values -->
                                                                                    <!-- <input type="hidden" name="ls_id" value="" readonly required class="form-control" id="ls_id">
                                                                                </div>

                                                                                <div class="form-group col-md-6">
                                                                                    <label for="sm_number">Study Materials Code</label>
                                                                                    <input type="text" name="sm_number" readonly value="" required class="form-control" id="sm_number" autocomplete="on">
                                                                                </div>

                                                                                <div class="form-group col-md-12">
                                                                                    <label for="sm_price">Study Materials Amount</label>
                                                                                    <input type="text" name="sm_price" required class="form-control" id="sm_price" autocomplete="on">
                                                                                </div>
                                                                            </div>

                                                                            <div class="text-right">
                                                                                <button type="submit" name="add_payment" class="btn btn-outline-warning">Add Price</button>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                    <div class="modal-footer justify-content-between">
                                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div> -->
                                                        <!-- End Modal -->

                                                        <!-- View Study Materials Payment Modal -->
                                                        <div class="modal fade" id="view-<?php echo $paid_study_materials->psm_id; ?>">
                                                            <div class="modal-dialog  modal-lg">
                                                                <div class="modal-content">
                                                                    <div class="modal-header text-center">
                                                                        <h4 class="modal-title">View The Payment for <?php echo $paid_study_materials->c_name; ?> Study Material : <?php echo $paid_study_materials->sm_number; ?></h4>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <!-- Form -->
                                                                        <form method="post" enctype="multipart/form-data">

                                                                            <div class="row">
                                                                                <div class="form-group col-md-6">
                                                                                    <label for="c_name">Unit Name</label>
                                                                                    <input type="text" name="c_name" value="<?php echo $paid_study_materials->c_name; ?>" readonly required class="form-control" id="c_name" autocomplete="name">
                                                                                    <!-- Hidden Values -->
                                                                                    <input type="hidden" name="ls_id" value="<?php echo $paid_study_materials->ls_id; ?>" readonly required class="form-control" id="ls_id">
                                                                                    <input type="hidden" name="c_code" value="<?php echo $paid_study_materials->c_code; ?>" readonly required class="form-control" id="c_code">
                                                                                    <input type="hidden" name="c_id" value="<?php echo $paid_study_materials->c_id; ?>" readonly required class="form-control" id="c_id">
                                                                                    <input type="hidden" name="cc_id" value="<?php echo $paid_study_materials->cc_id; ?>" readonly required class="form-control" id="cc_id">
                                                                                    <input type="hidden" name="c_name" value="<?php echo $paid_study_materials->c_name; ?>" readonly required class="form-control" id="c_name">
                                                                                    <input type="hidden" name="c_category" value="<?php echo $paid_study_materials->c_category; ?>" readonly required class="form-control" id="c_category">
                                                                                    <input type="hidden" name="i_name" value="<?php echo $paid_study_materials->i_name; ?>" readonly required class="form-control" id="i_name">
                                                                                    <input type="hidden" name="i_id" value="<?php echo $paid_study_materials->i_id; ?>" readonly required class="form-control" id="i_id">


                                                                                </div>

                                                                                <div class="form-group col-md-6">
                                                                                    <label for="sm_number">Study Materials Code</label>
                                                                                    <input type="text" name="sm_number" readonly value="<?php echo $paid_study_materials->sm_number; ?>" required class="form-control" id="sm_number" autocomplete="on">
                                                                                </div>

                                                                                <!-- <div class="form-group col-md-6">
                                                                                    <label for="p_amt">Amount To Pay</label>
                                                                                    <input type="text" name="p_amt" readonly value="<?php echo $paid_study_materials->p_amt; ?>" required class="form-control" id="p_amt" autocomplete="on">
                                                                                </div> -->

                                                                                <div class="form-group col-md-6">
                                                                                    <label for="payment_method">Payment Method</label>
                                                                                    <input type="text" name="payment_method" readonly value="<?php echo $paid_study_materials->p_method; ?>" required class="form-control" id="payment_method" autocomplete="on">
                                                                                </div>

                                                                                <div class="form-group col-md-12">
                                                                                    <label for="p_code">Payment Code | Reference Number</label>
                                                                                    <input type="text" name="p_code" readonly value="<?php echo $paid_study_materials->p_code ?>" required class="form-control" id="p_code" autocomplete="on">
                                                                                </div>

                                                                                <div class="form-group col-md-12">
                                                                                    <label for="p_verification_status">Verification Status</label>
                                                                                    <input type="text" name="p_verification_status" readonly value="<?php echo strtoupper(isset($paid_study_materials->p_verification_status) && !empty($paid_study_materials->p_verification_status) ? $paid_study_materials->p_verification_status : 'pending'); ?>" required class="form-control" id="p_verification_status" autocomplete="on">
                                                                                </div>

                                                                                <?php if (isset($paid_study_materials->p_verification_status) && strtolower($paid_study_materials->p_verification_status) == 'rejected' && !empty($paid_study_materials->p_rejection_reason)) { ?>
                                                                                    <div class="form-group col-md-12">
                                                                                        <label for="field_321">Rejection Reason</label>
                                                                                        <textarea readonly class="form-control" rows="3" id="field_321" autocomplete="on"><?php echo $paid_study_materials->p_rejection_reason; ?></textarea>
                                                                                    </div>
                                                                                <?php } ?>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                    <div class="modal-footer justify-content-between">
                                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- End Modal -->

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
