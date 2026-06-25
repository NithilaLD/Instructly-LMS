<?php
    include('../config/config.php');
    include('../config/checklogin.php');
    userRoles(['admin', 'instructor']);
    require_once('../partials/analytics.php');

    /* Persist System Settings  */
    $ret = "SELECT * FROM system ";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute(); //ok
    $res = $stmt->get_result();
    while ($sys = $res->fetch_object()) {
        require_once('../partials/head.php');
    ?>

        <body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
            <div class="wrapper">
                <!-- Navbar -->
                <?php require_once('../partials/navbar.php'); ?>
                <!-- /.navbar -->

                <!-- Main Sidebar Container -->
                <?php
                require_once('../partials/sidebar.php');
                $view = isset($_GET['view']) ? (int)$_GET['view'] : 0;
                $ret = "SELECT * FROM courses where c_id = ?";
                $stmt = $mysqli->prepare($ret);
                $stmt->bind_param("i", $view);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($course = $res->fetch_object()) {
                    if($course->i_id != null)
                    {
                        $inc= "SELECT * FROM users WHERE user_id = '$course->i_id'";
                        $ins_stmt = $mysqli->prepare($inc);
                        $ins_stmt->execute();
                        $ins_res = $ins_stmt->get_result();
                        while ($instructor = $ins_res->fetch_object()) {
                            $course->instructor_name = $instructor->name;
                        }
                    }
                    else {
                        $course->instructor_name = "N/A";
                    }
                ?>
                    <!-- Content Wrapper. Contains page content -->
                    <div class="content-wrapper"><br>
                        <!-- Main content -->
                        <section class="content">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-md-3">

                                        <!-- Course Details -->
                                        <div class="card card-warning card-outline">
                                            <div class="card-body box-profile">
                                                <div class="text-center">
                                                    <?php
                                                    if ($course->c_dpic == '') {
                                                        $logo = $course->c_code.".png";
                                                    } else {
                                                        $logo = $course->c_dpic;
                                                    } ?>
                                                    <img class="img-fluid img-rectangle" src="../public/sys_data/uploads/courses/<?php echo $logo; ?>" alt="Course Logo">

                                                </div>

                                                <h3 class="profile-username text-center"><?php echo $course->c_code; ?></h3>

                                                <p class="text-muted text-center"><?php echo $course->c_name; ?></p>

                                                <ul class="list-group list-group-unbordered mb-3">
                                                    <li class="list-group-item">
                                                        <b>Instructor</b> <a class="float-right"><?php echo $course->instructor_name; ?></a>
                                                    </li>
                                                    <li class="list-group-item">
                                                        <b>Details</b><a class="float-right"><?php echo $course->c_desc; ?></a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="card card-warning card-outline">
                                            <div class="card-header p-2">
                                                <ul class="nav nav-pills">
                                                    <li class="nav-item"><a class="nav-link active" href="#units" data-toggle="tab">Units Available</a></li>
                                                    <li class="nav-item"><a class="nav-link" href="#enrolled_students" data-toggle="tab">Enrolled Students</a></li>
                                                </ul>
                                            </div>
                                            <div class="card-body">
                                                <div class="tab-content">
                                                    <div class="active tab-pane" id="units">
                                                        <table id="dash-1" class="table table-striped table-bordered display " style="width:100%">
                                                            <thead>
                                                                <tr>
                                                                    <th>Unit Code</th>
                                                                    <th>Unit Name</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                $stmt = $mysqli->prepare("SELECT * FROM units WHERE c_id = ?");
                                                                $stmt->bind_param("i", $view);
                                                                $stmt->execute();
                                                                $res = $stmt->get_result();
                                                                while ($units = $res->fetch_object()) {
                                                                ?>
                                                                    <tr>
                                                                        <td><?php echo $units->u_code; ?></td>
                                                                        <td><?php echo $units->u_name; ?></td>
                                                                    </tr>
                                                                <?php
                                                                } ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="tab-pane fade" id="enrolled_students">
                                                        <table id="dash-2" class="table table-striped table-bordered display" style="width:100%">
                                                            <thead>
                                                                <tr>
                                                                    <th>Student Name</th>
                                                                    <th>Enroll date</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                    $enrollstmt = $mysqli->prepare("
                                                                        SELECT DISTINCT
                                                                            ins.name AS i_name,
                                                                            stu.name AS s_name,
                                                                            e.en_date
                                                                        FROM enrollments e
                                                                        INNER JOIN courses c ON c.c_id = e.c_id
                                                                        INNER JOIN users ins ON ins.user_id = c.i_id
                                                                        INNER JOIN users stu ON stu.user_id = e.s_id
                                                                        WHERE e.c_id = ?
                                                                    ");
                                                                    $enrollstmt->bind_param("i", $view);
                                                                    $enrollstmt->execute();
                                                                    $enRes = $enrollstmt->get_result();

                                                                    while ($enrollments = $enRes->fetch_object()) {
                                                                ?>
                                                                        <tr>
                                                                            <td><?php echo $enrollments->s_name; ?></td>
                                                                            <td><?php echo date("d M Y", strtotime($enrollments->en_date)); ?></td>
                                                                        </tr>
                                                                    <?php } ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                <?php } ?>
            </div>
            <!-- ./wrapper -->
            <!-- Scripts -->
            <?php require_once('../partials/scripts.php'); ?>
        </body>
        </html>
    <?php
        } 
?>