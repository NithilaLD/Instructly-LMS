<?php
    include('../config/config.php');
    require_once('../config/audit.php');
    include('../config/checklogin.php');
    userRoles(['admin', 'instructor', 'student']);
    require_once('../config/codeGen.php');
    $role = $_SESSION['role'];
    $user_id = $_SESSION['user_id'];

    /* Enter Marks */
    if (isset($_POST['enter_marks']) && ($role === 'admin' || $role === 'instructor')) {
        $error = 0;

        if (isset($_POST['s_id']) && !empty($_POST['s_id'])) {
            $s_id = mysqli_real_escape_string($mysqli, trim($_POST['s_id']));
        } else {
            $error = 1;
            $err = "Student ID Cannot Be Empty";
        }
        if (isset($_POST['u_id']) && !empty($_POST['u_id'])) {
            $u_id = mysqli_real_escape_string($mysqli, trim($_POST['u_id']));
        } else {
            $error = 1;
            $err = "Unit ID Cannot Be Empty";
        }
        if (isset($_POST['r_code']) && !empty($_POST['r_code'])) {
            $r_code = mysqli_real_escape_string($mysqli, trim($_POST['r_code']));
        } else {
            $error = 1;
            $err = "Code Cannot Be Empty";
        }
        if (isset($_POST['u_cat1_marks']) && !empty($_POST['u_cat1_marks'])) {
            $u_cat1_marks = mysqli_real_escape_string($mysqli, trim($_POST['u_cat1_marks']));
        } else {
            $error = 1;
            $err = "Cat One Marks Cannot Be Empty";
        }
        if (isset($_POST['u_cat2_marks']) && !empty($_POST['u_cat2_marks'])) {
            $u_cat2_marks = mysqli_real_escape_string($mysqli, trim($_POST['u_cat2_marks']));
        } else {
            $error = 1;
            $err = "Cat Two Marks Cannot Be Empty";
        }
        if (isset($_POST['u_eos_marks']) && !empty($_POST['u_eos_marks'])) {
            $u_eos_marks = mysqli_real_escape_string($mysqli, trim($_POST['u_eos_marks']));
        } else {
            $error = 1;
            $err = "End Of Semester Marks Cannot Be Empty";
        }

        if (!$error) {
            // Prevent Double entries
            $sql = "SELECT * FROM marks WHERE (r_code=? OR (s_id=? AND u_id=?)) AND status <> 'deleted'";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('sss', $r_code, $s_id, $u_id);
            $stmt->execute();
            $res = $stmt->get_result();
            
            if ($res->num_rows > 0) {
                $err = "A Marks Entry With $r_code Or For This Unit Already Exists";
            } else {
                $query = "INSERT INTO marks (s_id, u_id, r_code, u_cat1_marks, u_cat2_marks, u_eos_marks) VALUES (?,?,?,?,?,?)";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param('ssssss', $s_id, $u_id, $r_code, $u_cat1_marks, $u_cat2_marks, $u_eos_marks);
                $stmt->execute();
                if ($stmt) {
                    logAuditAction($mysqli, 'enter_marks', 'Marks added', 'marks', 'mark', $r_code);
                    $_SESSION['flash_success'] = "Marks added successfully.";
                } else {
                    $_SESSION['flash_error'] = "Please Try Again Or Try Later";
                }
            }
        }
    }

    /* Update Marks */
    if (isset($_POST['update_marks']) && ($role === 'admin' || $role === 'instructor')) {
        $r_code = mysqli_real_escape_string($mysqli, $_POST['r_code']);
        $u_cat1_marks = mysqli_real_escape_string($mysqli, $_POST['u_cat1_marks']);
        $u_cat2_marks = mysqli_real_escape_string($mysqli, $_POST['u_cat2_marks']);
        $u_eos_marks  = mysqli_real_escape_string($mysqli, $_POST['u_eos_marks']);

        $query = "UPDATE marks SET u_cat1_marks = ?, u_cat2_marks = ?, u_eos_marks = ? WHERE r_code = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('ssss', $u_cat1_marks, $u_cat2_marks, $u_eos_marks, $r_code);
        $stmt->execute();

        if ($stmt) {
            logAuditAction($mysqli, 'update_marks', 'Marks updated', 'marks', 'mark', $r_code);
            $_SESSION['flash_success'] = "Marks updated successfully.";
            header("Location: marks.php");
            exit();
        } else {
            $_SESSION['flash_error'] = "Please Try Again Or Try Later";
            header("Location: marks.php");
            exit();
        }
    }

    /* Delete Marks */
    if (isset($_GET['delete']) && $role === 'admin') {
        $r_code = $_GET['delete'];

        $query = "UPDATE marks SET status = 'deleted' WHERE r_code = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('s', $r_code);

        if ($stmt->execute()) {
            logAuditAction($mysqli, 'delete_marks', 'Marks deleted', 'marks', 'mark', (string) $r_code);
            $_SESSION['flash_success'] = "Marks deleted successfully.";
            header("Location: marks.php");
            exit();
        } else {
            $_SESSION['flash_error'] = "Please Try Again Or Try Later";
            header("Location: marks.php");
            exit();
        }
    }

    /* Persist System Settings */
    $ret = "SELECT * FROM system";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($sys = $res->fetch_object()) {
        require_once('../partials/head.php'); ?>

        <body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
            <div class="wrapper">
                <!-- Navbar -->
                <?php require_once('../partials/navbar.php'); ?>

                <!-- Main Sidebar Container -->
                <?php require_once('../partials/sidebar.php'); ?>

                <!-- Content Wrapper. Contains page content -->
                <div class="content-wrapper">
                    <!-- Main content -->
                    <section class="content pt-3">
                        <div class="container-fluid">
                            <?php if ($role === 'admin' || $role === 'instructor') {
                                ?>
                            <div class="container" style="padding-top: 10px !important;">
                                <div class="text-right text-dark">
                                    <a class="btn btn-outline-warning" href="gradeunits.php">
                                        Grade Units Bulk
                                    </a>
                                    <a class="btn btn-outline-warning" href="gradestudents.php">
                                        Grade Students Bulk
                                    </a>
                                </div>
                            </div>
                            <hr>
                            <?php
                                } ?>
                            <div class="card">
                                <div class="col-md-12">
                                    <div class="card-body">
                                        <table id="dash-2" class="table table-striped table-bordered display" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <?php if ($role === 'admin' || $role === 'instructor') { ?>
                                                        <th>Unit Code</th>
                                                        <th>Unit Name</th>
                                                        <th>Admission Number</th>
                                                        <th>Student Name</th>
                                                        <th>Enroll date</th>
                                                        <th>Action</th>
                                                    <?php } elseif ($role === 'student') { ?>
                                                        <th>Entry Code</th>
                                                        <th>Unit Code</th>
                                                        <th>Unit Name</th>
                                                        <th>Std Admn</th>
                                                        <th>Std Name</th>
                                                        <th>Date Added</th>
                                                        <th>Action</th>
                                                    <?php } ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if ($role === 'admin') {
                                                    $query = "
                                                        SELECT e.en_id, e.en_date, e.s_id, c.c_name, u.u_id, u.u_code, u.u_name,
                                                            std.user_code as s_regno, std.name as s_name, ins.name as i_name,
                                                            m.r_id, m.r_code, m.u_cat1_marks, m.u_cat2_marks, m.u_eos_marks
                                                        FROM enrollments e
                                                        JOIN courses c ON e.c_id = c.c_id
                                                        JOIN units u ON c.c_id = u.c_id
                                                        JOIN users std ON e.s_id = std.user_id
                                                        LEFT JOIN users ins ON c.i_id = ins.user_id
                                                        LEFT JOIN marks m ON e.s_id = m.s_id AND u.u_id = m.u_id AND m.status <> 'deleted'
                                                        WHERE e.status <> 'inactive' AND c.status <> 'deleted' AND u.status <> 'deleted' AND std.status = 'active'
                                                    ";
                                                    $stmt = $mysqli->prepare($query);
                                                } elseif ($role === 'instructor') {
                                                    $query = "
                                                        SELECT e.en_id, e.en_date, e.s_id, c.c_name, u.u_id, u.u_code, u.u_name,
                                                            std.user_code as s_regno, std.name as s_name, ins.name as i_name,
                                                            m.r_id, m.r_code, m.u_cat1_marks, m.u_cat2_marks, m.u_eos_marks
                                                        FROM enrollments e
                                                        JOIN courses c ON e.c_id = c.c_id
                                                        JOIN units u ON c.c_id = u.c_id
                                                        JOIN users std ON e.s_id = std.user_id
                                                        LEFT JOIN users ins ON c.i_id = ins.user_id
                                                        LEFT JOIN marks m ON e.s_id = m.s_id AND u.u_id = m.u_id AND m.status <> 'deleted'
                                                        WHERE c.i_id = ? AND e.status <> 'inactive' AND c.status <> 'deleted' AND u.status <> 'deleted' AND std.status = 'active'
                                                    ";
                                                    $stmt = $mysqli->prepare($query);
                                                    $stmt->bind_param('s', $user_id);
                                                } elseif ($role === 'student') {
                                                    $query = "
                                                        SELECT m.r_id, m.r_code, m.r_date_added,
                                                            m.u_cat1_marks, m.u_cat2_marks, m.u_eos_marks,
                                                            u.u_code, u.u_name,
                                                            std.user_code AS s_regno, std.name AS s_name
                                                        FROM marks m
                                                        JOIN units u ON m.u_id = u.u_id
                                                        JOIN users std ON m.s_id = std.user_id
                                                        WHERE m.s_id = ? AND m.status <> 'deleted' AND u.status <> 'deleted' AND std.status = 'active'
                                                    ";
                                                    $stmt = $mysqli->prepare($query);
                                                    $stmt->bind_param('s', $user_id);
                                                    $stmt = $mysqli->prepare($query);
                                                    $stmt->bind_param('s', $user_id);
                                                }

                                                $stmt->execute();
                                                $res = $stmt->get_result();
                                                while ($row = $res->fetch_object()) {
                                                ?>
                                                    <tr>
                                                        <?php if ($role === 'admin' || $role === 'instructor') { ?>
                                                            <td><?php echo htmlspecialchars($row->u_code); ?></td>
                                                            <td><?php echo htmlspecialchars($row->u_name); ?></td>
                                                            <td><?php echo htmlspecialchars($row->s_regno); ?></td>
                                                            <td><?php echo htmlspecialchars($row->s_name); ?></td>
                                                            <td><?php echo date("d M Y g:ia", strtotime($row->en_date)); ?></td>
                                                            <td>
                                                                <?php if (!empty($row->r_code)) { ?>
                                                                    <!-- View Marks Button if marks exist -->
                                                                    <a class="badge badge-outline-success" data-toggle="modal" href="#view-<?php echo $row->en_id . '-' . $row->u_id; ?>">
                                                                        <i class="fas fa-eye"></i> View
                                                                    </a>

                                                                    <!-- View Marks Modal -->
                                                                    <div class="modal fade" id="view-<?php echo $row->en_id . '-' . $row->u_id; ?>">
                                                                        <div class="modal-dialog modal-lg">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h4 class="modal-title">Recorded Marks For <?php echo htmlspecialchars($row->s_name); ?> </h4>
                                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                        <span aria-hidden="true">&times;</span>
                                                                                    </button>
                                                                                </div>
                                                                                <div class="modal-body">
                                                                                    <div class="row">
                                                                                        <div class="form-group col-md-4">
                                                                                            <label>Registration Number</label>
                                                                                            <p class="form-control-static text-muted"><?php echo htmlspecialchars($row->s_regno); ?></p>
                                                                                        </div>
                                                                                        <div class="form-group col-md-4">
                                                                                            <label>Student Name</label>
                                                                                            <p class="form-control-static text-muted"><?php echo htmlspecialchars($row->s_name); ?></p>
                                                                                        </div>
                                                                                        <div class="form-group col-md-4">
                                                                                            <label>Results Code</label>
                                                                                            <p class="form-control-static text-muted"><?php echo htmlspecialchars($row->r_code); ?></p>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="row">
                                                                                        <div class="form-group col-md-6">
                                                                                            <label>Unit Code</label>
                                                                                            <p class="form-control-static text-muted"><?php echo htmlspecialchars($row->u_code); ?></p>
                                                                                        </div>
                                                                                        <div class="form-group col-md-6">
                                                                                            <label>Unit Name</label>
                                                                                            <p class="form-control-static text-muted"><?php echo htmlspecialchars($row->u_name); ?></p>
                                                                                        </div>
                                                                                    </div>
                                                                                    <hr>
                                                                                    <div class="row text-center">
                                                                                        <div class="form-group col-md-4">
                                                                                            <label>CA 1 Marks (Out of 30)</label>
                                                                                            <h3><span class="badge badge-info"><?php echo htmlspecialchars($row->u_cat1_marks); ?></span></h3>
                                                                                        </div>
                                                                                        <div class="form-group col-md-4">
                                                                                            <label>CA 2 Marks (Out of 30)</label>
                                                                                            <h3><span class="badge badge-info"><?php echo htmlspecialchars($row->u_cat2_marks); ?></span></h3>
                                                                                        </div>
                                                                                        <div class="form-group col-md-4">
                                                                                            <label>Sem End Exam (Out of 100)</label>
                                                                                            <h3><span class="badge badge-primary"><?php echo htmlspecialchars($row->u_eos_marks); ?></span></h3>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="modal-footer justify-content-end">
                                                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <!-- End View Marks Modal -->
                                                                    
                                                                    <a class="badge badge-outline-success" data-toggle="modal" href="#update-<?php echo $row->en_id; ?>">
                                                                    <i class="fas fa-edit"></i> Update
                                                                </a>

                                                                <div class="modal fade" id="update-<?php echo $row->en_id; ?>">
                                                                    <div class="modal-dialog modal-lg">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h4 class="modal-title">Update Marks For <?php echo $row->s_name; ?> </h4>
                                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                    <span aria-hidden="true">&times;</span>
                                                                                </button>
                                                                            </div>
                                                                            <div class="modal-body">
                                                                                <form method="post">
                                                                                    <input type="hidden" name="r_code" value="<?php echo htmlspecialchars($row->r_code); ?>">
                                                                                    <div class="row">
                                                                                        <div class="form-group col-md-4">
                                                                                            <label for="u_cat1_marks">CA 1 Marks(Out Of 30)</label>
                                                                                            <input type="text" name="u_cat1_marks" value="<?php echo htmlspecialchars($row->u_cat1_marks); ?>" class="form-control">
                                                                                        </div>
                                                                                        <div class="form-group col-md-4">
                                                                                            <label for="u_cat2_marks">CA 2 Marks(Out Of 30)</label>
                                                                                            <input type="text" name="u_cat2_marks" value="<?php echo htmlspecialchars($row->u_cat2_marks); ?>" class="form-control">
                                                                                        </div>
                                                                                        <div class="form-group col-md-4">
                                                                                            <label for="u_eos_marks">Sem End Exam Marks(Out Of 100)</label>
                                                                                            <input type="text" name="u_eos_marks" value="<?php echo htmlspecialchars($row->u_eos_marks); ?>" class="form-control">
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="text-right">
                                                                                        <button type="submit" name="update_marks" class="btn btn-outline-warning">Save Changes</button>
                                                                                    </div>
                                                                                </form>
                                                                            </div>
                                                                            <div class="modal-footer justify-content-between">
                                                                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            
                                                            <?php if ($role === 'admin') { ?>
                                                                <a class="badge badge-outline-danger" data-toggle="modal" href="#delete-<?php echo $row->en_id; ?>">
                                                                    <i class="fas fa-trash"></i> Delete
                                                                </a>

                                                                <div class="modal fade" id="delete-<?php echo $row->en_id; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h5 class="modal-title">CONFIRM</h5>
                                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                        <span aria-hidden="true">&times;</span>
                                                                                    </button>
                                                                                </div>
                                                                                <div class="modal-body text-center text-danger">
                                                                                    <h4>Delete Marks?</h4>
                                                                                    <br>
                                                                                        <button type="button" class="text-center btn btn-outline-warning" data-dismiss="modal">No</button>
                                                                                        <a href="marks.php?delete=<?php echo $row->r_code; ?>" class="text-center btn btn-outline-warning">Delete</a>
                                                                                    </form>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                <?php } ?>
                                                                <?php } else { ?>
                                                                    <!-- Grade Unit Button if marks do not exist -->
                                                                    <a class="badge badge-outline-warning" data-toggle="modal" href="#grade-<?php echo $row->en_id . '-' . $row->u_id; ?>">
                                                                        <i class="fas fa-check"></i> Grade Unit
                                                                    </a>

                                                                    <!-- Grade Modal -->
                                                                    <div class="modal fade" id="grade-<?php echo $row->en_id . '-' . $row->u_id; ?>">
                                                                        <div class="modal-dialog modal-lg">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h4 class="modal-title">Enter Marks For <?php echo htmlspecialchars($row->s_name); ?> </h4>
                                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                        <span aria-hidden="true">&times;</span>
                                                                                    </button>
                                                                                </div>
                                                                                <div class="modal-body">
                                                                                    <!-- Form -->
                                                                                    <form method="post" enctype="multipart/form-data">
                                                                                        <div class="row">
                                                                                            <div class="form-group col-md-4" style="display:none">
                                                                                                <label for="r_code">Results Code</label>
                                                                                                <input type="text" name="r_code" value="<?php echo 'RS' . substr(md5(uniqid(rand(), true)), 0, 13); ?>" required class="form-control" id="r_code" autocomplete="on">
                                                                                                <input type="hidden" name="s_id" value="<?php echo htmlspecialchars($row->s_id); ?>">
                                                                                                <input type="hidden" name="u_id" value="<?php echo htmlspecialchars($row->u_id); ?>">
                                                                                            </div>
                                                                                            <div class="form-group col-md-4">
                                                                                                <label for="s_regno">Registration Number</label>
                                                                                                <input type="text" name="s_regno" readonly required value="<?php echo htmlspecialchars($row->s_regno); ?>" class="form-control" id="s_regno" autocomplete="on">
                                                                                            </div>

                                                                                            <div class="form-group col-md-4">
                                                                                                <label for="s_name">Name</label>
                                                                                                <input type="text" name="s_name" readonly value="<?php echo htmlspecialchars($row->s_name); ?>" required class="form-control" id="s_name" autocomplete="name">
                                                                                            </div>

                                                                                            <div class="form-group col-md-4">
                                                                                                <label for="i_name">Instructor Name</label>
                                                                                                <input type="text" name="i_name" readonly value="<?php echo htmlspecialchars($row->i_name); ?>" required class="form-control" id="i_name" autocomplete="name">
                                                                                            </div>
                                                                                        </div>

                                                                                        <div class="row">
                                                                                            <div class="form-group col-md-6">
                                                                                                <label for="u_code">Unit Code</label>
                                                                                                <input type="text" name="u_code" readonly value="<?php echo htmlspecialchars($row->u_code); ?>" required class="form-control" id="u_code" autocomplete="on">
                                                                                            </div>

                                                                                            <div class="form-group col-md-6">
                                                                                                <label for="u_name">Unit Name</label>
                                                                                                <input type="text" name="u_name" readonly required value="<?php echo htmlspecialchars($row->u_name); ?>" class="form-control" id="u_name" autocomplete="name">
                                                                                            </div>
                                                                                        </div>

                                                                                        <div class="row">
                                                                                            <div class="form-group col-md-4">
                                                                                                <label for="u_cat1_marks">CA 1 Marks (Out Of 30)</label>
                                                                                                <input type="text" name="u_cat1_marks" required class="form-control" id="u_cat1_marks" autocomplete="on">
                                                                                            </div>

                                                                                            <div class="form-group col-md-4">
                                                                                                <label for="u_cat2_marks">CA 2 Marks (Out Of 30)</label>
                                                                                                <input type="text" name="u_cat2_marks" required class="form-control" id="u_cat2_marks" autocomplete="on">
                                                                                            </div>

                                                                                            <div class="form-group col-md-4">
                                                                                                <label for="u_eos_marks">Sem End Exam Marks (Out Of 100)</label>
                                                                                                <input type="text" name="u_eos_marks" class="form-control" id="u_eos_marks" autocomplete="on">
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="text-right">
                                                                                            <button type="submit" name="enter_marks" class="btn btn-outline-warning">Add Marks</button>
                                                                                        </div>
                                                                                    </form>
                                                                                </div>
                                                                                <div class="modal-footer justify-content-between">
                                                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <!-- End Grade Modal -->
                                                                <?php } ?>
                                                            </td>
                                                        <?php } elseif ($role === 'student') { ?>
                                                            <td><?php echo htmlspecialchars($row->r_code); ?></td>
                                                            <td><?php echo htmlspecialchars($row->u_code); ?></td>
                                                            <td><?php echo htmlspecialchars($row->u_name); ?></td>
                                                            <td><?php echo htmlspecialchars($row->s_regno); ?></td>
                                                            <td><?php echo htmlspecialchars($row->s_name); ?></td>
                                                            <td><?php echo date("d M Y", strtotime($row->r_date_added)); ?></td>
                                                            <td>
                                                                <?php if (!empty($row->r_code)) { ?>
                                                                    <!-- View Marks Button if marks exist -->
                                                                    <a class="badge badge-outline-success" data-toggle="modal" href="#view-<?php echo $row->r_code ?>">
                                                                        <i class="fas fa-eye"></i> View
                                                                    </a>

                                                                    <!-- View Marks Modal -->
                                                                    <div class="modal fade" id="view-<?php echo $row->r_code ?>">
                                                                        <div class="modal-dialog modal-lg">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h4 class="modal-title">Recorded Marks For <?php echo htmlspecialchars($row->s_name); ?> </h4>
                                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                        <span aria-hidden="true">&times;</span>
                                                                                    </button>
                                                                                </div>
                                                                                <div class="modal-body">
                                                                                    <div class="row">
                                                                                        <div class="form-group col-md-4">
                                                                                            <label>Registration Number</label>
                                                                                            <p class="form-control-static text-muted"><?php echo htmlspecialchars($row->s_regno); ?></p>
                                                                                        </div>
                                                                                        <div class="form-group col-md-4">
                                                                                            <label>Student Name</label>
                                                                                            <p class="form-control-static text-muted"><?php echo htmlspecialchars($row->s_name); ?></p>
                                                                                        </div>
                                                                                        <div class="form-group col-md-4">
                                                                                            <label>Results Code</label>
                                                                                            <p class="form-control-static text-muted"><?php echo htmlspecialchars($row->r_code); ?></p>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="row">
                                                                                        <div class="form-group col-md-6">
                                                                                            <label>Unit Code</label>
                                                                                            <p class="form-control-static text-muted"><?php echo htmlspecialchars($row->u_code); ?></p>
                                                                                        </div>
                                                                                        <div class="form-group col-md-6">
                                                                                            <label>Unit Name</label>
                                                                                            <p class="form-control-static text-muted"><?php echo htmlspecialchars($row->u_name); ?></p>
                                                                                        </div>
                                                                                    </div>
                                                                                    <hr>
                                                                                    <div class="row text-center">
                                                                                        <div class="form-group col-md-4">
                                                                                            <label>CA 1 Marks (Out of 30)</label>
                                                                                            <h3><span class="badge badge-info"><?php echo htmlspecialchars($row->u_cat1_marks); ?></span></h3>
                                                                                        </div>
                                                                                        <div class="form-group col-md-4">
                                                                                            <label>CA 2 Marks (Out of 30)</label>
                                                                                            <h3><span class="badge badge-info"><?php echo htmlspecialchars($row->u_cat2_marks); ?></span></h3>
                                                                                        </div>
                                                                                        <div class="form-group col-md-4">
                                                                                            <label>Sem End Exam (Out of 100)</label>
                                                                                            <h3><span class="badge badge-primary"><?php echo htmlspecialchars($row->u_eos_marks); ?></span></h3>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="modal-footer justify-content-end">
                                                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <!-- End View Marks Modal -->
                                                            </td>
                                                        <?php } }?>
                                                    </tr>
                                                <?php } ?>
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
