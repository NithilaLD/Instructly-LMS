<?php
session_start();
include('../config/config.php');
include('../config/checklogin.php');
instructor();
require_once('../config/codeGen.php');

/* Add Student */
if (isset($_POST['add_std'])) {
    //Error Handling and prevention of posting double entries
    $error = 0;
    $s_regno = '';


    if (isset($_POST['s_regno']) && !empty($_POST['s_regno'])) {
        $s_regno = mysqli_real_escape_string($mysqli, trim($_POST['s_regno']));
    } else {
        $error = 1;
        $err = "Admo Number Cannot Be Empty";
    }

    if (isset($_POST['s_name']) && !empty($_POST['s_name'])) {
        $s_name = mysqli_real_escape_string($mysqli, trim($_POST['s_name']));
    } else {
        $error = 1;
        $err = "Name Cannot Be Empty";
    }

    if (isset($_POST['s_email']) && !empty($_POST['s_email'])) {
        $s_email = mysqli_real_escape_string($mysqli, trim($_POST['s_email']));
    } else {
        $error = 1;
        $err = "Email Cannot Be Empty";
    }

    if (isset($_POST['s_phoneno']) && !empty($_POST['s_phoneno'])) {
        $s_phoneno = mysqli_real_escape_string($mysqli, trim($_POST['s_phoneno']));
    } else {
        $error = 1;
        $err = "Phone Cannot Be Empty";
    }

    if (isset($_POST['s_course']) && !empty($_POST['s_course'])) {
        $s_course = mysqli_real_escape_string($mysqli, trim($_POST['s_course']));
    } else {
        $error = 1;
        $err = "Student Course  Cannot Be Empty";
    }


    if (isset($_POST['s_pwd']) && !empty($_POST['s_pwd'])) {
        $s_pwd = mysqli_real_escape_string($mysqli, trim(sha1(md5($_POST['s_pwd']))));
    } else {
        $error = 1;
        $err = "Password Cannot Be Empty";
    }

    $s_dpic = "";
    $uploadedFile = $_FILES["s_dpic"]["tmp_name"];
    $fileName = $_FILES["s_dpic"]["name"];
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    // $s_dpic = $_FILES["s_dpic"]["name"];
    $s_dpic = $s_regno . '.' . $fileExtension;

    if (!$error) {
        //prevent Double entries
        $stmt = $mysqli->prepare("SELECT * FROM lms_student WHERE s_regno = ?");
        $stmt->bind_param("s", $s_regno);
        $stmt->execute();
        $res = $stmt->get_result();
        if (mysqli_num_rows($res) > 0) {
            $row = mysqli_fetch_assoc($res);
            if ($s_regno == $row['s_regno']) {
                $err =  "A Student  With $s_regno Already Exists";
            }
        } else {
            $query = "INSERT INTO lms_student (s_regno, s_course, s_name, s_email, s_pwd, s_phoneno, s_dpic) VALUES (?,?,?,?,?,?,?)";
            $stmt = $mysqli->prepare($query);
            $rc = $stmt->bind_param('sssssss', $s_regno, $s_course, $s_name, $s_email, $s_pwd, $s_phoneno, $s_dpic);
            $stmt->execute();
            if ($stmt) {
                move_uploaded_file($_FILES["s_dpic"]["tmp_name"], "../public/sys_data/uploads/users/" . $s_dpic);
                $success = "Added" && header("refresh:1; url=ins_students.php");
            } else {
                $info = "Please Try Again Or Try Later";
            }
        }
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
            <div class="content-wrapper"><br>
                <!-- Main content -->
                <section class="content">
                    <div class="container-fluid">
                        <div class="container">
                            <div class="text-right text-dark">
                                <!-- <button type="button" class="btn btn-outline-warning" data-toggle="modal" data-target="#import-modal">Import Studentrs Records </button> -->
                                <button type="button" class="btn btn-outline-warning" data-toggle="modal" data-target="#add-modal">Add Student</button>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
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
                                                            <label for="s_regno">Registration Number</label>
                                                            <input type="text" name="s_regno" value="<?php echo isset($a, $b) ? $a . $b : ''; ?>" readonly required class="form-control" id="s_regno" autocomplete="on">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label for="s_name">Full Name</label>
                                                            <input type="text" name="s_name" required class="form-control" autocomplete="name" id="s_name">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="form-group col-md-6">
                                                            <label for="s_phoneno">Phone Number</label>
                                                            <input type="text" name="s_phoneno" class="form-control" autocomplete="phone" id="s_phoneno">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label for="inputGroupSelect03">Course</label>
                                                            <select class="custom-select" id="inputGroupSelect03" name="s_course">
                                                                <option selected>Choose...</option>
                                                                <?php
                                                                $ret = "SELECT  * FROM  lms_course_categories";
                                                                $stmt = $mysqli->prepare($ret);
                                                                $stmt->execute(); //ok
                                                                $res = $stmt->get_result();
                                                                while ($row = $res->fetch_object()) {
                                                                ?>
                                                                    <option><?php echo $row->cc_name; ?></option>
                                                                <?php
                                                                } ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="form-group col-md-4">
                                                            <label for="s_email">Email address</label>
                                                            <input type="email" name="s_email" class="form-control" autocomplete="email" id="s_email">
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label for="s_pwd">Password</label>
                                                            <input type="password" name="s_pwd" class="form-control" autocomplete="current-password" id="s_pwd">
                                                        </div>

                                                        <div class="form-group col-md-4">
                                                            <label for="exampleInputFile">Student Passport</label>
                                                            <div class="input-group">
                                                                <div class="custom-file">
                                                                    <input required name="s_dpic" type="file" class="custom-file-input" id="exampleInputFile" autocomplete="student-passport">
                                                                    <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <hr>
                                                    <div class="text-right">
                                                        <button type="submit" name="add_std" class="btn btn-outline-warning">Add Student</button>
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

                                <div class="card-body">
                                    <table id="dash-1" class="table table-striped table-bordered display no-wrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>RegNo</th>
                                                <th>Email</th>
                                                <th>Contact</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $ret = "SELECT  * FROM  lms_student";
                                            $stmt = $mysqli->prepare($ret);
                                            $stmt->execute(); //ok
                                            $res = $stmt->get_result();
                                            while ($students = $res->fetch_object()) {
                                            ?>

                                                <tr>
                                                    <td><?php echo $students->s_name; ?></td>
                                                    <td><?php echo $students->s_regno; ?></td>
                                                    <td><?php echo $students->s_email; ?></td>
                                                    <td><?php echo $students->s_phoneno; ?></td>
                                                    <td>
                                                        <a class="badge badge-outline-warning" href="ins_view_student.php?view=<?php echo $students->s_id; ?>">
                                                            <i class="fas fa-external-link-alt"></i>
                                                            View
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
