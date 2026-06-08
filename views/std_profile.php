<?php
    session_start();
    include('../config/config.php');
    include('../config/checklogin.php');
    student();

    /* Update Student */
    if (isset($_POST['update_student'])) {
        //Error Handling and prevention of posting double entries
        $error = 0;
        $s_id = '';
        $s_regno = '';

        if (isset($_SESSION['s_id']) && !empty($_SESSION['s_id'])) {
            $s_id = mysqli_real_escape_string($mysqli, trim($_SESSION['s_id']));
        } else {
            $error = 1;
            $err = "Student ID Cannot Be Empty";
        }

        if (isset($_POST['s_regno']) && !empty($_POST['s_regno'])) {
            $s_regno = mysqli_real_escape_string($mysqli, trim($_POST['s_regno']));
        } else {
            $error = 1;
            $err = "Admission Number Cannot Be Empty";
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
        
        $currentImage = '';
        if (isset($_POST['current_s_dpic']) && !empty($_POST['current_s_dpic'])) {
            $currentImage = mysqli_real_escape_string($mysqli, trim($_POST['current_s_dpic']));
        }

        $uploadedFile = isset($_FILES["s_dpic"]) ? $_FILES["s_dpic"]["tmp_name"] : '';
        $fileName = isset($_FILES["s_dpic"]["name"]) ? $_FILES["s_dpic"]["name"] : '';
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

        if (!$error) {
            $s_dpic = $currentImage;
            if (!empty($fileName)) {
                $s_dpic = $s_regno . '.' . $fileExtension;
                move_uploaded_file($uploadedFile, "../public/sys_data/uploads/users/" . $s_dpic);

                if (!empty($currentImage) && $currentImage !== $s_dpic) {
                    $oldImagePath = "../public/sys_data/uploads/users/" . basename($currentImage);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
            }
            
            $query = "UPDATE lms_student SET  s_name =?, s_email =?,  s_phoneno =?, s_dpic =? WHERE s_id = ?";
            $stmt = $mysqli->prepare($query);
            $rc = $stmt->bind_param('sssss',  $s_name, $s_email, $s_phoneno, $s_dpic, $s_id);
            $stmt->execute();
            if ($stmt) {
                $success = "Profile Updated" && header("refresh:1; url=std_profile.php");
            } else {
                $info = "Please Try Again Or Try Later";
            }
        }        
    }


    /* Change Password */
    if (isset($_POST['update_password'])) {
        //Change Password
        $error = 0;
        $old_password = '';
        $new_password = '';
        $confirm_password = '';
        if (isset($_SESSION['s_id']) && !empty($_SESSION['s_id'])) {
            $s_id = mysqli_real_escape_string($mysqli, trim((($_SESSION['s_id']))));
        } else {
            $error = 1;
            $err = "Session ID Cannot Be Empty";
        }
        if (isset($_POST['old_password']) && !empty($_POST['old_password'])) {
            $old_password = mysqli_real_escape_string($mysqli, trim(sha1(md5($_POST['old_password']))));
        } else {
            $error = 1;
            $err = "Old Password Cannot Be Empty";
        }
        if (isset($_POST['new_password']) && !empty($_POST['new_password'])) {
            $new_password = mysqli_real_escape_string($mysqli, trim(sha1(md5($_POST['new_password']))));
        } else {
            $error = 1;
            $err = "New Password Cannot Be Empty";
        }
        if (isset($_POST['confirm_password']) && !empty($_POST['confirm_password'])) {
            $confirm_password = mysqli_real_escape_string($mysqli, trim(sha1(md5($_POST['confirm_password']))));
        } else {
            $error = 1;
            $err = "Confirmation Password Cannot Be Empty";
        }

        if (!$error) {
            $sql = "SELECT * FROM  lms_student  WHERE s_id = '$s_id'";
            $res = mysqli_query($mysqli, $sql);
            if (mysqli_num_rows($res) > 0) {
                $row = mysqli_fetch_assoc($res);
                if ($old_password != $row['s_pwd']) {
                    $err =  "Please Enter Correct Old Password";
                } elseif ($new_password != $confirm_password) {
                    $err = "Confirmation Password Does Not Match";
                } else {
                    $query = "UPDATE lms_student SET s_pwd =? WHERE s_id ='$s_id' ";
                    $stmt = $mysqli->prepare($query);
                    $rc = $stmt->bind_param('s', $new_password);
                    $stmt->execute();
                    if ($stmt) {
                        $success = "Password Changes" && header("refresh:1; url=std_profile.php");
                    } else {
                        $err = "Please Try Again Or Try Later";
                    }
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
                <?php require_once('../partials/std_navbar.php'); ?>
                <!-- /.navbar -->

                <!-- Main Sidebar Container -->
                <?php
                require_once('../partials/std_sidebar.php');
                $id = $_SESSION['s_id'];
                $ret = "SELECT  * FROM  lms_student  WHERE s_id= '$id'";
                $stmt = $mysqli->prepare($ret);
                $stmt->execute(); //ok
                $res = $stmt->get_result();
                while ($loggedIn = $res->fetch_object()) {
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
                                                    if ($loggedIn->s_dpic == '') {
                                                        $dpic = 'Default_user.webp';
                                                    } else {
                                                        $dpic = $loggedIn->s_dpic;
                                                    } ?>
                                                    <img class="img-fluid img-rectangle" src="../public/sys_data/uploads/users/<?php echo $dpic; ?>" alt="Passport">
                                                </div>
                                                <h3 class="profile-username text-center"><?php echo $loggedIn->s_regno; ?></h3>
                                                <ul class="list-group list-group-unbordered mb-3">
                                                    <li class="list-group-item">
                                                        <b>Name: </b> <a class="float-right"><?php echo $loggedIn->s_name; ?></a>
                                                    </li>
                                                    <li class="list-group-item">
                                                        <b>Email: </b> <a class="float-right"><?php echo $loggedIn->s_email; ?></a>
                                                    </li>
                                                    <li class="list-group-item">
                                                        <b>Contact: </b> <a class="float-right"><?php echo $loggedIn->s_phoneno; ?></a>
                                                    </li>
                                                    
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="card card-warning card-outline">
                                            <div class="card-header p-2">
                                                <ul class="nav nav-pills">
                                                    <li class="nav-item"><a class="nav-link active" href="#edit-profile" data-toggle="tab">Update Profile </a></li>
                                                    <li class="nav-item"><a class="nav-link" href="#change-password" data-toggle="tab">Change Password</a></li>
                                                </ul>
                                            </div>
                                            <div class="card-body">
                                                <div class="tab-content">
                                                    <div class="active tab-pane" id="edit-profile">
                                                        <form method="post" enctype="multipart/form-data">
                                                            <div class="row">
                                                                <div class="form-group col-md-6">
                                                                    <label for="s_regno">Registration Number</label>
                                                                    <input type="text" name="s_regno" readonly value="<?php echo $loggedIn->s_regno; ?>" required class="form-control" id="s_regno" autocomplete="on">
                                                                    <input type="hidden" name="s_id" value="<?php echo $loggedIn->s_id; ?>" required class="form-control" id="s_id">

                                                                </div>
                                                                <div class="form-group col-md-6">
                                                                    <label for="s_name">Full Name</label>
                                                                    <input type="text" name="s_name" value="<?php echo $loggedIn->s_name; ?>" required class="form-control" id="s_name" autocomplete="name">
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="form-group col-md-6">
                                                                    <label for="s_phoneno">Phone Number</label>
                                                                    <input type="text" name="s_phoneno" value="<?php echo $loggedIn->s_phoneno; ?>" class="form-control" id="s_phoneno" autocomplete="tel">
                                                                </div>
                                                                <div class="form-group col-md-6">
                                                                    <label for="s_email">Email address</label>
                                                                    <input type="email" name="s_email" value="<?php echo $loggedIn->s_email; ?>" class="form-control" id="s_email" autocomplete="email">
                                                                </div>
                                                            </div>
                                                            <div class="row">

                                                                <div class="form-group col-md-12">
                                                                    <label for="exampleInputFile">Student Passport</label>
                                                                    <div class="input-group">
                                                                        <div class="custom-file">
                                                                            <input required name="s_dpic" type="file" class="custom-file-input" id="exampleInputFile">
                                                                            <label class="custom-file-label" for="current_s_dpic">Choose file</label>
                                                                        </div>
                                                                    </div>
                                                                    <input type="hidden" name="current_s_dpic" value="<?php echo $loggedIn->s_dpic; ?>" id="current_s_dpic">
                                                                </div>

                                                            </div>

                                                            <hr>
                                                            <div class="text-right">
                                                                <button type="submit" name="update_student" class="btn btn-outline-warning">Update Student</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <div class="tab-pane" id="change-password">
                                                        <div class="alert alert-primary" style="font-size: 16px !important; line-height: 1.5 !important; font-weight: 500 !important;">
                                                            <strong>If you don't remember your current password, please log out and use the "Forgot Password" option on the login page to reset it.</strong><br>
                                                            To change your password from this page, you must enter your current password for security verification.
                                                        </div>
                                                        <form method="post" enctype="multipart/form-data">
                                                            <!-- Hidden Username/Email Field -->
                                                                <input type="text"
                                                                    name="username"
                                                                    value="<?php echo $loggedIn->username; ?>"
                                                                    autocomplete="username"
                                                                    hidden>
                                                            <div class="row">
                                                                <div class="form-group col-md-12">
                                                                    <label for="exampleInputEmail1"> Old Password</label>
                                                                    <input type="password" name="old_password" required class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" autocomplete="current-password">
                                                                </div>
                                                                <div class="form-group col-md-12">
                                                                    <label for="exampleInputEmail1">New Password</label>
                                                                    <input type="password" name="new_password" required class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" autocomplete="new-password">
                                                                </div>
                                                                <div class="form-group col-md-12">
                                                                    <label for="exampleInputEmail1">Confirm New Password</label>
                                                                    <input type="password" name="confirm_password" required class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" autocomplete="new-password">
                                                                </div>
                                                            </div>
                                                            <hr>
                                                            <div class="text-right">
                                                                <button type="submit" name="update_password" class="btn btn-outline-warning">Update Password</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

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
