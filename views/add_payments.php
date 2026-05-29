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
        require_once('../partials/head.php'); 
?>
        <body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
            <div class="wrapper">
                <!-- Navbar -->
                <?php require_once('../partials/std_navbar.php'); ?>
                <!-- /.navbar -->
                <!-- Main Sidebar Container -->
                <?php
                    require_once('../partials/std_sidebar.php');
                    $id = $_SESSION['s_id'];
                    $ret = "SELECT * FROM `lms_student` WHERE s_id = '$id' ";
                    $stmt = $mysqli->prepare($ret);
                    $stmt->execute(); //ok
                    $res = $stmt->get_result();
                    while ($loggedInUser = $res->fetch_object()) {
                ?>
                    <!-- Content Wrapper. Contains page content -->
                    <div class="content-wrapper">
                        <!-- Main content -->
                        <section class="content">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card-body">
                                            <table id="dash-1" class="table table-striped table-bordered display no-wrap" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>Code</th>
                                                        <th>Course</th>
                                                        <th>Unit Code</th>
                                                        <th>Unit Name</th>
                                                        <th>Instructor Name</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                        $id = isset($_GET['view']) ? $_GET['view'] : '';
                                                        $retry = isset($_GET['retry']) ? $_GET['retry'] : '';

                                                        if (!empty($retry)) {
                                                            // Show the specific study material related to the rejected payment so student can resubmit
                                                            $psm_id = mysqli_real_escape_string($mysqli, $retry);
                                                            $ret = "SELECT sm.* FROM lms_study_material sm INNER JOIN lms_paid_study_materials pm ON sm.ls_id = pm.ls_id WHERE pm.psm_id = '$psm_id' ";
                                                        } else {
                                                            // Default: show unpaid study materials for the course/unit
                                                            $id_esc = mysqli_real_escape_string($mysqli, $id);
                                                            $ret = "SELECT  *  FROM  lms_study_material WHERE c_id = '$id_esc' && payment_status = 'Unpaid' ";
                                                        }
                                                        $stmt = $mysqli->prepare($ret);
                                                        $stmt->execute(); //ok
                                                        $res = $stmt->get_result();
                                                        while ($study_materials = $res->fetch_object()) {
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $study_materials->sm_number; ?></td>
                                                        <td><?php echo $study_materials->c_category; ?></td>
                                                        <td><?php echo $study_materials->c_code; ?></td>
                                                        <td><?php echo $study_materials->c_name; ?></td>
                                                        <td><?php echo $study_materials->i_name; ?></td>
                                                        <td>
                                                            <a class="badge badge-outline-warning" data-toggle="modal" href="#pay-<?php echo $study_materials->ls_id; ?>">
                                                                <i class="fas fa-file-invoice-dollar"></i>
                                                                Make Payment
                                                            </a>
                                                            <div class="modal fade" id="pay-<?php echo $study_materials->ls_id; ?>">
                                                                <div class="modal-dialog  modal-lg">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header text-center">
                                                                            <h4 class="modal-title">Pay For <?php echo $study_materials->c_name; ?> Study Materials Number : <?php echo $study_materials->sm_number; ?></h4>
                                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                <span aria-hidden="true">&times;</span>
                                                                            </button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <!-- Form -->
                                                                            <form action="../payment/checkout.php" method="post" enctype="multipart/form-data">
                                                                                <div class="row">
                                                                                    <div class="form-group col-md-6">
                                                                                        <label for="c_name">Unit Name</label>
                                                                                        <input type="text" name="c_name" value="<?php echo $study_materials->c_name; ?>" readonly required class="form-control" id="c_name" autocomplete="name">
                                                                                        <!-- Hidden Values -->
                                                                                        <input type="hidden" name="ls_id" value="<?php echo $study_materials->ls_id; ?>" readonly required class="form-control" id="ls_id">
                                                                                        <input type="hidden" name="c_code" value="<?php echo $study_materials->c_code; ?>" readonly required class="form-control" id="c_code">
                                                                                        <input type="hidden" name="c_id" value="<?php echo $study_materials->c_id; ?>" readonly required class="form-control" id="c_id">
                                                                                        <input type="hidden" name="cc_id" value="<?php echo $study_materials->cc_id; ?>" readonly required class="form-control" id="cc_id">
                                                                                        <input type="hidden" name="c_name" value="<?php echo $study_materials->c_name; ?>" readonly required class="form-control" id="c_name">
                                                                                        <input type="hidden" name="c_category" value="<?php echo $study_materials->c_category; ?>" readonly required class="form-control" id="c_category">
                                                                                        <input type="hidden" name="i_name" value="<?php echo $study_materials->i_name; ?>" readonly required class="form-control" id="i_name">
                                                                                        <input type="hidden" name="i_id" value="<?php echo $study_materials->i_id; ?>" readonly required class="form-control" id="i_id">
                                                                                        <input type="hidden" name="s_id" value="<?php echo $loggedInUser->s_id; ?>" readonly required class="form-control" id="s_id">
                                                                                        <input type="hidden" name="s_name" value="<?php echo $loggedInUser->s_name; ?>" readonly required class="form-control" id="s_name">
                                                                                        <input type="hidden" name="s_regno" value="<?php echo $loggedInUser->s_regno; ?>" readonly required class="form-control" id="s_regno">
                                                                                        <input type="hidden" name="s_email" value="<?php echo $loggedInUser->s_email; ?>" readonly required class="form-control" id="s_email">
                                                                                        <input type="hidden" name="s_phoneno" value="<?php echo $loggedInUser->s_phoneno; ?>" readonly required class="form-control" id="s_phoneno">
                                                                                        <input type="hidden" name="payment_method" value="Credit/Debit Card" readonly required class="form-control" id="payment_method">
                                                                                    </div>
                                                                                    <div class="form-group col-md-6">
                                                                                        <label for="sm_number">Study Materials Code</label>
                                                                                        <input type="text" name="sm_number" readonly value="<?php echo $study_materials->sm_number; ?>" required class="form-control" id="sm_number" autocomplete="on">
                                                                                    </div>
                                                                                    <div class="form-group col-md-6">
                                                                                        <label for="p_amt">Amount</label>
                                                                                        <input type="text" name="p_amt" readonly value="<?php echo $study_materials->sm_price; ?>" required class="form-control" id="p_amt" autocomplete="on">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="text-right">
                                                                                    <button type="submit" name="pay_for_reading_material" class="btn btn-outline-warning">Pay</button>
                                                                                </div>
                                                                            </form>
                                                                        </div>
                                                                        <div class="modal-footer justify-content-between">
                                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <?php
                                                        } 
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                <?php
                    } 
                ?>
            </div>
            <!-- ./wrapper -->
            <!-- Scripts -->
            <?php require_once('../partials/scripts.php'); ?>
        </body>
    </html>
<?php
    } 
?>
