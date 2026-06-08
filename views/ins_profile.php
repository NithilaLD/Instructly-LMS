<?php
    session_start();
    include('../config/config.php');
    include('../config/checklogin.php');
    instructor();

    /* Update Instructor */
    if (isset($_POST['update_ins'])) {
        //Error Handling and prevention of posting double entries
        $error = 0;
        $i_id = '';
        $i_number = '';

        if (isset($_SESSION['i_id']) && !empty($_SESSION['i_id'])) {
            $i_id = mysqli_real_escape_string($mysqli, trim($_SESSION['i_id']));
        } else {
            $error = 1;
            $err = "Instructor ID Cannot Be Empty";
        }

        if (isset($_POST['i_number']) && !empty($_POST['i_number'])) {
            $i_number = mysqli_real_escape_string($mysqli, trim($_POST['i_number']));
        } else {
            $error = 1;
            $err = "Number Cannot Be Empty";
        }

        if (isset($_POST['i_name']) && !empty($_POST['i_name'])) {
            $i_name = mysqli_real_escape_string($mysqli, trim($_POST['i_name']));
        } else {
            $error = 1;
            $err = "Name Cannot Be Empty";
        }

        if (isset($_POST['i_email']) && !empty($_POST['i_email'])) {
            $i_email = mysqli_real_escape_string($mysqli, trim($_POST['i_email']));
        } else {
            $error = 1;
            $err = "Email Cannot Be Empty";
        }

        if (isset($_POST['i_phone']) && !empty($_POST['i_phone'])) {
            $i_phone = mysqli_real_escape_string($mysqli, trim($_POST['i_phone']));
        } else {
            $error = 1;
            $err = "Phone Cannot Be Empty";
        }

        $currentImage = '';
            if (isset($_POST['current_i_dpic']) && !empty($_POST['current_i_dpic'])) {
            $currentImage = mysqli_real_escape_string($mysqli, trim($_POST['current_i_dpic']));
        }

        $uploadedFile = isset($_FILES["i_dpic"]) ? $_FILES["i_dpic"]["tmp_name"] : '';
        $fileName = isset($_FILES["i_dpic"]["name"]) ? $_FILES["i_dpic"]["name"] : '';
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        
        if (!$error) {
            $i_dpic = $currentImage;
            if (!empty($fileName)) {
                $i_dpic = $i_number . '.' . $fileExtension;
                move_uploaded_file($uploadedFile, "../public/sys_data/uploads/users/" . $i_dpic);
            
                if (!empty($currentImage) && $currentImage !== $i_dpic) {
                    $oldImagePath = "../public/sys_data/uploads/users/" . basename($currentImage);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
            }

            $query = "UPDATE lms_instructor SET i_number =?, i_name =?, i_phone =?, i_email =?, i_dpic =? WHERE i_id = ?";
            $stmt = $mysqli->prepare($query);
            $rc = $stmt->bind_param('ssssss', $i_number, $i_name, $i_phone, $i_email, $i_dpic, $i_id);
            $stmt->execute();
            if ($stmt) {
                $success = "Updated" && header("refresh:1; url=ins_profile.php");
            } else {
                $info = "Please Try Again Or Try Later";
            }
        }
    }
    // Initialize variables to avoid undefined variable warnings
    $old_password = $new_password = $confirm_password = '';
    $error = 0;

    /* Change Password */
    if (isset($_POST['update_password'])) {
        //Change Password
        $error = 0;
        if (isset($_SESSION['i_id']) && !empty($_SESSION['i_id'])) {
            $i_id = mysqli_real_escape_string($mysqli, trim((($_SESSION['i_id']))));
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
            $stmt = $mysqli->prepare("SELECT * FROM lms_instructor WHERE i_email = ?");
            $stmt->bind_param("s", $i_email);
            $stmt->execute();
            $res = $stmt->get_result();
            if (mysqli_num_rows($res) > 0) {
                $row = mysqli_fetch_assoc($res);
                if ($old_password != $row['i_pwd']) {
                    $err =  "Please Enter Correct Old Password";
                } elseif ($new_password != $confirm_password) {
                    $err = "Confirmation Password Does Not Match";
                } else {
                    $query = "UPDATE lms_instructor SET i_pwd =? WHERE i_id ='$i_id' ";
                    $stmt = $mysqli->prepare($query);
                    $rc = $stmt->bind_param('s', $new_password);
                    $stmt->execute();
                    if ($stmt) {
                        $success = "Password Changes" && header("refresh:1; url=ins_profile.php");
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
                <?php require_once('../partials/ins_navbar.php'); ?>
                <!-- /.navbar -->

                <!-- Main Sidebar Container -->
                <?php
                require_once('../partials/ins_sidebar.php');
                $i_id = $_SESSION['i_id'];
                $ret = "SELECT  * FROM  lms_instructor  WHERE i_id= '$i_id'";
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
                                                    if ($loggedIn->i_dpic == '') {
                                                        $dpic = 'Default_user.webp';
                                                    } else {
                                                        $dpic = $loggedIn->i_dpic;
                                                    } ?>
                                                    <img class="img-fluid img-rectangle" src="../public/sys_data/uploads/users/<?php echo $dpic; ?>" alt="Passport">
                                                </div>
                                                <h3 class="profile-username text-center"><?php echo $loggedIn->i_number; ?></h3>
                                                <ul class="list-group list-group-unbordered mb-3">
                                                    <li class="list-group-item">
                                                        <b>Name: </b> <a class="float-right"><?php echo $loggedIn->i_name; ?></a>
                                                    </li>
                                                    <li class="list-group-item">
                                                        <b>Email: </b> <a class="float-right"><?php echo $loggedIn->i_email; ?></a>
                                                    </li>
                                                    <li class="list-group-item">
                                                        <b>Contact Number: </b> <a class="float-right"><?php echo $loggedIn->i_phone; ?></a>
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
                                                                    <label for="exampleInputEmail1">Instructor Number</label>
                                                                    <input type="text" name="i_number" value="<?php echo $loggedIn->i_number ?>" required class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" autocomplete="on">
                                                                    <input type="hidden" name="i_id" value="<?php echo $loggedIn->i_id ?>" required class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">

                                                                </div>
                                                                <div class="form-group col-md-6">
                                                                    <label for="exampleInputEmail1">Instructor Full Name</label>
                                                                    <input type="text" name="i_name" value="<?php echo $loggedIn->i_name; ?>" required class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" autocomplete="name">
                                                                </div>

                                                                <div class="form-group col-md-6">
                                                                    <label for="exampleInputEmail1">Email Address</label>
                                                                    <input type="email" name="i_email" value="<?php echo $loggedIn->i_email; ?>" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" autocomplete="email">
                                                                </div>

                                                                <div class="form-group col-md-6">
                                                                    <label for="exampleInputEmail1">Phone Number</label>
                                                                    <input type="text" name="i_phone" value="<?php echo $loggedIn->i_phone; ?>" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" autocomplete="tel">
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="form-group col-md-12">
                                                                    <label for="exampleInputFile">Instructor Passport</label>
                                                                    <div class="input-group">
                                                                        <div class="custom-file">
                                                                            <input required name="i_dpic" accept=".png, .jpg" type="file" class="custom-file-input" id="exampleInputFile">
                                                                            <label class="custom-file-label" for="current_i_dpic">Choose file</label>
                                                                        </div>
                                                                    </div>
                                                                    <input type="hidden" name="current_i_dpic" value="<?php echo $loggedIn->i_dpic; ?>" id="current_i_dpic">
                                                                </div>
                                                            </div>
                                                            <hr>
                                                            <div class="text-right">
                                                                <button type="submit" name="update_ins" class="btn btn-outline-warning">Update Profile</button>
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
                                                                    <input type="password" name="old_password" required class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" autocomplete="old-password">
                                                                </div>
                                                                <div class="form-group col-md-12">
                                                                    <label for="exampleInputEmail1">New Password</label>
                                                                    <input type="password" name="new_password" required class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" autocomplete="new-password">
                                                                </div>
                                                                <div class="form-group col-md-12">
                                                                    <label for="exampleInputEmail1">Confirm New Password</label>
                                                                    <input type="password" name="confirm_password" required class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" autocomplete="confirm-password">
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