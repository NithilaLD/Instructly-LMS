<?php
session_start();
include('../config/config.php');
include('../config/checklogin.php');
instructor();
require_once('../partials/analytics.php');
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
            <?php
            require_once('../partials/ins_sidebar.php');
            $view = $_GET['view'];
            $ret = "SELECT  * FROM  lms_results WHERE rs_id = '$view' ";
            $stmt = $mysqli->prepare($ret);
            $stmt->execute(); //ok
            $res = $stmt->get_result();
            while ($mark = $res->fetch_object()) {
                $course_id = $mark->cc_id;
                $unit = $mark->c_id;
                /* Course Details */
                $ret = "SELECT  * FROM  lms_course_categories  WHERE cc_id = '$course_id' ";
                $stmt = $mysqli->prepare($ret);
                $stmt->execute(); //ok
                $res = $stmt->get_result();
                while ($course = $res->fetch_object()) {
                    /* Unit Details */
                    $ret = "SELECT  * FROM  lms_course  WHERE c_id = '$unit' ";
                    $stmt = $mysqli->prepare($ret);
                    $stmt->execute(); //ok
                    $res = $stmt->get_result();
                    while ($unit = $res->fetch_object()) {

                        /* Perform University-Level Grading */

                        // Raw marks
                        $cat1    = $mark->c_cat1_marks;   // out of 30
                        $cat2    = $mark->c_cat2_marks;   // out of 30
                        $sem_end = $mark->c_eos_marks;    // out of 100

                        // Continuous Assessment (30%)
                        $ca_total      = $cat1 + $cat2;           // out of 60
                        $ca_converted  = ($ca_total / 60) * 30;   // convert to 30%

                        // End of Semester Exam (70%)
                        $eos_converted = ($sem_end / 100) * 70;   // convert to 70%

                        // Final Mark (out of 100)
                        $total_avg = $ca_converted + $eos_converted;
                        $total_avg = round($total_avg);
                        //Get The Grade
                        if ($total_avg >= 85) {
                            $grade = "A+";
                        } elseif ($total_avg >= 75) {
                            $grade = "A";
                        } elseif ($total_avg >= 70) {
                            $grade = "A-";
                        } elseif ($total_avg >= 65) {
                            $grade = "B+";
                        } elseif ($total_avg >= 60) {
                            $grade = "B";
                        } elseif ($total_avg >= 55) {
                            $grade = "B-";
                        } elseif ($total_avg >= 50) {
                            $grade = "C+";
                        } elseif ($total_avg >= 45) {
                            $grade = "C";
                        } elseif ($total_avg >= 40) {
                            $grade = "C-";
                        } else {
                            $grade = "F";
                        }

                        ?>
                        <!-- Content Wrapper. Contains page content -->
                        <div class="content-wrapper"><br>
                            <!-- Main content -->
                            <section class="content">
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="card-body">
                                                <div class="tab-content">
                                                    <div class="active tab-pane">
                                                        <div class="" id="Print">
                                                            <div class="col-md-12">
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <!-- Course Details -->
                                                                        <div class="card card-warning card-outline">
                                                                            <div class="card-body text-center box-profile">
                                                                                <div class="col-sm-12">
                                                                                    <address>
                                                                                        <h1>
                                                                                            <img src="../public/sys_data/logo/<?php echo $sys->sys_logo; ?>" height="100" width="100" alt="">
                                                                                        </h1>
                                                                                        <h1><?php echo $sys->sys_name; ?></h1>
                                                                                        <h4>PARTIAL TRANSCRIPT</h4>
                                                                                        <strong>Name : <?php echo $mark->s_name; ?></strong><br>
                                                                                        Reg No : <?php echo $mark->s_regno; ?><br>
                                                                                        Enrolled Course : <?php echo $course->cc_name; ?><br>
                                                                                        Enrolled Unit : <?php echo $unit->c_name; ?><br>
                                                                                    </address>
                                                                                    <br>
                                                                                </div>
                                                                                <table class="table">

                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th>CA 1 Marks(Out Of 30)</th>
                                                                                            <th>CA 2 Marks(Out Of 30)</th>
                                                                                            <th>Final Exam(Out Of 100)</th>
                                                                                            <th>Average Marks</th>
                                                                                            <th>Grade</th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                        <tr>
                                                                                            <td><?php echo $mark->c_cat1_marks; ?></td>
                                                                                            <td><?php echo $mark->c_cat2_marks; ?></td>
                                                                                            <td><?php echo $mark->c_eos_marks; ?></td>
                                                                                            <td><?php echo $total_avg; ?></td>
                                                                                            <td><?php echo $grade; ?></td>
                                                                                        </tr>
                                                                                    </tbody>
                                                                                </table>
                                                                                <br><br><br>
                                                                                </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        <button id="print" onclick="printContent('Print');" type="button" class="btn btn-outline-warning">
                                                            <i class="fas fa-print"></i>
                                                            Print Transcription
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                        <!-- Main Footer -->
            <?php require_once('../partials/footer.php');
                    }
                }
            } ?>
        </div>
        <!-- ./wrapper -->
        <!-- Scripts -->
        <?php require_once('../partials/scripts.php'); ?>

    </body>

    </html>
<?php
} ?>