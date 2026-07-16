<?php
    include('../config/config.php');
    include('../config/checklogin.php');
    userRoles(['admin', 'instructor', 'student']);
    $role = $_SESSION['role'];
    $user_id = $_SESSION['user_id'];
    require_once('../partials/analytics.php');
    $ret = "SELECT * FROM system ";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute(); //ok
    $res = $stmt->get_result();
    while ($sys = $res->fetch_object())
    {
        require_once('../partials/head.php');
    ?>
        <body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
            <div class="wrapper">
                <?php require_once('../partials/navbar.php'); ?>
                <?php require_once('../partials/sidebar.php'); ?>
                <div class="content-wrapper pt-2" style="margin-bottom: 0 !important;">
                    <section class="content">
                        <div class="container-fluid">
                            <?php if ($role === 'admin'): ?>
                                <div class="row">
                                    <div class="col-12 col-sm-6 col-md-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-outline-warning elevation-1"><i class="fas fa-book-open"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Courses</span>
                                                <span class="info-box-number"><?php echo $courses ?? 0; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-outline-warning elevation-1"><i class="fas fa-book-reader"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Units</span>
                                                <span class="info-box-number"><?php echo $units ?? 0; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-outline-warning elevation-1"><i class="fas fa-user-tie"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Instructors</span>
                                                <span class="info-box-number"><?php echo $instructors ?? 0; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-outline-warning elevation-1"><i class="fas fa-user-graduate"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Students</span>
                                                <span class="info-box-number"><?php echo $std ?? 0; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card" style="margin-bottom: 8px !important;">
                                            <div class="card-header"> <h5 class="card-title">Allocation Overview</h5> </div>
                                            <div class="card-body">
                                                <table id="dash-2" class="table table-striped table-bordered display" style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th>Course Code</th>
                                                            <th>Course</th>
                                                            <th>Instructor Name</th>
                                                            <th>Allocation Date</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $ret = "SELECT * FROM courses WHERE status <> 'rejected'";
                                                        $stmt = $mysqli->prepare($ret);
                                                        $stmt->execute();
                                                        $res = $stmt->get_result();
                                                        if ($res->num_rows > 0)
                                                        {
                                                            while ($row = $res->fetch_object())
                                                            {
                                                                if($row->i_id != null)
                                                                {
                                                                    $inc= "SELECT * FROM users WHERE user_id = '$row->i_id'";
                                                                    $ins_stmt = $mysqli->prepare($inc);
                                                                    $ins_stmt->execute();
                                                                    $ins_res = $ins_stmt->get_result();
                                                                    while ($instructor = $ins_res->fetch_object()) {$row->i_name = $instructor->name;}
                                                        ?>
                                                                    <tr>
                                                                        <td><?php echo $row->c_code; ?></td>
                                                                        <td><?php echo $row->c_name; ?></td>
                                                                        <td><?php echo $row->i_name; ?></td>
                                                                        <td><?php if($row->i_id != null){echo date("d M Y", strtotime($row->al_date)); } else { echo "N/A"; } ?></td>
                                                                    </tr>
                                                        <?php
                                                                }
                                                            }
                                                        }
                                                        else
                                                        {
                                                        ?>
                                                            <tr><td colspan="4" class="text-center">No allocations found.</td></tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="card" style="margin-bottom: 8px !important;">
                                            <div class="card-header"> <h5 class="card-title">Enrollment Overview</h5> </div>
                                            <div class="card-body">
                                                <table id="dash-1" class="table table-striped table-bordered display" style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th>Course Code</th>
                                                            <th>Course</th>
                                                            <th>Student Name</th>
                                                            <th>Enrolled Date</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                            $ret = "
                                                                SELECT
                                                                    c.c_code,
                                                                    c.c_name,
                                                                    su.name AS s_name,
                                                                    e.en_date
                                                                FROM enrollments e
                                                                JOIN courses c ON e.c_id = c.c_id
                                                                LEFT JOIN users iu ON c.i_id = iu.user_id
                                                                LEFT JOIN users su ON e.s_id = su.user_id
                                                            ";
                                                            $stmt = $mysqli->prepare($ret);
                                                            $stmt->execute();
                                                            $res = $stmt->get_result();
                                                            if ($res->num_rows > 0)
                                                            {
                                                                while ($row = $res->fetch_object())
                                                                {
                                                        ?>
                                                                    <tr>
                                                                        <td><?php echo $row->c_code; ?></td>
                                                                        <td><?php echo $row->c_name; ?></td>
                                                                        <td><?php echo $row->s_name; ?></td>
                                                                        <td><?php echo date("d M Y", strtotime($row->en_date)); ?></td>
                                                                    </tr>
                                                        <?php
                                                                }
                                                            }
                                                            else
                                                            {
                                                        ?>
                                                                <tr><td colspan="4" class="text-center">No enrollments found.</td></tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php elseif ($role === 'instructor'): ?>
                                <div class="row">
                                    <div class="col-12 col-sm-6 col-md-4">
                                        <div class="info-box mb-3">
                                            <span class="info-box-icon bg-outline-warning elevation-1"><i class="fas fa-user-check"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Student Enrollments</span>
                                                <span class="info-box-number"><?php echo $students_enrollments ?? 0; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-4">
                                        <div class="info-box mb-3">
                                            <span class="info-box-icon bg-outline-warning elevation-1"><i class="fas fa-chalkboard-teacher"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">My Allocations</span>
                                                <span class="info-box-number"><?php echo $allocated_units ?? 0; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-4">
                                        <div class="info-box mb-3">
                                            <span class="info-box-icon bg-outline-warning elevation-1"><i class="fas fa-file-invoice-dollar"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Payments Received</span>
                                                <span class="info-box-number">Rs. <?php echo $paid_bills ?? 0; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card" style="margin-bottom: 8px !important;">
                                            <div class="card-header"> <h5 class="card-title">My Allocations</h5> </div>
                                            <div class="card-body">
                                                <table class="table table-striped table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Course Code</th>
                                                            <th>Course</th>
                                                            <th>Instructor Name</th>
                                                            <th>Allocation Date</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                            $ret = "
                                                                SELECT
                                                                    c.c_code,
                                                                    c.c_name,
                                                                    u.name AS i_name,
                                                                    c.al_date
                                                                FROM courses c
                                                                LEFT JOIN users u ON c.i_id = u.user_id
                                                                WHERE c.i_id = ? AND c.status <> 'rejected' AND u.status = 'active'
                                                                ORDER BY c.al_date DESC
                                                            ";
                                                            $stmt = $mysqli->prepare($ret);
                                                            $stmt->bind_param("i", $user_id);
                                                            $stmt->execute();
                                                            $res = $stmt->get_result();
                                                            if ($res->num_rows > 0)
                                                            {
                                                                while ($row = $res->fetch_object())
                                                                {
                                                        ?>
                                                                    <tr>
                                                                        <td><?php echo $row->c_code; ?></td>
                                                                        <td><?php echo $row->c_name; ?></td>
                                                                        <td><?php echo $row->i_name; ?></td>
                                                                        <td><?php echo $row->al_date; ?></td>
                                                                    </tr>
                                                        <?php
                                                                }
                                                            }
                                                            else
                                                            {
                                                        ?>
                                                            <tr><td colspan="4" class="text-center">No allocations found.</td></tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php elseif ($role === 'student'): ?>
                                <div class="row">
                                    <div class="col-24 col-sm-12 col-md-6">
                                        <div class="info-box mb-3">
                                            <span class="info-box-icon bg-outline-warning elevation-1"><i class="fas fa-user-check"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Courses</span>
                                                <span class="info-box-number"><?php echo $students_enrollments ?? 0; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-24 col-sm-12 col-md-6">
                                        <div class="info-box mb-3">
                                            <span class="info-box-icon bg-outline-warning elevation-1"><i class="fas fa-file-invoice-dollar"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Payments Did</span>
                                                <span class="info-box-number">Rs. <?php echo $paid_bills ?? 0; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card" style="margin-bottom: 8px !important;">
                                            <div class="card-header"> <h5 class="card-title">My Enrollment Overview</h5> </div>
                                            <div class="card-body">
                                                <table class="table table-striped table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Course Code</th>
                                                            <th>Course</th>
                                                            <th>Instructor</th>
                                                            <th>Enrolled Date</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                            $ret = "
                                                                SELECT 
                                                                    c.c_code,
                                                                    c.c_name,
                                                                    u.name AS i_name,
                                                                    e.en_date
                                                                FROM enrollments e
                                                                JOIN courses c ON e.c_id = c.c_id
                                                                LEFT JOIN users u ON c.i_id = u.user_id
                                                                WHERE e.s_id = ? AND e.status <> 'inactive' AND c.status <> 'rejected' AND u.status = 'active'
                                                            ";
                                                            $stmt = $mysqli->prepare($ret);
                                                            $stmt->bind_param("i", $user_id);
                                                            $stmt->execute();
                                                            $res = $stmt->get_result();
                                                            if ($res->num_rows > 0)
                                                            {
                                                                while ($row = $res->fetch_object())
                                                                {
                                                        ?>
                                                                    <tr>
                                                                        <td><?php echo $row->c_code; ?></td>
                                                                        <td><?php echo $row->c_name; ?></td>
                                                                        <td><?php echo $row->i_name; ?></td>
                                                                        <td><?php echo date("d M Y", strtotime($row->en_date)); ?></td>
                                                                    </tr>
                                                        <?php
                                                                }
                                                            }
                                                            else
                                                            {
                                                        ?>
                                                                <tr>
                                                                    <td colspan="4" class="text-center">No enrollments found.</td>
                                                                </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
            </div>
            <?php require_once('../partials/scripts.php'); ?>
        </body>
    </html>
<?php 
        }
?>