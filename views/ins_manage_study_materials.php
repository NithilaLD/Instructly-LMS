<?php
session_start();
include('../config/config.php');
include('../config/checklogin.php');
instructor();
require_once('../config/codeGen.php');

/* Update Study Materials */
if (isset($_POST['update_studymaterial'])) {
    //Error Handling and prevention of posting double entries
    $error = 0;


    if (isset($_POST['sm_number']) && !empty($_POST['sm_number'])) {
        $sm_number = mysqli_real_escape_string($mysqli, trim($_POST['sm_number']));
    } else {
        $error = 1;
        $err = "Study Material Cannot Be Empty";
    }

    if (isset($_POST['ls_id']) && !empty($_POST['ls_id'])) {
        $ls_id = mysqli_real_escape_string($mysqli, trim($_POST['ls_id']));
    } else {
        $error = 1;
        $err = "Study Material ID Cannot Be Empty";
    }

    // Upload study materials with unique filename
    $sm_materials = '';
    if (isset($_FILES["sm_materials"]) && $_FILES["sm_materials"]["error"] == 0) {
        $original_name = basename($_FILES["sm_materials"]["name"]);
        $ext = pathinfo($original_name, PATHINFO_EXTENSION);
        $unique_name = 'mat_' . uniqid() . '.' . $ext;
        $upload_path = "../public/sys_data/uploads/study_materials/" . $unique_name;
        if (move_uploaded_file($_FILES["sm_materials"]["tmp_name"], $upload_path)) {
            $sm_materials = $unique_name;
        } else {
            $error = 1;
            $err = "File upload failed.";
        }
    } else {
        $error = 1;
        $err = "No file uploaded.";
    }

    if (!$error) {
        $query = "UPDATE lms_study_material SET sm_materials =?, sm_number = ? WHERE ls_id = ?";
        $stmt = $mysqli->prepare($query);
        $rc = $stmt->bind_param('sss', $sm_materials, $sm_number, $ls_id);
        $stmt->execute();
        if ($stmt) {
            $success = "Updated successfully";
            header("refresh:1; url=ins_manage_study_materials.php");
        } else {
            $info = "Please Try Again Or Try Later";
        }
    }
}

/* Delete Study Materials */
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $adn = "DELETE FROM lms_study_material WHERE ls_id= '$id' ";
    $stmt = $mysqli->prepare($adn);
    $stmt->execute();
    $stmt->close();
    if ($stmt) {
        $success = "Deleted" && header("refresh:1; url=ins_manage_study_materials.php");
    } else {
        $info = "Please Try Again Or Try Later";
    }
}

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
                            <!-- Add button hidden as requested -->
                        </div>
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
                                            $id = $_SESSION['i_id'];
                                            $ret = "SELECT  *  FROM  lms_study_material  WHERE i_id = '$id' ORDER BY ls_id ASC ";
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
                                                        <?php
                                                        $file_url = '../public/sys_data/uploads/study_materials/' . $study_materials->sm_materials;
                                                        ?>
                                                        <a class="badge badge-outline-warning" target="_blank" href="<?php echo $file_url; ?>">
                                                            <i class="fas fa-file-download"></i> View
                                                        </a>
                                                        <!-- <a class="badge badge-outline-warning" data-toggle="modal" href="#edit-<?php echo $study_materials->ls_id; ?>">
                                                            <i class="fas fa-pencil-alt"></i>
                                                            Update
                                                        </a> -->
                                                        <!-- Upload Study Materials Modal -->
                                                        <div class="modal fade" id="edit-<?php echo $study_materials->ls_id; ?>">
                                                            <div class="modal-dialog  modal-lg">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h4 class="modal-title">Updated Study Materials For <?php echo $study_materials->c_name; ?></h4>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <!-- Form -->
                                                                        <form method="post" enctype="multipart/form-data">

                                                                            <div class="row">
                                                                                <div class="form-group col-md-12">
                                                                                    <label for="sm_number">Study Materials Code</label>
                                                                                    <input type="text" name="sm_number" value="<?php echo $study_materials->sm_number; ?>" readonly required class="form-control" id="sm_number" autocomplete="on">
                                                                                    <input type="hidden" name="ls_id" value="<?php echo $study_materials->ls_id; ?>" readonly required class="form-control" id="ls_id">
                                                                                </div>
                                                                            </div>
                                                                            <div class="row">
                                                                                <div class="form-group col-md-12">
                                                                                    <label for="exampleInputFile">Upload Study Materials Either in A (.pdf, .docx, .pptx) Formart </label>
                                                                                    <div class="input-group">
                                                                                        <div class="custom-file">
                                                                                            <input required name="sm_materials" type="file" class="custom-file-input" id="exampleInputFile">
                                                                                            <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <hr>
                                                                            <div class="text-right">
                                                                                <button type="submit" name="update_studymaterial" class="btn btn-outline-warning">Update Materials</button>
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
        <script>
        $(function() {
            if (!$.fn.DataTable.isDataTable('#dash-1')) {
                $('#dash-1').DataTable({
                    "order": []
                });
            }
        });
        </script>
    </body>
</html>
<?php } ?>
