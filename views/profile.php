<?php
    include('../config/config.php');
    include('../config/checklogin.php');
    userRoles(['admin', 'instructor', 'student']);
    $role = $_SESSION['role'];
    $uploadDir = "../public/sys_data/uploads/users/";
    if (isset($_SESSION['active_tab']))
    {
        $active_tab = $_SESSION['active_tab'];
        unset($_SESSION['active_tab']);
    }
    else { $active_tab = "edit-profile"; }
    function uploadProfileImage(string $fieldName, string $prefix, string $currentImage, string $uploadDir)
    {
        if (!isset($_FILES[$fieldName]) || empty($_FILES[$fieldName]['name'])) { return $currentImage; }
        $tmpName = $_FILES[$fieldName]['tmp_name'];
        $fileName = $_FILES[$fieldName]['name'];
        $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($fileExt, ['jpg', 'jpeg', 'png', 'webp'])) { return $currentImage; }

        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
        $newImageName = $prefix . '.' . $fileExt;
        $newImagePath  = $uploadDir . $newImageName;
        if (move_uploaded_file($tmpName, $newImagePath))
        {
            if (!empty($currentImage) && $currentImage !== $newImageName)
            {
                $oldPath = $uploadDir . basename($currentImage);
                if (file_exists($oldPath)) { unlink($oldPath); }
            }
            return $newImageName;
        }
        return $currentImage;
    }
    if (isset($_POST['update_profile']))
    {
        $user_code = $_POST['user_id'];
        $user_id = (int) $_SESSION['user_id'];
        $name  = trim($_POST['name'] );
        $email = trim($_POST['email'] );
        $phone = trim($_POST['phone'] );
        $currentImage = trim($_POST['current_dpic'] );
        if ($name === '') { $_SESSION['flash_error'] = "Name cannot be empty"; }
        elseif ($email === '') {  $_SESSION['flash_error'] = "Email cannot be empty"; }
        else
        { 
            $dpic = uploadProfileImage('dpic', $user_code, $currentImage, $uploadDir);
            $query = "UPDATE users SET name = ?, email = ?, phone = ?, dpic = ? WHERE user_id = ?";
            $stmt = $mysqli->prepare($query);   
            if (!$stmt) { $_SESSION['flash_error'] = "Prepare failed: " . $mysqli->error; }
            else
            {
                $stmt->bind_param('ssssi', $name, $email, $phone, $dpic, $user_id);
                if ($stmt->execute())
                {
                    $_SESSION['flash_success'] = "Profile Updated Successfully";
                    $_SESSION['active_tab'] = "edit-profile";
                    header("Location: profile.php");
                    exit;
                }
                else
                {
                    $_SESSION['flash_error'] = "Failed to Update Profile: " . $stmt->error;
                    $_SESSION['active_tab'] = "edit-profile";
                    header("Location: profile.php");
                    exit;
                }
            }
        }
    }
    if (isset($_POST['update_password']))
    {
        $new_password = trim($_POST['new_password'] );
        $confirm_password = trim($_POST['confirm_password'] );
        $stmt = $mysqli->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($new_password === '' || $confirm_password === '') { $_SESSION['flash_error'] = "All Fields are Required"; }
        elseif ($new_password !== $confirm_password) { $_SESSION['flash_error'] = "Passwords Do not Match. Please try again."; }
        else
        {
            $hashed_new = sha1(md5($new_password));
            $user_id = (int) $_SESSION['user_id'];
            $stmt = $mysqli->prepare("SELECT password FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc())
            {
                if ($row['password'] === $hashed_new) { $_SESSION['flash_error'] = "New password and the old password cannot be the same"; }
                else {
                    $stmt = $mysqli->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                    $stmt->bind_param("si", $hashed_new, $user_id);
                    $stmt->execute();

                    if ($stmt->execute()) {
                        $_SESSION['flash_success'] = "Password Changed Successfully";
                        $_SESSION['active_tab'] = "change-password";
                        header("Location: profile.php");
                        exit;
                    } else {
                        $_SESSION['flash_error'] = "Failed to Change Password";
                        $_SESSION['active_tab'] = "change-password";
                        header("Location: profile.php");
                        exit;
                    }
                }
            }
        }
    }
    $user_id = (int) $_SESSION['user_id'];
    $stmt = $mysqli->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $loggedIn = $stmt->get_result()->fetch_object();
    if ($loggedIn)
    {
        $displayName = $loggedIn->name;
        $displayCode = $loggedIn->user_code;
        $displayEmail = $loggedIn->email;
        $displayPhone = $loggedIn->phone;
        $displayImage = $loggedIn->dpic;
    }
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
                <?php
                    require_once('../partials/navbar.php');
                    require_once('../partials/sidebar.php');    
                ?>
                <div class="content-wrapper"><br>
                    <section class="content">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="card card-warning card-outline">
                                        <div class="card-body box-profile text-center">
                                            <img class="img-fluid img-rectangle" src="../public/sys_data/uploads/users/<?php echo ($displayImage); ?>" alt="Profile">
                                            <h3 class="profile-username text-center mt-3"> <?php echo htmlspecialchars($displayCode); ?> </h3>
                                            <table class="table mb-3">
                                                <tbody>
                                                    <tr>
                                                        <th style="width: 25% !important; padding-left: 0.25rem !important;">Name:</th>
                                                        <td style=" word-break: break-word !important; overflow-wrap: anywhere !important; padding-right: 0.25rem !important;"><?php echo htmlspecialchars($displayName); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th style="width: 25% !important; padding-left: 0.25rem !important;">Contact:</th>
                                                        <td style=" word-break: break-word !important; overflow-wrap: anywhere !important; padding-right: 0.25rem !important;"><?php echo htmlspecialchars($displayPhone); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th style="width: 25% !important; padding-left: 0.25rem !important;">Email:</th>
                                                        <td style=" word-break: break-word !important; overflow-wrap: anywhere !important; padding-right: 0.25rem !important;"><?php echo htmlspecialchars($displayEmail); ?></td>
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
                                                <li class="nav-item"><a class="nav-link active" href="#edit-profile" data-toggle="tab">Update Profile</a></li>
                                                <li class="nav-item"><a class="nav-link" href="#change-password" data-toggle="tab">Change Password</a></li>
                                            </ul>
                                        </div>
                                        <div class="card-body">
                                            <div class="tab-content">
                                                <div class="active tab-pane" id="edit-profile">
                                                    <form method="post" enctype="multipart/form-data">
                                                        <div class="row">
                                                            <div class="form-group col-md-6">
                                                                <label for="user_id">User ID</label>
                                                                <input type="text" id="user_id" name="user_id" value="<?php echo htmlspecialchars($loggedIn->user_code ); ?>" class="form-control" readonly>
                                                            </div>
                                                            <div class="form-group col-md-6">
                                                                <label for="name">Full Name</label>
                                                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($loggedIn->name ); ?>" class="form-control" required autocomplete="off">
                                                            </div>
                                                            <div class="form-group col-md-6">
                                                                <label for="email">Email Address</label>
                                                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($loggedIn->email ); ?>" class="form-control" required autocomplete="username">
                                                            </div>
                                                            <div class="form-group col-md-6">
                                                                <label for="phone">Contact Number</label>
                                                                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($loggedIn->phone ); ?>" class="form-control" autocomplete="tel">
                                                            </div>
                                                            <img id="imagePreview" style="display:none; max-width:500px; margin-top:10px; border-radius:10px;" alt="Preview">
                                                            <div class="form-group col-md-12">
                                                                <label for="dpic">Profile Photo</label><br>
                                                                <input type="file" name="dpic" id="dpic" accept=".png,.jpg,.jpeg,.webp">
                                                                <input type="hidden" name="current_dpic" value="<?php echo htmlspecialchars($loggedIn->dpic ?? ''); ?>">
                                                            </div>
                                                        </div>
                                                        <div class="text-right"><button type="submit" name="update_profile" class="btn btn-outline-warning">Update Profile</button></div>
                                                    </form>
                                                </div>
                                                <div class="tab-pane" id="change-password">
                                                    <form method="post">
                                                        <div class="row">
                                                            <div class="form-group col-md-12">
                                                                <label for="new_password">New Password</label>
                                                                <input type="password" name="new_password" id="new_password" class="form-control" required autocomplete="new-password">
                                                            </div>
                                                            <div class="form-group col-md-12">
                                                                <label for="confirm_password">Confirm New Password</label>
                                                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required autocomplete="new-password">
                                                            </div>
                                                        </div>
                                                        <div class="text-right"><button type="submit" name="update_password" class="btn btn-outline-warning">Update Password</button></div>
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
            </div>
            <?php  require_once('../partials/scripts.php'); ?>
            <script>
                document.getElementById('dpic').addEventListener('change', function (event) {
                    const file = event.target.files[0];
                    const preview = document.getElementById('imagePreview');
                    if (!file)
                    {
                        preview.style.display = 'none';
                        preview.src = '';
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = function (e)
                    {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                });
            </script>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <?php if (!empty($_SESSION['success'])): ?>
            <script>
                Swal.fire
                ({
                    icon: 'success',
                    title: 'Success',
                    text: <?php echo json_encode($_SESSION['success']); ?>
                });
            </script>
            <?php unset($_SESSION['success']); endif; ?>
            <?php if (!empty($_SESSION['error'])): ?>
            <script>
                Swal.fire
                ({
                    icon: 'error',
                    title: 'Error',
                    text: <?php echo json_encode($_SESSION['error']); ?>
                });
            </script>
            <?php unset($_SESSION['error']); endif; ?>
            <?php $active_tab = $active_tab ?? 'edit-profile'; ?>
            <script>
                document.addEventListener('DOMContentLoaded', function ()
                {
                    const activeTab = <?php echo json_encode($active_tab); ?>;
                    const tabLink = document.querySelector('a[href="#' + activeTab + '"]');
                    if (tabLink) { $(tabLink).tab('show'); }
                });
            </script>
        </body>
    </html>
<?php
    }
?>