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
                                            $id = $_GET['view'];
                                            // Only show materials where the payment has been VERIFIED
                                            $ret = "SELECT sm.* FROM lms_study_material sm
                                                    INNER JOIN lms_paid_study_materials pm ON sm.ls_id = pm.ls_id
                                                    WHERE sm.c_id = '$id' 
                                                    AND pm.s_id = " . $_SESSION['s_id'] . "
                                                    AND pm.p_verification_status = 'verified'
                                                    ORDER BY sm.ls_id ASC";
                                            $stmt = $mysqli->prepare($ret);
                                            $stmt->execute();
                                            $res = $stmt->get_result();
                                            
                                            if ($res->num_rows == 0) {
                                                echo '<tr><td colspan="6" class="text-center text-muted"><br><strong>No verified materials available yet.</strong><br><br></td></tr>';
                                            }
                                            
                                            while ($study_materials = $res->fetch_object()) {
                                            ?>
                                                <tr>
                                                    <td><?php echo $study_materials->sm_number; ?></td>
                                                    <td><?php echo $study_materials->c_category; ?></td>
                                                    <td><?php echo $study_materials->c_code; ?></td>
                                                    <td><?php echo $study_materials->c_name; ?></td>
                                                    <td><?php echo $study_materials->i_name; ?></td>
                                                    <td>
                                                        <a class="badge badge-outline-warning" data-toggle="modal" href="#download-<?php echo $study_materials->ls_id; ?>">
                                                            <i class="fas fa-file-download"></i>
                                                            View
                                                        </a>
                                                        <div class="modal fade" id="download-<?php echo $study_materials->ls_id; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="exampleModalLabel">CONFIRM ACTION</h5>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body text-center text-danger">
                                                                        <h4>View <?php echo $study_materials->sm_number; ?>?</h4>
                                                                        <br>
                                                                        <button type="button" class="text-center btn btn-outline-warning" data-dismiss="modal">No</button>
                                                                        <a target="_blank" href="../public/sys_data/uploads/study_materials/<?php echo $study_materials->sm_materials; ?>" class="text-center btn btn-outline-warning"> View </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

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