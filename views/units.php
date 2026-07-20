<?php
    include('../config/config.php');
    require_once('../config/audit.php');
    include('../config/checklogin.php');
    userRoles(['admin', 'instructor','student']);
    require_once('../config/codeGen.php');
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];
    
    /* Add Units */
    if (isset($_POST['add_unit']) && ($role == 'instructor' || $role == 'admin')) {
        //Error Handling and prevention of posting double entries
        $error = 0;
        // initialize variables to avoid undefined variable notices
        $u_code = $u_name = $u_desc = '';
        $c_id = $_POST['c_id'];

        if (isset($_POST['u_code']) && !empty($_POST['u_code'])) {
            $u_code = mysqli_real_escape_string($mysqli, trim($_POST['u_code']));
        } else {
            $error = 1;
            $err = "Code Cannot Be Empty";
        }
        if (isset($_POST['u_name']) && !empty($_POST['u_name'])) {
            $u_name = mysqli_real_escape_string($mysqli, trim($_POST['u_name']));
        } else {
            $error = 1;
            $err = "Name Cannot Be Empty";
        }

        if (isset($_POST['u_desc']) && !empty($_POST['u_desc'])) {
            $u_desc = mysqli_real_escape_string($mysqli, trim($_POST['u_desc']));
        } else {
            $error = 1;
            $err = "Description Cannot Be Empty";
        }
        $a_id = $_SESSION['user_id'];
        if (!$error) {
            //prevent Double entries
            $stmt = $mysqli->prepare("SELECT * FROM units WHERE u_code = ? AND status <> 'deleted'");
            $stmt->bind_param("s", $u_code);
            $stmt->execute();
            $res = $stmt->get_result();
            if (mysqli_num_rows($res) > 0) {
                $row = mysqli_fetch_assoc($res);
                if ($u_code == $row['u_code']) {
                    $err =  "A Unit With $u_code Exists";
                }
            } else {
                $query = "INSERT INTO units (c_id, a_id, u_name, u_code, u_desc) VALUES (?,?,?,?,?)";
                $stmt = $mysqli->prepare($query);
                $rc = $stmt->bind_param('sssss', $c_id, $a_id, $u_name, $u_code, $u_desc);
                $stmt->execute();
                if ($stmt) {
                    logAuditAction($mysqli, 'add_unit', 'Unit created', 'units', 'unit', $u_code);
                    $_SESSION['flash_success'] = "Unit added successfully.";
                    header("Location: units.php");
                } else {
                    $_SESSION['flash_error'] = "Failed to add unit.";
                    header("Location: units.php");
                }
            }
        }
    }

    /* Update Unit */
    if (isset($_POST['update_unit']) && ($role == 'instructor' || $role == 'admin')) {
        //Error Handling and prevention of posting double entries
        $error = 0;


        if (isset($_POST['u_code']) && !empty($_POST['u_code'])) {
            $u_code = mysqli_real_escape_string($mysqli, trim($_POST['u_code']));
        } else {
            $error = 1;
            $err = "Code Cannot Be Empty";
        }
        if (isset($_POST['u_name']) && !empty($_POST['u_name'])) {
            $u_name = mysqli_real_escape_string($mysqli, trim($_POST['u_name']));
        } else {
            $error = 1;
            $err = "Name Cannot Be Empty";
        }

        if (isset($_POST['u_desc']) && !empty($_POST['u_desc'])) {
            $u_desc = mysqli_real_escape_string($mysqli, trim($_POST['u_desc']));
        } else {
            $error = 1;
            $err = "Description Cannot Be Empty";
        }

        if (isset($_POST['u_id']) && !empty($_POST['u_id'])) {
            $u_id = mysqli_real_escape_string($mysqli, trim($_POST['u_id']));
        } else {
            $error = 1;
            $err = "Unit ID Missing";
        }

        if (!$error) {

            $query = "UPDATE units SET u_name =?, u_code =?, u_desc =? WHERE u_id = ?";
            $stmt = $mysqli->prepare($query);
            $rc = $stmt->bind_param('ssss', $u_name, $u_code, $u_desc, $u_id);
            $stmt->execute();
            if ($stmt) {
                logAuditAction($mysqli, 'update_unit', 'Unit updated', 'units', 'unit', (string) $u_id);
                $_SESSION['flash_success'] = "Unit updated successfully.";
                header("Location: units.php");
            } else {
                $_SESSION['flash_error'] = "Failed to update unit.";
                header("Location: units.php");
            }
        }
    }

    /* Delete Unit */
    if (isset($_GET['delete']) && $role == 'admin') {
        $id = (int) $_GET['delete'];
        $adn = "UPDATE units SET status = 'deleted' WHERE u_id = ?";
        $stmt = $mysqli->prepare($adn);
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            logAuditAction($mysqli, 'delete_unit', 'Unit deleted', 'units', 'unit', (string) $id);
            $_SESSION['flash_success'] = "Unit deleted successfully.";
            header("Location: units.php");
        } else {
            $_SESSION['flash_error'] = "Failed to delete unit.";
            header("Location: units.php");
        }
    }

    /* Persist System Settings  */
    $ret = "SELECT * FROM system ";
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
                    <section class="content <?php if ($role == 'student'): ?> pt-3 <?php endif; ?>">
                        <div class="container-fluid">
                        <?php if ($role == 'instructor' || $role == 'admin'): ?>
                            <div class="container" style="padding-top: 10px !important;">
                                <div class="text-right text-dark">
                                    <button type="button" class="btn btn-outline-warning" data-toggle="modal" data-target="#add-modal">Add Unit</button>
                                    <a class="btn btn-outline-warning" href="addunits.php">
                                        Add Units Bulk
                                    </a>
                                </div>
                            </div>
                            <hr>
                        <?php endif; ?>
                            <div class="card">
                                <div class="col-md-12">
                                <?php if ($role == 'instructor' || $role == 'admin'): ?>
                                    <!-- Add   Modal -->
                                    <div class="modal fade" id="add-modal">
                                        <div class="modal-dialog  modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">Fill All Given Fields</h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <!-- Form -->
                                                    <form method="post" enctype="multipart/form-data">
                                                        <div class="row">
                                                            <div class="form-group col-md-6">
                                                                <label for="Course_Code">Course Code</label>
                                                                <select name="c_code" style="width: 100%;" onchange="GetCourseDetails(this.value)" id="Course_Code" required class="form-control select2bs4">
                                                                    <option>Select Course</option>
                                                                    <?php
                                                                    $ret = "SELECT c_id, c_code, c_name FROM courses WHERE status <> 'deleted'";
                                                                    $stmt = $mysqli->prepare($ret);
                                                                    $stmt->execute(); //ok
                                                                    $res = $stmt->get_result();
                                                                    while ($courses = $res->fetch_object()) {
                                                                    ?>
                                                                        <option><?php echo $courses->c_code; ?></option>
                                                                    <?php
                                                                    } ?>
                                                                </select>
                                                            </div>
                                                            <div class="form-group col-md-6">
                                                                <label for="Course_Name">Course Name</label>
                                                                <input type="text" id="Course_Name" required class="form-control" autocomplete="on">
                                                                <input type="hidden" name="c_id" id="Course_Id" required class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="form-group col-md-6">
                                                                <label for="u_name">Unit Name</label>
                                                                <input type="text" name="u_name" required class="form-control" id="u_name" autocomplete="name">
                                                            </div>
                                                            <div class="form-group col-md-6">
                                                                <label for="u_code">Unit Code</label>
                                                                <input type="text" name="u_code" required class="form-control" id="u_code" autocomplete="on">
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="form-group col-md-12">
                                                                <label for="editor">Unit Description</label>
                                                                <textarea type="text" name="u_desc" class="form-control" id="editor"></textarea autocomplete="on">
                                                            </div>
                                                        </div>
                                                        <hr>
                                                        <div class="text-right">
                                                            <button type="submit" name="add_unit" class="btn btn-outline-warning">Add Unit</button>
                                                        </div>
                                                    </form>
                                                </div>
                                                <div class="modal-footer justify-content-between">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Add  Modal -->
                                <?php endif; ?>
                                    <div class="card-body">
                                        <table id="dash-1" class="table table-striped table-bordered display no-wrap" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Unit Code</th>
                                                    <th>Unit Name</th>
                                                    <th>Course</th>
                                                    <th>Description</th>
                                                    <th>Manage</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if ($role == 'admin') {
                                                    $ret = "
                                                        SELECT u.u_id, u.u_code, u.u_name, u.u_desc, c.c_id, c.c_name AS course_name
                                                        FROM units u
                                                        INNER JOIN courses c ON c.c_id = u.c_id
                                                        WHERE u.status <> 'deleted' AND c.status <> 'deleted'
                                                    ";
                                                    $stmt = $mysqli->prepare($ret);
                                                } else if ($role === 'instructor') {
                                                    $ret = "
                                                        SELECT DISTINCT
                                                            u.u_id,
                                                            u.u_code,
                                                            u.u_name,
                                                            u.u_desc,
                                                            c.c_id,
                                                            c.c_name AS course_name
                                                        FROM units u
                                                        INNER JOIN courses c ON c.c_id = u.c_id
                                                        WHERE c.i_id = ? AND u.status <> 'deleted' AND c.status <> 'deleted'
                                                    ";
                                                    $stmt = $mysqli->prepare($ret);
                                                    $stmt->bind_param("i", $user_id);
                                                }
                                                else if ($role === 'student') {
                                                    $ret = "
                                                        SELECT DISTINCT
                                                            u.u_id,
                                                            u.u_code,
                                                            u.u_name,
                                                            u.u_desc,
                                                            c.c_id,
                                                            c.c_name AS course_name
                                                        FROM units u
                                                        INNER JOIN courses c ON c.c_id = u.c_id
                                                        INNER JOIN enrollments e ON e.c_id = c.c_id
                                                        WHERE e.s_id = ? AND e.status <> 'deleted' AND u.status <> 'deleted' AND c.status <> 'deleted'
                                                    ";
                                                    $stmt = $mysqli->prepare($ret);
                                                    $stmt->bind_param("i", $user_id);
                                                }
                                                $stmt->execute();
                                                $res = $stmt->get_result();
                                                while ($units = $res->fetch_object()) {
                                                ?>
                                                    <tr>
                                                        <td><?php echo $units->u_code; ?></td>
                                                        <td><?php echo $units->u_name; ?></td>
                                                        <td><?php echo $units->course_name; ?></td>
                                                        <td><textarea class="form-control" rows="1" readonly id="u_desc_<?php echo $units->u_id; ?>" name="u_desc_<?php echo $units->u_id; ?>"><?php echo $units->u_desc; ?></textarea></td>
                                                        <td>
                                                            <a class="badge badge-outline-warning" data-toggle="modal" href="#update-<?php echo $units->u_id; ?>">
                                                                <i class="fas fa-pencil-alt"></i>
                                                                Update
                                                            </a>
                                                            <!-- Update Modal -->
                                                            <div class="modal fade" id="update-<?php echo $units->u_id; ?>">
                                                                <div class="modal-dialog  modal-lg">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h4 class="modal-title">Update <?php echo $units->u_name; ?></h4>
                                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                <span aria-hidden="true">&times;</span>
                                                                            </button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <!-- Form -->
                                                                            <form method="post" enctype="multipart/form-data">
                                                                                <div class="row">
                                                                                    <div class="form-group col-md-6">
                                                                                        <label for="u_name">Unit Name</label>
                                                                                        <input type="text" name="u_name" required value="<?php echo $units->u_name; ?>" class="form-control" id="u_name" autocomplete="name">
                                                                                        <input type="hidden" name="u_id" value="<?php echo $units->u_id; ?>" required class="form-control" id="u_id">
                                                                                    </div>
                                                                                    <div class="form-group col-md-6">
                                                                                        <label for="u_code">Unit Code</label>
                                                                                        <input type="text" name="u_code" value="<?php echo $units->u_code; ?>" required class="form-control" id="u_code" autocomplete="on">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="row">
                                                                                    <div class="form-group col-md-12">
                                                                                        <label for="u_desc">Unit Description</label>
                                                                                        <textarea type="text" name="u_desc" class="form-control" id="u_desc"><?php echo $units->u_desc; ?></textarea autocomplete="on">
                                                                                    </div>
                                                                                </div>
                                                                                <hr>
                                                                                <div class="text-right">
                                                                                    <button type="submit" name="update_unit" class="btn btn-outline-warning">Update Unit</button>
                                                                                </div>
                                                                            </form>
                                                                        </div>
                                                                        <div class="modal-footer justify-content-between">
                                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- End Update Modal -->
                                                        <?php
                                                            if($role === 'admin')
                                                            {
                                                        ?>
                                                            <a class="badge badge-outline-warning" data-toggle="modal" href="#delete-<?php echo $units->u_id; ?>">
                                                                <i class="fas fa-trash-alt"></i>
                                                                Delete
                                                            </a>
                                                            <!-- Delete Modal -->
                                                            <div class="modal fade" id="delete-<?php echo $units->u_id; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                                <div class="modal-dialog modal-dialog-centered" role="document">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title" id="exampleModalLabel">CONFIRM</h5>
                                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                <span aria-hidden="true">&times;</span>
                                                                            </button>
                                                                        </div>
                                                                        <div class="modal-body text-center text-danger">
                                                                            <h4>Delete <?php echo $units->u_code; ?> - <?php echo $units->u_name; ?> ?</h4>
                                                                            <br>
                                                                            <button type="button" class="text-center btn btn-outline-warning" data-dismiss="modal">No</button>
                                                                            <a href="units.php?delete=<?php echo $units->u_id; ?>" class="text-center btn btn-outline-warning"> Delete </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- End Delete Modal -->
                                                        <?php
                                                            }
                                                        ?>
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
        }
?>
