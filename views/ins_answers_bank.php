<?php
session_start();
include('../config/config.php');
include('../config/checklogin.php');
instructor();
require_once('../config/codeGen.php');
/* Add Questions */
if (isset($_POST['add_answers_bank'])) {
    //Error Handling and prevention of posting double entries
    $error = 0;

    if (isset($_POST['q_id']) && !empty($_POST['q_id'])) {
        $q_id = mysqli_real_escape_string($mysqli, trim($_POST['q_id']));
    } else {
        $error = 1;
        $err = "Question ID Cannot Be Empty";
    }
    if (isset($_POST['an_code']) && !empty($_POST['an_code'])) {
        $an_code = mysqli_real_escape_string($mysqli, trim($_POST['an_code']));
    } else {
        $error = 1;
        $err = "Answer Code Cannot Be Empty";
    }
    if (isset($_POST['cc_id']) && !empty($_POST['cc_id'])) {
        $cc_id = mysqli_real_escape_string($mysqli, trim($_POST['cc_id']));
    } else {
        $error = 1;
        $err = "Course ID Cannot Be Empty";
    }
    if (isset($_POST['c_code']) && !empty($_POST['c_code'])) {
        $c_code = mysqli_real_escape_string($mysqli, trim($_POST['c_code']));
    } else {
        $error = 1;
        $err = "Code Cannot Be Empty";
    }
    if (isset($_POST['c_name']) && !empty($_POST['c_name'])) {
        $c_name = mysqli_real_escape_string($mysqli, trim($_POST['c_name']));
    } else {
        $error = 1;
        $err = "Name Cannot Be Empty";
    }

    if (isset($_POST['c_id']) && !empty($_POST['c_id'])) {
        $c_id = mysqli_real_escape_string($mysqli, trim($_POST['c_id']));
    } else {
        $error = 1;
        $err = "Course ID Cannot Be Empty";
    }

    if (isset($_POST['i_id']) && !empty($_POST['i_id'])) {
        $i_id = mysqli_real_escape_string($mysqli, trim($_POST['i_id']));
    } else {
        $error = 1;
        $err = "Instructor ID Cannot Be Empty";
    }

    if (isset($_POST['q_details']) && !empty($_POST['q_details'])) {
        $q_details = $_POST['q_details'];
    } else {
        $error = 1;
        $err = "Question Description Cannot Be Empty";
    }

    if (isset($_POST['ans_details']) && !empty($_POST['ans_details'])) {
        $ans_details = $_POST['ans_details'];
    } else {
        $error = 1;
        $err = "Question Description Cannot Be Empty";
    }

    if (isset($_POST['q_code']) && !empty($_POST['q_code'])) {
        $q_code = mysqli_real_escape_string($mysqli, trim($_POST['q_code']));
    } else {
        $error = 1;
        $err = "Question Code Cannot Be Empty";
    }

    if (!$error) {
        //prevent Double entries
        $stmt = $mysqli->prepare("SELECT * FROM lms_answers WHERE an_code = ?");
        $stmt->bind_param("s", $an_code);
        $stmt->execute();
        $res = $stmt->get_result();
        if (mysqli_num_rows($res) > 0) {
            $row = mysqli_fetch_assoc($res);
            if ($an_code == $row['an_code']) {
                $err =  "An Answer Bank With $an_code Exists";
            }
        } else {
            $query = "INSERT INTO lms_answers (q_code, cc_id, c_id, c_code, i_id, q_id, an_code, c_name, q_details, ans_details) VALUES(?,?,?,?,?,?,?,?,?,?)";
            $stmt = $mysqli->prepare($query);
            $rc = $stmt->bind_param('ssssssssss', $q_code, $cc_id, $c_id, $c_code, $i_id, $q_id, $an_code, $c_name, $q_details, $ans_details);
            $stmt->execute();
            if ($stmt) {
                $success = "Added" && header("refresh:1; url=ins_answers_bank.php");
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
                                <a class="btn btn-outline-warning" href="ins_manage_answers_bank.php">Manage Answers Bank</a>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card-body">
                                    <table id="dash-1" class="table table-striped table-bordered display no-wrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Questions Code</th>
                                                <th>Unit Code</th>
                                                <th>Unit Name</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            <?php
                                            $id  = $_SESSION['i_id'];
                                            $ret = "SELECT  * FROM  lms_questions  WHERE i_id = '$i_id' ";
                                            $stmt = $mysqli->prepare($ret);
                                            $stmt->execute(); //ok
                                            $res = $stmt->get_result();
                                            while ($questions = $res->fetch_object()) {
                                            ?>

                                                <tr>
                                                    <td><?php echo $questions->q_code; ?></td>
                                                    <td><?php echo $questions->c_code; ?></td>
                                                    <td><?php echo $questions->c_name; ?></td>
                                                    <td>
                                                        <a class="badge badge-outline-warning" data-toggle="modal" href="#add-<?php echo $questions->q_id; ?>">
                                                            <i class="fas fa-external-link-alt"></i>
                                                            Create Answers Bank
                                                        </a>
                                                        <!-- Add Answer Bank Modal -->
                                                        <div class="modal fade" id="add-<?php echo $questions->q_id; ?>">
                                                            <div class="modal-dialog  modal-xl">
                                                                <div class="modal-content">
                                                                    <div class="modal-header text-center">
                                                                        <h4 class="modal-title ">Create Answers Bank For <?php echo $questions->q_code; ?></h4>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <!-- Form -->
                                                                        <form method="post" enctype="multipart/form-data">
                                                                            <div class="row" style="display:none">
                                                                                <div class="form-group col-md-6" style="display:none">
                                                                                    <label for="an_code">Ans Code</label>
                                                                                    <input type="text" id="an_code" name="an_code" readonly value="<?php echo (function_exists('genCode') ? genCode() : 'AN'.uniqid()); ?>" required class="form-control" autocomplete="on">
                                                                                    <label for="c_name">Unit Name</label>
                                                                                    <input type="text" id="c_name" name="c_name" value="<?php echo $questions->c_name; ?>" readonly required class="form-control" autocomplete="name">
                                                                                </div>

                                                                                <div class="form-group col-md-6">
                                                                                    <label for="c_code">Unit Code</label>
                                                                                    <input type="text" id="c_code" name="c_code" value="<?php echo $questions->c_code; ?>" required class="form-control" autocomplete="on">
                                                                                    <label for="q_details">Questions</label>
                                                                                    <textarea type="text" id="q_details" name="q_details" class="form-control"><?php echo $questions->q_details; ?></textarea autocomplete="on">
                                                                                    <label for="q_code">Questions Code</label>
                                                                                    <input type="text" id="q_code" name="q_code" value="<?php echo $questions->q_code; ?>" readonly required class="form-control" autocomplete="on">
                                                                                    <label for="cc_id">Course Code</label>
                                                                                    <input type="text" id="cc_id" name="cc_id" value="<?php echo $questions->cc_id; ?>" readonly required class="form-control" autocomplete="on">
                                                                                    <label for="c_id">Category ID</label>
                                                                                    <input type="text" id="c_id" name="c_id" value="<?php echo $questions->c_id; ?>" readonly required class="form-control" autocomplete="on">
                                                                                    <label for="i_id">Instructor ID</label>
                                                                                    <input type="text" id="i_id" name="i_id" value="<?php echo $questions->i_id; ?>" readonly required class="form-control" autocomplete="on">
                                                                                    <input type="text" id="q_id" name="q_id" value="<?php echo $questions->q_id; ?>" readonly required class="form-control" autocomplete="on">
                                                                                </div>
                                                                            </div>
                                                                            <div class="row">
                                                                                <div class="form-group col-md-12">
                                                                                    <p>
                                                                                        <b>Questions</b><br><br>
                                                                                        <?php echo nl2br(htmlspecialchars($questions->q_details)); ?>
                                                                                    </p>
                                                                                </div>
                                                                            </div>
                                                                            <hr>
                                                                            <div class="row">
                                                                                <div class="form-group col-md-12">
                                                                                    <label for="ans-editor-<?php echo $questions->q_id; ?>">Answers</label>
                                                                                    <textarea type="text" name="ans_details" class="form-control" id="ans-editor-<?php echo $questions->q_id; ?>"></textarea autocomplete="on">
                                                                                </div>
                                                                            </div>
                                                                            <hr>
                                                                            <div class="text-right">
                                                                                <button type="submit" name="add_answers_bank" class="btn btn-outline-warning">Add Answers</button>
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
