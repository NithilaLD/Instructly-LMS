<?php
    include('../config/config.php');
    include('../config/checklogin.php');
    userRole('admin');

    require_once('../partials/analytics.php');

    /* Persist System Settings  */
    $ret = "SELECT * FROM system";
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
                $stmt = $mysqli->prepare("SELECT  * FROM  users WHERE user_id = ?");
                $stmt->bind_param("i", $view);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($ins = $res->fetch_object()) {
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
                                                    if ($ins->dpic == '') {
                                                        $dpic = $ins->user_code.'.png';
                                                    } else {
                                                        $dpic = $ins->dpic;
                                                    }
                                                    ?>
                                                    <img class="img-fluid img-rectangle" src="../public/sys_data/uploads/users/<?php echo $dpic; ?>" alt="Image">

                                                </div>

                                                <h3 class="profile-username text-center"><?php echo $ins->user_code; ?></h3>

                                                <p class="text-muted text-center"><?php echo $ins->name; ?></p>

                                                <table class="table mb-3">
                                                    <tbody>
                                                        <tr>
                                                            <th style="width: 25%;padding-left: 0.25rem;">Contact:</th>
                                                            <td style=" word-break: break-word;overflow-wrap: anywhere; padding-right: 0.25rem;"><?php echo htmlspecialchars($ins->phone); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th style="width: 25%;padding-left: 0.25rem;">Email:</th>
                                                            <td style=" word-break: break-word;overflow-wrap: anywhere; padding-right: 0.25rem;"><?php echo htmlspecialchars($ins->email); ?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="card card-warning card-outline">
                                            <div class="card-header p-2">
                                                <ul class="nav nav-pills">
                                                    <li class="nav-item"><a class="nav-link active" href="#units" data-toggle="tab">Allocated Courses</a></li>
                                                </ul>
                                            </div>
                                            <div class="card-body">
                                                <div class="tab-content">
                                                    <div class="active tab-pane" id="units">
                                                        <table id="dash-1" class="table table-striped table-bordered display " style="width:100%">
                                                            <thead>
                                                                <tr>
                                                                    <th>Course Code</th>
                                                                    <th>Course</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                $stmt = $mysqli->prepare("
                                                                    SELECT c_id, c_code, c_name
                                                                    FROM courses
                                                                    WHERE i_id = ?
                                                                ");
                                                                $stmt->bind_param("i", $view);
                                                                $stmt->execute();
                                                                $res = $stmt->get_result();

                                                                while ($allocated = $res->fetch_object()) {
                                                                ?>
                                                                    <tr>
                                                                        <td><?php echo $allocated->c_code; ?></td>
                                                                        <td><?php echo $allocated->c_name; ?></td>
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
                    <!-- Main Footer -->
                <?php 
                } ?>
            </div>
            <!-- ./wrapper -->
            <!-- Scripts -->
            <?php require_once('../partials/scripts.php'); ?>
        </body>
        </html>
    <?php
        }
?>