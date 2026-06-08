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
            <div class="content-wrapper"><br>
                <!-- Main content -->
                <section class="content">
                    <div class="container-fluid">
                        <div class="container">
                            <div class="text-right text-dark">
                                <a class="btn btn-outline-warning" href="ins_manage_billings.php">Manage Study Materials Payments</a>
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
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $id = $_SESSION['i_id'];
                                            // use the session id variable ($id) in the query
                                            $ret = "SELECT  *  FROM  lms_study_material WHERE i_id ='$id' ";
                                            $stmt = $mysqli->prepare($ret);
                                            $stmt->execute(); //ok
                                            $res = $stmt->get_result();
                                            while ($study_materials = $res->fetch_object()) {
                                                $lsid = $study_materials->ls_id;
                                                $ret1 = "SELECT  *  FROM  lms_paid_study_materials WHERE ls_id ='$lsid' ";
                                                $stmt1 = $mysqli->prepare($ret1);
                                                $stmt1->execute(); //ok
                                                $res1 = $stmt1->get_result();
                                                $paid_study_materials = $res1->fetch_object();
                                                if (!empty($paid_study_materials)) {
                                                // if ($study_materials->sm_price != '') {
                                            ?>
                                                <tr>
                                                    <td><?php echo $study_materials->sm_number; ?></td>
                                                    <td><?php echo $study_materials->c_code; ?></td>
                                                    <td><?php echo $study_materials->c_name; ?></td>
                                                    <td><?php echo $study_materials->i_name; ?></td>
                                                    <td><a class='badge badge-outline-warning' data-toggle='modal' href='#view-<?php echo $study_materials->ls_id; ?>'><i class='fas fa-file-invoice-dollar'></i> View</a>
                                            <?php } ?>
                                                        <!-- View Study Materials Payment Modal -->
                                                        <div class="modal fade" id="view-<?php echo $study_materials->ls_id; ?>">
                                                            <div class="modal-dialog  modal-lg">
                                                                <div class="modal-content">
                                                                    <div class="modal-header text-center">
                                                                        <h4 class="modal-title">View The Payment for <?php echo $study_materials->c_name; ?> Study Material : <?php echo $study_materials->sm_number; ?></h4>
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
                                                                                    <input type="text" name="c_name" id="c_name" value="<?php echo $study_materials->c_name; ?>" readonly required class="form-control" autocomplete="name">
                                                                                    <!-- Hidden Values -->
                                                                                    <input type="hidden" name="ls_id" value="<?php echo $study_materials->ls_id; ?>" readonly required class="form-control" id="ls_id">
                                                                                    <input type="hidden" name="c_code" value="<?php echo $study_materials->c_code; ?>" readonly required class="form-control" id="c_code">
                                                                                    <input type="hidden" name="c_id" value="<?php echo $study_materials->c_id; ?>" readonly required class="form-control" id="c_id">
                                                                                    <input type="hidden" name="cc_id" value="<?php echo $study_materials->cc_id; ?>" readonly required class="form-control" id="cc_id">
                                                                                    <input type="hidden" name="c_name" value="<?php echo $study_materials->c_name; ?>" readonly required class="form-control" id="c_name">
                                                                                    <input type="hidden" name="c_category" value="<?php echo $study_materials->c_category; ?>" readonly required class="form-control" id="c_category">
                                                                                    <input type="hidden" name="i_name" value="<?php echo $study_materials->i_name; ?>" readonly required class="form-control" id="i_name">
                                                                                    <input type="hidden" name="i_id" value="<?php echo $study_materials->i_id; ?>" readonly required class="form-control" id="i_id">


                                                                                </div>

                                                                                <div class="form-group col-md-6">
                                                                                    <label for="sm_number">Study Materials Code</label>
                                                                                    <input type="text" name="sm_number" id="sm_number" readonly value="<?php echo $study_materials->sm_number; ?>" required class="form-control" autocomplete="on">
                                                                                </div>

                                                                                

                                                                                <div class="form-group col-md-6">
                                                                                    <label for="payment_method">Payment Method</label>
                                                                                    <input type="text" name="payment_method" id="payment_method" readonly value="<?php echo $paid_study_materials->p_method; ?>" required class="form-control" autocomplete="on">
                                                                                </div>

                                                                                <div class="form-group col-md-12">
                                                                                    <label for="p_code">Payment Code | Reference Number</label>
                                                                                    <input type="text" name="p_code" id="p_code" readonly value="<?php echo $paid_study_materials->p_code ?>" required class="form-control" autocomplete="on">
                                                                                </div>
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
